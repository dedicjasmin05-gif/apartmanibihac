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
    if ($x = abu_seo_pitanja_meta()) { return $x[0]; }
    if (is_singular('objekt')) {
        $id = get_queried_object_id();
        $mj = (string) get_post_meta($id, '_abu_mjesto', true);
        $kap = (int) get_post_meta($id, '_abu_kapacitet', true);
        $tip = get_post_meta($id, '_abu_grupa', true) === 'vlastiti' ? 'apartman' : 'smještaj';
        return get_the_title($id) . ': ' . $tip . ' ' . ($mj && stripos($mj, 'Bihać') === false ? $mj . ', Bihać' : 'Bihać') . ($kap ? ', do ' . $kap . ' osoba' : '');
    }
    return $t;
}, 20);

/** Meta opis: SEO stranice, naslovnica, objekti, ostale stranice iz sažetka. */
add_action('wp_head', function () {
    if (abu_seo_plugin()) { return; }
    $opis = '';
    $s = abu_seo_tekuca();
    if ($s) { $opis = $s['meta']; }
    elseif ($x = abu_seo_pitanja_meta()) { $opis = $x[1]; }
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

/** Naslov i opis za stranice s pitanjima (bs/en/de). */
function abu_seo_pitanja_meta() {
    if (!is_page() || !function_exists('abu_pitanja_teme_sve')) { return null; }
    $id = get_queried_object_id();
    $k = get_post_meta($id, '_abu_pitanja', true);
    if (!$k) { return null; }
    $l = get_post_meta($id, '_abu_pitanja_lang', true) ?: 'bs';
    $J = abu_pitanja_jezici()[$l];
    $cta = array('bs' => 'Smještaj u Bihaću od 25 € po noći.', 'en' => 'Whole apartments in Bihać from €25.', 'de' => 'Ganze Wohnungen in Bihać ab 25 €.');
    $faq = array('bs' => 'pitanja i odgovori', 'en' => 'FAQ', 'de' => 'FAQ');
    if ($k === 'sve') {
        $nas = array('bs' => 'Česta pitanja o Bihaću, Uni i smještaju', 'en' => 'Bihać travel FAQ: Una, Plitvice, accommodation', 'de' => 'Bihać FAQ: Una, Plitvicer Seen, Unterkunft');
        $op = array('bs' => '1000 odgovora o Bihaću: kako doći, granica, NP Una, Štrbački buk, rafting, Plitvice, hrana, novac. ', 'en' => '1000 answers about Bihać: getting there, the border, Una National Park, rafting, Plitvice Lakes, food, money. ', 'de' => '1000 Antworten zu Bihać: Anreise, Grenze, Nationalpark Una, Rafting, Plitvicer Seen, Essen, Geld. ');
        return array($nas[$l], $op[$l] . $cta[$l]);
    }
    $t = abu_pitanja_teme_sve();
    if (!isset($t[$k])) { return null; }
    $naslov = $t[$k][$J['i'] + 1];
    $opis = $t[$k][$J['i'] + 2];
    $n = count(abu_pitanja_ucitaj($k, $l));
    $br = array('bs' => $n . ' odgovora: ', 'en' => $n . ' answers: ', 'de' => $n . ' Antworten: ');
    $title = $naslov . ' – ' . $faq[$l] . (strpos($naslov, 'Bihać') === false && strpos($naslov, 'Una') === false ? ' | Bihać' : '');
    $meta = $br[$l] . $opis . ' ' . $cta[$l];
    if (mb_strlen($meta) < 120) {
        $prva = abu_pitanja_ucitaj($k, $l);
        if ($prva) { $meta .= ' ' . wp_trim_words($prva[0]['q'], 12, '…'); }
    }
    return array($title, mb_substr($meta, 0, 158));
}

/** Naslovnica: blok "Naše preporuke" (restorani, noćni život, rafting, česta pitanja), kao na starom sajtu. */
add_filter('the_content', function ($c) {
    if (is_admin() || !in_the_loop() || !is_main_query() || !is_front_page() || strpos($c, 'abu-pr-blok') !== false) { return $c; }
    $st = array(
        array('restorani-bihac', 'Restorani u Bihaću', 'Riba iz Une, travnički ćevapi, pizza i domaća jela.'),
        array('nocni-zivot-bihac', 'Noćni život', 'Barovi na obali Une, ljetne terase i klubovi.'),
        array('rafting-na-uni', 'Rafting na Uni', 'Spust kroz kanjon uz skipera, s ručkom uz rijeku.'),
        array('team-building-bihac', 'Team building na Uni', 'Rafting, smještaj i ručak za firme i grupe na jednom mjestu.'),
        array('vikendice-s-bazenom', 'Vikendice s bazenom', 'Kuće uz Unu s bazenom za obitelji i društva.'),
        array('cesta-pitanja', 'Česta pitanja', '1000 odgovora o Bihaću, NP Una, Plitvicama i smještaju.'),
    );
    $h = '<style>.abu-pr-blok{margin:32px 0}.abu-pr-blok h2{margin:0 0 14px}.abu-pr-g{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}.abu-pr-k{display:block;padding:18px;border-radius:14px;background:#f6f8f4;color:inherit!important;text-decoration:none;border:1px solid #e5e9e2}.abu-pr-k:hover{border-color:#14532d}.abu-pr-k strong{display:block;font-size:17px;margin-bottom:6px;color:#14532d}.abu-pr-k span{font-size:14px;color:#444}</style>'
        . '<section class="abu-pr-blok"><h2>Naše preporuke za Bihać</h2><div class="abu-pr-g">';
    foreach ($st as $s) {
        $p = get_page_by_path($s[0]);
        if (!$p || $p->post_status !== 'publish') { continue; }
        $h .= '<a class="abu-pr-k" href="' . esc_url(get_permalink($p)) . '"><strong>' . esc_html($s[1]) . '</strong><span>' . esc_html($s[2]) . '</span></a>';
    }
    return $c . $h . '</div></section>';
}, 25);
