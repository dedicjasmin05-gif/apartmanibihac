<?php
/**
 * Vodič: restorani, izlasci, izleti i rafting iz sadrzaj/*.json, na bosanskom, engleskom i njemačkom.
 * Svaka stranica ima fotografije (galerija), kartu sa svim mjestima, filtere, kontakte i schema.org.
 * Fotografije se preuzimaju u medije alatom alati/slike.php (cron), mapiranje u opciji abu_vodic_slike.
 */
if (!defined('ABSPATH')) { exit; }

/** Vodiči: ključ => datoteka, polje u datoteci (za team building), stranice po jeziku, schema tip. */
function abu_vodic_def() {
    return array(
        'restorani' => array('dat' => 'restorani.json', 'polje' => '', 'schema' => 'Restaurant', 'kat' => 'restorani',
            'put' => array('bs' => 'restorani-bihac', 'en' => 'en/restaurants-bihac', 'de' => 'de/restaurants-bihac'),
            'h1' => array('bs' => 'Restorani u Bihaću: gdje jesti uz Unu', 'en' => 'Restaurants in Bihać: where to eat by the Una', 'de' => 'Restaurants in Bihać: Essen an der Una'),
            'title' => array('bs' => 'Restorani Bihać: riba iz Une, ćevapi, pizza | vodič s kartom', 'en' => 'Best restaurants in Bihać: river fish, ćevapi, pizza', 'de' => 'Restaurants in Bihać: Una-Fisch, Ćevapi, Pizza'),
            'uvod' => array(
                'bs' => 'Riba iz Une, travnički ćevapi, roštilj, pizza i kolači: najbolji restorani u Bihaću i uz rijeku, s fotografijama, kartom, telefonima i preporukama što naručiti. Popis redovno dopunjavamo prema iskustvu naših gostiju.',
                'en' => 'River fish from the Una, ćevapi, grill, pizza and cakes: the best restaurants in Bihać and along the river, with photos, a map, phone numbers and what to order. We keep the list updated based on what our guests tell us.',
                'de' => 'Fisch aus der Una, Ćevapi, Grill, Pizza und Kuchen: die besten Restaurants in Bihać und am Fluss, mit Fotos, Karte, Telefonnummern und Empfehlungen, was man bestellen sollte. Die Liste ergänzen wir laufend nach den Erfahrungen unserer Gäste.')),
        'izlasci' => array('dat' => 'izlasci.json', 'polje' => '', 'schema' => 'BarOrPub', 'kat' => 'izlasci',
            'put' => array('bs' => 'nocni-zivot-bihac', 'en' => 'en/nightlife-bihac', 'de' => 'de/nachtleben-bihac'),
            'h1' => array('bs' => 'Noćni život u Bihaću: gdje izaći', 'en' => 'Nightlife in Bihać: where to go out', 'de' => 'Nachtleben in Bihać: wo man ausgeht'),
            'title' => array('bs' => 'Gdje izaći u Bihaću: barovi na Uni, terase i klubovi', 'en' => 'Bihać nightlife: riverside bars, terraces and clubs', 'de' => 'Nachtleben Bihać: Bars an der Una, Terrassen und Clubs'),
            'uvod' => array(
                'bs' => 'Večer u Bihaću počinje na terasi uz Unu, a vikendom se nastavlja u klubovima. Kafići, barovi i klubovi s fotografijama, kartom i kontaktima. Grad je malen, pa većinu obiđete pješice iz našeg smještaja u centru.',
                'en' => 'An evening in Bihać starts on a terrace by the Una and carries on in the clubs at the weekend. Cafés, bars and clubs with photos, a map and contacts. The town is small, so you can walk to most of them from our apartments in the centre.',
                'de' => 'Ein Abend in Bihać beginnt auf einer Terrasse an der Una und geht am Wochenende in den Clubs weiter. Cafés, Bars und Clubs mit Fotos, Karte und Kontakten. Die Stadt ist klein, die meisten Lokale erreichen Sie von unseren Wohnungen im Zentrum zu Fuß.')),
        'izleti' => array('dat' => 'izleti.json', 'polje' => '', 'schema' => 'TouristAttraction', 'kat' => 'izleti',
            'put' => array('bs' => 'izleti-bihac', 'en' => 'en/day-trips-bihac', 'de' => 'de/ausfluege-bihac'),
            'h1' => array('bs' => 'Izleti iz Bihaća: slapovi, tvrđave, Plitvice', 'en' => 'Day trips from Bihać: waterfalls, fortresses, Plitvice', 'de' => 'Ausflüge ab Bihać: Wasserfälle, Burgen, Plitvice'),
            'title' => array('bs' => 'Izleti iz Bihaća: Štrbački buk, Martin Brod, Plitvice', 'en' => 'Day trips from Bihać: Štrbački buk, Martin Brod, Plitvice', 'de' => 'Ausflüge ab Bihać: Štrbački buk, Martin Brod, Plitvice'),
            'uvod' => array(
                'bs' => 'Nacionalni park Una, Štrbački buk, Martin Brod, srednjovjekovne tvrđave, Plitvička jezera i desetine mjesta uz rijeku. Za svako mjesto: udaljenost od Bihaća, kako doći, ulaznice, fotografije i karta.',
                'en' => 'Una National Park, Štrbački buk, Martin Brod, medieval fortresses, Plitvice Lakes and dozens of places along the river. For each one: distance from Bihać, how to get there, tickets, photos and a map.',
                'de' => 'Nationalpark Una, Štrbački buk, Martin Brod, mittelalterliche Burgen, die Plitvicer Seen und Dutzende Orte am Fluss. Zu jedem Ziel: Entfernung von Bihać, Anfahrt, Eintritt, Fotos und Karte.')),
        'rafting' => array('dat' => 'teambuilding.json', 'polje' => 'rafting', 'schema' => 'SportsActivityLocation', 'kat' => 'bihac',
            'put' => array('bs' => 'rafting-na-uni', 'en' => 'en/rafting-una', 'de' => 'de/rafting-una'),
            'h1' => array('bs' => 'Rafting na Uni kod Bihaća', 'en' => 'Rafting on the Una river near Bihać', 'de' => 'Rafting auf der Una bei Bihać'),
            'title' => array('bs' => 'Rafting na Uni u Bihaću: kampovi, cijene i smještaj', 'en' => 'Una river rafting in Bihać: camps, prices, places to stay', 'de' => 'Rafting auf der Una in Bihać: Camps, Preise, Unterkunft'),
            'uvod' => array(
                'bs' => 'Una je jedna od najljepših rijeka za rafting u Europi: bistra, zelena, s brzacima za početnike i iskusne. Na prvom mjestu preporučujemo Una Olympic Rafting iz Grmuše, a ispod su i ostali rafting kampovi uz Unu. Smještaj i rafting dogovaramo u istom upitu.',
                'en' => 'The Una is one of the most beautiful rafting rivers in Europe: clear, green, with rapids for beginners and experienced paddlers. Our first pick is Una Olympic Rafting in Grmuša; the other rafting camps on the Una are listed below. We arrange accommodation and rafting in one enquiry.',
                'de' => 'Die Una ist einer der schönsten Raftingflüsse Europas: klar, grün, mit Stromschnellen für Anfänger und Erfahrene. Unsere erste Empfehlung ist Una Olympic Rafting in Grmuša, darunter finden Sie die anderen Raftingcamps an der Una. Unterkunft und Rafting organisieren wir in einer Anfrage.')),
    );
}

function abu_vodic_podaci($kljuc) {
    static $c = array();
    if (isset($c[$kljuc])) { return $c[$kljuc]; }
    $d = abu_vodic_def();
    if (!isset($d[$kljuc])) { return array(); }
    $f = __DIR__ . '/sadrzaj/' . $d[$kljuc]['dat'];
    $x = is_readable($f) ? json_decode((string) file_get_contents($f), true) : array();
    if ($d[$kljuc]['polje']) { $x = isset($x[$d[$kljuc]['polje']]) ? $x[$d[$kljuc]['polje']] : array(); }
    return $c[$kljuc] = is_array($x) ? array_values($x) : array();
}

/** Sve fotografije iz svih JSON datoteka (za uvoz): url => [ime, izvor, licenca, autor]. */
function abu_vodic_sve_slike() {
    $out = array();
    $dir = __DIR__ . '/sadrzaj/';
    foreach (glob($dir . '*.json') as $f) {
        $x = json_decode((string) file_get_contents($f), true);
        if (!is_array($x)) { continue; }
        $liste = isset($x[0]) ? array($x) : array_values($x);
        foreach ($liste as $lista) {
            if (!is_array($lista)) { continue; }
            foreach ($lista as $e) {
                if (empty($e['slike']) || !is_array($e['slike'])) { continue; }
                foreach ($e['slike'] as $s) {
                    if (!empty($s['url'])) { $out[$s['url']] = array(isset($e['ime']) ? $e['ime'] : '', isset($s['izvor']) ? $s['izvor'] : '', isset($s['licenca']) ? $s['licenca'] : '', isset($s['autor']) ? $s['autor'] : ''); }
                }
            }
        }
    }
    return $out;
}

/** ID-ovi uvezenih fotografija za jedno mjesto. */
function abu_vodic_slike_ids($e) {
    $map = get_option('abu_vodic_slike', array());
    $ids = array();
    foreach ((array) (isset($e['slike']) ? $e['slike'] : array()) as $s) {
        if (!empty($s['url']) && !empty($map[$s['url']]) && $map[$s['url']] > 0) { $ids[] = (int) $map[$s['url']]; }
    }
    // položene (vodoravne) fotografije prve, okomite na kraj
    $vod = array(); $okom = array();
    foreach ($ids as $i) {
        $m = wp_get_attachment_metadata($i);
        if (!empty($m['width']) && !empty($m['height']) && $m['height'] > $m['width'] * 1.05) { $okom[] = $i; } else { $vod[] = $i; }
    }
    return array_merge($vod, $okom);
}

/** Prva fotografija vodiča (za naslovnicu teme). */
function abu_vodic_naslovna($kljuc, $vel = 'large') {
    if ($kljuc === 'teambuilding') {
        $f = __DIR__ . '/sadrzaj/teambuilding.json';
        $x = is_readable($f) ? json_decode((string) file_get_contents($f), true) : array();
        $lista = array_merge(isset($x['aktivnosti']) ? $x['aktivnosti'] : array(), isset($x['rafting']) ? $x['rafting'] : array());
    } else {
        $lista = abu_vodic_podaci($kljuc);
    }
    foreach ($lista as $e) {
        $ids = abu_vodic_slike_ids($e);
        if ($ids) { return wp_get_attachment_image($ids[0], $vel, false, array('loading' => 'lazy', 'alt' => isset($e['ime']) ? $e['ime'] : '')); }
    }
    return '';
}

function abu_vodic_t($k, $l) {
    $t = array(
        'karta' => array('bs' => 'Karta', 'en' => 'Map', 'de' => 'Karte'),
        'nazovi' => array('bs' => 'Nazovi', 'en' => 'Call', 'de' => 'Anrufen'),
        'web' => array('bs' => 'Web', 'en' => 'Website', 'de' => 'Webseite'),
        'sve' => array('bs' => 'Sve', 'en' => 'All', 'de' => 'Alle'),
        'adresa' => array('bs' => 'Adresa', 'en' => 'Address', 'de' => 'Adresse'),
        'radno' => array('bs' => 'Radno vrijeme', 'en' => 'Opening hours', 'de' => 'Öffnungszeiten'),
        'cijene' => array('bs' => 'Cijene', 'en' => 'Prices', 'de' => 'Preise'),
        'probati' => array('bs' => 'Probajte', 'en' => 'Try', 'de' => 'Probieren Sie'),
        'udaljenost' => array('bs' => 'od Bihaća', 'en' => 'from Bihać', 'de' => 'von Bihać'),
        'kako' => array('bs' => 'Kako doći', 'en' => 'Getting there', 'de' => 'Anfahrt'),
        'ulaz' => array('bs' => 'Ulaznica', 'en' => 'Admission', 'de' => 'Eintritt'),
        'trajanje' => array('bs' => 'Trajanje', 'en' => 'Time needed', 'de' => 'Dauer'),
        'djeca' => array('bs' => 'Za djecu', 'en' => 'Good for kids', 'de' => 'Für Kinder'),
        'uz_rijeku' => array('bs' => 'Uz rijeku', 'en' => 'By the river', 'de' => 'Am Fluss'),
        'mjesta' => array('bs' => 'mjesta na karti', 'en' => 'places on the map', 'de' => 'Orte auf der Karte'),
        'smjestaj_h' => array('bs' => 'Smještaj u Bihaću od 25 € po noći', 'en' => 'Stay in Bihać from €25 a night', 'de' => 'Unterkunft in Bihać ab 25 € pro Nacht'),
        'smjestaj_p' => array('bs' => 'Cijeli apartman po cijeni sobe, plaćanje gotovinom pri dolasku. Pišite nam i preporučimo i gdje jesti, izaći i kamo na izlet.', 'en' => 'A whole apartment for the price of a room, pay cash on arrival. Message us and we will also tell you where to eat, go out and what to see.', 'de' => 'Eine ganze Wohnung zum Preis eines Zimmers, Barzahlung bei Ankunft. Schreiben Sie uns, wir empfehlen Ihnen auch Restaurants, Bars und Ausflüge.'),
        'pogledaj' => array('bs' => 'Pogledaj smještaj', 'en' => 'See accommodation', 'de' => 'Unterkünfte ansehen'),
        'foto' => array('bs' => 'Foto', 'en' => 'Photo', 'de' => 'Foto'),
        'preporuka' => array('bs' => 'Naša preporuka', 'en' => 'Our pick', 'de' => 'Unsere Empfehlung'),
        'novosti' => array('bs' => 'Novosti', 'en' => 'News', 'de' => 'Neuigkeiten'),
        'azurirano' => array('bs' => 'Ažurirano', 'en' => 'Updated', 'de' => 'Aktualisiert'),
    );
    return isset($t[$k][$l]) ? $t[$k][$l] : (isset($t[$k]['bs']) ? $t[$k]['bs'] : $k);
}

/** Vrsta mjesta na jeziku stranice. */
function abu_vodic_tip($tip, $l) {
    if ($l === 'bs' || !$tip) { return $tip; }
    $m = array(
        'restoran' => array('Restaurant', 'Restaurant'), 'riblji restoran' => array('Fish restaurant', 'Fischrestaurant'), 'ćevabdžinica' => array('Ćevapi grill', 'Ćevapi-Grill'),
        'pizzeria' => array('Pizzeria', 'Pizzeria'), 'slastičarna' => array('Cake shop', 'Konditorei'), 'kafić' => array('Café', 'Café'), 'bar' => array('Bar', 'Bar'), 'klub' => array('Club', 'Club'),
        'caffe na rijeci' => array('Riverside café', 'Café am Fluss'), 'buregdžinica' => array('Burek bakery', 'Burek-Bäckerei'), 'roštilj' => array('Grill', 'Grill'), 'pivnica' => array('Pub', 'Bierlokal'),
        'np una' => array('Una National Park', 'Nationalpark Una'), 'priroda' => array('Nature', 'Natur'), 'tvrđave i povijest' => array('Fortresses & history', 'Burgen & Geschichte'),
        'vjerski objekti' => array('Religious sites', 'Religiöse Stätten'), 'aktivnosti' => array('Activities', 'Aktivitäten'), 'gradovi u okolici' => array('Nearby towns', 'Städte in der Umgebung'),
        'hrvatska (plitvice i sl.)' => array('Croatia (Plitvice etc.)', 'Kroatien (Plitvice usw.)'), 'rafting' => array('Rafting', 'Rafting'),
    );
    $k = mb_strtolower(trim($tip));
    return isset($m[$k]) ? $m[$k][$l === 'en' ? 0 : 1] : $tip;
}

/* ---------- Stranice: stvaranje i povezivanje ---------- */
add_action('init', function () {
    $ver = ABU_VER . '|' . md5(implode('', array_map(function ($f) { return (string) @filemtime($f) . filesize($f); }, glob(__DIR__ . '/sadrzaj/*.json') ?: array())));
    if (get_option('abu_vodic_ver') === $ver || get_transient('abu_vodic_lock')) { return; }
    set_transient('abu_vodic_lock', 1, 120);
    foreach (abu_vodic_def() as $k => $d) {
        if (!abu_vodic_podaci($k)) { continue; }
        foreach ($d['put'] as $l => $put) { abu_vodic_stranica($put, $d['h1'][$l], $d['uvod'][$l], array('_abu_vodic' => $k, '_abu_vodic_lang' => $l)); }
    }
    update_option('abu_vodic_ver', $ver);
    delete_transient('abu_vodic_lock');
}, 37);

/** Stvori ili osvježi stranicu na putanji (roditelj en/de mora postojati). */
function abu_vodic_stranica($put, $naslov, $izvod, $meta, $sadrzaj = '') {
    $dij = explode('/', $put);
    $slug = array_pop($dij);
    $rod = 0;
    if ($dij) { $r = get_page_by_path(implode('/', $dij)); if (!$r) { return 0; } $rod = $r->ID; }
    $p = get_page_by_path($put);
    $pod = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $rod, 'post_name' => $slug, 'post_title' => $naslov, 'post_excerpt' => $izvod);
    if ($sadrzaj !== '') { $pod['post_content'] = $sadrzaj; }
    if ($p) { $pod['ID'] = $p->ID; wp_update_post($pod); $id = $p->ID; } else { $pod['post_content'] = $sadrzaj; $id = wp_insert_post($pod); }
    if ($id && !is_wp_error($id)) { foreach ($meta as $mk => $mv) { update_post_meta($id, $mk, $mv); } }
    return $id;
}

function abu_vodic_tekuci() {
    if (!is_page()) { return null; }
    $id = get_queried_object_id();
    $k = get_post_meta($id, '_abu_vodic', true);
    if (!$k || !isset(abu_vodic_def()[$k]) || !abu_vodic_podaci($k)) { return null; }
    return array($k, get_post_meta($id, '_abu_vodic_lang', true) ?: 'bs');
}

add_filter('template_include', function ($t) {
    return abu_vodic_tekuci() ? __DIR__ . '/predlosci/vodic.php' : $t;
}, 30);

add_action('wp_enqueue_scripts', function () {
    if (!abu_vodic_tekuci() && !(function_exists('abu_tb_tekuci') && abu_tb_tekuci())) { return; }
    $u = plugins_url('assets/', __FILE__);
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null, true);
    wp_enqueue_style('abu-vodic', $u . 'vodic.css', array(), ABU_VER);
    wp_enqueue_script('abu-vodic', $u . 'vodic.js', array('leaflet'), ABU_VER, true);
});

/* ---------- SEO: naslov, opis, hreflang, schema ---------- */
add_filter('pre_get_document_title', function ($t) {
    $v = abu_vodic_tekuci();
    if (!$v) { return $t; }
    $d = abu_vodic_def()[$v[0]];
    return $d['title'][$v[1]];
}, 25);

add_action('wp_head', function () {
    $v = abu_vodic_tekuci();
    if (!$v) { return; }
    list($k, $l) = $v;
    $d = abu_vodic_def()[$k];
    foreach ($d['put'] as $jl => $put) { echo '<link rel="alternate" hreflang="' . esc_attr($jl) . '" href="' . esc_url(home_url('/' . $put . '/')) . '">' . "\n"; }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/' . $d['put']['bs'] . '/')) . '">' . "\n";
    $el = array();
    foreach (abu_vodic_podaci($k) as $i => $e) {
        $o = array('@type' => $d['schema'], 'name' => $e['ime'], 'description' => wp_strip_all_tags(abu_vodic_opis($e, $l)));
        if (!empty($e['adresa'])) { $o['address'] = array('@type' => 'PostalAddress', 'streetAddress' => $e['adresa'], 'addressLocality' => 'Bihać', 'addressCountry' => 'BA'); }
        if (!empty($e['lat']) && !empty($e['lng'])) { $o['geo'] = array('@type' => 'GeoCoordinates', 'latitude' => (float) $e['lat'], 'longitude' => (float) $e['lng']); }
        if (!empty($e['telefon'])) { $o['telephone'] = $e['telefon']; }
        $same = array_values(array_filter(array(isset($e['web']) ? $e['web'] : '', isset($e['facebook']) ? $e['facebook'] : '', isset($e['instagram']) ? $e['instagram'] : '')));
        if ($same) { $o['sameAs'] = $same; }
        if (!empty($e['specijaliteti']) && $d['schema'] === 'Restaurant') { $o['servesCuisine'] = array_slice((array) $e['specijaliteti'], 0, 5); }
        $ids = abu_vodic_slike_ids($e);
        if ($ids) { $o['image'] = array_values(array_filter(array_map(function ($i) { return wp_get_attachment_image_url($i, 'large'); }, array_slice($ids, 0, 3)))); }
        $el[] = array('@type' => 'ListItem', 'position' => $i + 1, 'item' => $o);
    }
    $g = array('@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => $d['h1'][$l], 'itemListElement' => $el);
    echo '<script type="application/ld+json">' . wp_json_encode($g, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    echo '<meta name="description" content="' . esc_attr(mb_substr($d['uvod'][$l], 0, 158)) . '">' . "\n";
}, 2);

/** Opis mjesta na jeziku stranice (rezervno bosanski). */
function abu_vodic_opis($e, $l) {
    foreach (array('opis_' . $l, 'opis_bs', 'opis') as $f) { if (!empty($e[$f])) { return $e[$f]; } }
    return '';
}
function abu_vodic_kratko($e, $l) {
    if ($l === 'bs' && !empty($e['kratko_bs'])) { return $e['kratko_bs']; }
    if (!empty($e['kratko_' . $l])) { return $e['kratko_' . $l]; }
    $o = abu_vodic_opis($e, $l);
    return $o ? wp_trim_words(preg_replace('/(?<=[.!?])\s.*/s', '', $o), 16, '…') : '';
}
