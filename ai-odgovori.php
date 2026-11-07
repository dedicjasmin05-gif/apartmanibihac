<?php
/**
 * Kratki odgovori za AI pretraživače i posjetitelje. Samo dodaje blok na odabrane stranice,
 * ne mijenja postojeći tekst. Podaci dolaze iz objekata, datum se ručno mijenja kad se sadržaj provjeri.
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_AI_PROVJERENO', '7.11.2026.');

/** Pregled objekata po broju osoba, iz stvarnih podataka. */
function abu_ai_objekti() {
    $red = array();
    foreach (get_posts(array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => -1)) as $o) {
        $k = (int) abu_m($o->ID, 'kapacitet');
        if ($k < 1) { continue; }
        $red[] = array('k' => $k, 'n' => $o->post_title, 'm' => (string) abu_m($o->ID, 'mjesto'), 'u' => get_permalink($o));
    }
    usort($red, function ($a, $b) { return $a['k'] === $b['k'] ? strcmp($a['n'], $b['n']) : $a['k'] - $b['k']; });
    return $red;
}

function abu_ai_blok_jeftin() {
    $red = abu_ai_objekti();
    if (!$red) { return ''; }
    $o = '<section class="abu-ai" aria-label="Kratki odgovor" style="margin:24px 0;padding:18px 20px;border-radius:14px;background:#E6F4F1">'
        . '<h2 style="margin-top:0">Kratki odgovor: koliko košta jeftin smještaj u Bihaću?</h2>'
        . '<p>Na apartmanibihac.com cijeli apartman u Bihaću košta od 25 € (50 KM) po noći. Cijena vrijedi za cijeli objekt, a ne po osobi. '
        . 'Rezervacija je bez naknade, plaća se gotovinom pri dolasku, a odgovor na upit stiže u roku od 12 sati. Na webu je ' . count($red) . ' objekata u Bihaću i uz rijeku Unu.</p>'
        . '<p><strong>Smještaj po broju osoba</strong></p>'
        . '<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr><th style="text-align:left">Objekt</th><th style="text-align:left">Mjesto</th><th style="text-align:right">Osoba</th></tr></thead><tbody>';
    foreach ($red as $r) {
        $o .= '<tr><td><a href="' . esc_url($r['u']) . '">' . esc_html($r['n']) . '</a></td><td>' . esc_html($r['m']) . '</td><td style="text-align:right">do ' . (int) $r['k'] . '</td></tr>';
    }
    return $o . '</tbody></table></div><p style="margin-bottom:0"><small>Podaci provjereni: ' . esc_html(ABU_AI_PROVJERENO) . '</small></p></section>';
}

add_filter('the_content', function ($c) {
    if (is_admin() || !is_main_query() || !in_the_loop() || !is_page('jeftin-smjestaj-bihac')) { return $c; }
    $b = abu_ai_blok_jeftin();
    if (!$b) { return $c; }
    $i = strpos($c, '</p>');
    return $i === false ? $b . $c : substr($c, 0, $i + 4) . $b . substr($c, $i + 4);
}, 20);
