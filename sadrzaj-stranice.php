<?php
/** Stranice i blog članci koje sajt sam napravi pri nadogradnji dodatka. */
if (!defined('ABSPATH')) { exit; }

function abu_seed_mjesto($naziv, $tag, $opis, $adresa, $upit) {
    return '<h2>' . esc_html($naziv) . '</h2><p><strong>' . esc_html($tag) . '.</strong> ' . esc_html($opis) . '</p>'
        . ($adresa ? '<p>Adresa: ' . esc_html($adresa) . '</p>' : '')
        . '<p><a href="https://www.google.com/maps/search/?api=1&amp;query=' . rawurlencode($upit) . '" target="_blank" rel="noopener">Pogledaj na karti</a></p>';
}

function abu_seed_stranice() {
    $wa = '<a href="https://wa.me/38761591320" target="_blank" rel="noopener">WhatsApp</a>';
    return array(
        'rafting-na-uni' => array('Rafting na Uni', 'Rafting na Uni kroz kanjon kod Bihaća, s licenciranim skiperima, opremom i ručkom uz rijeku. Smještaj i rafting u istom upitu.',
            '<p>Una je jedna od najljepših rijeka za rafting u regiji: bistra, zelena i s brzacima koji su uzbudljivi, ali prikladni i za početnike. Rafting organizira <strong>Una Olympic Rafting</strong> iz Grmuše, sa skiperima, kacigama, prslucima i neoprenskim odijelima.</p>'
            . '<h2>Što je uključeno</h2><ul><li>Vođeni spust uz skipera u svakom čamcu</li><li>Kompletna zaštitna oprema</li><li>Upute o sigurnosti prije polaska</li><li>Prijevoz do starta i natrag, ovisno o turi</li><li>Ručak u restoranu uz rijeku (u paketu)</li></ul>'
            . '<h2>Za koga je</h2><p>Za grupe prijatelja, obitelji sa starijom djecom, team building i sve koji prvi put idu na rafting. Organizator bira dionicu prema iskustvu grupe i vodostaju. Za točnu dob djece i trajanje ture pitajte u upitu.</p>'
            . '<h2>Smještaj i rafting zajedno</h2><p>Najbliži smještaj raftingu je u Grmuši, desetak minuta od starta. Pošaljite upit za objekt i u poruci napišite da želite i rafting, pa vam šaljemo zajedničku ponudu. Kontakt organizatora: +387 67 103 7177.</p>'
            . '<p>Radije porukom? Pišite nam na ' . $wa . '.</p>'),
        'restorani-bihac' => array('Restorani u Bihaću', 'Gdje jesti u Bihaću: riba iz Une, travnički ćevapi, roštilj, pizza i domaća kuhana jela. Preporuke za goste.',
            '<p>Riba iz Une, travnički ćevapi, pizza ili domaća kuhana jela: ovo su mjesta koja preporučujemo našim gostima.</p>'
            . abu_seed_mjesto('River', 'Za ribu i ručak uz Unu', 'Restoran s terasom tik uz rijeku u centru grada. Ljeti je terasa jedno od najljepših mjesta u Bihaću. Preporuka: riba i bosanska jela.', 'Džemala Bijedića 12, Bihać', 'River Una Bihać')
            . abu_seed_mjesto('Kostelski buk', 'Za ambijent uz slapove Une', 'Restoran u Kosteli, oko 13 km od centra, s terasom s pogledom na slapove. Riječna riba, janjetina i domaća jela.', 'Kostela bb, Bihać', 'Kostelski buk Bihać')
            . abu_seed_mjesto('Merak', 'Za travničke ćevape', 'Ćevabdžinica poznata po travničkim ćevapima u somunu s kajmakom i lukom.', 'Plješevička 59, Bihać', 'Ćevabdžinica Merak Bihać')
            . abu_seed_mjesto('Bušel', 'Za kuhana jela', 'Domaća kuhana jela i dnevni meni, u Plješevičkoj ulici preko puta Meraka. Brzo, obilno i povoljno.', 'Plješevička, Bihać', 'Bušel Bihać')
            . abu_seed_mjesto('City', 'Za roštilj', 'Lokalna adresa za ćevape, pljeskavicu i miješano meso, za ručak ili večeru s društvom.', '', 'City Bihać')
            . abu_seed_mjesto('Pizzeria ASA', 'Za pizzu', 'Naša preporuka kad vam se jede pizza, za opušten ručak ili večeru u gradu.', '', 'Pizzeria ASA Bihać')
            . '<h2>Što probati u Bihaću</h2><p>Riječna riba, pastrmka i lipljen, prvo je što gosti traže. Uz to su bosanska klasika: ćevapi, pita, begova čorba i jela ispod sača. Za kolače svratite po baklavu i tufahiju u neku od slastičarni u centru.</p>'),
        'nocni-zivot-bihac' => array('Noćni život u Bihaću', 'Gdje izaći u Bihaću: barovi na obali Une, ljetne terase i klubovi za kasni izlazak.',
            '<p>Večer u Bihaću obično počinje šetnjom uz Unu i pićem na terasi uz rijeku. Ljeti se život seli na vodu, a vikendom se nastavlja u klubovima. Grad je malen, pa većinu lokala obiđete pješice.</p>'
            . abu_seed_mjesto('Gig Bar', 'Kafa danju, izlazak noću', 'Moderan bar uz samu rijeku. Danju za kavu na terasi, navečer za druženje i ples.', 'Husrefa Redžića 3, Bihać', 'Gig Bar Bihać')
            . abu_seed_mjesto('Bistro', 'Večer uz Unu', 'Kafić i bar na obali Une u centru, za večernje piće uz pogled na rijeku.', '', 'Bistro Una Bihać')
            . abu_seed_mjesto('Svarog Winter House Club', 'Za kasni izlazak', 'Klub s DJ programom i nastupima vikendom.', '', 'Svarog Winter House Bihać')
            . abu_seed_mjesto('Piramida', 'Za večernje druženje', 'Popularno mjesto za izlazak s društvom, s glazbenim programom vikendom.', '', 'Piramida Bihać')),
        'prijavite-objekt' => array('Prijavite svoj objekt', 'Iznajmljujete apartman ili vikendicu u Bihaću ili uz Unu? Prijavite objekt i dobivajte upite gostiju direktno.',
            '<p>Iznajmljujete apartman, kuću ili vikendicu u Bihaću ili uz Unu? Objavljujemo vaš objekt, a upiti gostiju stižu vama na e-mail i SMS, s gumbom za potvrdu. Kalendar se može povezati s vašim Bookingom ili Airbnbom, pa nema dvostrukih rezervacija.</p>'
            . '<h2>Kako radi</h2><ol><li>Ispunite obrazac ispod.</li><li>Javimo se u roku od 24 sata i dogovorimo uvjete.</li><li>Objavimo objekt s fotografijama i kalendarom.</li><li>Upite potvrđujete jednim klikom, a gost plaća kaparu prije dolaska.</li></ol>'
            . '[abu_prijava]'),
        'uvjeti-i-privatnost' => array('Uvjeti rezervacije i privatnost', 'Kako funkcionira upit, kapara, otkaz i kako koristimo vaše podatke.',
            '<h2>Upit i potvrda</h2><p>Upit nije rezervacija. Termin je za vas držan 12 sati, dok vlasnik ne potvrdi da je slobodan. Nakon potvrde dobivate ukupnu cijenu i upute za kaparu.</p>'
            . '<h2>Kapara i plaćanje</h2><p>Kapara iznosi 30 % ukupne cijene i plaća se u roku od 24 sata od potvrde, karticom ili na račun. Bez uplate se termin oslobađa. Ostatak plaćate vlasniku pri dolasku.</p>'
            . '<h2>Otkaz</h2><p>Do 14 dana prije dolaska kapara se vraća umanjena za trošak plaćanja. Kasnije se kapara ne vraća.</p>'
            . '<h2>Vaši podaci</h2><p>Ime, e-mail i broj mobitela koristimo samo za obradu upita i rezervacije. Dijelimo ih isključivo s vlasnikom objekta, i to tek nakon uplate kapare. Podatke ne prodajemo i ne šaljemo trećima. Za brisanje podataka pišite na e-mail naveden u podnožju stranice.</p>'),
        'vodic' => array('Vodič za Bihać i Unu', 'Izleti, rafting, Plitvice, gdje odsjesti i gdje jesti u Bihaću.', ''),
        'header-slike' => array('Header slike', '', '<p>Ovdje dodajte 3 do 5 fotografija za veliku sliku na početnoj stranici (Dodaj medij). Redoslijed slika je redoslijed prikaza.</p>'),
    );
}

function abu_seed_clanci() {
    $poz = '<p><a href="/smjestaj/">Pogledajte slobodan smještaj u Bihaću i uz Unu</a> ili nam pošaljite termin na <a href="https://wa.me/38761591320" target="_blank" rel="noopener">WhatsApp</a>.</p>';
    return array(
        'gdje-odsjesti-u-bihacu' => array('Gdje odsjesti u Bihaću: centar, Lohovo, Pritoka ili Grmuša',
            'Bihać ima četiri vrlo različita dijela za smještaj: centar grada, mirno Lohovo, Pritoku uz rub grada i Grmušu za rafting. Evo kako odabrati.',
            '<p><strong>Kratki odgovor:</strong> za kratki boravak bez auta odaberite apartman u centru Bihaća. Za odmor na vodi idite u Lohovo ili Pritoku, a za rafting u Grmušu.</p>'
            . '<h2>Centar Bihaća</h2><p>Sve je na pješačkoj udaljenosti: šetnica uz Unu, Kapetanova kula, restorani i kafići. Najbolje za parove, poslovne goste i vikend u gradu. Apartmani primaju od 2 do 12 osoba.</p>'
            . '<h2>Lohovo</h2><p>Mirno naselje uz Unu južno od grada, desetak kilometara od centra. Bistra voda, kupanje i kuće s izlazom na rijeku, a neke i s bazenom. Za obitelji i duži odmor.</p>'
            . '<h2>Pritoka</h2><p>Kuće na Uni na samom rubu grada. Spavate uz rijeku, a do centra se vozite nekoliko minuta. Dobar kompromis između mira i grada.</p>'
            . '<h2>Grmuša</h2><p>Oko 20 km od centra, uz rafting dionicu Une. Ovdje su baza Una Olympic Raftinga i restoran uz rijeku. Za grupe, team building i aktivan odmor.</p>'
            . '<h2>Usporedba ukratko</h2><ul><li><strong>Bez auta:</strong> centar Bihaća</li><li><strong>Kupanje u Uni:</strong> Lohovo, Pritoka</li><li><strong>Rafting:</strong> Grmuša</li><li><strong>Velike grupe (10+):</strong> veće vile u okolici i apartmani u centru</li></ul>' . $poz),
        'plitvicka-jezera-iz-bihaca' => array('Plitvička jezera iz Bihaća: udaljenost, granica i savjeti',
            'Do ulaza u Nacionalni park Plitvička jezera iz Bihaća ima oko 35 km. Kako stići, što ponijeti za granicu i kada krenuti.',
            '<p><strong>Kratki odgovor:</strong> od Bihaća do ulaza u Nacionalni park Plitvička jezera ima oko 35 km, otprilike 40 minuta vožnje, uz prelazak granice BiH i Hrvatske kod Izačića.</p>'
            . '<h2>Kako stići</h2><p>Iz Bihaća vozite prema graničnom prijelazu Izačić, pa dalje kroz Ličko Petrovo Selo prema Plitvicama. Cesta je asfaltirana i dobro označena.</p>'
            . '<h2>Granica</h2><p>Hrvatska je u schengenskom prostoru, pa na izlazu iz BiH očekujte graničnu kontrolu. Ponesite putovnicu ili osobnu iskaznicu koja vrijedi za ulazak u EU, prometnu dozvolu i zelenu kartu za auto. Ljeti i vikendom računajte na čekanje, pa krenite rano.</p>'
            . '<h2>Savjeti za posjet</h2><ul><li>Krenite rano ujutro, dok nema gužve na stazama.</li><li>Ulaznice kupite online na službenoj stranici parka, posebno ljeti.</li><li>Za obilazak računajte 4 do 6 sati.</li><li>Navečer se vratite u Bihać na večeru uz Unu.</li></ul>'
            . '<p>Bihać je odlična baza za Plitvice: smještaj je povoljniji nego uz sam park, a uz to imate Unu, rafting i Štrbački buk.</p>' . $poz),
        'kako-doci-do-bihaca' => array('Kako doći do Bihaća iz Zagreba, Beča i Ljubljane',
            'Udaljenosti i rute do Bihaća autom: iz Zagreba oko 160 km, iz Ljubljane oko 300 km, iz Beča oko 500 km. Granica, ceste i savjeti.',
            '<p><strong>Kratki odgovor:</strong> autom iz Zagreba do Bihaća ima oko 160 km (otprilike 2,5 sata), iz Ljubljane oko 300 km, a iz Beča oko 500 km. Sve rute prelaze granicu kod Izačića.</p>'
            . '<h2>Iz Zagreba</h2><p>Autocestom do Karlovca, pa državnom cestom preko Slunja i Plitvičkih jezera do granice Izačić. Usput možete stati na Plitvicama.</p>'
            . '<h2>Iz Ljubljane</h2><p>Autocestom do Zagreba i dalje kao iz Zagreba, preko Karlovca i Slunja.</p>'
            . '<h2>Iz Beča i Graza</h2><p>Autocestom preko Graza i Maribora do Zagreba, pa preko Karlovca prema Bihaću. Za dijasporu iz Austrije i Njemačke to je najčešća ruta.</p>'
            . '<h2>Granica Izačić</h2><p>Ponesite putovnicu ili osobnu iskaznicu, prometnu dozvolu i zelenu kartu. Ljeti, za praznike i vikendom zna biti čekanja, pa je najbolje krenuti rano.</p>'
            . '<h2>Parking u Bihaću</h2><p>Većina naših objekata ima vlastiti parking, a u opisu svakog objekta piše ima li ga.</p>' . $poz),
        'cijene-smjestaja-u-bihacu' => array('Koliko košta smještaj u Bihaću',
            'Cijene apartmana i vikendica u Bihaću i uz Unu kreću od 25 € po noći za cijeli objekt. Što utječe na cijenu i kako platiti manje.',
            '<p><strong>Kratki odgovor:</strong> apartmani i kuće za odmor u našoj ponudi koštaju od 25 do 100 € po noći za cijeli objekt, ovisno o veličini, sezoni i opremi.</p>'
            . '<h2>Što utječe na cijenu</h2><ul><li><strong>Sezona:</strong> srpanj i kolovoz su najtraženiji, a proljeće i jesen povoljniji.</li><li><strong>Veličina:</strong> studio za 2 osobe je jeftiniji od vile za 10 osoba, ali po osobi su velike kuće često najisplativije.</li><li><strong>Položaj:</strong> izlaz na Unu i bazen podižu cijenu.</li><li><strong>Duljina boravka:</strong> za duži boravak pitajte za popust.</li></ul>'
            . '<h2>Zašto je direktno povoljnije</h2><p>Kad rezervirate direktno kod nas, nema provizije velikih platformi, pa vlasnik može dati bolju cijenu. Kaparu od 30 % plaćate nakon potvrde, a ostatak pri dolasku.</p>' . $poz),
        'kupanje-na-uni' => array('Kupanje na Uni: gdje se kupati u Bihaću i okolici',
            'Una je ljeti čista i pogodna za kupanje. Gdje se kupati u gradu, u Lohovu i Pritoci te na što paziti.',
            '<p><strong>Kratki odgovor:</strong> ljeti se Una kupa i u samom Bihaću, a mirnija mjesta s plićacima su u Lohovu i Pritoci. Najljepše je iz kuće s vlastitim izlazom na rijeku.</p>'
            . '<h2>U gradu</h2><p>Uz šetnicu i gradske plaže kupa se većina mještana. Voda je osvježavajuća i u najtoplijim danima.</p>'
            . '<h2>Lohovo i Pritoka</h2><p>Ovdje je Una mirnija, s plićacima i travnatim obalama. Mnoge kuće za odmor imaju vlastiti izlaz na vodu i terasu iznad rijeke.</p>'
            . '<h2>Na što paziti</h2><ul><li>Una je hladna i brza na brzacima, zato djecu ne ostavljajte bez nadzora.</li><li>Ne skačite u nepoznatu vodu, dubina se brzo mijenja.</li><li>Ne ostavljajte smeće uz obalu.</li></ul>' . $poz),
        'nacionalni-park-una' => array('Nacionalni park Una: Štrbački buk, Martin Brod i Ripač',
            'Nacionalni park Una osnovan je 2008. godine. Štrbački buk, slapovi u Martin Brodu i rafting: što vidjeti i kako planirati dan iz Bihaća.',
            '<p><strong>Kratki odgovor:</strong> Nacionalni park Una osnovan je 2008. godine i obuhvaća gornji tok Une i Unca. Najpoznatiji je Štrbački buk, slap visok oko 25 metara na granici s Hrvatskom.</p>'
            . '<h2>Štrbački buk</h2><p>Najpoznatiji slap Une, s vidikovcima i drvenim stazama. Do njega se iz Bihaća vozi prema jugu, a zadnji dio puta je makadam, pa vozite polako i prije polaska provjerite stanje ceste kod uprave parka.</p>'
            . '<h2>Martin Brod</h2><p>Mjesto gdje se Unac ulijeva u Unu, s nizom slapova i sedrenih kaskada. Odličan kao drugi dio izleta.</p>'
            . '<h2>Ripač i okolica</h2><p>Uz Unu južno od Bihaća su arheološki lokaliteti i mirna mjesta za kupanje i ribolov.</p>'
            . '<h2>Plan dana</h2><ol><li>Ujutro Štrbački buk, prije gužve.</li><li>Ručak uz rijeku.</li><li>Popodne Martin Brod ili kupanje.</li><li>Navečer povratak u Bihać.</li></ol>'
            . '<p>Ulaznice i radno vrijeme provjerite na službenoj stranici Nacionalnog parka Una.</p>' . $poz),
    );
}
