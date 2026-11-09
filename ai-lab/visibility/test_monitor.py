"""Offline tests for manually logged AI Search Lab observations."""
import csv
import json
from pathlib import Path
from tempfile import TemporaryDirectory
import unittest

from monitor import read_prompts, validate_observation, record, read_log, summarize


class MonitorTests(unittest.TestCase):
    def setUp(self):
        self.tmp = TemporaryDirectory()
        self.root = Path(self.tmp.name)
        self.prompts_path = self.root / "prompts.json"
        self.prompts_path.write_text(json.dumps({
            "prompts": [
                {"id": "general-bs", "query": "Smještaj u Bihaću", "language": "bs"},
                {"id": "river-en", "query": "Holiday house by the Una", "language": "en"},
            ]
        }), encoding="utf-8")
        self.prompts = read_prompts(self.prompts_path)
        self.log = self.root / "observations.csv"

    def tearDown(self):
        self.tmp.cleanup()

    def sample(self, **changes):
        row = {
            "timestamp_utc": "2026-11-09T05:20:00+00:00",
            "platform": "google-ai",
            "query_id": "general-bs",
            "language": "",
            "market": "Croatia",
            "brand_mentioned": "no",
            "site_cited": "no",
            "competitor_domains": "booking.com;agoda.com",
            "evidence_note": "Visible portion of user-provided screenshot 2026-11-09",
        }
        row.update(changes)
        return row

    def test_prompt_registry(self):
        self.assertEqual(len(self.prompts), 2)
        self.assertEqual(self.prompts["river-en"]["language"], "en")

    def test_record_and_read_roundtrip(self):
        record(self.log, self.sample(), self.prompts)
        rows = read_log(self.log, self.prompts)
        self.assertEqual(len(rows), 1)
        self.assertEqual(rows[0]["language"], "bs")
        self.assertEqual(rows[0]["competitor_domains"], "booking.com;agoda.com")
        self.assertEqual(rows[0]["evidence_note"], self.sample()["evidence_note"])

    def test_can_record_citation_without_explicit_brand_name(self):
        row = validate_observation(
            self.sample(site_cited="yes", brand_mentioned="no"), self.prompts
        )
        self.assertEqual(row["site_cited"], "yes")

    def test_reject_unknown_query_or_platform(self):
        with self.assertRaises(ValueError):
            validate_observation(self.sample(query_id="unknown"), self.prompts)
        with self.assertRaises(ValueError):
            validate_observation(self.sample(platform="fake-engine"), self.prompts)

    def test_evidence_mandatory(self):
        with self.assertRaises(ValueError):
            validate_observation(self.sample(evidence_note=""), self.prompts)

    def test_report_measured_counts_without_rank_fabrication(self):
        rows = [validate_observation(self.sample(), self.prompts),
                validate_observation(self.sample(
                    brand_mentioned="yes", site_cited="yes", query_id="river-en"
                ), self.prompts)]
        summary = summarize(rows)
        self.assertEqual(summary[("google-ai", "bs", "Croatia")]["n"], 1)
        self.assertEqual(summary[("google-ai", "en", "Croatia")]["cited"], 1)

    def test_invalid_csv_header_rejected(self):
        self.log.write_text("untrusted,column\nfoo,bar\n", encoding="utf-8")
        with self.assertRaises(ValueError):
            read_log(self.log, self.prompts)

    def test_missing_log_is_not_zero_search_visibility(self):
        self.assertEqual(read_log(self.log, self.prompts), [])


if __name__ == "__main__":
    unittest.main()
