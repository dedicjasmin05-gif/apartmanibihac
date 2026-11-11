<?php
/**
 * Tehnički SEO popravci (audit 11.11.2026., vidi SEO-AUDIT.md). Svaki blok je samostalan;
 * za povrat cijele datoteke dovoljno je ukloniti njen require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

/** Pravilna množina: 1 osoba, 2–4 osobe, 5+ osoba (21 osoba, 22 osobe, 12–14 osoba). */
function abu_osobe($n) {
    $n = (int) $n; $m10 = $n % 10; $m100 = $n % 100;
    return $n . ' ' . (($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) ? 'osobe' : 'osoba');
}

/**
 * 1. Spremljena pravila adresa ponekad ostanu bez pravila teme (/smjestaj/ pretraga, /oprema/…).
 * Tada /smjestaj/?mjesto=… WordPress preusmjeri na /smjestaj-bihac/ (filter se izgubi), a /oprema/… daje 404.
 * Ako pravilo nedostaje, adrese se jednom osvježe (najviše jednom u 10 minuta).
 */
add_action('init', function () {
    $treba = array();
    if (function_exists('ab_upit_objekti')) { $treba[] = '^smjestaj/?$'; }
    if (taxonomy_exists('oprema')) { $treba[] = 'oprema/([^/]+)/?$'; }
    if (!$treba) { return; }
    $r = get_option('rewrite_rules');
    foreach ($treba as $k) {
        if (is_array($r) && isset($r[$k])) { continue; }
        if (get_transient('abu_rw_popravak')) { return; }
        set_transient('abu_rw_popravak', 1, 600);
        flush_rewrite_rules(false);
        update_option('abu_rw_popravljeno', current_time('mysql') . ' (' . $k . ')', false);
        return;
    }
}, 100);

/**
 * 2. Stranice koje ne trebaju biti u Googleu: pretraga /smjestaj/ (isti popis kao /smjestaj-bihac/, uz filtere),
 * arhive opreme (samo popis kartica, ~24 riječi), pomoćna stranica "header-slike". Linkovi se i dalje prate.
 */
function abu_seo_bez_indeksa() {
    if (get_query_var('ab_smjestaj') || is_tax('oprema')) { return true; }
    return is_page('header-slike');
}
add_filter('wp_robots', function ($r) {
    if (abu_seo_bez_indeksa()) { $r['noindex'] = true; $r['follow'] = true; unset($r['max-image-preview']); }
    return $r;
});

/** Mapa sajta: bez arhiva opreme i pomoćne stranice. */
add_filter('wp_sitemaps_taxonomies', function ($t) { unset($t['oprema']); return $t; });
add_filter('wp_sitemaps_posts_query_args', function ($a, $tip) {
    if ($tip === 'page') {
        $p = get_page_by_path('header-slike');
        if ($p) { $a['post__not_in'] = array_merge(isset($a['post__not_in']) ? (array) $a['post__not_in'] : array(), array($p->ID)); }
    }
    return $a;
}, 10, 2);

/**
 * 3. Tema već ispisuje naslov kao H1. Kad tekst stranice počinje istim H1, naslov se vidi dvaput
 * i stranica ima dva H1. Uklanja se samo početni H1 koji je jednak naslovu; ostali sadržaj ostaje.
 */
add_filter('the_content', function ($c) {
    if (is_admin() || !is_singular() || !in_the_loop() || !is_main_query()) { return $c; }
    if (!preg_match('~^\s*(?:<!--[^>]*-->\s*)*<h1[^>]*>(.*?)</h1>\s*~is', $c, $m)) { return $c; }
    $norm = function ($s) { return trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($s), ENT_QUOTES, 'UTF-8'))); };
    if ($norm($m[1]) !== $norm(get_the_title())) { return $c; }
    return substr($c, strlen($m[0]));
}, 11); // nakon do_blocks (9) i wpautop (10)

/** 4. Članci: BlogPosting schema (naslov, datumi, slika, izdavač). Bez ocjena i recenzija. */
add_action('wp_head', function () {
    if (!is_singular('post')) { return; }
    $id = get_queried_object_id();
    $d = array('@context' => 'https://schema.org', '@type' => 'BlogPosting',
        'headline' => wp_strip_all_tags(get_the_title($id)),
        'url' => get_permalink($id), 'mainEntityOfPage' => get_permalink($id),
        'datePublished' => get_the_date('c', $id), 'dateModified' => get_the_modified_date('c', $id),
        'inLanguage' => 'bs',
        'author' => array('@type' => 'Organization', 'name' => 'Apartmani & Vikendice Bihać', 'url' => home_url('/')),
        'publisher' => array('@id' => home_url('/#org')));
    $img = get_the_post_thumbnail_url($id, 'large');
    if ($img) { $d['image'] = $img; }
    $opis = has_excerpt($id) ? get_the_excerpt($id) : '';
    if ($opis) { $d['description'] = wp_strip_all_tags($opis); }
    $kat = get_the_category($id);
    $d2 = array('@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => array(
        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Početna', 'item' => home_url('/')),
        array('@type' => 'ListItem', 'position' => 2, 'name' => 'Vodič', 'item' => home_url('/vodic/')),
        array('@type' => 'ListItem', 'position' => 3, 'name' => wp_strip_all_tags(get_the_title($id)), 'item' => get_permalink($id))));
    if ($kat && !is_wp_error($kat)) { $d['articleSection'] = $kat[0]->name; }
    echo '<script type="application/ld+json">' . wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($d2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 6);

/**
 * 5. Članci međusobno linkaju na /vodic/naziv/, što svaki put ide preko 301 na stvarnu adresu članka.
 * Pri prikazu se takav link zamijeni stalnom adresom članka (sadržaj u bazi ostaje isti).
 */
add_filter('the_content', function ($c) {
    if (is_admin() || strpos($c, '/vodic/') === false) { return $c; }
    static $mapa = array();
    $dom = preg_quote(untrailingslashit(home_url()), '~');
    return preg_replace_callback('~href=(["\'])(?:' . $dom . ')?/vodic/([a-z0-9-]+)/?\1~i', function ($m) use (&$mapa) {
        $slug = strtolower($m[2]);
        if ($slug === 'page') { return $m[0]; }
        if (!array_key_exists($slug, $mapa)) {
            $p = get_posts(array('name' => $slug, 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1));
            $mapa[$slug] = $p ? get_permalink($p[0]) : '';
        }
        return $mapa[$slug] ? 'href=' . $m[1] . esc_url($mapa[$slug]) . $m[1] : $m[0];
    }, $c);
}, 12);

/** 6. Slike bez alt teksta: naslov objave kojoj slika pripada (team building, vodič, vijesti). Uređeni alt ostaje. */
add_filter('wp_get_attachment_image_attributes', function ($a, $att) {
    if (isset($a['alt']) && trim($a['alt']) !== '') { return $a; }
    $rod = $att && !empty($att->post_parent) ? get_post($att->post_parent) : null;
    if ($rod && $rod->post_status === 'publish' && $rod->post_title !== '') { $a['alt'] = wp_strip_all_tags($rod->post_title); }
    return $a;
}, 10, 2);

/**
 * 7. Google skraćuje naslove dulje od ~60 znakova. Kad naslov stranice već sadrži Bihać ili Unu,
 * dodatak „– Apartmani i vikendice Bihać“ samo ga produži, pa se izostavlja. Naslovnica i ručno postavljeni naslovi ostaju.
 */
add_filter('document_title_parts', function ($d) {
    if (is_front_page() || empty($d['title'])) { return $d; }
    if (preg_match('~biha[cć]|\buna\b|\buni\b|\bunu\b~iu', $d['title'])) { unset($d['site'], $d['tagline']); }
    return $d;
});
