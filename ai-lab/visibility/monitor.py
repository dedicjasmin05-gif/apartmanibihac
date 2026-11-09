#!/usr/bin/env python3
"""Local, manual AI visibility log. No browsing, scraping, API or production writes."""
from __future__ import annotations

import argparse
import csv
from datetime import datetime, timezone
import json
from pathlib import Path

HERE = Path(__file__).resolve().parent
PROMPTS = HERE / "prompts.json"
DEFAULT_LOG = HERE / "observations.local.csv"
COLUMNS = [
    "timestamp_utc", "platform", "query_id", "language", "market",
    "brand_mentioned", "site_cited", "competitor_domains", "evidence_note",
]
PLATFORMS = ("google-ai", "chatgpt", "bing-copilot", "perplexity", "claude")
ANSWER = ("yes", "no", "unclear")


def read_prompts(path=PROMPTS):
    data = json.loads(Path(path).read_text(encoding="utf-8"))
    prompts = data["prompts"]
    ids = [x["id"] for x in prompts]
    if len(ids) != len(set(ids)):
        raise ValueError("duplicate query IDs")
    if not prompts or any(not x.get("query") or not x.get("language") for x in prompts):
        raise ValueError("incomplete prompt collection")
    return {p["id"]: p for p in prompts}


def validate_observation(obs, prompt_map):
    if obs["query_id"] not in prompt_map:
        raise ValueError("Unknown query ID")
    if obs["platform"] not in PLATFORMS:
        raise ValueError("Unknown platform")
    if obs["brand_mentioned"] not in ANSWER or obs["site_cited"] not in ANSWER:
        raise ValueError("Expected yes/no/unclear")
    if not obs["evidence_note"].strip():
        raise ValueError("An evidence note is mandatory: screenshot or source, date, and visibility limits")
    if len(obs["evidence_note"]) > 300:
        raise ValueError("Evidence note too long; do not copy entire AI responses or personal details")
    if not obs["market"].strip():
        raise ValueError("Specify country/market of the test")
    if "\n" in obs["evidence_note"] or "\r" in obs["evidence_note"]:
        raise ValueError("Keep evidence notes on one line")
    obs["language"] = prompt_map[obs["query_id"]]["language"]
    return obs


def record(log_file, obs, prompt_map):
    obs = validate_observation(dict(obs), prompt_map)
    path = Path(log_file)
    path.parent.mkdir(parents=True, exist_ok=True)
    exists = path.exists() and path.stat().st_size > 0
    with path.open("a", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=COLUMNS)
        if not exists:
            writer.writeheader()
        writer.writerow({key: obs.get(key, "") for key in COLUMNS})


def read_log(log_file, prompt_map):
    path = Path(log_file)
    if not path.exists():
        return []
    with path.open(newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        if reader.fieldnames != COLUMNS:
            raise ValueError("Unknown or invalid observation CSV schema")
        entries = list(reader)
    return [validate_observation(row, prompt_map) for row in entries]


def summarize(rows):
    """Measured counts only, no inferred market share or rank."""
    buckets = {}
    for r in rows:
        key = (r["platform"], r["language"], r["market"])
        b = buckets.setdefault(key, {"n": 0, "mentioned": 0, "cited": 0,
                                     "unclear_mentions": 0, "unclear_citations": 0})
        b["n"] += 1
        b["mentioned"] += int(r["brand_mentioned"] == "yes")
        b["cited"] += int(r["site_cited"] == "yes")
        b["unclear_mentions"] += int(r["brand_mentioned"] == "unclear")
        b["unclear_citations"] += int(r["site_cited"] == "unclear")
    return buckets


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--prompts", type=Path, default=PROMPTS)
    parser.add_argument("--log", type=Path, default=DEFAULT_LOG)
    commands = parser.add_subparsers(dest="command", required=True)
    commands.add_parser("list", help="Print fixed query IDs and text")
    rec = commands.add_parser("record", help="Record one manual AI response observation")
    rec.add_argument("--platform", choices=PLATFORMS, required=True)
    rec.add_argument("--query-id", required=True)
    rec.add_argument("--market", required=True, help="Market, e.g. Croatia or Bosnia")
    rec.add_argument("--mentioned", choices=ANSWER, required=True)
    rec.add_argument("--cited", choices=ANSWER, required=True)
    rec.add_argument("--competitors", default="", help="Known cited domains, separated by semicolons")
    rec.add_argument("--evidence", required=True, help="Short evidence note or screenshot filename")
    commands.add_parser("report", help="Print observation counts, not ranking estimates")
    args = parser.parse_args(argv)
    prompt_map = read_prompts(args.prompts)
    if args.command == "list":
        for p in prompt_map.values():
            print(f"{p['id']:20} [{p['language']}] {p['query']}")
        return
    if args.command == "record":
        record(args.log, {
            "timestamp_utc": datetime.now(timezone.utc).isoformat(),
            "platform": args.platform, "query_id": args.query_id,
            "market": args.market, "brand_mentioned": args.mentioned,
            "site_cited": args.cited, "competitor_domains": args.competitors,
            "evidence_note": args.evidence,
        }, prompt_map)
        print("Observation saved locally (no websites contacted).")
        return
    rows = read_log(args.log, prompt_map)
    if not rows:
        print("No observations collected yet. Zero is not a visibility score.")
        return
    for (platform, language, market), b in sorted(summarize(rows).items()):
        print(f"{platform} | {language} | {market}: {b['n']} observations, "
              f"{b['mentioned']} explicit mentions, {b['cited']} site citations, "
              f"{b['unclear_mentions']} unclear mentions. "
              "This is NOT a representative AI search ranking.")


if __name__ == "__main__":
    main()
