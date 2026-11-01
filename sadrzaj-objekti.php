<?php
/** Objekti s prethodnog sajta: tekst, kapacitet, mjesto, oprema. Slike se dodaju naknadno. */
if (!defined('ABSPATH')) { exit; }

function abu_seed_lok($k) {
    $L = array(
        'centar' => 'Bihać je grad na rijeci Uni, a većina znamenitosti je na pješačkoj udaljenosti od centra: Kapetanova kula, Fethija džamija, Gradski park i šetnica uz Unu. U centru su restorani, kafići, trgovine i ljekarne, pa automobil tijekom boravka gotovo ne trebate. Do ulaza u Nacionalni park Plitvička jezera ima oko 35 km, a Štrbački buk u Nacionalnom parku Una idealan je poludnevni izlet.',
        'grad' => 'Iz apartmana brzo stižete do centra Bihaća i rijeke Une: Kapetanova kula, Fethija džamija, šetnica uz rijeku, restorani i kafići. Bihać je dobra baza za izlete. Do ulaza u Nacionalni park Plitvička jezera ima oko 35 km, a Štrbački buk u Nacionalnom parku Una idealan je poludnevni izlet.',
        'lohovo' => 'Lohovo je mirno naselje uz Unu južno od Bihaća, desetak kilometara od centra grada. Ovdje je Una sporija i bistrija nego u gradu, pa su kupanje i boravak uz vodu glavni razlog dolaska. Do centra Bihaća vozite se petnaestak minuta.',
        'grmusa' => 'Grmuša je naselje uz Unu sjeveroistočno od Bihaća, jedno od glavnih mjesta za rafting na Uni. U Grmuši i Salihovcu je baza Una Olympic Raftinga s restoranom uz rijeku. Do centra Bihaća ima oko 20 km.',
        'pritoka' => 'Pritoka je naselje uz Unu na samom rubu Bihaća, nekoliko minuta vožnje od centra. Imate mir kuće na rijeci, a trgovine, restorani i gradske znamenitosti su blizu.',
        'okolica' => 'Objekt je uz rijeku Unu u okolici Bihaća, izvan gradske gužve, a opet nekoliko minuta vožnje od centra. Odavde lako stižete na Štrbački buk, rafting u Grmuši i do Plitvičkih jezera, oko 35 km od grada.',
    );
    return $L[$k];
}

function abu_seed_objekti() {
    // slug => [naziv, mjesto, lokacija, kapacitet, grupa, [uvod1, uvod2], [oprema za popis], [oprema za filter], min_noci]
    return array(
        'apartman-centar' => array('Apartman Centar', 'Bihać', 'centar', 3, 'posrednicki',
            array('Apartman Centar nalazi se u najstrožem centru Bihaća, u ulici 5. korpusa, 50 metara od gradskog trga. Apartman od 45 m² kompletno je renoviran i moderno uređen, u prizemlju zgrade.', 'Za dvije osobe tu je krevet dimenzija 200 x 200 cm, a apartman prima i treću osobu. Idealan je za parove i kraće gradske boravke.'),
            array('Kapacitet 2 + 1 osoba', 'Krevet 200 x 200 cm', 'Dnevni boravak', 'Kuhinja s hladnjakom', 'Tuš kabina i sušilo za kosu', 'TV i Wi-Fi', 'Grijanje', 'Parking'),
            array('Strogi centar', 'Kuhinja', 'Parking', 'Wi-Fi'), 1),
        'apartman-city-centar' => array('Apartman City Centar', 'Bihać', 'grad', 7, 'posrednicki',
            array('Apartman City Centar je prostrani gradski apartman u ulici HVO-a u Bihaću, s kapacitetom do 7 osoba. Jedan je od rijetkih apartmana u gradu u koji stane cijela obitelj ili veće društvo.', 'Ima dnevni boravak i potpuno opremljenu kuhinju s pećnicom, pa odgovara i za duži boravak, posjet rodbini ili grupna putovanja.'),
            array('Kapacitet do 7 osoba', 'Dnevni boravak', 'Kuhinja s pećnicom i hladnjakom', 'Flat-screen TV', 'Wi-Fi'),
            array('Kuhinja', 'Wi-Fi'), 1),
        'apartman-hari' => array('Apartman Hari', 'Bihać', 'grad', 4, 'posrednicki',
            array('Apartman Hari nalazi se u ulici Alije Đerzeleza u Bihaću i prima do 4 osobe. Ima vlastitu terasu, dnevni boravak i opremljenu kuhinju.', 'Perilica rublja i besplatan parking čine ga praktičnim izborom za obitelji i goste koji ostaju više dana.'),
            array('Kapacitet do 4 osobe', 'Dnevni boravak', 'Vanjska terasa', 'Kuhinja s pećnicom', 'Perilica rublja', 'Flat-screen TV', 'Kupaonica s tuš kabinom', 'Besplatan parking', 'Wi-Fi'),
            array('Terasa', 'Kuhinja', 'Perilica rublja', 'Parking', 'Wi-Fi'), 1),
        'apartman-m-mahala' => array('Apartman M. Mahala', 'Bihać', 'grad', 6, 'posrednicki',
            array('Apartman M. Mahala nalazi se u Midžića mahali u Bihaću i prima 4 + 2 osobe. Ima dvije vanjske terase i roštilj, što je rijetkost za gradski smještaj.', 'Klimatiziran je, s dnevnim boravkom, perilicom posuđa i perilicom rublja, pa odgovara obiteljima s djecom i dužim boravcima.'),
            array('Kapacitet 4 + 2 osobe', 'Klima uređaj', 'Dnevni boravak', 'Dvije vanjske terase', 'Roštilj', 'Kuhinja s pećnicom', 'Perilica posuđa i rublja', 'TV', 'Kupaonica s kadom', 'Besplatan parking', 'Wi-Fi'),
            array('Klima', 'Terasa', 'Roštilj', 'Parking', 'Wi-Fi'), 1),
        'farah-superior-apartments' => array('Farah Superior Apartments', 'Bihać', 'grad', 8, 'posrednicki',
            array('Farah Superior Apartments nalaze se na Privilici u Bihaću, s direktnim izlazom na Unu i vlastitom plažom. Dva apartmana primaju 8 i 5 osoba.', 'Gosti mogu koristiti čamac i ribolovni pribor, a uz apartmane su tri vanjske terase i roštilj. Grad i rijeka na istom mjestu.'),
            array('2 apartmana: 8 i 5 osoba', 'Izlaz na Unu i plaža', 'Čamac i ribolovni pribor', 'Tri vanjske terase', 'Roštilj', 'Klima uređaj', 'Kuhinja s perilicom posuđa i mikrovalnom', 'Perilica i sušilica rublja', 'Kupaonica s kadom i tuš kabinom', 'Besplatan parking', 'Wi-Fi'),
            array('Izlaz na Unu', 'Plaža', 'Klima', 'Roštilj', 'Parking', 'Wi-Fi'), 2),
        'feel-luxury-apartments' => array('Feel Luxury Apartments', 'Bihać', 'grad', 12, 'posrednicki',
            array('Feel Luxury Apartments u ulici Irfana Ljubijankića u Bihaću primaju do 12 osoba, što ih čini jednim od najvećih apartmanskih smještaja u gradu.', 'Opremljeni su za grupe i velike obitelji: klima, perilica posuđa, perilica i sušilica rublja, aparat za kavu i besplatan parking.'),
            array('Kapacitet do 12 osoba', 'Klima uređaj', 'Kuhinja s perilicom posuđa', 'Aparat za kavu', 'Perilica i sušilica rublja', 'Kupaonica s kadom i tuš kabinom', 'Besplatan parking', 'Wi-Fi'),
            array('Klima', 'Kuhinja', 'Perilica rublja', 'Parking', 'Wi-Fi'), 2),
        'lux-apartments-bihac' => array('LUX Apartments Bihać', 'Bihać', 'grad', 10, 'posrednicki',
            array('LUX Apartments Bihać u ulici Irfana Ljubijankića primaju do 10 osoba. Klimatizirani su i opremljeni za grupe, obitelji i poslovne boravke.', 'Kuhinja ima perilicu posuđa, pećnicu, mikrovalnu i aparat za kavu, a tu su i perilica i sušilica rublja te besplatan parking.'),
            array('Kapacitet do 10 osoba', 'Klima uređaj', 'Kuhinja s perilicom posuđa', 'Aparat za kavu', 'Perilica i sušilica rublja', 'Kupaonica s kadom i tuš kabinom', 'Besplatan parking', 'Wi-Fi'),
            array('Klima', 'Kuhinja', 'Parking', 'Wi-Fi'), 2),
        'green-river' => array('Green River', 'Grmuša', 'grmusa', 8, 'posrednicki',
            array('Green River je vikendica u Grmuši s izlazom na Unu i vanjskom terasom iznad same rijeke. Prima 6 + 2 osobe.', 'Jutarnja kava na terasi nad Unom, kupanje i rafting pored kuće razlog su zašto se gosti vraćaju. Kuća ima dnevni boravak, kuhinju i perilicu rublja.'),
            array('Kapacitet 6 + 2 osobe', 'Izlaz na Unu', 'Terasa nad Unom', 'Dnevni boravak', 'Kuhinja', 'Perilica rublja', 'Flat-screen TV', 'Kupaonica s tuš kabinom', 'Parking', 'Wi-Fi'),
            array('Izlaz na Unu', 'Terasa', 'Perilica rublja', 'Parking', 'Wi-Fi'), 2),
        'vikendica-unadom' => array('Vikendica Unadom', 'Grmuša', 'grmusa', 8, 'posrednicki',
            array('Vikendica Unadom u Grmuši prima 6 + 2 osobe i ima izlaz na Unu s terasom iznad rijeke.', 'Smještena je u srcu rafting zone, pa odgovara grupama koje dolaze na rafting, ali i obiteljima koje žele tihi odmor uz vodu.'),
            array('Kapacitet 6 + 2 osobe', 'Izlaz na Unu', 'Terasa nad Unom', 'Dnevni boravak', 'Kuhinja', 'Flat-screen TV', 'Kupaonica s tuš kabinom', 'Parking', 'Wi-Fi'),
            array('Izlaz na Unu', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'vila-enio' => array('Vila Enio', 'Lohovo', 'lohovo', 6, 'posrednicki',
            array('Vila Enio u Lohovu je kuća za odmor za 6 osoba s bazenom i izlazom na Unu. Ima dvije terase: jednu iznad rijeke i jednu uz bazen.', 'Jedna je od rijetkih vikendica oko Bihaća koja nudi i bazen i rijeku na istom mjestu. Kuća ima dnevni boravak, kuhinju, kupaonicu i dodatni vanjski WC.'),
            array('Kapacitet do 6 osoba', 'Bazen', 'Izlaz na Unu', 'Terasa nad Unom', 'Terasa uz bazen', 'Dnevni boravak', 'Kuhinja', 'TV', 'Kupaonica i vanjski WC', 'Parking', 'Wi-Fi'),
            array('Bazen', 'Izlaz na Unu', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'vikendica-rekic' => array('Vikendica Rekić', 'Pritoka', 'pritoka', 8, 'posrednicki',
            array('Vikendica Rekić u Pritoci prima do 8 osoba i ima izlaz na Unu s terasom iznad rijeke.', 'Kuća ima dnevni boravak, opremljenu kuhinju i kupaonicu s tuš kabinom. Grad je blizu, a spavate uz šum rijeke.'),
            array('Kapacitet do 8 osoba', 'Izlaz na Unu', 'Terasa nad Unom', 'Dnevni boravak', 'Kuhinja', 'Flat-screen TV', 'Kupaonica s tuš kabinom', 'Parking', 'Wi-Fi'),
            array('Izlaz na Unu', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'japodska-kucica-na-drvetu' => array('Japodska kućica na drvetu', 'Bihać i okolica', 'okolica', 4, 'posrednicki',
            array('Japodska kućica na drvetu je kućica za 4 osobe u krošnji, s izlazom na Unu. Jedan je od najneobičnijih smještaja u Bihaću i okolici.', 'Ispod kućice je natkriveni prostor za sjedenje, a uz nju terasa. Za parove i obitelji koje žele odmor u prirodi s pričom.'),
            array('Kapacitet do 4 osobe', 'Izlaz na Unu', 'Terasa', 'Prostor za sjedenje ispod kućice', 'TV', 'Wi-Fi', 'Besplatan parking'),
            array('Izlaz na Unu', 'Terasa', 'Parking', 'Wi-Fi'), 1),
        'vikend-kuca-basic' => array('Vikend kuća Bašić', 'Bihać i okolica', 'okolica', 6, 'posrednicki',
            array('Vikend kuća Bašić je kuća za odmor za 6 osoba s izlazom na Unu. Kreveti su bračni i odvojeni, pa kuća odgovara i obiteljima i društvu.', 'Uz kuću su terasa i roštilj, a u kući dnevni boravak i opremljena kuhinja.'),
            array('Kapacitet do 6 osoba', 'Bračni i odvojeni kreveti', 'Izlaz na Unu', 'Terasa', 'Roštilj', 'Dnevni boravak', 'Kuhinja', 'TV', 'Wi-Fi', 'Parking'),
            array('Izlaz na Unu', 'Roštilj', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'vikend-kuca-emira' => array('Vikend kuća Emira', 'Bihać i okolica', 'okolica', 7, 'posrednicki',
            array('Vikend kuća Emira prima do 7 osoba u odvojenim krevetima i ima izlaz na Unu. Odličan izbor za društvo prijatelja, rafting ekipe i grupe.', 'Kuća ima dnevni boravak, kuhinju i terasu, a parking je uz objekt.'),
            array('Kapacitet do 7 osoba', 'Odvojeni kreveti', 'Izlaz na Unu', 'Terasa', 'Dnevni boravak', 'Kuhinja', 'Parking'),
            array('Izlaz na Unu', 'Terasa', 'Parking'), 2),
        'villa-dzane' => array('Villa Džane', 'Bihać i okolica', 'okolica', 9, 'posrednicki',
            array('Villa Džane prima do 9 osoba i ima izlaz na Unu. Kreveti su bračni i odvojeni.', 'Uz vilu su terasa i roštilj, a u kući dnevni boravak i kuhinja. Za veće grupe može se kombinirati s Vikendicom Džane 2.'),
            array('Kapacitet do 9 osoba', 'Bračni i odvojeni kreveti', 'Izlaz na Unu', 'Terasa', 'Roštilj', 'Dnevni boravak', 'Kuhinja', 'TV', 'Wi-Fi', 'Parking'),
            array('Izlaz na Unu', 'Roštilj', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'vikendica-dzane-2' => array('Vikendica Džane 2', 'Bihać i okolica', 'okolica', 7, 'posrednicki',
            array('Vikendica Džane 2 prima 5 + 2 osobe i ima izlaz na Unu. Uz kuću su terasa i roštilj.', 'Ima dnevni boravak i opremljenu kuhinju, pa odgovara obiteljima i manjim grupama. Za veće grupe može se kombinirati s Villom Džane.'),
            array('Kapacitet 5 + 2 osobe', 'Izlaz na Unu', 'Terasa', 'Roštilj', 'Dnevni boravak', 'Kuhinja', 'TV', 'Wi-Fi', 'Parking'),
            array('Izlaz na Unu', 'Roštilj', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'vikendica-karakas' => array('Vikendica Karakaš', 'Bihać i okolica', 'okolica', 10, 'posrednicki',
            array('Vikendica Karakaš prima 9 + 1 osobu i ima izlaz na Unu. Prostrana terasa i roštilj središte su druženja.', 'Kreveti su bračni i odvojeni, a kuća ima dnevni boravak i kuhinju. Za velike obitelji i grupe prijatelja.'),
            array('Kapacitet 9 + 1 osoba', 'Bračni i odvojeni kreveti', 'Izlaz na Unu', 'Prostrana terasa', 'Roštilj', 'Dnevni boravak', 'Kuhinja', 'TV'),
            array('Izlaz na Unu', 'Roštilj', 'Terasa'), 2),
        'villa-mekic' => array('Villa Mekić', 'Bihać i okolica', 'okolica', 9, 'posrednicki',
            array('Villa Mekić prima do 9 osoba u odvojenim krevetima, u tri sobe: 2 + 4 + 3.', 'Ima dnevni boravak, kuhinju, terasu i roštilj. Raspored soba odgovara grupama prijatelja, sportskim ekipama i obiteljima koje putuju zajedno.'),
            array('Kapacitet do 9 osoba', 'Sobe 2 + 4 + 3', 'Odvojeni kreveti', 'Terasa', 'Roštilj', 'Dnevni boravak', 'Kuhinja', 'TV', 'Wi-Fi', 'Parking'),
            array('Roštilj', 'Terasa', 'Parking', 'Wi-Fi'), 2),
        'villa-mekic-2' => array('Villa Mekić 2', 'Bihać i okolica', 'okolica', 20, 'posrednicki',
            array('Villa Mekić 2 prima do 20 osoba u apartmanima, što je čini jednim od najvećih objekata u ponudi.', 'Idealna je za team building, rafting grupe, sportske ekipe, svadbene goste i velika obiteljska okupljanja. Na raspolaganju su terasa, roštilj, kuhinja i parking.'),
            array('Kapacitet do 20 osoba', 'Smještaj u apartmanima', 'Terasa', 'Roštilj', 'Dnevni boravak', 'Kuhinja', 'TV', 'Wi-Fi', 'Parking'),
            array('Roštilj', 'Terasa', 'Parking', 'Wi-Fi'), 2),
    );
}

function abu_seed_objekt_html($o) {
    $h = '';
    foreach ($o[5] as $p) { $h .= '<p>' . esc_html($p) . "</p>\n"; }
    $h .= "<h2>Što objekt nudi</h2>\n<ul>\n";
    foreach ($o[6] as $a) { $h .= '<li>' . esc_html($a) . "</li>\n"; }
    $h .= "</ul>\n<h2>Gdje se nalazi</h2>\n<p>" . esc_html(abu_seed_lok($o[2])) . "</p>\n";
    $h .= '<h2>Kako rezervirati</h2>' . "\n" . '<p>Odaberite datume u kalendaru i pošaljite upit. Vlasnik potvrđuje u roku od 12 sati, a potvrdu s cijenom dobivate e-mailom i SMS-om. Uz potvrdu rezervacije šaljemo adresu i kontakt vlasnika. Plaćanje gotovinom pri dolasku.</p>';
    return $h;
}
