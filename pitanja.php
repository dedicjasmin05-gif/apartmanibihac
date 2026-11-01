<?php
/**
 * Česta pitanja na tri jezika:
 *   bs: /cesta-pitanja/<tema>/   iz pitanja/NN-*.json
 *   en: /en/faq/<topic>/          iz pitanja/en/NN-*.json
 *   de: /de/faq/<thema>/          iz pitanja/de/NN-*.json
 * FAQPage schema, hreflang između jezika, /llms-full.txt sa svim odgovorima.
 * Stranice se same osvježe kad se promijeni bilo koja JSON datoteka.
 */
if (!defined('ABSPATH')) { exit; }

/** kljuc => array(bs: slug, naslov, opis; en: slug, naslov, opis; de: slug, naslov, opis) */
function abu_pitanja_teme_sve() {
    return array(
        '01-bihac-opcenito' => array('bihac-opcenito', 'Bihać općenito', 'Gdje je Bihać, što ga čini posebnim i kada doći.', 'bihac-general', 'Bihać: general questions', 'Where Bihać is, what makes it special and when to come.', 'bihac-allgemein', 'Bihać allgemein', 'Wo Bihać liegt, was die Stadt besonders macht und wann man kommt.'),
        '02-kako-doci' => array('kako-doci-u-bihac', 'Kako doći u Bihać', 'Aerodromi, vožnja autom, autobusi i parking.', 'getting-to-bihac', 'Getting to Bihać', 'Airports, driving, buses and parking.', 'anreise-bihac', 'Anreise nach Bihać', 'Flughäfen, Anreise mit dem Auto, Busse und Parken.'),
        '03-granica-dokumenti' => array('granica-i-dokumenti', 'Granica i dokumenti', 'Granični prijelazi, dokumenti, osiguranje auta.', 'border-and-documents', 'Border and documents', 'Border crossings, ID, car insurance.', 'grenze-und-dokumente', 'Grenze und Dokumente', 'Grenzübergänge, Ausweis, Autoversicherung.'),
        '04-smjestaj-bihac' => array('smjestaj-u-bihacu', 'Smještaj u Bihaću', 'Apartmani u gradu ili kuća na Uni, za koga je što.', 'accommodation-in-bihac', 'Accommodation in Bihać', 'Apartments in town or a house on the Una.', 'unterkunft-in-bihac', 'Unterkunft in Bihać', 'Wohnung in der Stadt oder Haus an der Una.'),
        '05-rezervacija-placanje' => array('rezervacija-i-placanje', 'Rezervacija i plaćanje', 'Upit, potvrda i plaćanje kod nas.', 'booking-and-payment', 'Booking and payment', 'Enquiry, confirmation and payment.', 'buchung-und-zahlung', 'Buchung und Zahlung', 'Anfrage, Bestätigung und Bezahlung.'),
        '06-vikendice-na-uni' => array('vikendice-na-uni', 'Kuće i vikendice uz Unu', 'Lohovo, Pritoka i Grmuša: odmor na samoj rijeci.', 'holiday-homes-on-the-una', 'Holiday homes on the Una', 'Lohovo, Pritoka and Grmuša: right on the river.', 'ferienhaeuser-an-der-una', 'Ferienhäuser an der Una', 'Lohovo, Pritoka und Grmuša: direkt am Fluss.'),
        '07-rijeka-una' => array('rijeka-una', 'Rijeka Una', 'Izvor, tok, boja vode i slapovi.', 'una-river', 'The Una river', 'Source, course, colour of the water and waterfalls.', 'fluss-una', 'Der Fluss Una', 'Quelle, Verlauf, Wasserfarbe und Wasserfälle.'),
        '08-np-una' => array('nacionalni-park-una', 'Nacionalni park Una', 'Ulazi, lokacije, karte i pravila.', 'una-national-park', 'Una National Park', 'Entrances, sights, tickets and rules.', 'nationalpark-una', 'Nationalpark Una', 'Eingänge, Sehenswürdigkeiten, Tickets und Regeln.'),
        '09-strbacki-buk' => array('strbacki-buk', 'Štrbački buk', 'Najveći slap na Uni: put, vidikovci, kupanje.', 'strbacki-buk-waterfall', 'Štrbački buk waterfall', 'The Una’s largest waterfall: route, viewpoints, swimming.', 'wasserfall-strbacki-buk', 'Wasserfall Štrbački buk', 'Der größte Wasserfall der Una: Anfahrt, Aussichtspunkte, Baden.'),
        '10-martin-brod-izleti-np' => array('martin-brod-i-okolica', 'Martin Brod, Kulen Vakuf i okolica', 'Jednodnevni izlet u južni dio parka.', 'martin-brod-and-kulen-vakuf', 'Martin Brod and Kulen Vakuf', 'A day trip to the southern part of the park.', 'martin-brod-und-kulen-vakuf', 'Martin Brod und Kulen Vakuf', 'Tagesausflug in den Süden des Nationalparks.'),
        '11-rafting' => array('rafting-pitanja', 'Rafting na Uni', 'Dionice, dob, sigurnost i sezona.', 'rafting-on-the-una', 'Rafting on the Una', 'Sections, age, safety and season.', 'rafting-auf-der-una', 'Rafting auf der Una', 'Strecken, Alter, Sicherheit und Saison.'),
        '12-kajak-kanu-aktivnosti' => array('aktivni-odmor', 'Kajak, bicikl i planinarenje', 'Aktivni odmor oko Bihaća.', 'outdoor-activities', 'Kayaking, cycling and hiking', 'Active holidays around Bihać.', 'aktivurlaub', 'Kajak, Radfahren und Wandern', 'Aktivurlaub rund um Bihać.'),
        '13-ribolov' => array('ribolov-na-uni', 'Ribolov na Uni', 'Vrste riba, dozvole i sezona.', 'fishing-on-the-una', 'Fishing on the Una', 'Fish species, permits and season.', 'angeln-an-der-una', 'Angeln an der Una', 'Fischarten, Lizenzen und Saison.'),
        '14-kupanje-una' => array('kupanje-u-uni', 'Kupanje u Uni', 'Gdje se kupati, temperatura vode, sigurnost.', 'swimming-in-the-una', 'Swimming in the Una', 'Where to swim, water temperature, safety.', 'baden-in-der-una', 'Baden in der Una', 'Badestellen, Wassertemperatur, Sicherheit.'),
        '15-plitvice-iz-bihaca' => array('plitvice-iz-bihaca', 'Plitvička jezera iz Bihaća', 'Udaljenost, ulazi i plan izleta.', 'plitvice-lakes-from-bihac', 'Plitvice Lakes from Bihać', 'Distance, entrances and a day plan.', 'plitvicer-seen-von-bihac', 'Plitvicer Seen von Bihać aus', 'Entfernung, Eingänge und Tagesplan.'),
        '16-hrana-restorani' => array('hrana-i-restorani', 'Hrana i restorani', 'Što jesti u Bihaću i koliko košta.', 'food-and-restaurants', 'Food and restaurants', 'What to eat in Bihać and what it costs.', 'essen-und-restaurants', 'Essen und Restaurants', 'Was man in Bihać isst und was es kostet.'),
        '17-nocni-zivot-kafici' => array('izlasci-u-bihacu', 'Izlasci i kafići', 'Noćni život, terase i ljetni događaji.', 'nightlife-and-cafes', 'Nightlife and cafés', 'Bars, terraces and summer events.', 'nachtleben-und-cafes', 'Nachtleben und Cafés', 'Bars, Terrassen und Sommerveranstaltungen.'),
        '18-povijest-znamenitosti' => array('povijest-i-znamenitosti', 'Povijest i znamenitosti', 'Kapetanova kula, Fethija, Japodi i stari grad.', 'history-and-sights', 'History and sights', 'Captain’s Tower, Fethija, the Iapydes and the old town.', 'geschichte-und-sehenswuerdigkeiten', 'Geschichte und Sehenswürdigkeiten', 'Kapitänsturm, Fethija, Japoden und Altstadt.'),
        '19-izleti-okolica' => array('izleti-iz-bihaca', 'Izleti iz Bihaća', 'Ostrožac, Cazin, Krupa, Rastoke, more.', 'day-trips-from-bihac', 'Day trips from Bihać', 'Ostrožac, Cazin, Krupa, Rastoke, the coast.', 'ausfluege-ab-bihac', 'Ausflüge ab Bihać', 'Ostrožac, Cazin, Krupa, Rastoke, das Meer.'),
        '20-obitelji-djeca' => array('odmor-s-djecom', 'Odmor s djecom', 'Što raditi s djecom u Bihaću i uz Unu.', 'family-holidays', 'Holidays with children', 'What to do with kids in Bihać and on the Una.', 'urlaub-mit-kindern', 'Urlaub mit Kindern', 'Was man mit Kindern in Bihać und an der Una macht.'),
        '21-kucni-ljubimci' => array('putovanje-s-ljubimcem', 'Putovanje s ljubimcem', 'Ulazak u BiH s psom, smještaj i povratak u EU.', 'travelling-with-pets', 'Travelling with pets', 'Entering Bosnia with a dog, accommodation and returning to the EU.', 'reisen-mit-haustier', 'Reisen mit Haustier', 'Einreise mit Hund, Unterkunft und Rückreise in die EU.'),
        '22-novac-cijene' => array('novac-i-cijene', 'Novac i cijene', 'Konvertibilna marka, kartice, roaming.', 'money-and-prices', 'Money and prices', 'Convertible mark, cards, roaming.', 'geld-und-preise', 'Geld und Preise', 'Konvertible Mark, Karten, Roaming.'),
        '23-vrijeme-sezone' => array('vrijeme-i-sezone', 'Vrijeme i sezone', 'Temperature po mjesecima i kada doći.', 'weather-and-seasons', 'Weather and seasons', 'Temperatures by month and when to come.', 'wetter-und-reisezeit', 'Wetter und Reisezeit', 'Temperaturen nach Monaten und beste Reisezeit.'),
        '24-sigurnost-zdravlje' => array('sigurnost-i-zdravlje', 'Sigurnost i zdravlje', 'Hitni brojevi, bolnica, mine, voda.', 'safety-and-health', 'Safety and health', 'Emergency numbers, hospital, landmines, water.', 'sicherheit-und-gesundheit', 'Sicherheit und Gesundheit', 'Notrufnummern, Krankenhaus, Minen, Wasser.'),
        '25-prakticne-info' => array('prakticne-informacije', 'Praktične informacije', 'Utičnice, trgovine, praznici, jezik.', 'practical-information', 'Practical information', 'Plugs, shops, holidays, language.', 'praktische-informationen', 'Praktische Informationen', 'Steckdosen, Geschäfte, Feiertage, Sprache.'),
    );
}

/** Jezici: mapa s JSON-ima, roditelj, slug glavne stranice i tekstovi. */
function abu_pitanja_jezici() {
    return array(
        'bs' => array('dir' => '', 'roditelj' => '', 'hub' => 'cesta-pitanja', 'i' => 0,
            'hub_naslov' => 'Česta pitanja o Bihaću i smještaju', 'sufiks' => ': česta pitanja', 'pitanja' => 'pitanja i odgovora za goste Bihaća i rijeke Une.',
            'hub_uvod' => 'Sve što gosti pitaju prije dolaska u Bihać: kako doći, gdje spavati, Nacionalni park Una, rafting, Plitvice, hrana, novac i praktične informacije. Ukupno %d pitanja i odgovora, podijeljenih po temama.',
            'hub_opis' => 'Odgovori na %d pitanja o Bihaću, rijeci Uni, izletima i smještaju.',
            'sva' => 'Sva česta pitanja', 'cta' => 'Cijeli apartman u Bihaću ili kuća uz Unu od 25 € (50 KM) po noći: pošaljite upit na sajtu ili pišite na <a href="https://wa.me/38761591320">WhatsApp</a>.',
            'kraj' => 'Niste našli odgovor? Pišite nam na <a href="https://wa.me/38761591320">WhatsApp ili Viber +387 61 591 320</a> ili na apartmaniubihacu@gmail.com.'),
        'en' => array('dir' => 'en/', 'roditelj' => 'en', 'hub' => 'faq', 'i' => 3,
            'hub_naslov' => 'Bihać travel FAQ: accommodation, Una National Park and Plitvice', 'sufiks' => ': FAQ', 'pitanja' => 'questions and answers for visitors to Bihać and the Una river.',
            'hub_uvod' => 'Everything guests ask before coming to Bihać, Bosnia and Herzegovina: getting here, the border, where to stay, Una National Park, rafting, Plitvice Lakes, food, money and practical tips. %d questions and answers by topic.',
            'hub_opis' => 'Answers to %d questions about Bihać, the Una river, day trips and accommodation.',
            'sva' => 'All FAQ topics', 'cta' => 'A whole apartment in Bihać or a house on the Una from €25 per night: send an enquiry on the website or message us on <a href="https://wa.me/38761591320">WhatsApp</a>. We speak English.',
            'kraj' => 'Didn’t find your answer? Message us on <a href="https://wa.me/38761591320">WhatsApp or Viber +387 61 591 320</a> or e-mail apartmaniubihacu@gmail.com. We speak English and German.'),
        'de' => array('dir' => 'de/', 'roditelj' => 'de', 'hub' => 'faq', 'i' => 6,
            'hub_naslov' => 'Bihać Reise-FAQ: Unterkunft, Nationalpark Una und Plitvicer Seen', 'sufiks' => ': häufige Fragen', 'pitanja' => 'Fragen und Antworten für Gäste in Bihać und an der Una.',
            'hub_uvod' => 'Alles, was Gäste vor der Reise nach Bihać (Bosnien und Herzegowina) fragen: Anreise, Grenze, Unterkunft, Nationalpark Una, Rafting, Plitvicer Seen, Essen, Geld und praktische Tipps. %d Fragen und Antworten nach Themen.',
            'hub_opis' => 'Antworten auf %d Fragen zu Bihać, der Una, Ausflügen und Unterkunft.',
            'sva' => 'Alle Themen', 'cta' => 'Eine ganze Wohnung in Bihać oder ein Haus an der Una ab 25 € pro Nacht: Anfrage auf der Website senden oder per <a href="https://wa.me/38761591320">WhatsApp</a> schreiben. Wir sprechen Deutsch.',
            'kraj' => 'Keine Antwort gefunden? Schreiben Sie uns per <a href="https://wa.me/38761591320">WhatsApp oder Viber +387 61 591 320</a> oder an apartmaniubihacu@gmail.com. Wir sprechen Deutsch.'),
    );
}

/** Zbog stare upotrebe (llms.txt u uvoz.php): bs teme u obliku slug, naslov, opis. */
function abu_pitanja_teme() {
    $o = array();
    foreach (abu_pitanja_teme_sve() as $k => $t) { $o[$k] = array($t[0], $t[1], $t[2]); }
    return $o;
}

function abu_pitanja_ucitaj($kljuc, $lang = 'bs') {
    $j = abu_pitanja_jezici();
    $f = __DIR__ . '/pitanja/' . (isset($j[$lang]) ? $j[$lang]['dir'] : '') . $kljuc . '.json';
    if (!is_readable($f)) { return array(); }
    $d = json_decode((string) file_get_contents($f), true);
    return is_array($d) ? array_values(array_filter($d, function ($x) { return !empty($x['q']) && !empty($x['a']); })) : array();
}

function abu_pitanja_put($lang, $slug = '') {
    $j = abu_pitanja_jezici()[$lang];
    return trim(($j['roditelj'] ? $j['roditelj'] . '/' : '') . $j['hub'] . ($slug ? '/' . $slug : ''), '/');
}

function abu_pitanja_html($lista) {
    $h = '';
    foreach ($lista as $x) { $h .= '<h3>' . esc_html($x['q']) . "</h3>\n<p>" . esc_html($x['a']) . "</p>\n"; }
    return $h;
}

function abu_pitanja_osvjezi_jezik($lang) {
    $J = abu_pitanja_jezici()[$lang];
    $nad = 0;
    if ($J['roditelj']) {
        $r = get_page_by_path($J['roditelj']);
        if (!$r) { return; }
        $nad = $r->ID;
    }
    $hub = get_page_by_path(abu_pitanja_put($lang));
    $rid = $hub ? $hub->ID : wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_parent' => $nad, 'post_name' => $J['hub'], 'post_title' => $J['hub_naslov']));
    $ukupno = 0;
    $popis = '';
    foreach (abu_pitanja_teme_sve() as $k => $t) {
        $slug = $t[$J['i']]; $naslov = $t[$J['i'] + 1]; $opis = $t[$J['i'] + 2];
        $lista = abu_pitanja_ucitaj($k, $lang);
        $p = get_page_by_path(abu_pitanja_put($lang, $slug));
        if (!$lista) { if ($p) { wp_update_post(array('ID' => $p->ID, 'post_status' => 'draft')); } continue; }
        $ukupno += count($lista);
        $sadrzaj = '<p class="lead">' . esc_html($opis) . ' ' . count($lista) . ' ' . esc_html($J['pitanja']) . '</p>' . "\n"
            . abu_pitanja_html($lista)
            . '<p><a href="' . esc_url(home_url('/' . abu_pitanja_put($lang) . '/')) . '">' . esc_html($J['sva']) . '</a> · ' . $J['cta'] . '</p>';
        $podaci = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $rid, 'post_name' => $slug,
            'post_title' => $naslov . $J['sufiks'], 'post_excerpt' => $opis, 'post_content' => $sadrzaj, 'menu_order' => (int) $k);
        if ($p) { $podaci['ID'] = $p->ID; wp_update_post($podaci); $id = $p->ID; } else { $id = wp_insert_post($podaci); }
        update_post_meta($id, '_abu_pitanja', $k);
        update_post_meta($id, '_abu_pitanja_lang', $lang);
        $popis .= '<li><a href="' . esc_url(get_permalink($id)) . '"><strong>' . esc_html($naslov) . '</strong></a> (' . count($lista) . '): ' . esc_html($opis) . "</li>\n";
    }
    wp_update_post(array('ID' => $rid, 'post_status' => $ukupno ? 'publish' : 'draft', 'post_title' => $J['hub_naslov'],
        'post_excerpt' => sprintf($J['hub_opis'], $ukupno),
        'post_content' => '<p class="lead">' . esc_html(sprintf($J['hub_uvod'], $ukupno)) . "</p>\n<ul>\n" . $popis . "</ul>\n<p>" . $J['kraj'] . '</p>'));
    update_post_meta($rid, '_abu_pitanja', 'sve');
    update_post_meta($rid, '_abu_pitanja_lang', $lang);
}

/** Osvježi stranice kad se promijeni sadržaj. */
add_action('init', function () {
    $pot = ABU_VER;
    foreach (array_keys(abu_pitanja_jezici()) as $l) {
        foreach (array_keys(abu_pitanja_teme_sve()) as $k) {
            $f = __DIR__ . '/pitanja/' . abu_pitanja_jezici()[$l]['dir'] . $k . '.json';
            $pot .= is_readable($f) ? md5_file($f) : '-';
        }
    }
    $ver = md5($pot);
    if (get_option('abu_pitanja_ver') === $ver || get_transient('abu_pitanja_lock')) { return; }
    set_transient('abu_pitanja_lock', 1, 300);
    foreach (array_keys(abu_pitanja_jezici()) as $l) { abu_pitanja_osvjezi_jezik($l); }
    // Testni članci iz stare teme ne idu javno
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1)) as $x) {
        if (preg_match('/^(zdravo-svijete|hello-world|link-post-format|standar-post-type|gallery-post-format-type|standard-post-format|video-post-format|audio-post-format|quote-post-format|aside-post-format)(-\d+)?$/', $x->post_name)) {
            wp_update_post(array('ID' => $x->ID, 'post_status' => 'draft'));
        }
    }
    update_option('abu_pitanja_ver', $ver);
    delete_transient('abu_pitanja_lock');
}, 35);

/** FAQPage schema i hreflang između jezičnih verzija iste teme. */
add_action('wp_head', function () {
    if (!is_page()) { return; }
    $id = get_queried_object_id();
    $k = get_post_meta($id, '_abu_pitanja', true);
    if (!$k) { return; }
    $lang = get_post_meta($id, '_abu_pitanja_lang', true) ?: 'bs';
    $teme = abu_pitanja_teme_sve();
    foreach (abu_pitanja_jezici() as $l => $J) {
        $put = $k === 'sve' ? abu_pitanja_put($l) : (isset($teme[$k]) ? abu_pitanja_put($l, $teme[$k][$J['i']]) : '');
        $p = $put ? get_page_by_path($put) : null;
        if ($p && $p->post_status === 'publish') {
            echo '<link rel="alternate" hreflang="' . $l . '" href="' . esc_url(get_permalink($p)) . '">' . "\n";
            if ($l === 'bs') { echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(get_permalink($p)) . '">' . "\n"; }
        }
    }
    if ($k === 'sve') { return; }
    $lista = abu_pitanja_ucitaj($k, $lang);
    if (!$lista) { return; }
    $d = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'inLanguage' => $lang, 'mainEntity' => array());
    foreach ($lista as $x) { $d['mainEntity'][] = array('@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $x['a'])); }
    echo '<script type="application/ld+json">' . wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 4);

/** /llms-full.txt: sva pitanja i odgovori na svim jezicima kao čisti tekst. */
add_action('init', function () {
    $path = isset($_SERVER['REQUEST_URI']) ? strtok(wp_unslash($_SERVER['REQUEST_URI']), '?') : '';
    if ($path !== '/llms-full.txt') { return; }
    header('Content-Type: text/plain; charset=utf-8');
    echo "# Apartmani i vikendice Bihać (apartmanibihac.com): FAQ\n\n> Accommodation in Bihać and on the Una river, Bosnia and Herzegovina. 19 properties, a whole apartment from €25 (50 KM) per night, payment in cash on arrival, enquiry answered within 12 hours. Check-in from 12:00, check-out by 11:00. WhatsApp/Viber +387 61 591 320, apartmaniubihacu@gmail.com. We speak Bosnian, English and German.\n\n";
    foreach (abu_pitanja_jezici() as $l => $J) {
        foreach (abu_pitanja_teme_sve() as $k => $t) {
            $lista = abu_pitanja_ucitaj($k, $l);
            if (!$lista) { continue; }
            echo '## ' . $t[$J['i'] + 1] . ' [' . $l . '] (' . home_url('/' . abu_pitanja_put($l, $t[$J['i']]) . '/') . ")\n\n";
            foreach ($lista as $x) { echo '### ' . $x['q'] . "\n" . $x['a'] . "\n\n"; }
        }
    }
    exit;
}, 1);
