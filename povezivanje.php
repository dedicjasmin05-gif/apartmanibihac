<?php
/**
 * Tematsko povezivanje: na kraju članka i stranice teme s pitanjima prikazuju se 3 stvarno povezana objekta.
 * Objekti se biraju iz stvarnih podataka (mjesto, oprema, kapacitet), ne iz teksta. Sadržaj u bazi ostaje isti.
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

/** Profil => [opis odabira]. Prvo se uzimaju objekti iz 'prvo', ostatak do 3 iz 'dopuna'. */
function abu_pov_profili() {
    return array(
        'grad'   => array('prvo' => array('mjesto' => 'Bihać')),
        'rijeka' => array('prvo' => array('oprema' => 'izlaz-na-unu')),
        'rafting'=> array('prvo' => array('mjesto' => 'Grmuša'), 'dopuna' => array('oprema' => 'izlaz-na-unu')),
        'park'   => array('prvo' => array('mjesto' => 'Lohovo'), 'dopuna' => array('oprema' => 'izlaz-na-unu')),
        'bazen'  => array('prvo' => array('oprema' => 'bazen'), 'dopuna' => array('oprema' => 'izlaz-na-unu')),
        'grupe'  => array('prvo' => array('min' => 9)),
        'obitelj'=> array('prvo' => array('min' => 6)),
        'mix'    => array('prvo' => array('mjesto' => 'Bihać'), 'dopuna' => array('oprema' => 'izlaz-na-unu'), 'po' => 1),
        // Psi i mačke su na upit u svim objektima: isti odabir kao 'mix', uz napomenu ispod naslova.
        'ljubimci' => array('prvo' => array('mjesto' => 'Bihać'), 'dopuna' => array('oprema' => 'izlaz-na-unu'), 'po' => 1),
    );
}

function abu_pov_naslovi() {
    return array(
        'grad'    => array('bs' => 'Apartmani u Bihaću', 'en' => 'Apartments in Bihać', 'de' => 'Ferienwohnungen in Bihać'),
        'rijeka'  => array('bs' => 'Kuće s izlazom na Unu', 'en' => 'Houses with access to the Una', 'de' => 'Häuser mit Zugang zur Una'),
        'rafting' => array('bs' => 'Smještaj za rafting na Uni', 'en' => 'Where to stay for rafting on the Una', 'de' => 'Unterkunft für Rafting auf der Una'),
        'park'    => array('bs' => 'Smještaj za izlet u Nacionalni park Una', 'en' => 'Where to stay for Una National Park', 'de' => 'Unterkunft für den Nationalpark Una'),
        'bazen'   => array('bs' => 'Kuće s bazenom i uz Unu', 'en' => 'Houses with a pool and on the Una', 'de' => 'Häuser mit Pool und an der Una'),
        'grupe'   => array('bs' => 'Smještaj za grupe', 'en' => 'Accommodation for groups', 'de' => 'Unterkunft für Gruppen'),
        'obitelj' => array('bs' => 'Smještaj za obitelji', 'en' => 'Accommodation for families', 'de' => 'Unterkunft für Familien'),
        'mix'     => array('bs' => 'Smještaj u Bihaću i uz Unu', 'en' => 'Stays in Bihać and on the Una', 'de' => 'Unterkünfte in Bihać und an der Una'),
        'ljubimci'=> array('bs' => 'Smještaj s psom ili mačkom', 'en' => 'Stays with a dog or cat', 'de' => 'Unterkunft mit Hund oder Katze'),
    );
}

/** Kratka napomena ispod naslova bloka (samo gdje je potrebna). */
function abu_pov_napomene() {
    return array(
        'ljubimci' => array(
            'bs' => 'Psi i mačke su u svim objektima na upit. U upitu napišite vrstu i veličinu ljubimca, a vlasnik potvrđuje uz odgovor na upit, zajedno s eventualnom doplatom.',
            'en' => 'Dogs and cats are welcome on request in all properties. Mention the type and size of your pet in the enquiry; the owner confirms in the reply, together with any extra charge.',
            'de' => 'Hunde und Katzen sind in allen Unterkünften auf Anfrage möglich. Nennen Sie Art und Größe des Tieres in der Anfrage; der Vermieter bestätigt in der Antwort, zusammen mit einem eventuellen Aufpreis.'),
    );
}

/** Tema pitanja (ključ iz pitanja.php) => profil. */
function abu_pov_teme() {
    return array(
        '01-bihac-opcenito' => 'mix', '02-kako-doci' => 'grad', '03-granica-dokumenti' => 'grad', '04-smjestaj-bihac' => 'mix',
        '05-rezervacija-placanje' => 'mix', '06-vikendice-na-uni' => 'rijeka', '07-rijeka-una' => 'rijeka', '08-np-una' => 'park',
        '09-strbacki-buk' => 'park', '10-martin-brod-izleti-np' => 'park', '11-rafting' => 'rafting', '12-kajak-kanu-aktivnosti' => 'rijeka',
        '13-ribolov' => 'rijeka', '14-kupanje-una' => 'rijeka', '15-plitvice-iz-bihaca' => 'grad', '16-hrana-restorani' => 'grad',
        '17-nocni-zivot-kafici' => 'grad', '18-povijest-znamenitosti' => 'grad', '19-izleti-okolica' => 'mix', '20-obitelji-djeca' => 'obitelj',
        '21-kucni-ljubimci' => 'ljubimci',
        '22-novac-cijene' => 'mix', '23-vrijeme-sezone' => 'mix', '24-sigurnost-zdravlje' => 'mix', '25-prakticne-info' => 'mix',
    );
}

/** Članak => profil prema adresi (slug). Prvo pravilo koje odgovara. */
function abu_pov_clanak($slug) {
    $pravila = array(
        'sa-psom|ljubim|mack' => 'ljubimci',
        'team-building|za-grupe|50-ljudi|sale-za' => 'grupe',
        'bazen' => 'bazen',
        'rafting' => 'rafting',
        'strbacki|nacionalni-park|martin-brod' => 'park',
        'japodski' => 'rijeka', // Japodski otoci su na Uni, ali izvan Nacionalnog parka
        'djec|obitelj' => 'obitelj',
        'centar-ili|gdje-odsjesti|za-dvoje' => 'mix', // usporedba grada i rijeke
        'restoran|cevapi|rucak|izaci|kafa|setnja|centar|duzi-boravak|kisa|plitvic|kako-doci' => 'grad',
        'kupanje|uz-unu|vikendica' => 'rijeka',
    );
    foreach ($pravila as $uzorak => $profil) {
        if (preg_match('~' . $uzorak . '~', $slug)) { return $profil; }
    }
    return 'mix';
}

/** Svi objekti s podacima potrebnim za odabir (jednom po zahtjevu). */
function abu_pov_objekti() {
    static $o = null;
    if ($o !== null) { return $o; }
    $o = array();
    foreach (get_posts(array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'menu_order title', 'order' => 'ASC')) as $p) {
        $op = wp_get_post_terms($p->ID, 'oprema', array('fields' => 'slugs'));
        $o[] = array('id' => $p->ID, 'mjesto' => (string) get_post_meta($p->ID, '_abu_mjesto', true), 'kap' => (int) get_post_meta($p->ID, '_abu_kapacitet', true),
            'oprema' => is_wp_error($op) ? array() : (array) $op, 'url' => get_permalink($p));
    }
    return $o;
}

function abu_pov_odgovara($x, $uvjet) {
    if (isset($uvjet['mjesto']) && $x['mjesto'] !== $uvjet['mjesto']) { return false; }
    if (isset($uvjet['oprema']) && !in_array($uvjet['oprema'], $x['oprema'], true)) { return false; }
    if (isset($uvjet['min']) && $x['kap'] < $uvjet['min']) { return false; }
    return true;
}

/**
 * Odabir 3 objekta za profil. Redoslijed unutar skupine ovisi o stranici (sjeme), da različite stranice
 * linkaju različite objekte. Objekti već linkani u tekstu preskaču se.
 */
function abu_pov_odaberi($profil, $sjeme, $objekti, $sadrzaj = '', $broj = 3) {
    $pr = abu_pov_profili();
    if (!isset($pr[$profil])) { return array(); }
    $p = $pr[$profil];
    $ostali = array_values(array_filter($objekti, function ($x) use ($sadrzaj) { return $sadrzaj === '' || strpos($sadrzaj, untrailingslashit($x['url'])) === false; }));
    $mijesaj = function ($lista) use ($sjeme) {
        usort($lista, function ($a, $b) use ($sjeme) { return crc32($sjeme . '|' . $a['id']) <=> crc32($sjeme . '|' . $b['id']); });
        return $lista;
    };
    $prvo = $mijesaj(array_values(array_filter($ostali, function ($x) use ($p) { return abu_pov_odgovara($x, $p['prvo']); })));
    $dopuna = isset($p['dopuna']) ? $mijesaj(array_values(array_filter($ostali, function ($x) use ($p) { return abu_pov_odgovara($x, $p['dopuna']) && !abu_pov_odgovara($x, $p['prvo']); }))) : array();
    $izbor = isset($p['po']) ? array_merge(array_slice($prvo, 0, $p['po']), array_slice($dopuna, 0, $broj - $p['po'])) : array_merge($prvo, $dopuna);
    if (count($izbor) < $broj && isset($p['po'])) { $izbor = array_merge($izbor, array_slice($prvo, $p['po'])); }
    return array_slice($izbor, 0, $broj);
}

/** Profil i jezik za tekuću stranicu, ili null ako blok ne ide. */
function abu_pov_tekuca() {
    if (!is_singular()) { return null; }
    $id = get_queried_object_id();
    if (is_singular('post')) {
        $profil = abu_pov_clanak((string) get_post_field('post_name', $id));
        return $profil ? array($profil, 'bs') : null;
    }
    if (is_page()) {
        $k = get_post_meta($id, '_abu_pitanja', true);
        $t = abu_pov_teme();
        if ($k && isset($t[$k])) { return array($t[$k], get_post_meta($id, '_abu_pitanja_lang', true) ?: 'bs'); }
    }
    return null;
}

function abu_pov_blok($profil, $l, $izbor) {
    if (!$izbor) { return ''; }
    $n = abu_pov_naslovi();
    $sve = array('bs' => array('/smjestaj-bihac/', 'Sav smještaj'), 'en' => array('/en/accommodation-bihac/', 'All accommodation'), 'de' => array('/de/unterkunft-bihac/', 'Alle Unterkünfte'));
    $l = isset($sve[$l]) ? $l : 'bs';
    $nap = abu_pov_napomene();
    $h = '<section class="abu-povezano" style="margin:36px 0 8px"><h2>' . esc_html($n[$profil][$l]) . '</h2>'
        . (isset($nap[$profil][$l]) ? '<p>' . esc_html($nap[$profil][$l]) . '</p>' : '') . '<div class="grid grid--cards">';
    foreach ($izbor as $x) {
        if (function_exists('ab_kartica')) { $h .= ab_kartica($x['id'], null, $l === 'bs' ? array() : array('lang' => $l)); continue; }
        $h .= '<p><a href="' . esc_url($x['url']) . '">' . esc_html(get_the_title($x['id'])) . '</a> · ' . esc_html($x['mjesto']) . '</p>';
    }
    $h .= '</div><p style="margin-top:14px"><a class="link" href="' . esc_url(home_url($sve[$l][0])) . '">' . esc_html($sve[$l][1]) . ' →</a></p></section>';
    return $h;
}

add_filter('the_content', function ($c) {
    if (is_admin() || !in_the_loop() || !is_main_query() || is_feed() || strpos($c, 'abu-povezano') !== false) { return $c; }
    $t = abu_pov_tekuca();
    if (!$t) { return $c; }
    return $c . abu_pov_blok($t[0], $t[1], abu_pov_odaberi($t[0], (string) get_queried_object_id(), abu_pov_objekti(), $c));
}, 40);
