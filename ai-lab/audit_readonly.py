#!/usr/bin/env python3
"""Read-only AI/SEO audit for apartmanibihac.com (Python standard library only)."""
from __future__ import annotations
import argparse
from datetime import datetime, timezone
from html.parser import HTMLParser
import json
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urljoin, urlsplit
from urllib.request import HTTPRedirectHandler, Request, build_opener
import xml.etree.ElementTree as ET

TARGET = "https://apartmanibihac.com"
HOSTS = frozenset(("apartmanibihac.com", "www.apartmanibihac.com"))
PATHS = ("/", "/smjestaj/", "/smjestaj/apartman-centar/",
         "/jeftin-smjestaj-bihac/", "/cesta-pitanja/", "/en/", "/de/")
REDIRECTS = {301, 302, 303, 307, 308}
USER_AGENT = "ApartmaniBihac-AI-Lab-ReadOnly/1.0 (GET-only; owner audit)"

class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None

def approved_url(url):
    try:
        p = urlsplit(url)
        return (p.scheme == "https" and p.hostname in HOSTS
                and p.port in (None, 443) and p.username is None
                and p.password is None and not p.fragment)
    except ValueError:
        return False

def fetch_public(url, max_bytes=1_500_000):
    """Only public GET; refuse redirects outside HTTPS allowlist."""
    if not approved_url(url):
        return {"url": url, "error": "outside-allowlist"}
    opener = build_opener(NoRedirect())
    current = url
    for _ in range(4):
        request = Request(current, headers={
            "User-Agent": USER_AGENT,
            "Accept": "text/html, application/xml, text/plain;q=0.8",
        }, method="GET")
        try:
            with opener.open(request, timeout=14) as response:
                raw = response.read(max_bytes + 1)
                if len(raw) > max_bytes:
                    return {"url": url, "error": "response-too-large"}
                charset = response.headers.get_content_charset() or "utf-8"
                return {"url": url, "final_url": current, "status": response.status,
                        "text": raw.decode(charset, "replace")}
        except HTTPError as exc:
            if exc.code not in REDIRECTS:
                return {"url": url, "status": exc.code, "error": "http-error"}
            next_url = urljoin(current, exc.headers.get("Location", ""))
            if not approved_url(next_url):
                return {"url": url, "error": "redirect-outside-allowlist"}
            current = next_url
        except (URLError, TimeoutError, OSError, ValueError) as exc:
            return {"url": url, "error": type(exc).__name__}
    return {"url": url, "error": "too-many-redirects"}

class PageParser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.title = self.description = self.og_title = self.og_description = ""
        self.canonical = self.lang = ""
        self.h1, self.hreflang, self.images, self.forms, self.jsonld = [], [], [], [], []
        self._capture, self._chunks, self._form = "", [], None

    def handle_starttag(self, tag, attrs):
        d = dict(attrs)
        if tag == "html":
            self.lang = d.get("lang", "")
        if tag in ("title", "h1") or (tag == "script" and d.get("type", "").lower() == "application/ld+json"):
            self._capture = "jsonld" if tag == "script" else tag
            self._chunks = []
        if tag == "meta":
            if d.get("name", "").lower() == "description":
                self.description = d.get("content", "")
            if d.get("property", "").lower() == "og:title":
                self.og_title = d.get("content", "")
            if d.get("property", "").lower() == "og:description":
                self.og_description = d.get("content", "")
        if tag == "link":
            rel = (d.get("rel") or "").lower().split()
            if "canonical" in rel:
                self.canonical = d.get("href", "")
            if "alternate" in rel and d.get("hreflang"):
                self.hreflang.append({"lang": d["hreflang"], "href": d.get("href", "")})
        if tag == "img":
            self.images.append({"alt": bool(d.get("alt", "").strip()),
                                "dimensions": bool(d.get("width") and d.get("height"))})
        if tag == "form":
            action = (d.get("action") or "").strip()
            action_parts = urlsplit(action) if action else None
            self._form = {
                "method": (d.get("method") or "GET").upper(),
                "classes": (d.get("class") or "").split(),
                "action_host": (action_parts.hostname or "") if action_parts else "",
                "action_path": action_parts.path if action_parts else "",
                "fields": [], "submit": False,
            }
        if tag in ("input", "select", "textarea") and self._form is not None:
            self._form["fields"].append(d.get("name", ""))
        if tag == "button" and self._form is not None and d.get("type", "submit") == "submit":
            self._form["submit"] = True
        if tag == "input" and self._form is not None and d.get("type") == "submit":
            self._form["submit"] = True

    def handle_data(self, data):
        if self._capture:
            self._chunks.append(data)

    def handle_endtag(self, tag):
        if tag == "form" and self._form is not None:
            self.forms.append(self._form)
            self._form = None
        if tag == self._capture or (tag == "script" and self._capture == "jsonld"):
            value = "".join(self._chunks).strip()
            if self._capture == "title":
                self.title = value
            elif self._capture == "h1":
                self.h1.append(value)
            elif self._capture == "jsonld":
                try:
                    self.jsonld.append(json.loads(value))
                except json.JSONDecodeError:
                    self.jsonld.append({"error": "invalid-jsonld"})
            self._capture, self._chunks = "", []

def jsonld_types(value):
    if isinstance(value, list):
        return [t for part in value for t in jsonld_types(part)]
    if isinstance(value, dict):
        typ = value.get("@type", [])
        types = typ if isinstance(typ, list) else ([typ] if typ else [])
        if "@graph" in value:
            types += jsonld_types(value["@graph"])
        return types
    return []

def summarize_html(html, path=""):
    p = PageParser()
    p.feed(html)
    flags = []
    if not p.title:
        flags.append("missing-title")
    if path == "/" and p.title.strip().casefold() in {"početna", "pocetna", "home", "startseite"}:
        flags.append("generic-homepage-title")
    if not p.description:
        flags.append("missing-meta-description")
    if not p.og_description:
        flags.append("empty-og-description")
    if not p.canonical:
        flags.append("missing-canonical")
    if path.startswith("/smjestaj/") and path.count("/") > 2 and not p.forms:
        flags.append("no-static-form-check-template-not-proof-of-failure")
    if path.startswith("/smjestaj/") and path.count("/") > 2:
        for form in p.forms:
            if "abu-form" not in form.get("classes", []):
                continue
            if not (form["method"] == "POST"
                    and form["action_path"] == "/wp-admin/admin-post.php"
                    and form["action_host"] in ("", "apartmanibihac.com", "www.apartmanibihac.com")):
                flags.append("booking-form-target-requires-review")
    return {"title": p.title, "description": p.description,
            "og_title": p.og_title, "og_description": p.og_description,
            "html_lang": p.lang, "canonical": p.canonical, "hreflang": p.hreflang,
            "h1": p.h1, "image_count": len(p.images),
            "images_without_alt": sum(not x["alt"] for x in p.images),
            "images_without_dimensions": sum(not x["dimensions"] for x in p.images),
            "forms": p.forms, "jsonld_types": list(dict.fromkeys(jsonld_types(p.jsonld))),
            "review_flags": flags}

def summarize_sitemap(xml):
    root = ET.fromstring(xml)
    locs = [el.text.strip() for el in root.iter()
            if el.tag.rsplit("}", 1)[-1] == "loc" and el.text]
    return {"entry_count": len(locs), "first_20": locs[:20]}

def audit():
    result = {"site": TARGET, "checked_at_utc": datetime.now(timezone.utc).isoformat(),
              "policy": "GET-only, public pages, no forms or credentials", "pages": {}}
    for path in PATHS:
        res = fetch_public(TARGET + path)
        item = {k: v for k, v in res.items() if k != "text"}
        if "text" in res:
            item["analysis"] = summarize_html(res["text"], path)
        result["pages"][path] = item
    for path, key in (("/robots.txt", "robots"), ("/wp-sitemap.xml", "sitemap")):
        res = fetch_public(TARGET + path, max_bytes=300_000)
        out = {k: v for k, v in res.items() if k != "text"}
        if "text" in res and key == "robots":
            out["ai_user_agents"] = [line.strip() for line in res["text"].splitlines()
                if line.lower().startswith("user-agent:") and any(
                    x in line.lower() for x in ("gptbot", "oai-searchbot", "claude", "perplexity"))]
        if "text" in res and key == "sitemap":
            try:
                out.update(summarize_sitemap(res["text"]))
            except ET.ParseError:
                out["error"] = "invalid-xml"
        result[key] = out
    return result

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", default="ai-lab-baseline.json")
    args = parser.parse_args()
    output = Path(args.output)
    output.write_text(json.dumps(audit(), ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print("Saved:", output, "| read-only GET audit finished")
