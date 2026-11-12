<?php
/**
 * Članci na tri jezika i usporedba smještaja.
 * - seo/parovi.json: bs članak (slug objave) ↔ en/de stranica (putanja) → hreflang na sve tri.
 * - [abu_vodici lang="en"]: popis vodiča ispod /en/guide/ ili /de/reisefuehrer/ (automatski, iz stranica).
 * - [abu_usporedba lang="bs"]: tablica svih objekata iz stvarnih podataka (mjesto, osobe, oprema, min. noći).
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

function abu_parovi() {
    static $p = null;
    if ($p !== null) { return $p; }
    $f = __DIR__ . '/seo/parovi.json';
    $p = is_readable($f) ? (array) json_decode((string) file_get_contents($f), true) : array();
    return $p;
}

/** URL-ovi za jedan par (bs objava po slugu, en/de stranice po putanji). */
function abu_par_urlovi($par) {
    $o = array();
    if (!empty($par['bs'])) { $x = get_posts(array('name' => $par['bs'], 'post_type' => array('post', 'page'), 'post_status' => 'publish', 'numberposts' => 1)); if ($x) { $o['bs'] = get_permalink($x[0]); } }
    foreach (array('en', 'de') as $l) { if (!empty($par[$l])) { $x = get_page_by_path($par[$l]); if ($x && $x->post_status === 'publish') { $o[$l] = get_permalink($x); } } }
    return $o;
}

add_action('wp_head', function () {
    if (!is_singular()) { return; }
    $id = get_queried_object_id();
    $slug = get_post_field('post_name', $id);
    $put = is_page() ? trim(str_replace(home_url(), '', get_permalink($id)), '/') : '';
    foreach (abu_parovi() as $par) {
        $moj = (!empty($par['bs']) && $par['bs'] === $slug) || ($put !== '' && in_array($put, array($par['en'] ?? '', $par['de'] ?? ''), true));
        if (!$moj) { continue; }
        $u = abu_par_urlovi($par);
        if (count($u) < 2) { return; }
        foreach ($u as $l => $url) { echo '<link rel="alternate" hreflang="' . esc_attr($l) . '" href="' . esc_url($url) . '">' . "\n"; }
        if (isset($u['bs'])) { echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($u['bs']) . '">' . "\n"; }
        return;
    }
}, 3);

/** [abu_vodici lang="en"]: kartice vodiča (stranice ispod en/guide ili de/reisefuehrer). */
add_shortcode('abu_vodici', function ($a) {
    $a = shortcode_atts(array('lang' => 'en'), $a);
    $rod = get_page_by_path($a['lang'] === 'de' ? 'de/reisefuehrer' : 'en/guide');
    if (!$rod) { return ''; }
    $h = '<ul class="abu-vodici">';
    foreach (get_pages(array('parent' => $rod->ID, 'sort_column' => 'post_title', 'post_status' => 'publish')) as $p) {
        $h .= '<li><a href="' . esc_url(get_permalink($p)) . '"><strong>' . esc_html($p->post_title) . '</strong></a>' . ($p->post_excerpt ? '<br>' . esc_html($p->post_excerpt) : '') . '</li>';
    }
    return $h . '</ul>';
});

/** [abu_usporedba lang="bs"]: usporedba svih objekata iz stvarnih podataka. */
add_shortcode('abu_usporedba', function ($a) {
    $a = shortcode_atts(array('lang' => 'bs'), $a);
    $l = in_array($a['lang'], array('bs', 'en', 'de'), true) ? $a['lang'] : 'bs';
    $T = array(
        'bs' => array('Objekt', 'Mjesto', 'Osoba', 'Izlaz na Unu', 'Bazen', 'Klima', 'Parking', 'Wi-Fi', 'Min. noći', 'da', '–'),
        'en' => array('Property', 'Location', 'Guests', 'River access', 'Pool', 'A/C', 'Parking', 'Wi-Fi', 'Min. nights', 'yes', '–'),
        'de' => array('Unterkunft', 'Ort', 'Personen', 'Zugang zur Una', 'Pool', 'Klima', 'Parkplatz', 'WLAN', 'Min. Nächte', 'ja', '–'),
    );
    $t = $T[$l];
    $stupci = array('izlaz-na-unu', 'bazen', 'klima', 'parking', 'wi-fi');
    $red = array();
    foreach (get_posts(array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => 200)) as $o) {
        $op = wp_get_post_terms($o->ID, 'oprema', array('fields' => 'slugs'));
        $red[] = array('o' => $o, 'mj' => (string) get_post_meta($o->ID, '_abu_mjesto', true), 'kap' => (int) get_post_meta($o->ID, '_abu_kapacitet', true),
            'min' => max(1, (int) get_post_meta($o->ID, '_abu_min_noci', true)), 'op' => is_wp_error($op) ? array() : (array) $op);
    }
    usort($red, function ($x, $y) { return array($x['mj'] === 'Bihać' ? 0 : 1, $x['kap'], $x['o']->post_title) <=> array($y['mj'] === 'Bihać' ? 0 : 1, $y['kap'], $y['o']->post_title); });
    $h = '<div style="overflow-x:auto"><table class="abu-usporedba"><thead><tr><th>' . esc_html($t[0]) . '</th><th>' . esc_html($t[1]) . '</th><th>' . esc_html($t[2]) . '</th>';
    for ($i = 3; $i <= 8; $i++) { $h .= '<th>' . esc_html($t[$i]) . '</th>'; }
    $h .= '</tr></thead><tbody>';
    foreach ($red as $r) {
        $url = get_permalink($r['o']);
        if ($l !== 'bs') { $x = function_exists('abu_objekt_url') ? abu_objekt_url($r['o']->ID, $l) : ''; $url = $x ? $x : add_query_arg('lang', $l, $url); }
        $h .= '<tr><td><a href="' . esc_url($url) . '">' . esc_html($r['o']->post_title) . '</a></td><td>' . esc_html($r['mj']) . '</td><td>' . (int) $r['kap'] . '</td>';
        foreach ($stupci as $s) { $h .= '<td>' . esc_html(in_array($s, $r['op'], true) ? $t[9] : $t[10]) . '</td>'; }
        $h .= '<td>' . (int) $r['min'] . '</td></tr>';
    }
    return $h . '</tbody></table></div>';
});

/** llms.txt: dodaj vodiče na engleskom i njemačkom, usporedbu i promet (nakon postojećeg ispisa). */
add_filter('abu_llms_dodatak', function ($o) {
    foreach (array('en/guide' => 'Guides in English', 'de/reisefuehrer' => 'Reiseführer auf Deutsch') as $put => $naslov) {
        $r = get_page_by_path($put);
        if (!$r) { continue; }
        $o .= "\n## " . $naslov . "\n";
        foreach (get_pages(array('parent' => $r->ID, 'sort_column' => 'post_title', 'post_status' => 'publish')) as $p) {
            $o .= '- [' . $p->post_title . '](' . get_permalink($p) . '): ' . wp_strip_all_tags($p->post_excerpt) . "\n";
        }
    }
    $o .= "\n## Usporedba i promet\n";
    foreach (array('usporedba-smjestaja', 'en/compare-accommodation', 'de/unterkuenfte-vergleich', 'promet-granice', 'en/traffic-border-crossings', 'de/verkehr-grenzuebergaenge') as $s) {
        $p = get_page_by_path($s);
        if ($p && $p->post_status === 'publish') { $o .= '- [' . $p->post_title . '](' . get_permalink($p) . '): ' . wp_strip_all_tags($p->post_excerpt) . "\n"; }
    }
    return $o;
});
