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
/** Članci skriveni od tražilica (odluka vlasnika 11.11.2026.): stari članak iz 2024. o hostelima kojih nema u ponudi. */
function abu_seo_skriveni_clanci() {
    return array('naslov-pronadite-odlican-i-jeftin-smjestaj-u-bihacu-vodic-za-putnike');
}
function abu_seo_bez_indeksa() {
    if (get_query_var('ab_smjestaj') || is_tax('oprema')) { return true; }
    if (is_singular('post') && in_array(get_post_field('post_name', get_queried_object_id()), abu_seo_skriveni_clanci(), true)) { return true; }
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
    if ($tip === 'post') {
        $ids = array();
        foreach (abu_seo_skriveni_clanci() as $s) { $x = get_posts(array('name' => $s, 'post_type' => 'post', 'numberposts' => 1, 'fields' => 'ids')); if ($x) { $ids[] = $x[0]; } }
        if ($ids) { $a['post__not_in'] = array_merge(isset($a['post__not_in']) ? (array) $a['post__not_in'] : array(), $ids); }
    }
    return $a;
}, 10, 2);

/**
 * 3. Tema već ispisuje naslov kao H1. Kad tekst stranice sadrži isti H1 (često iza putanje ili oznake),
 * naslov se vidi dvaput i stranica ima dva H1. Uklanja se samo prvi H1 u sadržaju koji je jednak naslovu.
 */
add_filter('the_content', function ($c) {
    if (is_admin() || !is_singular() || !in_the_loop() || !is_main_query()) { return $c; }
    if (!preg_match('~<h1[^>]*>(.*?)</h1>\s*~is', $c, $m, PREG_OFFSET_CAPTURE)) { return $c; }
    $norm = function ($s) { return trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($s), ENT_QUOTES, 'UTF-8'))); };
    if ($norm($m[1][0]) !== $norm(get_the_title())) { return $c; }
    return substr($c, 0, $m[0][1]) . substr($c, $m[0][1] + strlen($m[0][0]));
}, 11); // nakon do_blocks (9) i wpautop (10)

/** 4. Članci: BlogPosting (vijesti: NewsArticle s izvorom) schema (naslov, datumi, slika, izdavač). Bez ocjena i recenzija. */
add_action('wp_head', function () {
    if (!is_singular('post')) { return; }
    $id = get_queried_object_id();
    $vijest = get_post_meta($id, '_abu_dnevna_vijest', true) || (function_exists('abu_vijesti_kat_ime') && abu_vijesti_kat_ime($id) !== 'Vodič');
    $d = array('@context' => 'https://schema.org', '@type' => $vijest ? 'NewsArticle' : 'BlogPosting',
        'headline' => wp_strip_all_tags(get_the_title($id)),
        'url' => get_permalink($id), 'mainEntityOfPage' => get_permalink($id),
        'datePublished' => get_the_date('c', $id), 'dateModified' => get_the_modified_date('c', $id),
        'inLanguage' => 'bs',
        'author' => array('@type' => 'Organization', 'name' => 'Apartmani & Vikendice Bihać', 'url' => home_url('/')),
        'publisher' => array('@id' => home_url('/#org')));
    $img = function_exists('abu_grafika') ? abu_grafika($id, 'fb') : '';
    $img2 = get_the_post_thumbnail_url($id, 'large');
    if ($img && $img2) { $d['image'] = array($img, $img2); } elseif ($img || $img2) { $d['image'] = $img ? $img : $img2; }
    $iz = get_post_meta($id, '_abu_izvor_url', true);
    if ($iz) { $d['isBasedOn'] = $iz; }
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
    $dugo = !empty($d['site']) && mb_strlen(wp_strip_all_tags($d['title'])) + 3 + mb_strlen($d['site']) > 60;
    if ($dugo || preg_match('~biha[cć]|\buna\b|\buni\b|\bunu\b~iu', $d['title'])) { unset($d['site'], $d['tagline']); }
    return $d;
});

/**
 * 8. Stranice prenesene sa starog sajta (lokacije, uz Unu, s bazenom) imaju kartice sa slikama /assets/….webp,
 * koje na ovom serveru ne postoje (404). Slika se pri prikazu zamijeni istaknutom fotografijom objekta na koji kartica
 * vodi; ako objekt nema fotografiju, prazna slika se uklanja. Sadržaj u bazi ostaje isti.
 */
add_filter('the_content', function ($c) {
    if (is_admin() || strpos($c, '/assets/') === false) { return $c; }
    return preg_replace_callback('~(<a href="(?:' . preg_quote(untrailingslashit(home_url()), '~') . ')?/smjestaj/([a-z0-9-]+)/"[^>]*>)\s*<img[^>]*\bsrc="/assets/[^"]+"[^>]*>~i', function ($m) {
        $p = get_page_by_path($m[2], OBJECT, 'objekt');
        $id = $p ? get_post_thumbnail_id($p) : 0;
        if (!$id && $p && function_exists('ab_slike')) { $s = ab_slike($p->ID, 1); $id = $s ? $s[0] : 0; }
        if (!$id) { return $m[1]; }
        $mj = (string) get_post_meta($p->ID, '_abu_mjesto', true);
        return $m[1] . wp_get_attachment_image($id, 'ab-card', false, array('loading' => 'lazy', 'alt' => get_the_title($p) . ($mj ? ', ' . $mj : '')));
    }, $c);
}, 13);

/** 9. Kategorije vijesti i vodiča: meta opis i canonical (WordPress ih za arhive ne ispisuje). */
add_action('wp_head', function () {
    if (!is_category() || (function_exists('abu_seo_plugin') && abu_seo_plugin())) { return; }
    $t = get_queried_object();
    if (!$t || empty($t->term_id)) { return; }
    $opis = trim(wp_strip_all_tags(term_description($t)));
    if ($opis === '') { $opis = $t->name . ': novosti i vodiči o Bihaću, rijeci Uni i smještaju. Apartmani i kuće uz Unu od 25 € po noći.'; }
    echo '<meta name="description" content="' . esc_attr(mb_substr($opis, 0, 158)) . '">' . "\n";
    if (!is_paged()) { echo '<link rel="canonical" href="' . esc_url(get_term_link($t)) . '">' . "\n"; }
}, 2);

/* §10 Tablice u člancima: zaglavlje poravnato lijevo kao ćelije, čitljivo na mobitelu. */
add_action('wp_head', function () {
    if (!is_singular()) { return; }
    echo '<style id="abu-tablice">.prose table{width:100%;border-collapse:collapse;margin:1.2em 0}.prose th,.prose td{text-align:left;vertical-align:top;padding:.55em .6em;border-bottom:1px solid rgba(0,0,0,.12)}.prose thead th{border-bottom:2px solid rgba(0,0,0,.25)}@media(max-width:600px){.prose th,.prose td{padding:.5em .35em;font-size:.95em}}</style>' . "\n";
}, 20);
