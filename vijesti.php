<?php
/**
 * Vijesti: kategorije (Bihać i Una, Team building, Restorani, Izlasci, Smještaj, Izleti),
 * traka s vijestima koja se vrti na vrhu, blok vijesti na naslovnici i na vrhu stranica vodiča.
 * Nove vijesti se objavljuju kao obične objave u jednoj od ovih kategorija.
 */
if (!defined('ABSPATH')) { exit; }

function abu_vijesti_kategorije() {
    return array(
        'bihac' => 'Bihać i Una',
        'team-building' => 'Team building',
        'restorani' => 'Restorani',
        'izlasci' => 'Izlasci',
        'izleti' => 'Izleti',
        'smjestaj' => 'Smještaj',
        'turizam' => 'Vijesti iz turizma',
    );
}

add_action('init', function () {
    if (get_option('abu_vijesti_ver') === ABU_VER) { return; }
    $rod = term_exists('vijesti', 'category');
    if (!$rod) { $rod = wp_insert_term('Vijesti', 'category', array('slug' => 'vijesti')); }
    $rid = is_array($rod) ? (int) $rod['term_id'] : (int) $rod;
    foreach (abu_vijesti_kategorije() as $slug => $ime) {
        if (!term_exists('vijesti-' . $slug, 'category')) { wp_insert_term($ime, 'category', array('slug' => 'vijesti-' . $slug, 'parent' => $rid)); }
    }
    update_option('abu_vijesti_ver', ABU_VER);
}, 30);

function abu_vijesti_kat_ids() {
    static $ids = null;
    if ($ids !== null) { return $ids; }
    $ids = array();
    $r = get_term_by('slug', 'vijesti', 'category');
    if ($r) { $ids = array_merge(array((int) $r->term_id), array_map('intval', get_term_children($r->term_id, 'category'))); }
    return $ids;
}

/** Vijesti iz kategorije (ili sve vijesti); ako vijesti još nema, najnoviji članci. */
function abu_vijesti_upit($kat = '', $broj = 6) {
    $q = array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $broj, 'no_found_rows' => true);
    if ($kat) { $q['category_name'] = 'vijesti-' . $kat; }
    elseif (abu_vijesti_kat_ids()) { $q['category__in'] = abu_vijesti_kat_ids(); }
    $p = get_posts($q);
    if (!$p && !$kat) { unset($q['category__in']); $p = get_posts($q); }
    return $p;
}

function abu_vijesti_kat_ime($pid) {
    foreach (get_the_category($pid) as $c) {
        if (strpos($c->slug, 'vijesti-') === 0) { return $c->name; }
    }
    return 'Vodič';
}

/** Traka s naslovima koja se vrti (ispod zaglavlja). */
function abu_vijesti_traka() {
    $p = abu_vijesti_upit('', 10);
    if (!$p) { return ''; }
    $li = '';
    foreach ($p as $x) { $li .= '<li><a href="' . esc_url(get_permalink($x)) . '"><small>' . esc_html(abu_vijesti_kat_ime($x->ID)) . '</small>' . esc_html(get_the_title($x)) . '</a></li>'; }
    $lbl = array('bs' => 'Novo', 'en' => 'News', 'de' => 'Neu');
    $l = function_exists('abu_dr_jezik') ? abu_dr_jezik() : 'bs';
    // popis dvaput, da se vrti bez prekida
    return '<div class="ticker" role="region" aria-label="Novosti"><div class="wrap ticker__in"><span class="ticker__lbl">' . esc_html($lbl[$l]) . '</span><div class="ticker__win"><ul class="ticker__list">' . $li . str_replace(array('<li>', '<a '), array('<li aria-hidden="true">', '<a tabindex="-1" '), $li) . '</ul></div></div></div>';
}

/** Blok vijesti: jedna velika i nekoliko manjih. */
function abu_vijesti_blok($kat = '', $broj = 4, $naslov = 'Novosti') {
    $p = abu_vijesti_upit($kat, $broj);
    // Ako u rubrici ima manje vijesti od traženog broja, dopuni najnovijim ostalim novostima (bez ponavljanja).
    if ($kat && count($p) < $broj) {
        $ima = wp_list_pluck($p, 'ID');
        foreach (abu_vijesti_upit('', $broj + count($ima)) as $x) { if (count($p) >= $broj) { break; } if (!in_array($x->ID, $ima, true)) { $p[] = $x; } }
    }
    if (!$p) { return ''; }
    $h = '<section class="news" aria-label="' . esc_attr($naslov) . '"><div class="wrap"><div class="section__head"><div><p class="eyebrow">Novosti</p><h2 class="h2">' . esc_html($naslov) . '</h2></div>'
        . '<a class="link" href="' . esc_url(home_url($kat ? '/category/vijesti/vijesti-' . $kat . '/' : '/vodic/')) . '">Sve novosti →</a></div><div class="news__grid">';
    foreach ($p as $i => $x) {
        $img = get_the_post_thumbnail($x, $i === 0 ? 'ab-wide' : 'ab-card', array('loading' => 'lazy', 'alt' => ''));
        $h .= '<article class="news__item"><span class="news__img">' . $img . '</span><div class="news__b"><p class="news__cat">' . esc_html(abu_vijesti_kat_ime($x->ID)) . '</p>'
            . '<h3 class="news__t"><a href="' . esc_url(get_permalink($x)) . '">' . esc_html(get_the_title($x)) . '</a></h3>'
            . ($i === 0 ? '<p class="news__e">' . esc_html(get_the_excerpt($x)) . '</p>' : '')
            . '<time class="news__d" datetime="' . esc_attr(get_the_date('c', $x)) . '">' . esc_html(get_the_date('j. n. Y.', $x)) . '</time></div></article>';
    }
    return $h . '</div></div></section>';
}
add_shortcode('abu_vijesti', function ($a) {
    $a = shortcode_atts(array('kat' => '', 'broj' => 4, 'naslov' => 'Novosti'), $a);
    return abu_vijesti_blok($a['kat'], (int) $a['broj'], $a['naslov']);
});

/** Mjesto iz sadrzaj/*.json po slugu (za fotografiju uz vijest). */
function abu_vijesti_mjesto($slug) {
    static $sva = null;
    if ($sva === null) {
        $sva = array();
        foreach (glob(__DIR__ . '/sadrzaj/*.json') as $f) {
            if (basename($f) === 'vijesti.json') { continue; }
            $x = json_decode((string) file_get_contents($f), true);
            if (!is_array($x)) { continue; }
            foreach (isset($x[0]) ? array($x) : array_values($x) as $lista) {
                if (!is_array($lista)) { continue; }
                foreach ($lista as $e) { if (is_array($e) && !empty($e['slug'])) { $sva[$e['slug']] = $e; } }
            }
        }
    }
    return isset($sva[$slug]) ? $sva[$slug] : null;
}

/** Ključne riječi u slugu članka => mjesto čija fotografija ide kao naslovna. */
function abu_vijesti_slika_za($slug, $zadano = '') {
    $m = array('strbacki' => 'strbacki-buk', 'plitvic' => 'plitvicka-jezera', 'rafting' => 'una-olympic-rafting', 'team-building' => 'una-olympic-rafting', 'martin-brod' => 'martin-brod',
        'nacionalni-park' => 'np-una-posjeta', 'np-una' => 'np-una-posjeta', 'kupanje' => 'lohovo-troslap-dvoslap', 'kostel' => 'kostela-kostelski-buk', 'ostrozac' => 'ostrozac', 'sokolac' => 'tvrdjava-sokolac',
        'zeljava' => 'zeljava', 'ribolov' => 'ribolov-una', 'bicikl' => 'biciklizam-una', 'restoran' => 'restoran-cardak-na-uni', 'izlas' => 'bihac-una-otoci', 'nocni' => 'bihac-una-otoci', 'kapetanova' => 'kapetanova-kula',
        'gdje-odsjesti' => 'bihac-una-otoci', 'smjestaj' => 'bihac-una-otoci', 'kako-doci' => 'kanjon-une-bihac-krupa', 'cijene' => 'bihac-una-otoci', 'jesen' => 'kanjon-une-bihac-krupa', 'zima' => 'pljesevica');
    $kand = array();
    if ($zadano) { $kand[] = $zadano; }
    foreach ($m as $k => $v) { if (strpos($slug, $k) !== false) { $kand[] = $v; } }
    $kand = array_merge($kand, array('strbacki-buk', 'bihac-una-otoci', 'kanjon-une-bihac-krupa', 'martin-brod'));
    foreach ($kand as $s) {
        $e = abu_vijesti_mjesto($s);
        if ($e && function_exists('abu_vodic_slike_ids') && ($ids = abu_vodic_slike_ids($e))) { return $ids[abs(crc32($slug)) % count($ids)]; }
    }
    return 0;
}

/** Objavi vijesti iz sadrzaj/vijesti.json (samo nove) i dodijeli naslovne fotografije člancima bez njih. */
add_action('init', function () {
    $f = __DIR__ . '/sadrzaj/vijesti.json';
    $map = get_option('abu_vodic_slike', array());
    $ver = ABU_VER . '|' . (is_readable($f) ? md5_file($f) : '-') . '|' . count($map);
    if (get_option('abu_vijesti_seed') === $ver || get_transient('abu_vijesti_lock')) { return; }
    set_transient('abu_vijesti_lock', 1, 120);
    $x = is_readable($f) ? json_decode((string) file_get_contents($f), true) : array();
    $n = 0;
    foreach ((array) $x as $i => $v) {
        if (empty($v['slug']) || empty($v['naslov']) || get_page_by_path($v['slug'], OBJECT, 'post') || get_posts(array('name' => $v['slug'], 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1))) { continue; }
        $kat = get_term_by('slug', 'vijesti-' . (isset($v['kat']) ? $v['kat'] : 'bihac'), 'category');
        $id = wp_insert_post(array('post_type' => 'post', 'post_status' => 'publish', 'post_name' => $v['slug'], 'post_title' => $v['naslov'],
            'post_excerpt' => isset($v['izvod']) ? $v['izvod'] : '', 'post_content' => wp_kses_post($v['html']),
            'post_category' => $kat ? array($kat->term_id) : array(),
            // "datum" (lokalno vrijeme, npr. "2026-11-14 11:00:00"): u budućnosti => zakazana objava (FB/IG kad izađe)
            'post_date' => !empty($v['datum']) ? $v['datum'] : gmdate('Y-m-d H:i:s', current_time('timestamp') - ($i * 5400))));
        if ($id && !is_wp_error($id)) {
            update_post_meta($id, '_abu_slika_mjesto', isset($v['slika']) ? $v['slika'] : '');
            if (!empty($v['fb'])) { update_post_meta($id, '_abu_fb_tekst', $v['fb']); }
            if (!empty($v['ig'])) { update_post_meta($id, '_abu_ig_tekst', $v['ig']); }
            $n++;
        }
    }
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids')) as $pid) {
        if (has_post_thumbnail($pid)) { continue; }
        $img = abu_vijesti_slika_za(get_post_field('post_name', $pid), (string) get_post_meta($pid, '_abu_slika_mjesto', true));
        if ($img) { set_post_thumbnail($pid, $img); }
    }
    update_option('abu_vijesti_seed', $ver);
    delete_transient('abu_vijesti_lock');
}, 39);
