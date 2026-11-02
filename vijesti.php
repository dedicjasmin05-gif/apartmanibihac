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
    return '<div class="ticker" role="region" aria-label="Novosti"><div class="wrap ticker__in"><span class="ticker__lbl">' . esc_html($lbl[$l]) . '</span><div class="ticker__win"><ul class="ticker__list">' . $li . str_replace('<li>', '<li aria-hidden="true">', $li) . '</ul></div></div></div>';
}

/** Blok vijesti: jedna velika i nekoliko manjih. */
function abu_vijesti_blok($kat = '', $broj = 4, $naslov = 'Novosti') {
    $p = abu_vijesti_upit($kat, $broj);
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
