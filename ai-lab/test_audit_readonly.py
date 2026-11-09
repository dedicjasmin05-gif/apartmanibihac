"""Offline tests for read-only AI audit; no network requests."""
import unittest
from unittest.mock import patch, MagicMock
from urllib.error import HTTPError
from audit_readonly import approved_url, fetch_public, summarize_html, summarize_sitemap

class AuditTests(unittest.TestCase):
    def test_allowlist(self):
        self.assertTrue(approved_url("https://apartmanibihac.com/en/"))
        self.assertTrue(approved_url("https://www.apartmanibihac.com/"))
        for url in ("http://apartmanibihac.com/", "https://evil.example/",
                    "https://apartmanibihac.com.evil.example/",
                    "https://a:b@apartmanibihac.com/",
                    "https://apartmanibihac.com:8443/",
                    "https://apartmanibihac.com/#x"):
            self.assertFalse(approved_url(url), url)

    def test_homepage_title_and_og(self):
        html = """<html lang="bs"><head><title>Početna</title>
        <meta name="description" content="Smještaj u Bihaću">
        <meta property="og:description" content="">
        <link rel="canonical" href="https://apartmanibihac.com/">
        <script type="application/ld+json">
        {"@context":"https://schema.org","@graph":[{"@type":"WebSite"},{"@type":"Organization"}]}
        </script></head><body><h1>Smještaj Bihać</h1></body></html>"""
        found = summarize_html(html, "/")
        self.assertIn("generic-homepage-title", found["review_flags"])
        self.assertIn("empty-og-description", found["review_flags"])
        self.assertEqual(found["jsonld_types"], ["WebSite", "Organization"])
        self.assertEqual(found["html_lang"], "bs")

    def test_form_does_not_expose_hidden_values(self):
        html = """<html><title>Test</title><form method="post">
        <input name="nonce" value="SECRET"><input name="dolazak" required>
        <button type="submit">Send</button></form></html>"""
        found = summarize_html(html, "/smjestaj/apartman-centar/")
        self.assertEqual(found["forms"][0]["fields"], ["nonce", "dolazak"])
        self.assertTrue(found["forms"][0]["submit"])
        self.assertNotIn("SECRET", str(found))

    def test_missing_static_form_is_only_review_flag(self):
        found = summarize_html("<html><title>Apartman</title></html>", "/smjestaj/apartman-centar/")
        self.assertIn("no-static-form-check-template-not-proof-of-failure", found["review_flags"])

    def test_sitemap(self):
        text = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>https://apartmanibihac.com/a.xml</loc></sitemap></sitemapindex>'
        self.assertEqual(summarize_sitemap(text)["entry_count"], 1)

    def test_offsite_redirect_is_never_followed(self):
        url = "https://apartmanibihac.com/"
        redirect = HTTPError(url, 302, "Found", {"Location": "https://evil.example/private"}, None)
        opener = MagicMock()
        opener.open.side_effect = redirect
        with patch("audit_readonly.build_opener", return_value=opener):
            found = fetch_public(url)
        self.assertEqual(found["error"], "redirect-outside-allowlist")
        self.assertEqual(opener.open.call_count, 1)

if __name__ == "__main__":
    unittest.main()
