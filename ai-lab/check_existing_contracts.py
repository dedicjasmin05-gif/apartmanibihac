#!/usr/bin/env python3
"""Read-only source audit of existing WP SEO and booking contracts.

Only inspects named local PHP source files; never executes PHP or contacts a website.
"""
from __future__ import annotations
import argparse
from pathlib import Path
import json

SOURCES = (
    'seo.php', 'apartmani-upiti.php', 'sadrzaj-objekti.php', 'alati/seo-test.php',
)


def check(root: Path) -> dict:
    root = root.resolve()
    files = {}
    for rel in SOURCES:
        path = root / rel
        files[rel] = path.read_text(encoding='utf-8') if path.is_file() else None

    seo, main, seed, existing = [files[p] or '' for p in SOURCES]
    checks = {
        'source_seo_php_available': files['seo.php'] is not None,
        'source_booking_php_available': files['apartmani-upiti.php'] is not None,
        'seo_title_filter_exists': "add_filter('pre_get_document_title'" in seo,
        'seo_frontpage_title_exists': "if (is_front_page())" in seo,
        'seo_plugin_guard_skips_custom_title': 'if (abu_seo_plugin()) { return $t; }' in seo,
        'seo_included_in_booking_plugin': "require_once __DIR__ . '/seo.php';" in main,
        'calendar_shortcode_exists': "add_shortcode('abu_kalendar'" in main,
        'enquiry_shortcode_exists': "add_shortcode('abu_upit'" in main,
        'enquiry_form_has_explicit_post_target': "admin_url('admin-post.php')" in main,
        'enquiry_handler_exists': "admin_post_nopriv_abu_upit" in main,
        'enquiry_has_nonce': "wp_nonce_field('abu_upit_'" in main and 'wp_verify_nonce(' in main,
        'enquiry_has_spam_honeypot': 'name="web"' in main and "!empty($_POST['web'])" in main,
        'seed_content_refers_to_calendar': 'Odaberite datume u kalendaru' in seed,
        'seed_content_has_calendar_shortcode': '[abu_kalendar' in seed,
        'seed_content_has_enquiry_shortcode': '[abu_upit' in seed,
        'existing_seo_cli_test_available': files['alati/seo-test.php'] is not None,
        'wp_theme_source_present': (root / 'wp-content' / 'themes').is_dir(),
    }
    investigation = []
    if checks['seo_plugin_guard_skips_custom_title']:
        investigation.append('Determine whether Yoast/RankMath/AIOSEO is active; custom title filter intentionally yields to those plugins.')
    if not checks['wp_theme_source_present']:
        investigation.append('WP theme is not in this repository: investigate its document-title filter/template in a separate approved environment.')
    if checks['seed_content_refers_to_calendar'] and not checks['seed_content_has_calendar_shortcode']:
        investigation.append('Property seed text mentions calendar but does not embed shortcode: confirm theme inserts shortcode output; do not duplicate form.')
    if checks['calendar_shortcode_exists'] and checks['enquiry_shortcode_exists']:
        investigation.append('Booking building blocks exist already; test rendered form on staging instead of replacing booking plugin.')
    return {
        'tool': 'apartmanibihac-existing-contracts-v1',
        'mode': 'offline-read-only',
        'source_files_present': [p for p, value in files.items() if value is not None],
        'checks': checks,
        'investigation': investigation,
        'caveat': 'Static code presence does not prove runtime WordPress activation, form submission, effective meta tags or actual bookings.',
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--repo', type=Path, default=Path(__file__).resolve().parent.parent)
    args = parser.parse_args()
    print(json.dumps(check(args.repo), indent=2, ensure_ascii=False))


if __name__ == '__main__':
    main()
