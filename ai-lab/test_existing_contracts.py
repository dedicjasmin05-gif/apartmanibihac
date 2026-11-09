"""Offline checks for the analyzer; no network and no PHP execution."""
from pathlib import Path
from tempfile import TemporaryDirectory
import unittest
from check_existing_contracts import check

class ExistingContractsTests(unittest.TestCase):
    def setUp(self):
        self.temp = TemporaryDirectory()
        self.root = Path(self.temp.name)
        (self.root / 'alati').mkdir()
        (self.root / 'seo.php').write_text("add_filter('pre_get_document_title', function ($t) { if (abu_seo_plugin()) { return $t; } if (is_front_page()) { return 'Smještaj Bihać'; } });", encoding='utf-8')
        (self.root / 'apartmani-upiti.php').write_text('''add_shortcode('abu_kalendar', function () {});
add_shortcode('abu_upit', function () { return admin_url('admin-post.php') . wp_nonce_field('abu_upit_' . $id); });
add_action('admin_post_nopriv_abu_upit', 'abu_handle_upit');
wp_verify_nonce($nonce, 'test');
if (!empty($_POST['web'])) {}
require_once __DIR__ . '/seo.php';
''', encoding='utf-8')
        (self.root / 'sadrzaj-objekti.php').write_text('Odaberite datume u kalendaru', encoding='utf-8')
        (self.root / 'alati/seo-test.php').write_text('test', encoding='utf-8')

    def tearDown(self):
        self.temp.cleanup()

    def test_inspects_only_known_files_and_sources(self):
        report = check(self.root)
        self.assertEqual(len(report['source_files_present']), 4)
        self.assertTrue(report['checks']['seo_title_filter_exists'])
        self.assertTrue(report['checks']['seo_frontpage_title_exists'])
        self.assertTrue(report['checks']['enquiry_form_has_explicit_post_target'])

    def test_does_not_mistake_missing_seed_shortcode_for_missing_booking_feature(self):
        report = check(self.root)
        self.assertTrue(report['checks']['calendar_shortcode_exists'])
        self.assertFalse(report['checks']['seed_content_has_calendar_shortcode'])
        self.assertTrue(any('do not duplicate' in x for x in report['investigation']))

    def test_guard_reports_seo_plugin_possible_conflict(self):
        report = check(self.root)
        self.assertTrue(report['checks']['seo_plugin_guard_skips_custom_title'])
        self.assertTrue(any('Yoast' in x for x in report['investigation']))

    def test_missing_sources_not_silent_pass(self):
        (self.root / 'seo.php').unlink()
        report = check(self.root)
        self.assertFalse(report['checks']['source_seo_php_available'])
        self.assertFalse(report['checks']['seo_title_filter_exists'])

    def test_theme_not_assumed_present(self):
        report = check(self.root)
        self.assertFalse(report['checks']['wp_theme_source_present'])

if __name__ == '__main__':
    unittest.main()
