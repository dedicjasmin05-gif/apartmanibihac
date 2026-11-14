<?php
/**
 * Udaljenosti na stranici svakog objekta (bs, en, de): mjesto objekta i udaljenosti od centra Bihaća do glavnih mjesta.
 * Brojke su iste kao na /brze-cinjenice/ (izvori: česta pitanja, promet-granice, vodiči). Bez izmišljanja: za objekte
 * „Bihać i okolica“ ne navodi se udaljenost objekta, samo udaljenosti od centra.
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

function abu_ud_t($l) {
    $t = array(
        'bs' => array('h' => 'Udaljenosti', 'od' => 'Od centra Bihaća:', 'mj' => array('Bihać' => 'Objekt je u Bihaću; do Une, Kapetanove kule i restorana stižete pješice ili za nekoliko minuta vožnje.', 'Lohovo' => 'Objekt je u Lohovu, oko 10 km (15 minuta vožnje) južno od centra Bihaća; tu je i najbliži ulaz u Nacionalni park Una.', 'Grmuša' => 'Objekt je u Grmuši, oko 20 km sjeveroistočno od Bihaća, jednom od glavnih mjesta za rafting na Uni.', 'Pritoka' => 'Objekt je u Pritoci, uz Unu na rubu Bihaća, nekoliko minuta vožnje od centra.'),
            'r' => array('Štrbački buk (NP Una): 30 do 35 km, oko 45 minuta do sat vožnje', 'Plitvička jezera: oko 35 km, 35 do 40 minuta', 'Granični prelaz Izačić (prema Hrvatskoj): oko 12 km, 15 minuta')),
        'en' => array('h' => 'Distances', 'od' => 'From Bihać town centre:', 'mj' => array('Bihać' => 'The property is in Bihać; the Una, the Captain’s Tower and restaurants are within walking distance or a few minutes’ drive.', 'Lohovo' => 'The property is in Lohovo, about 10 km (15 minutes’ drive) south of Bihać centre, next to the nearest entrance to Una National Park.', 'Grmuša' => 'The property is in Grmuša, about 20 km north-east of Bihać, one of the main rafting spots on the Una.', 'Pritoka' => 'The property is in Pritoka, on the Una at the edge of Bihać, a few minutes’ drive from the centre.'),
            'r' => array('Štrbački buk (Una National Park): 30 to 35 km, about 45 minutes to an hour', 'Plitvice Lakes: about 35 km, 35 to 40 minutes', 'Izačić border crossing (to Croatia): about 12 km, 15 minutes')),
        'de' => array('h' => 'Entfernungen', 'od' => 'Vom Zentrum von Bihać:', 'mj' => array('Bihać' => 'Die Unterkunft liegt in Bihać; Una, Kapitänsturm und Restaurants erreichen Sie zu Fuß oder in wenigen Autominuten.', 'Lohovo' => 'Die Unterkunft liegt in Lohovo, etwa 10 km (15 Autominuten) südlich des Zentrums von Bihać, beim nächsten Eingang zum Nationalpark Una.', 'Grmuša' => 'Die Unterkunft liegt in Grmuša, etwa 20 km nordöstlich von Bihać, einem der wichtigsten Rafting-Orte an der Una.', 'Pritoka' => 'Die Unterkunft liegt in Pritoka an der Una am Stadtrand von Bihać, wenige Autominuten vom Zentrum.'),
            'r' => array('Štrbački buk (Nationalpark Una): 30 bis 35 km, etwa 45 Minuten bis eine Stunde', 'Plitvicer Seen: etwa 35 km, 35 bis 40 Minuten', 'Grenzübergang Izačić (nach Kroatien): etwa 12 km, 15 Minuten')),
    );
    return $t[$l];
}

function abu_ud_blok($oid, $l) {
    $T = abu_ud_t(in_array($l, array('en', 'de'), true) ? $l : 'bs');
    $mj = (string) get_post_meta($oid, '_abu_mjesto', true);
    $h = '<h2>' . esc_html($T['h']) . '</h2>';
    if (isset($T['mj'][$mj])) { $h .= '<p>' . esc_html($T['mj'][$mj]) . '</p>'; }
    $h .= '<p>' . esc_html($T['od']) . '</p><ul>';
    foreach ($T['r'] as $r) { $h .= '<li>' . esc_html($r) . '</li>'; }
    return '<div class="abu-udaljenosti">' . $h . '</ul></div>';
}

add_filter('the_content', function ($c) {
    if (is_admin() || !in_the_loop() || !is_main_query() || is_feed() || strpos($c, 'abu-udaljenosti') !== false) { return $c; }
    if (is_singular('objekt')) { return $c . abu_ud_blok(get_the_ID(), 'bs'); }
    if (is_page() && function_exists('abu_oj_tekuca') && ($x = abu_oj_tekuca())) {
        // na EN/DE stranici objekta: prije naslova kalendara i obrasca upita
        $b = abu_ud_blok($x[0], $x[1]);
        $i = strpos($c, '<h2 id="upit"');
        if ($i === false) { $i = strpos($c, '<div class="abu-kal'); }
        return $i === false ? $c . $b : substr($c, 0, $i) . $b . substr($c, $i);
    }
    return $c;
}, 45);
