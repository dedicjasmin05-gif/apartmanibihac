<?php
/**
 * Česta pitanja: /cesta-pitanja/ + tematske podstranice iz pitanja/*.json,
 * FAQPage schema za Google, /llms-full.txt sa svim pitanjima za AI pretraživače.
 * Stranice se same osvježe kad se promijeni bilo koja JSON datoteka.
 */
if (!defined('ABSPATH')) { exit; }

function abu_pitanja_teme() {
    return array(
        '01-bihac-opcenito' => array('bihac-opcenito', 'Bihać općenito', 'Gdje je Bihać, što ga čini posebnim i kada doći.'),
        '02-kako-doci' => array('kako-doci-u-bihac', 'Kako doći u Bihać', 'Aerodromi, vožnja autom, autobusi i parking.'),
        '03-granica-dokumenti' => array('granica-i-dokumenti', 'Granica i dokumenti', 'Granični prijelazi, dokumenti, osiguranje auta.'),
        '04-smjestaj-bihac' => array('smjestaj-u-bihacu', 'Smještaj u Bihaću', 'Apartmani u gradu ili kuća na Uni, za koga je što.'),
        '05-rezervacija-placanje' => array('rezervacija-i-placanje', 'Rezervacija i plaćanje', 'Upit, potvrda, kapara i plaćanje kod nas.'),
        '06-vikendice-na-uni' => array('vikendice-na-uni', 'Kuće i vikendice uz Unu', 'Lohovo, Pritoka i Grmuša: odmor na samoj rijeci.'),
        '07-rijeka-una' => array('rijeka-una', 'Rijeka Una', 'Izvor, tok, boja vode i slapovi.'),
        '08-np-una' => array('nacionalni-park-una', 'Nacionalni park Una', 'Ulazi, lokacije, karte i pravila.'),
        '09-strbacki-buk' => array('strbacki-buk', 'Štrbački buk', 'Najveći slap na Uni: put, vidikovci, kupanje.'),
        '10-martin-brod-izleti-np' => array('martin-brod-i-okolica', 'Martin Brod, Kulen Vakuf i okolica', 'Jednodnevni izlet u južni dio parka.'),
        '11-rafting' => array('rafting-pitanja', 'Rafting na Uni', 'Dionice, dob, sigurnost i sezona.'),
        '12-kajak-kanu-aktivnosti' => array('aktivni-odmor', 'Kajak, bicikl i planinarenje', 'Aktivni odmor oko Bihaća.'),
        '13-ribolov' => array('ribolov-na-uni', 'Ribolov na Uni', 'Vrste riba, dozvole i sezona.'),
        '14-kupanje-una' => array('kupanje-u-uni', 'Kupanje u Uni', 'Gdje se kupati, temperatura vode, sigurnost.'),
        '15-plitvice-iz-bihaca' => array('plitvice-iz-bihaca', 'Plitvička jezera iz Bihaća', 'Udaljenost, ulazi i plan izleta.'),
        '16-hrana-restorani' => array('hrana-i-restorani', 'Hrana i restorani', 'Što jesti u Bihaću i koliko košta.'),
        '17-nocni-zivot-kafici' => array('izlasci-u-bihacu', 'Izlasci i kafići', 'Noćni život, terase i ljetni događaji.'),
        '18-povijest-znamenitosti' => array('povijest-i-znamenitosti', 'Povijest i znamenitosti', 'Kapetanova kula, Fethija, Japodi i stari grad.'),
        '19-izleti-okolica' => array('izleti-iz-bihaca', 'Izleti iz Bihaća', 'Ostrožac, Cazin, Krupa, Rastoke, more.'),
        '20-obitelji-djeca' => array('odmor-s-djecom', 'Odmor s djecom', 'Što raditi s djecom u Bihaću i uz Unu.'),
        '21-kucni-ljubimci' => array('putovanje-s-ljubimcem', 'Putovanje s ljubimcem', 'Ulazak u BiH s psom, smještaj i povratak u EU.'),
        '22-novac-cijene' => array('novac-i-cijene', 'Novac i cijene', 'Konvertibilna marka, kartice, roaming.'),
        '23-vrijeme-sezone' => array('vrijeme-i-sezone', 'Vrijeme i sezone', 'Temperature po mjesecima i kada doći.'),
        '24-sigurnost-zdravlje' => array('sigurnost-i-zdravlje', 'Sigurnost i zdravlje', 'Hitni brojevi, bolnica, mine, voda.'),
        '25-prakticne-info' => array('prakticne-informacije', 'Praktične informacije', 'Utičnice, trgovine, praznici, jezik.'),
    );
}

function abu_pitanja_ucitaj($kljuc) {
    $f = __DIR__ . '/pitanja/' . $kljuc . '.json';
    if (!is_readable($f)) { return array(); }
    $d = json_decode((string) file_get_contents($f), true);
    return is_array($d) ? array_values(array_filter($d, function ($x) { return !empty($x['q']) && !empty($x['a']); })) : array();
}

function abu_pitanja_html($lista) {
    $h = '';
    foreach ($lista as $x) {
        $h .= '<h3>' . esc_html($x['q']) . "</h3>\n<p>" . esc_html($x['a']) . "</p>\n";
    }
    return $h;
}

/** Osvježi stranice kad se promijeni sadržaj. */
add_action('init', function () {
    $teme = abu_pitanja_teme();
    $pot = '';
    foreach (array_keys($teme) as $k) { $f = __DIR__ . '/pitanja/' . $k . '.json'; $pot .= is_readable($f) ? md5_file($f) : '-'; }
    $ver = md5($pot . ABU_VER);
    if (get_option('abu_pitanja_ver') === $ver || get_transient('abu_pitanja_lock')) { return; }
    set_transient('abu_pitanja_lock', 1, 300);

    $roditelj = get_page_by_path('cesta-pitanja');
    $rid = $roditelj ? $roditelj->ID : wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'cesta-pitanja', 'post_title' => 'Česta pitanja o Bihaću i smještaju'));
    $ukupno = 0;
    $popis = '';
    foreach ($teme as $k => $t) {
        $lista = abu_pitanja_ucitaj($k);
        $p = get_page_by_path('cesta-pitanja/' . $t[0]);
        if (!$lista) { if ($p) { wp_update_post(array('ID' => $p->ID, 'post_status' => 'draft')); } continue; }
        $ukupno += count($lista);
        $sadrzaj = '<p class="lead">' . esc_html($t[2]) . ' ' . count($lista) . ' pitanja i odgovora za goste Bihaća i rijeke Une.</p>' . "\n"
            . abu_pitanja_html($lista)
            . '<p><a href="/cesta-pitanja/">Sva česta pitanja</a> · Smještaj u Bihaću i uz Unu: pošaljite upit na sajtu ili pišite na <a href="https://wa.me/38761591320">WhatsApp</a>.</p>';
        $podaci = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $rid, 'post_name' => $t[0],
            'post_title' => $t[1] . ': česta pitanja', 'post_excerpt' => $t[2], 'post_content' => $sadrzaj, 'menu_order' => (int) $k);
        if ($p) { $podaci['ID'] = $p->ID; wp_update_post($podaci); $id = $p->ID; } else { $id = wp_insert_post($podaci); }
        update_post_meta($id, '_abu_pitanja', $k);
        $popis .= '<li><a href="' . esc_url(get_permalink($id)) . '"><strong>' . esc_html($t[1]) . '</strong></a> (' . count($lista) . '): ' . esc_html($t[2]) . "</li>\n";
    }
    wp_update_post(array('ID' => $rid, 'post_status' => 'publish', 'post_excerpt' => 'Odgovori na ' . $ukupno . ' pitanja o Bihaću, rijeci Uni, izletima i smještaju.',
        'post_content' => '<p class="lead">Sve što gosti pitaju prije dolaska u Bihać: kako doći, gdje spavati, Nacionalni park Una, rafting, Plitvice, hrana, novac i praktične informacije. Ukupno ' . $ukupno . ' pitanja i odgovora, podijeljenih po temama.</p>' . "\n<ul>\n" . $popis . "</ul>\n"
            . '<p>Niste našli odgovor? Pišite nam na <a href="https://wa.me/38761591320">WhatsApp ili Viber +387 61 591 320</a> ili na apartmaniubihacu@gmail.com.</p>'));
    update_post_meta($rid, '_abu_pitanja', 'sve');

    // Testni članci iz stare teme ne idu javno
    foreach (array('zdravo-svijete', 'hello-world-2', 'link-post-format', 'standar-post-type', 'gallery-post-format-type') as $s) {
        $x = get_posts(array('name' => $s, 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1));
        if ($x) { wp_update_post(array('ID' => $x[0]->ID, 'post_status' => 'draft')); }
    }
    update_option('abu_pitanja_ver', $ver);
    delete_transient('abu_pitanja_lock');
}, 35);

/** FAQPage schema. */
add_action('wp_head', function () {
    if (!is_page()) { return; }
    $k = get_post_meta(get_queried_object_id(), '_abu_pitanja', true);
    if (!$k || $k === 'sve') { return; }
    $lista = abu_pitanja_ucitaj($k);
    if (!$lista) { return; }
    $d = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array());
    foreach ($lista as $x) { $d['mainEntity'][] = array('@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $x['a'])); }
    echo '<script type="application/ld+json">' . wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 4);

/** /llms-full.txt: sva pitanja i odgovori kao čisti tekst. */
add_action('init', function () {
    $path = isset($_SERVER['REQUEST_URI']) ? strtok(wp_unslash($_SERVER['REQUEST_URI']), '?') : '';
    if ($path !== '/llms-full.txt') { return; }
    header('Content-Type: text/plain; charset=utf-8');
    echo "# Apartmani i vikendice Bihać (apartmanibihac.com): česta pitanja\n\n> Smještaj u Bihaću i uz rijeku Unu, BiH. 19 objekata, od 25 do 100 € po noći za cijeli objekt, upit na sajtu, odgovor u 12 sati, kapara 30 %. WhatsApp/Viber +387 61 591 320, apartmaniubihacu@gmail.com.\n\n";
    foreach (abu_pitanja_teme() as $k => $t) {
        $lista = abu_pitanja_ucitaj($k);
        if (!$lista) { continue; }
        echo '## ' . $t[1] . ' (' . home_url('/cesta-pitanja/' . $t[0] . '/') . ")\n\n";
        foreach ($lista as $x) { echo '### ' . $x['q'] . "\n" . $x['a'] . "\n\n"; }
    }
    exit;
}, 1);
