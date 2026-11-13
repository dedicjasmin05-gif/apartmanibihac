<?php
/**
 * Team building Bihać: stranica (bs/en/de) s konfiguratorom. Tvrtka sama odabere smještaj, rafting,
 * aktivnosti, hranu i izvođače, a upit stiže e-mailom na adresu za upite (i sprema se u admin, Team building upiti).
 * Ponuđači su u sadrzaj/teambuilding.json.
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_TB_MAIL', 'apartmaniubihacu@gmail.com');

function abu_tb_put() { return array('bs' => 'team-building-bihac', 'en' => 'en/team-building-bihac', 'de' => 'de/teambuilding-bihac'); }

function abu_tb_tekuci() {
    if (!is_page()) { return ''; }
    return (string) get_post_meta(get_queried_object_id(), '_abu_tb', true);
}

function abu_tb_podaci() {
    static $d = null;
    if ($d !== null) { return $d; }
    $f = __DIR__ . '/sadrzaj/teambuilding.json';
    $d = is_readable($f) ? json_decode((string) file_get_contents($f), true) : array();
    return $d = is_array($d) ? $d : array();
}

/** Tekstovi stranice. */
function abu_tb_txt($l) {
    $t = array(
        'bs' => array(
            'title' => 'Team building Bihać: rafting, smještaj i program na Uni',
            'h1' => 'Team building u Bihaću, na rijeci Uni',
            'lead' => 'Rafting, kajaci, kvadovi, jahanje, ručak na rijeci, tamburaši i DJ. Odaberite što želite, a mi spojimo hotele, rafting kuće, restorane i izvođače u Bihaću u jednu ponudu. Bez obaveze, odgovor u 24 sata.',
            'meta' => 'Team building u Bihaću: rafting na Uni, smještaj za grupe, aktivnosti, ručak na rijeci i muzika uživo. Složite program sami i pošaljite upit, ponuda u 24 sata.',
            'why' => array(
                array('Sve na jednom mjestu', 'Sarađujemo s hotelima, rafting kampovima, restoranima i izvođačima u Bihaću. Vi imate jedan kontakt i jednu ponudu.'),
                array('Una, najljepša rijeka', 'Rafting kroz kanjon, slapovi Štrbačkog buka i Nacionalni park Una. Program koji se pamti.'),
                array('Za 10 ili 200 ljudi', 'Od manjih timova u vikendicama do velikih grupa u hotelima. Prilagodimo program i budžet.'),
                array('Cijene u Bosni', 'Smještaj od 25 € po apartmanu, rafting s ručkom i program za mnogo manje nego na moru.'),
            ),
            'f_h' => 'Složite svoj team building',
            'f_p' => 'Označite sve što vas zanima. Ništa nije obavezno, a ponudu prilagodimo vašem budžetu.',
            's1' => 'Osnovno', 's1p' => 'Koliko vas je i kada dolazite?',
            'osobe' => 'Broj osoba', 'od' => 'Dolazak', 'do' => 'Odlazak', 'budzet' => 'Budžet po osobi',
            'budzeti' => array('' => 'Nije bitno', 'do 50 €' => 'do 50 €', '50–100 €' => '50–100 €', '100–200 €' => '100–200 €', '200 €+' => 'više od 200 €'),
            's2' => 'Smještaj', 's2p' => 'Naši apartmani i vikendice ili hotel. Možete odabrati više opcija.',
            'nasi' => 'Apartmani & Vikendice', 'nasi_k' => 'Cijeli apartmani i kuće uz Unu, od 25 € po noći',
            's3' => 'Rafting', 's3p' => 'Rafting kampovi na Uni. Na prvom mjestu preporučujemo Una Olympic Rafting.',
            's4' => 'Aktivnosti', 's4p' => 'Sve što se može raditi na Uni i oko Bihaća.',
            's5' => 'Hrana i druženje', 's5p' => 'Ručak na rijeci, roštilj u kampu ili večera u restoranu.',
            's6' => 'Muzika i zabava', 's6p' => 'Tamburaši, pjevači, bendovi i DJ za večernji program.',
            's7' => 'Kontakt', 's7p' => 'Upit stiže nama na e-mail. Javljamo se s ponudom u roku od 24 sata.',
            'ime' => 'Ime i prezime', 'firma' => 'Firma', 'email' => 'E-mail', 'tel' => 'Telefon', 'nap' => 'Napomena (program, želje, posebni zahtjevi)',
            'privola' => 'Slažem se da podatke koristite za izradu ponude.',
            'odabrano' => 'odabrano', 'posalji' => 'Pošalji upit za team building',
            'ok' => 'Hvala! Upit je poslan. Javljamo se s ponudom u roku od 24 sata. Za hitno: +387 61 591 320 (telefon, WhatsApp, Viber).',
            'err' => 'Upit nije poslan. Upišite e-mail ili telefon i pokušajte ponovo, ili nas nazovite na +387 61 591 320.',
            'faq_h' => 'Česta pitanja o team buildingu u Bihaću',
            'faq' => array(
                array('Koliko košta team building u Bihaću?', 'Zavisi od broja osoba, smještaja i programa. Smještaj u našim apartmanima kreće od 25 € po apartmanu po noći, a rafting s ručkom i program dogovaramo s partnerima po grupnim cijenama. Pošaljite upit s brojem osoba i budžetom i dobit ćete tačnu ponudu.'),
                array('Za koliko ljudi možete organizovati team building?', 'Od malih timova od 10 osoba do grupa od 200 i više. Za veće grupe kombinujemo hotele u Bihaću, rafting kampove i naše apartmane.'),
                array('Je li rafting na Uni siguran za početnike?', 'Da. Rafting vode licencirani skiperi, svi dobiju kacigu, prsluk i neopren, a dionicu biramo prema iskustvu grupe i vodostaju.'),
                array('Kada je najbolje vrijeme za team building na Uni?', 'Od maja do septembra za rafting i aktivnosti na vodi. U proljeće je rijeka najjača, a ljeti je najtoplije za kupanje. Izvan sezone nudimo izlete, jahanje, kvadove i program u zatvorenom.'),
                array('Možete li organizovati prevoz?', 'Da, na upit organizujemo prevoz do Bihaća i između lokacija programa.'),
            ),
        ),
        'en' => array(
            'title' => 'Team building in Bihać: rafting, stays and activities',
            'h1' => 'Team building in Bihać, on the Una river',
            'lead' => 'Rafting, kayaks, quads, horse riding, lunch on the river, tamburica bands and DJs. Pick what you like and we put hotels, rafting camps, restaurants and performers in Bihać together into one offer. No obligation, reply within 24 hours.',
            'meta' => 'Team building in Bihać: Una river rafting, group accommodation, activities, lunch by the river and live music. Build your programme and get an offer within 24 hours.',
            'why' => array(
                array('One contact for everything', 'We work with hotels, rafting camps, restaurants and performers in Bihać. You get one contact and one offer.'),
                array('The Una river', 'Rafting through the canyon, Štrbački buk waterfall and Una National Park. A programme people remember.'),
                array('From 10 to 200 people', 'Small teams in holiday homes or large groups in hotels. We adapt the programme and the budget.'),
                array('Bosnian prices', 'Accommodation from €25 per apartment, rafting with lunch and a full programme for much less than on the coast.'),
            ),
            'f_h' => 'Build your team building', 'f_p' => 'Tick everything you are interested in. Nothing is mandatory; we adapt the offer to your budget.',
            's1' => 'Basics', 's1p' => 'How many of you and when?', 'osobe' => 'Number of people', 'od' => 'Arrival', 'do' => 'Departure', 'budzet' => 'Budget per person',
            'budzeti' => array('' => 'Flexible', 'do 50 €' => 'up to €50', '50–100 €' => '€50–100', '100–200 €' => '€100–200', '200 €+' => 'over €200'),
            's2' => 'Accommodation', 's2p' => 'Our apartments and holiday homes, or a hotel. You can choose more than one.', 'nasi' => 'Apartmani & Vikendice', 'nasi_k' => 'Whole apartments and houses by the Una, from €25 a night',
            's3' => 'Rafting', 's3p' => 'Rafting camps on the Una. Our first pick is Una Olympic Rafting.',
            's4' => 'Activities', 's4p' => 'Everything you can do on the Una and around Bihać.',
            's5' => 'Food', 's5p' => 'Lunch on the river, barbecue at the camp or dinner in a restaurant.',
            's6' => 'Music and entertainment', 's6p' => 'Tamburica bands, singers, live bands and DJs for the evening.',
            's7' => 'Contact', 's7p' => 'Your enquiry comes to us by e-mail. We reply with an offer within 24 hours.',
            'ime' => 'Name', 'firma' => 'Company', 'email' => 'E-mail', 'tel' => 'Phone', 'nap' => 'Notes (programme, wishes, special requests)',
            'privola' => 'I agree that you use my details to prepare an offer.', 'odabrano' => 'selected', 'posalji' => 'Send team building enquiry',
            'ok' => 'Thank you! Your enquiry has been sent. We will reply with an offer within 24 hours. Urgent: +387 61 591 320 (phone, WhatsApp, Viber).',
            'err' => 'The enquiry was not sent. Please enter an e-mail or phone and try again, or call +387 61 591 320.',
            'faq_h' => 'Team building in Bihać: FAQ',
            'faq' => array(
                array('How much does team building in Bihać cost?', 'It depends on group size, accommodation and programme. Our apartments start at €25 per apartment per night; rafting with lunch and activities are arranged with partners at group rates. Send an enquiry with group size and budget for an exact offer.'),
                array('How large a group can you host?', 'From teams of 10 to groups of 200 or more, combining hotels in Bihać, rafting camps and our apartments.'),
                array('Is Una rafting safe for beginners?', 'Yes. Licensed skippers lead every boat, everyone gets a helmet, life jacket and wetsuit, and the section is chosen to suit the group and water level.'),
                array('When is the best time?', 'May to September for rafting and water activities. Outside the season we offer trips, horse riding, quads and indoor programmes.'),
            ),
        ),
        'de' => array(
            'title' => 'Teambuilding in Bihać: Rafting, Unterkunft, Programm',
            'h1' => 'Teambuilding in Bihać an der Una',
            'lead' => 'Rafting, Kajak, Quads, Reiten, Mittagessen am Fluss, Tamburica-Musik und DJ. Wählen Sie aus, was Sie möchten, und wir verbinden Hotels, Raftingcamps, Restaurants und Künstler in Bihać zu einem Angebot. Unverbindlich, Antwort innerhalb von 24 Stunden.',
            'meta' => 'Teambuilding in Bihać: Rafting auf der Una, Gruppenunterkunft, Aktivitäten, Essen am Fluss und Live-Musik. Programm zusammenstellen, Angebot in 24 Stunden.',
            'why' => array(
                array('Alles aus einer Hand', 'Wir arbeiten mit Hotels, Raftingcamps, Restaurants und Künstlern in Bihać zusammen. Sie haben einen Ansprechpartner und ein Angebot.'),
                array('Die Una', 'Rafting durch die Schlucht, der Wasserfall Štrbački buk und der Nationalpark Una. Ein Programm, das man nicht vergisst.'),
                array('Für 10 bis 200 Personen', 'Kleine Teams in Ferienhäusern oder große Gruppen in Hotels. Programm und Budget passen wir an.'),
                array('Bosnische Preise', 'Unterkunft ab 25 € pro Wohnung, Rafting mit Mittagessen und Programm deutlich günstiger als an der Küste.'),
            ),
            'f_h' => 'Stellen Sie Ihr Teambuilding zusammen', 'f_p' => 'Markieren Sie alles, was Sie interessiert. Nichts ist Pflicht; das Angebot passen wir Ihrem Budget an.',
            's1' => 'Eckdaten', 's1p' => 'Wie viele Personen und wann?', 'osobe' => 'Personen', 'od' => 'Anreise', 'do' => 'Abreise', 'budzet' => 'Budget pro Person',
            'budzeti' => array('' => 'Flexibel', 'do 50 €' => 'bis 50 €', '50–100 €' => '50–100 €', '100–200 €' => '100–200 €', '200 €+' => 'über 200 €'),
            's2' => 'Unterkunft', 's2p' => 'Unsere Wohnungen und Ferienhäuser oder ein Hotel. Mehrfachauswahl möglich.', 'nasi' => 'Apartmani & Vikendice', 'nasi_k' => 'Ganze Wohnungen und Häuser an der Una, ab 25 € pro Nacht',
            's3' => 'Rafting', 's3p' => 'Raftingcamps an der Una. Unsere erste Empfehlung: Una Olympic Rafting.',
            's4' => 'Aktivitäten', 's4p' => 'Alles, was man an der Una und um Bihać unternehmen kann.',
            's5' => 'Essen', 's5p' => 'Mittagessen am Fluss, Grillen im Camp oder Abendessen im Restaurant.',
            's6' => 'Musik und Unterhaltung', 's6p' => 'Tamburica-Bands, Sänger, Bands und DJs für den Abend.',
            's7' => 'Kontakt', 's7p' => 'Ihre Anfrage kommt per E-Mail zu uns. Wir antworten innerhalb von 24 Stunden mit einem Angebot.',
            'ime' => 'Name', 'firma' => 'Firma', 'email' => 'E-Mail', 'tel' => 'Telefon', 'nap' => 'Anmerkungen (Programm, Wünsche)',
            'privola' => 'Ich bin einverstanden, dass meine Daten für das Angebot verwendet werden.', 'odabrano' => 'ausgewählt', 'posalji' => 'Teambuilding-Anfrage senden',
            'ok' => 'Vielen Dank! Ihre Anfrage wurde gesendet. Wir melden uns innerhalb von 24 Stunden. Dringend: +387 61 591 320 (Telefon, WhatsApp, Viber).',
            'err' => 'Die Anfrage wurde nicht gesendet. Bitte E-Mail oder Telefon angeben oder +387 61 591 320 anrufen.',
            'faq_h' => 'Teambuilding in Bihać: häufige Fragen',
            'faq' => array(
                array('Was kostet ein Teambuilding in Bihać?', 'Das hängt von Gruppengröße, Unterkunft und Programm ab. Unsere Wohnungen beginnen bei 25 € pro Wohnung und Nacht; Rafting mit Mittagessen und Aktivitäten vereinbaren wir mit Partnern zu Gruppenpreisen.'),
                array('Wie groß darf die Gruppe sein?', 'Von 10 bis über 200 Personen; wir kombinieren Hotels in Bihać, Raftingcamps und unsere Wohnungen.'),
                array('Ist Rafting auf der Una für Anfänger geeignet?', 'Ja. Lizenzierte Skipper führen jedes Boot, alle erhalten Helm, Schwimmweste und Neoprenanzug.'),
            ),
        ),
    );
    return isset($t[$l]) ? $t[$l] : $t['bs'];
}

/** Rezervne aktivnosti, ako ih JSON nema dovoljno. */
function abu_tb_osnovne($l) {
    $a = array(
        array('Kajak i kanu', 'Mirnije dionice Une za cijelu grupu', 'Kayak & canoe', 'Kajak & Kanu'),
        array('SUP na Uni', 'Daske na mirnoj vodi', 'Stand-up paddle', 'Stand-up-Paddling'),
        array('Kvad ture', 'Vožnja kroz šumu i makadam iznad Une', 'Quad tours', 'Quad-Touren'),
        array('Jeep safari', 'Plješevica, Željava i vidikovci', 'Jeep safari', 'Jeep-Safari'),
        array('Jahanje', 'Jahanje uz rijeku za početnike', 'Horse riding', 'Reiten'),
        array('Ručak na splavu', 'Plovidba i ručak na rijeci', 'Lunch on a river raft', 'Mittagessen auf dem Floß'),
        array('Paintball', 'Takmičenje timova na otvorenom', 'Paintball', 'Paintball'),
        array('Izlet: NP Una i Štrbački buk', 'Najljepši slap na Uni', 'Trip: Una NP & Štrbački buk', 'Ausflug: NP Una & Štrbački buk'),
        array('Izlet: Plitvička jezera', '35 km od Bihaća', 'Trip: Plitvice Lakes', 'Ausflug: Plitvicer Seen'),
        array('Ribolov', 'Pastrmka i lipljen uz vodiča', 'Fishing', 'Angeln'),
        array('Biciklizam', 'Una Bike Trail i staze uz rijeku', 'Cycling', 'Radfahren'),
        array('Team building igre', 'Igre i izazovi uz voditelja', 'Team games', 'Teamspiele'),
    );
    $o = array();
    foreach ($a as $x) { $o[] = array('ime' => $l === 'en' ? $x[2] : ($l === 'de' ? $x[3] : $x[0]), 'kratko_bs' => $x[1], 'slug' => sanitize_title($x[0])); }
    return $o;
}

/* ---------- Stranice ---------- */
add_action('init', function () {
    if (get_option('abu_tb_ver') === ABU_VER) { return; }
    foreach (abu_tb_put() as $l => $put) {
        $t = abu_tb_txt($l);
        if (function_exists('abu_vodic_stranica')) { abu_vodic_stranica($put, $t['h1'], $t['meta'], array('_abu_tb' => $l)); }
    }
    update_option('abu_tb_ver', ABU_VER);
}, 38);

add_filter('template_include', function ($t) { return abu_tb_tekuci() ? __DIR__ . '/predlosci/teambuilding.php' : $t; }, 31);
add_filter('pre_get_document_title', function ($t) { $l = abu_tb_tekuci(); return $l ? abu_tb_txt($l)['title'] : $t; }, 26);
add_action('wp_head', function () {
    $l = abu_tb_tekuci();
    if (!$l) { return; }
    $tx = abu_tb_txt($l);
    echo '<meta name="description" content="' . esc_attr($tx['meta']) . '">' . "\n";
    foreach (abu_tb_put() as $jl => $put) { echo '<link rel="alternate" hreflang="' . esc_attr($jl) . '" href="' . esc_url(home_url('/' . $put . '/')) . '">' . "\n"; }
    $faq = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array());
    foreach ($tx['faq'] as $f) { $faq['mainEntity'][] = array('@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f[1])); }
    $svc = array('@context' => 'https://schema.org', '@type' => 'Service', 'serviceType' => 'Team building', 'name' => $tx['h1'], 'description' => $tx['meta'],
        'areaServed' => array('@type' => 'City', 'name' => 'Bihać'), 'provider' => array('@type' => 'TravelAgency', 'name' => 'Apartmani & Vikendice Bihać', 'telephone' => '+387 61 591 320', 'email' => ABU_TB_MAIL, 'url' => home_url('/')));
    echo '<script type="application/ld+json">' . wp_json_encode($faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($svc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 2);

/* ---------- Upiti: spremanje i e-mail ---------- */
add_action('init', function () {
    register_post_type('tb_upit', array('label' => 'Team building upiti', 'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-groups', 'supports' => array('title', 'editor'), 'capability_type' => 'post'));
});

function abu_tb_obradi() {
    $l = isset($_POST['lang']) && in_array($_POST['lang'], array('bs', 'en', 'de'), true) ? $_POST['lang'] : 'bs';
    $natrag = home_url('/' . abu_tb_put()[$l] . '/');
    $p = wp_unslash($_POST);
    $s = function ($k) use ($p) { return isset($p[$k]) ? sanitize_text_field($p[$k]) : ''; };
    $email = sanitize_email(isset($p['email']) ? $p['email'] : '');
    $tel = $s('tel');
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $limit = 'abu_tb_' . md5($ip);
    $spam = !empty($p['web_stranica']) || (isset($p['t0']) && time() - (int) $p['t0'] < 4);
    if ($spam || (!$email && !$tel) || (int) get_transient($limit) >= 5) {
        wp_safe_redirect(add_query_arg('tb_status', $spam ? 'ok' : 'greska', $natrag) . '#tbf'); exit;
    }
    set_transient($limit, (int) get_transient($limit) + 1, HOUR_IN_SECONDS);
    $grupe = array('smjestaj' => 'Smještaj', 'rafting' => 'Rafting', 'aktivnosti' => 'Aktivnosti', 'hrana' => 'Hrana', 'izvodjaci' => 'Muzika i zabava');
    $tijelo = "TEAM BUILDING UPIT (apartmanibihac.com, jezik: " . strtoupper($l) . ")\n\n"
        . 'Firma: ' . $s('firma') . "\nIme: " . $s('ime') . "\nE-mail: " . $email . "\nTelefon: " . $tel . "\n\n"
        . 'Broj osoba: ' . $s('osobe') . "\nDolazak: " . $s('od') . "\nOdlazak: " . $s('do') . "\nBudžet po osobi: " . $s('budzet') . "\n";
    foreach ($grupe as $k => $naz) {
        $v = isset($p[$k]) ? array_map('sanitize_text_field', (array) $p[$k]) : array();
        $tijelo .= "\n" . $naz . ': ' . ($v ? "\n  - " . implode("\n  - ", $v) : '-') . "\n";
    }
    $tijelo .= "\nNapomena:\n" . sanitize_textarea_field(isset($p['nap']) ? $p['nap'] : '') . "\n";
    $naslov = 'Team building upit: ' . ($s('firma') ? $s('firma') : $s('ime')) . ', ' . ($s('osobe') ? $s('osobe') . ' osoba' : '') . ($s('od') ? ', ' . $s('od') : '');
    wp_insert_post(array('post_type' => 'tb_upit', 'post_status' => 'private', 'post_title' => $naslov, 'post_content' => $tijelo));
    $h = array('Content-Type: text/plain; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . ABU_OD_MAIL . '>');
    if ($email) { $h[] = 'Reply-To: ' . ($s('ime') ? str_replace(array(',', '<', '>'), '', $s('ime')) . ' ' : '') . '<' . $email . '>'; }
    wp_mail(ABU_TB_MAIL, $naslov, $tijelo, $h);
    wp_safe_redirect(add_query_arg('tb_status', 'ok', $natrag) . '#tbf');
    exit;
}
add_action('admin_post_nopriv_abu_tb', 'abu_tb_obradi');
add_action('admin_post_abu_tb', 'abu_tb_obradi');
