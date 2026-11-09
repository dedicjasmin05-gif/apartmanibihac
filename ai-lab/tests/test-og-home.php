<?php
/**
 * Offline regression tests for homepage Open Graph (no WordPress, network or DB).
 * Usage: php ai-lab/tests/test-og-home.php
 */
declare(strict_types=1);
define('ABSPATH', '/offline-test/');
$GLOBALS['head_actions'] = [];
$GLOBALS['front'] = true;
$GLOBALS['singular'] = true;
$GLOBALS['excerpt'] = '';
$GLOBALS['page_title'] = 'Početna';
$GLOBALS['document_title'] = 'Smještaj Bihać: apartmani i vikendice na Uni od 25 €';

function add_action($hook, $callback, $priority = 10) {
    $GLOBALS['head_actions'][$hook][$priority][] = $callback;
}
function add_filter($hook, $callback, $priority = 10) {}
function add_shortcode($code, $callback) {}
function is_front_page() { return $GLOBALS['front']; }
function is_singular($type = '') {
    return $GLOBALS['singular'] && (!$type || $type === ($GLOBALS['front'] ? 'page' : 'objekt'));
}
function is_page() { return true; }
function get_queried_object_id() { return $GLOBALS['front'] ? 30 : 4; }
function get_post_meta($id, $key, $single = true) { return ''; }
function get_the_title($id = null) { return $GLOBALS['page_title']; }
function wp_get_document_title() { return $GLOBALS['document_title']; }
function get_the_excerpt($id) { return $GLOBALS['excerpt']; }
function has_excerpt($id) { return $GLOBALS['excerpt'] !== ''; }
function get_post_field($field, $id) { return ''; }
function strip_shortcodes($str) { return $str; }
function wp_strip_all_tags($str) { return strip_tags($str); }
function wp_trim_words($str, $count, $more = '…') { return $str; }
function get_the_post_thumbnail_url($id, $size) { return false; }
function home_url($path = '/') { return 'https://apartmanibihac.com' . $path; }
function get_permalink($id) {
    return $GLOBALS['front'] ? 'https://apartmanibihac.com/' : 'https://apartmanibihac.com/smjestaj/apartman-centar/';
}
function esc_attr($str) { return htmlspecialchars((string) $str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_url($str) { return htmlspecialchars((string) $str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function wp_json_encode($value, $flags = 0) { return json_encode($value, $flags); }

require dirname(__DIR__, 2) . '/drustvene.php';
require dirname(__DIR__, 2) . '/seo.php';

function assert_has(string $needle, string $text, string $label) {
    if (strpos($text, $needle) === false) {
        fwrite(STDERR, "FAIL $label\nExpected: $needle\n");
        exit(1);
    }
}
function assert_not_has(string $needle, string $text, string $label) {
    if (strpos($text, $needle) !== false) {
        fwrite(STDERR, "FAIL $label\nUnexpected: $needle\n");
        exit(1);
    }
}
function render_og(): string {
    ob_start();
    foreach ($GLOBALS['head_actions']['wp_head'][3] ?? [] as $callback) {
        $callback();
    }
    return (string) ob_get_clean();
}
function render_seo(): string {
    ob_start();
    foreach ($GLOBALS['head_actions']['wp_head'][2] ?? [] as $callback) {
        $callback();
    }
    return (string) ob_get_clean();
}

// Frontpage: read current document title and same description as existing SEO.
$home = render_og();
$seo = render_seo();
$expected = abu_seo_front_description();
assert_has('<meta property="og:title" content="Smještaj Bihać: apartmani i vikendice na Uni od 25 €">', $home, 'home OG title');
assert_has('<meta property="og:description" content="' . esc_attr($expected) . '">', $home, 'home OG description');
assert_has('<meta name="description" content="' . esc_attr($expected) . '">', $seo, 'unchanged SEO description');
assert_not_has('<meta property="og:description" content="">', $home, 'no empty OG');
if (substr_count($home, 'property="og:title"') !== 1) {
    fwrite(STDERR, "FAIL: expected exactly one OG title from existing module\n");
    exit(1);
}

// Property pages must retain original title and individual description.
$GLOBALS['front'] = false;
$GLOBALS['page_title'] = 'Apartman Centar';
$GLOBALS['excerpt'] = 'Apartman Centar nalazi se u centru Bihaća.';
$property = render_og();
assert_has('<meta property="og:title" content="Apartman Centar">', $property, 'property title unchanged');
assert_has('<meta property="og:description" content="Apartman Centar nalazi se u centru Bihaća.">', $property, 'property description unchanged');
assert_has('https://apartmanibihac.com/smjestaj/apartman-centar/', $property, 'property canonical share URL');

// Archives still should not emit an extra OG block from this module.
$GLOBALS['singular'] = false;
$archive = render_og();
assert_not_has('property="og:title"', $archive, 'non-singular no new OG');
echo "PASS: homepage, SEO description, property preservation, archive guard\n";
