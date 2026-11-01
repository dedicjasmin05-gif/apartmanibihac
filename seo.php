<?php
/**
 * SEO ulazne stranice iz seo/stranice.json (bs/en/de), naslovi i meta opisi za Google,
 * FAQPage schema. Stranice se osvježe kad se JSON promijeni.
 */
if (!defined('ABSPATH')) { exit; }

function abu_seo_podaci() {
    static $d = null;
    if ($d !== null) { return $d; }
    $f = __DIR__ . '/seo/stranice.json';
    $d = is_readable($f) ? json_decode((string) file_get_contents($f), true) : array();
    return is_array($d) ? $d : array();
}

add_action('init', function () {
    $f = __DIR__ . '/seo/stranice.json';
    if (!is_readable($f)) { return; }
    $ver = md5_file($f) . ABU_VER;
    if (get_option('abu_seo_ver') === $ver || get_transient('abu_seo_lock')) { return; }
    set_transient('abu_seo_lock', 1, 300);
    foreach (abu_seo_podaci() as $put => $s) {
        $dijelovi = explode('/', $put);
        $slug = array_pop($dijelovi);
        $roditelj = 0;
        if ($dijelovi) {
            $r = get_page_by_path(implode('/', $dijelovi));
            if (!$r) { continue; }
            $roditelj = $r->ID;
        }
        $p = get_page_by_path($put);
        $podaci = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $roditelj, 'post_name' => $slug,
            'post_title' => $s['h1'], 'post_excerpt' => $s['meta'], 'post_content' => $s['html']);
        if ($p) { $podaci['ID'] = $p->ID; wp_update_post($podaci); $id = $p->ID; } else { $id = wp_insert_post($podaci); }
        if ($id && !is_wp_error($id)) { update_post_meta($id, '_abu_seo', $put); }
    }
    update_option('abu_seo_ver', $ver);
    delete_transient('abu_seo_lock');
}, 36);

function abu_seo_tekuca() {
    if (!is_page()) { return null; }
    $k = get_post_meta(get_queried_object_id(), '_abu_seo', true);
    $d = abu_seo_podaci();
    return ($k && isset($d[$k])) ? $d[$k] : null;
}

function abu_seo_plugin() { return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION'); }

/** Naslov u pregledniku i na Googleu. */
add_filter('pre_get_document_title', function ($t) {
    if (abu_seo_plugin()) { return $t; }
    $s = abu_seo_tekuca();
    if ($s && !empty($s['title'])) { return $s['title']; }
    if (is_front_page()) { return 'Smještaj Bihać: apartmani i vikendice na Uni od 25 €'; }
    return $t;
}, 20);

/** Meta opis: SEO stranice, naslovnica, objekti, ostale stranice iz sažetka. */
add_action('wp_head', function () {
    if (abu_seo_plugin()) { return; }
    $opis = '';
    $s = abu_seo_tekuca();
    if ($s) { $opis = $s['meta']; }
    elseif (is_front_page()) { $opis = 'Apartmani u Bihaću i kuće uz rijeku Unu: cijeli apartman od 25 € (50 KM) po noći. 19 objekata, upit bez naknade, odgovor u 12 sati. WhatsApp +387 61 591 320.'; }
    elseif (is_singular()) {
        $id = get_queried_object_id();
        $opis = has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $id))), 28, '…');
    }
    if ($opis) { echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($opis)) . '">' . "\n"; }
    if ($s && !empty($s['faq'])) {
        $d = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array());
        foreach ($s['faq'] as $x) { $d['mainEntity'][] = array('@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $x['a'])); }
        echo '<script type="application/ld+json">' . wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    if ($s && !empty($s['lang'])) {
        echo '<meta property="og:locale" content="' . ($s['lang'] === 'de' ? 'de_DE' : ($s['lang'] === 'en' ? 'en_GB' : 'bs_BA')) . '">' . "\n";
    }
}, 2);

/** Jezik stranice za SEO stranice ispod /en/ i /de/ već postavlja jezici.php; ovdje samo hreflang između parova. */
add_action('wp_head', function () {
    $s = abu_seo_tekuca();
    if (!$s) { return; }
    $parovi = array(
        array('bs' => 'smjestaj-bihac', 'en' => 'en/accommodation-bihac', 'de' => 'de/unterkunft-bihac'),
        array('bs' => 'jeftin-smjestaj-bihac', 'en' => 'en/cheap-apartments-bihac', 'de' => 'de/ferienwohnung-bihac'),
        array('bs' => 'vikendice-na-uni', 'en' => 'en/holiday-homes-una-river', 'de' => 'de/ferienhaus-una'),
    );
    $k = get_post_meta(get_queried_object_id(), '_abu_seo', true);
    foreach ($parovi as $par) {
        if (!in_array($k, $par, true)) { continue; }
        foreach ($par as $l => $put) {
            $p = get_page_by_path($put);
            if ($p) { echo '<link rel="alternate" hreflang="' . $l . '" href="' . esc_url(get_permalink($p)) . '">' . "\n"; }
        }
        $p = get_page_by_path($par['bs']);
        if ($p) { echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(get_permalink($p)) . '">' . "\n"; }
    }
}, 3);
