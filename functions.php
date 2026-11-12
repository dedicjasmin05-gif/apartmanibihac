<?php
if (!defined('ABSPATH')) { exit; }

define('AB_VER', '1.1.11');

// Brzina: bez WordPress emoji skripte i s ranijom vezom na Google Fonts.
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('wp_resource_hints', function ($urls, $relation) {
    if ($relation === 'preconnect') { $urls[] = 'https://fonts.googleapis.com'; }
    return $urls;
}, 10, 2);

// Google Fonts ne smije kočiti prvi prikaz. Dok se ne učitaju, vidi se sistemski font.
add_filter('style_loader_tag', function ($tag, $handle) {
    if ($handle !== 'ab-fonts') { return $tag; }
    $async = str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
    return $async . '<noscript>' . $tag . '</noscript>';
}, 10, 2);

// Manja verzija naslovne slike za mobitel, ako je slika u medijima.
function ab_hero_srcset($src) {
    $id = attachment_url_to_postid($src);
    $set = $id ? wp_get_attachment_image_srcset($id, 'full') : '';
    return $set ? ' srcset="' . esc_attr($set) . '" sizes="100vw"' : '';
}

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('custom-logo', array('flex-width' => true, 'flex-height' => true));
    add_theme_support('abu-kontakt'); // tema sama prikazuje kontakt traku; dodatak je ne ispisuje
    add_image_size('ab-card', 720, 540, true);
    add_image_size('ab-wide', 1600, 900, true);
    register_nav_menus(array('glavni' => 'Glavni izbornik', 'podnozje' => 'Podnožje'));
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ab-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap', array(), null);
    wp_enqueue_style('ab-main', get_template_directory_uri() . '/assets/main.css', array(), AB_VER);
    wp_enqueue_script('ab-main', get_template_directory_uri() . '/assets/main.js', array(), AB_VER, true);
});
add_filter('wp_resource_hints', function ($urls, $rel) {
    if ($rel === 'preconnect') { $urls[] = array('href' => 'https://fonts.gstatic.com', 'crossorigin'); }
    return $urls;
}, 10, 2);

/* ---------- Oprema (tags za objekte) ---------- */
add_action('init', function () {
    register_taxonomy('oprema', 'objekt', array(
        'label' => 'Oprema', 'public' => true, 'hierarchical' => false, 'show_in_rest' => true, 'show_admin_column' => true,
        'rewrite' => array('slug' => 'oprema'),
    ));
}, 20);

/* ---------- Postavke teme (Prilagodi) ---------- */
add_action('customize_register', function ($c) {
    $c->add_section('ab_kontakt', array('title' => 'Apartmani Bihać: kontakt i početna', 'priority' => 30));
    $polja = array(
        'ab_tel' => array('Telefon za goste', '+387 61 591 320'),
        'ab_wa' => array('WhatsApp broj (samo znamenke)', '38761591320'),
        'ab_email' => array('E-mail', 'apartmaniubihacu@gmail.com'),
        'ab_naslov' => array('Naslov na početnoj', 'Smještaj u Bihaću, uz rijeku Unu'),
        'ab_podnaslov' => array('Podnaslov na početnoj', 'Apartmani u gradu i kuće za odmor na obali. Pošaljite upit, vlasnik potvrđuje u roku od 12 sati.'),
        'ab_cijena_od' => array('Cijena od (€)', '25'),
        'ab_facebook' => array('Facebook URL', 'https://www.facebook.com/'),
        'ab_instagram' => array('Instagram URL', 'https://www.instagram.com/'),
    );
    foreach ($polja as $k => $p) {
        $c->add_setting($k, array('default' => $p[1], 'sanitize_callback' => 'sanitize_text_field'));
        $c->add_control($k, array('label' => $p[0], 'section' => 'ab_kontakt', 'type' => 'text'));
    }
});
function ab_mod($k, $d = '') { return get_theme_mod($k, $d); }
function ab_tel() { return ab_mod('ab_tel', '+387 61 591 320'); }
function ab_wa($text = '') {
    $n = preg_replace('/\D/', '', ab_mod('ab_wa', '38761591320'));
    return 'https://wa.me/' . $n . ($text ? '?text=' . rawurlencode($text) : '');
}

/* ---------- Mjesta uz Unu (redom niz rijeku) ---------- */
function ab_mjesta() {
    return array(
        'Lohovo'  => 'Mirna obala južno od grada, kupanje i bistra voda',
        'Bihać'   => 'Apartmani u gradu, sve na pješačkoj udaljenosti',
        'Pritoka' => 'Kuće na Uni na samom rubu grada',
        'Grmuša'  => 'Rafting i kuće za odmor sjeverno od Bihaća',
    );
}

/* ---------- Pomoćne funkcije za objekte ---------- */
function ab_m($id, $k) { return function_exists('abu_m') ? abu_m($id, $k) : get_post_meta($id, '_abu_' . $k, true); }
function ab_slike($id, $max = 30) {
    $ids = array();
    if (has_post_thumbnail($id)) { $ids[] = get_post_thumbnail_id($id); }
    foreach (get_attached_media('image', $id) as $a) { if (!in_array($a->ID, $ids, true)) { $ids[] = $a->ID; } }
    if (preg_match_all('/wp-image-(\d+)/', (string) get_post_field('post_content', $id), $m)) {
        foreach ($m[1] as $i) { $i = (int) $i; if (!in_array($i, $ids, true)) { $ids[] = $i; } }
    }
    return array_slice($ids, 0, $max);
}
function ab_cijena_txt($id, $l = 'bs') {
    $c = (float) ab_m($id, 'cijena');
    $t = array('bs' => array('od %s € / noć', 'cijena na upit'), 'en' => array('from €%s / night', 'price on request'), 'de' => array('ab %s € / Nacht', 'Preis auf Anfrage'));
    $t = isset($t[$l]) ? $t[$l] : $t['bs'];
    return $c > 0 ? sprintf($t[0], number_format($c, 0, ',', '.')) : $t[1];
}
function ab_osobe($n, $l = 'bs') {
    $n = (int) $n; $m10 = $n % 10; $m100 = $n % 100;
    if ($l === 'en') { return 'up to ' . $n . ' ' . ($n === 1 ? 'guest' : 'guests'); }
    if ($l === 'de') { return 'bis ' . $n . ' ' . ($n === 1 ? 'Person' : 'Personen'); }
    return 'do ' . $n . ' ' . (($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) ? 'osobe' : 'osoba');
}
/** Nazivi opreme na engleskom i njemačkom (kartice na /en/ i /de/). */
function ab_oprema_naziv($n, $l = 'bs') {
    $t = array(
        'Strogi centar' => array('City centre', 'Stadtzentrum'), 'Kuhinja' => array('Kitchen', 'Küche'), 'Parking' => array('Parking', 'Parkplatz'),
        'Wi-Fi' => array('Wi-Fi', 'WLAN'), 'Terasa' => array('Terrace', 'Terrasse'), 'Perilica rublja' => array('Washing machine', 'Waschmaschine'),
        'Klima' => array('Air conditioning', 'Klimaanlage'), 'Roštilj' => array('Barbecue', 'Grill'), 'Izlaz na Unu' => array('Access to the Una', 'Zugang zur Una'),
        'Plaža' => array('Beach', 'Badestelle'), 'Bazen' => array('Pool', 'Pool'),
    );
    $i = $l === 'en' ? 0 : ($l === 'de' ? 1 : -1);
    return ($i >= 0 && isset($t[$n])) ? $t[$n][$i] : $n;
}

/* ---------- Pretraga smještaja ---------- */
function ab_upit_objekti($args = array()) {
    $a = wp_parse_args($args, array('mjesto' => '', 'osobe' => 0, 'dolazak' => '', 'odlazak' => '', 'oprema' => '', 'broj' => -1, 'grupa' => ''));
    $meta = array('relation' => 'AND');
    if ($a['mjesto']) { $meta[] = array('key' => '_abu_mjesto', 'value' => $a['mjesto'], 'compare' => 'LIKE'); }
    if ($a['osobe']) { $meta[] = array('key' => '_abu_kapacitet', 'value' => (int) $a['osobe'], 'compare' => '>=', 'type' => 'NUMERIC'); }
    if ($a['grupa']) { $meta[] = array('key' => '_abu_grupa', 'value' => $a['grupa']); }
    $q = array('post_type' => 'objekt', 'post_status' => 'publish', 'posts_per_page' => 200, 'meta_query' => $meta, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true);
    if ($a['oprema']) { $q['tax_query'] = array(array('taxonomy' => 'oprema', 'field' => 'slug', 'terms' => $a['oprema'])); }
    $ids = get_posts(array_merge($q, array('fields' => 'ids')));
    // vlastiti objekti prvi
    usort($ids, function ($x, $y) { return (ab_m($x, 'grupa') === 'vlastiti' ? 0 : 1) - (ab_m($y, 'grupa') === 'vlastiti' ? 0 : 1); });
    $dat = ab_datumi_ok($a['dolazak'], $a['odlazak']);
    $out = array();
    foreach ($ids as $id) {
        $slobodno = null;
        if ($dat && function_exists('abu_zauzeto')) { $slobodno = !abu_zauzeto($id, $a['dolazak'], $a['odlazak']); }
        if ($dat && $slobodno === false) { continue; }
        $out[] = array('id' => $id, 'slobodno' => $slobodno);
        if ($a['broj'] > 0 && count($out) >= $a['broj']) { break; }
    }
    return $out;
}
function ab_datumi_ok($d, $o) {
    $ok = function ($s) { $x = DateTime::createFromFormat('!Y-m-d', (string) $s); return $x && $x->format('Y-m-d') === $s; };
    return $ok($d) && $ok($o) && $o > $d;
}
function ab_get($k) { return isset($_GET[$k]) ? sanitize_text_field(wp_unslash($_GET[$k])) : ''; }

/* ---------- Kartica objekta ---------- */
function ab_kartica($id, $slobodno = null, $qs = array()) {
    $img = get_the_post_thumbnail($id, 'ab-card', array('loading' => 'lazy', 'class' => 'card__img', 'alt' => get_the_title($id) . ', ' . ab_m($id, 'mjesto')));
    if (!$img) { $sl = ab_slike($id, 1); if ($sl) { $img = wp_get_attachment_image($sl[0], 'ab-card', false, array('loading' => 'lazy', 'class' => 'card__img')); } }
    $url = $qs ? add_query_arg($qs, get_permalink($id)) : get_permalink($id);
    $kap = (int) ab_m($id, 'kapacitet');
    $op = wp_get_post_terms($id, 'oprema', array('fields' => 'names'));
    $l = (isset($qs['lang']) && in_array($qs['lang'], array('en', 'de'), true)) ? $qs['lang'] : 'bs';
    ob_start(); ?>
    <article class="card">
      <a class="card__media" href="<?php echo esc_url($url); ?>" tabindex="-1" aria-hidden="true"><?php echo $img ? $img : '<span class="card__noimg"></span>'; // phpcs:ignore ?></a>
      <div class="card__body">
        <p class="card__place"><?php echo esc_html(ab_m($id, 'mjesto')); ?><?php if ($slobodno === true) : ?> <span class="tag tag--free"><?php echo esc_html($l === 'en' ? 'available for your dates' : ($l === 'de' ? 'frei für Ihre Daten' : 'slobodno za vaše datume')); ?></span><?php endif; ?></p>
        <h3 class="card__title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($id)); ?></a></h3>
        <ul class="card__facts">
          <?php if ($kap) : ?><li><?php echo esc_html(ab_osobe($kap, $l)); ?></li><?php endif; ?>
          <?php foreach (array_slice((array) $op, 0, 2) as $o) : ?><li><?php echo esc_html(ab_oprema_naziv($o, $l)); ?></li><?php endforeach; ?>
        </ul>
        <p class="card__price"><?php echo esc_html(ab_cijena_txt($id, $l)); ?></p>
      </div>
    </article>
    <?php return ob_get_clean();
}

/* ---------- Header slike (stranica "Header slike" s fotografijama) ---------- */
function ab_hero_slike() {
    $p = get_page_by_path('header-slike');
    $out = array();
    if ($p && preg_match_all('/<img[^>]+>/i', $p->post_content, $m)) {
        foreach ($m[0] as $tag) {
            if (preg_match('/src="([^"]+)"/', $tag, $s)) {
                preg_match('/alt="([^"]*)"/', $tag, $al);
                $out[] = array('src' => $s[1], 'alt' => isset($al[1]) ? $al[1] : '');
            }
        }
    }
    if (!$out) { foreach (ab_slike_svih(3) as $id) { $out[] = array('src' => wp_get_attachment_image_url($id, 'ab-wide'), 'alt' => get_post_meta($id, '_wp_attachment_image_alt', true)); } }
    return $out;
}
function ab_slike_svih($n) {
    $ids = get_posts(array('post_type' => 'objekt', 'posts_per_page' => $n, 'fields' => 'ids', 'meta_key' => '_thumbnail_id'));
    return array_filter(array_map('get_post_thumbnail_id', $ids));
}

/* ---------- SEO: schema.org ---------- */
add_action('wp_head', function () {
    $g = array();
    $site = array('@type' => 'WebSite', '@id' => home_url('/#web'), 'url' => home_url('/'), 'name' => get_bloginfo('name'), 'inLanguage' => 'bs');
    $org = array('@type' => 'TravelAgency', '@id' => home_url('/#org'), 'name' => get_bloginfo('name'), 'url' => home_url('/'), 'telephone' => ab_tel(),
        'address' => array('@type' => 'PostalAddress', 'addressLocality' => 'Bihać', 'postalCode' => '77000', 'addressCountry' => 'BA'),
        'priceRange' => ab_mod('ab_cijena_od', '25') . '–100 €',
        'areaServed' => array('Bihać', 'Lohovo', 'Grmuša', 'Pritoka', 'Nacionalni park Una'),
        'sameAs' => array(ab_fb(), ab_ig()));
    if (ab_mod('ab_email', '')) { $org['email'] = ab_mod('ab_email', ''); }
    $g[] = $site; $g[] = $org;
    if (is_singular('objekt')) {
        $id = get_queried_object_id();
        $sl = ab_slike($id, 6);
        $l = array('@type' => 'LodgingBusiness', 'name' => get_the_title($id), 'url' => get_permalink($id), 'description' => wp_strip_all_tags(get_the_excerpt($id)),
            'image' => array_values(array_filter(array_map(function ($i) { return wp_get_attachment_image_url($i, 'large'); }, $sl))),
            'address' => array('@type' => 'PostalAddress', 'addressLocality' => ab_m($id, 'mjesto') ? ab_m($id, 'mjesto') : 'Bihać', 'addressCountry' => 'BA'),
            'telephone' => ab_tel(), 'petsAllowed' => 'Psi i mačke na upit; vlasnik potvrđuje u odgovoru na upit.', 'containedInPlace' => array('@id' => home_url('/#org')));
        $c = (float) ab_m($id, 'cijena');
        if ($c) { $l['priceRange'] = 'od ' . $c . ' EUR'; }
        $kap = (int) ab_m($id, 'kapacitet');
        if ($kap) { $l['maximumAttendeeCapacity'] = $kap; }
        $am = wp_get_post_terms($id, 'oprema', array('fields' => 'names'));
        if ($am && !is_wp_error($am)) { $l['amenityFeature'] = array_map(function ($n) { return array('@type' => 'LocationFeatureSpecification', 'name' => $n, 'value' => true); }, $am); }
        $g[] = $l;
        $g[] = array('@type' => 'BreadcrumbList', 'itemListElement' => array(
            array('@type' => 'ListItem', 'position' => 1, 'name' => 'Početna', 'item' => home_url('/')),
            array('@type' => 'ListItem', 'position' => 2, 'name' => 'Smještaj', 'item' => home_url('/smjestaj/')),
            array('@type' => 'ListItem', 'position' => 3, 'name' => get_the_title($id), 'item' => get_permalink($id))));
    }
    if (is_front_page()) {
        $g[] = array('@type' => 'FAQPage', 'mainEntity' => array_map(function ($f) { return array('@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f[1])); }, ab_faq()));
    }
    echo '<script type="application/ld+json">' . wp_json_encode(array('@context' => 'https://schema.org', '@graph' => $g), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n"; // phpcs:ignore
    if (function_exists('abu_seo_tekuca')) { return; } // opis ispisuje dodatak (seo.php), bez duplikata
    if (is_singular() && !is_front_page()) {
        $d = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_queried_object_id())), 28, '…');
        if ($d) { echo '<meta name="description" content="' . esc_attr($d) . '">' . "\n"; }
    } elseif (is_front_page()) {
        echo '<meta name="description" content="' . esc_attr('Apartmani i vikendice u Bihaću, Lohovu, Grmuši i Pritoci, uz rijeku Unu. Od ' . ab_mod('ab_cijena_od', '25') . ' € po noći. Direktan upit, potvrda vlasnika u 12 sati.') . '">' . "\n";
    }
}, 5);

function ab_faq() {
    return array(
        array('Koliko košta smještaj u Bihaću?', 'Apartmani i kuće za odmor u našoj ponudi kreću od ' . ab_mod('ab_cijena_od', '25') . ' € po noći za cijeli objekt. Cijena ovisi o objektu, sezoni i broju gostiju, a točan iznos vidite u potvrdi upita.'),
        array('Kako rezervirati?', 'Odaberite objekt i datume i pošaljite upit. Vlasnik potvrđuje u roku od 12 sati. Većinu smještaja plaćate gotovinom pri dolasku. Avansno plaćanje kod pojedinih objekata ovisi o pravilima tog objekta, i to vidite u potvrdi.'),
        array('Ima li smještaj uz samu rijeku Unu?', 'Da. U Lohovu, Pritoci i Grmuši nudimo kuće s izlazom na Unu, neke s terasom iznad rijeke ili bazenom.'),
        array('Koliko je Bihać udaljen od Plitvičkih jezera?', 'Do ulaza u Nacionalni park Plitvička jezera ima oko 35 km, otprilike 40 minuta vožnje.'),
        array('Mogu li uz smještaj rezervirati rafting?', 'Da. Rafting na Uni, team building i ručak uz rijeku organizira Una Olympic Rafting, a paket smještaja i raftinga dogovaramo u istom upitu.'),
    );
}

/* ---------- Čistoća ---------- */
remove_action('wp_head', 'wp_generator');
add_filter('excerpt_length', function () { return 26; });
add_filter('excerpt_more', function () { return '…'; });

/* ---------- Stranica /smjestaj/ bez ručnog stvaranja ---------- */
add_action('init', function () {
    add_rewrite_rule('^smjestaj/?$', 'index.php?ab_smjestaj=1', 'top');
});
add_filter('query_vars', function ($v) { $v[] = 'ab_smjestaj'; return $v; });
add_filter('template_include', function ($t) {
    if (get_query_var('ab_smjestaj')) { status_header(200); return get_template_directory() . '/smjestaj.php'; }
    return $t;
});
add_filter('pre_get_document_title', function ($t) {
    if (get_query_var('ab_smjestaj')) { $m = ab_get('mjesto'); return ($m ? 'Smještaj ' . $m : 'Apartmani i vikendice u Bihaću i na Uni') . ' | ' . get_bloginfo('name'); }
    return $t;
});
add_action('after_switch_theme', function () { flush_rewrite_rules(); });

/* ---------- Jezik, prijevodi, ikone, izbornik (1.1) ---------- */
function ab_jezik() {
    if (function_exists('abu_jezik_stranice') && did_action('wp') && ($j = abu_jezik_stranice())) { return $j; }
    return 'bs';
}
function ab_t($k) {
    $t = array(
        'bs' => array('na_fb' => 'na Facebooku', 'recenzija' => 'recenzija', 'pratitelja' => 'pratitelja', 'preporuka' => 'preporuka', 'rezerviraj' => 'Pronađi smještaj',
            'smjestaj' => 'Smještaj', 'restorani' => 'Restorani', 'izlasci' => 'Izlasci', 'izleti' => 'Izleti', 'rafting' => 'Rafting', 'tb' => 'Team building', 'vodic' => 'Vodič',
            'nazovi' => 'Nazovi', 'upit' => 'Upit'),
        'en' => array('na_fb' => 'on Facebook', 'recenzija' => 'reviews', 'pratitelja' => 'followers', 'preporuka' => 'recommend', 'rezerviraj' => 'Find a stay',
            'smjestaj' => 'Stays', 'restorani' => 'Restaurants', 'izlasci' => 'Nightlife', 'izleti' => 'Day trips', 'rafting' => 'Rafting', 'tb' => 'Team building', 'vodic' => 'Guide',
            'nazovi' => 'Call', 'upit' => 'Enquire'),
        'de' => array('na_fb' => 'auf Facebook', 'recenzija' => 'Bewertungen', 'pratitelja' => 'Follower', 'preporuka' => 'Empfehlung', 'rezerviraj' => 'Unterkunft finden',
            'smjestaj' => 'Unterkünfte', 'restorani' => 'Restaurants', 'izlasci' => 'Ausgehen', 'izleti' => 'Ausflüge', 'rafting' => 'Rafting', 'tb' => 'Teambuilding', 'vodic' => 'Reiseführer',
            'nazovi' => 'Anrufen', 'upit' => 'Anfrage'),
    );
    $l = ab_jezik();
    return isset($t[$l][$k]) ? $t[$l][$k] : $t['bs'][$k];
}
function ab_fb() { $u = ab_mod('ab_facebook'); return ($u && strlen($u) > 26) ? $u : 'https://www.facebook.com/ApartmaniVikendice'; }
function ab_ig() { $u = ab_mod('ab_instagram'); return ($u && strlen($u) > 27) ? $u : 'https://www.instagram.com/apartmaniivikendice/'; }
/** Adresa vodiča na jeziku stranice (ako postoji prijevod). */
function ab_url($bs, $en = '', $de = '') {
    $l = ab_jezik();
    $p = $l === 'en' && $en ? $en : ($l === 'de' && $de ? $de : $bs);
    return home_url('/' . trim($p, '/') . '/');
}
function ab_izbornik() {
    return array(
        array(ab_t('smjestaj'), home_url('/smjestaj/')),
        array(ab_t('restorani'), ab_url('restorani-bihac', 'en/restaurants-bihac', 'de/restaurants-bihac')),
        array(ab_t('izlasci'), ab_url('nocni-zivot-bihac', 'en/nightlife-bihac', 'de/nachtleben-bihac')),
        array(ab_t('izleti'), ab_url('izleti-bihac', 'en/day-trips-bihac', 'de/ausfluege-bihac')),
        array(ab_t('rafting'), ab_url('rafting-na-uni', 'en/rafting-una', 'de/rafting-una')),
        array(ab_t('tb'), ab_url('team-building-bihac', 'en/team-building-bihac', 'de/teambuilding-bihac')),
        array(ab_t('vodic'), home_url('/vodic/')),
    );
}
function ab_ikona($m) {
    $p = array(
        'tel' => 'M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z',
        'mail' => 'M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z',
        'fb' => 'M14 8.5V6.8c0-.8.5-1 .9-1H17V2.2L14.1 2C11 2 10.2 4.3 10.2 6v2.5H8V12h2.2v10H14V12h2.8l.4-3.5z',
        'wa' => 'M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.8 14.1c-.2.7-1.4 1.3-2 1.4-.5.1-1.1.1-1.8-.1-.4-.1-1-.3-1.7-.6-3-1.3-5-4.3-5.1-4.5-.2-.2-1.2-1.6-1.2-3.1s.8-2.2 1-2.5c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.3.5-.4.5c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.1.1.7-.1 1.3z',
        'vb' => 'M11.4 1C8.6 1 3 1.6 3 9.2v2.6c0 3.4 1.1 5.6 3.5 6.6v3.3c0 .5.6.8 1 .4l2.7-2.9h1.3c2.8 0 9.5-.6 9.5-8.2V9.2C21 1.6 14.3 1 11.4 1zm4.7 13.8c-.3.6-1 1.2-1.6 1.1-.9-.2-3.8-1.6-5.6-3.7-1.8-2.1-2.4-4.3-2.4-4.9.1-.7.7-1.2 1.2-1.4.4-.1.7 0 .9.3l1 1.5c.2.4.1.8-.2 1l-.5.4c.4 1.2 1.6 2.7 3 3.2l.4-.5c.3-.3.7-.4 1-.2l1.6.9c.4.2.5.7.2 1.1z',
        'ig' => 'M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.3 5.5a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4zM16 1.2C14.3 1.1 9.7 1.1 8 1.2 4.3 1.4 1.4 4.3 1.2 8c-.1 1.7-.1 6.3 0 8 .2 3.7 3.1 6.6 6.8 6.8 1.7.1 6.3.1 8 0 3.7-.2 6.6-3.1 6.8-6.8.1-1.7.1-6.3 0-8C22.6 4.3 19.7 1.4 16 1.2zm4.9 14.6c-.1 2.8-2.3 5-5.1 5.1-1.6.1-6 .1-7.6 0-2.8-.1-5-2.3-5.1-5.1-.1-1.6-.1-6 0-7.6.1-2.8 2.3-5 5.1-5.1 1.6-.1 6-.1 7.6 0 2.8.1 5 2.3 5.1 5.1.1 1.6.1 6 0 7.6z',
        'pin' => 'M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z',
        'star' => 'M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z',
    );
    return isset($p[$m]) ? '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="' . $p[$m] . '"/></svg>' : '';
}
/** Blok povjerenja (Facebook): 139.000 pratitelja, 9.362 recenzije, 100 % preporuka. */
function ab_povjerenje($klasa = '') {
    ob_start(); ?>
    <a class="trust <?php echo esc_attr($klasa); ?>" href="<?php echo esc_url(ab_fb()); ?>" target="_blank" rel="noopener">
      <span class="trust__fb"><?php echo ab_ikona('fb'); // phpcs:ignore ?></span>
      <span class="trust__txt"><span class="stars" aria-label="5/5">★★★★★</span><b>9.362 <?php echo esc_html(ab_t('recenzija') . ' ' . ab_t('na_fb')); ?> · 100 % <?php echo esc_html(ab_t('preporuka')); ?></b><small>139.000 <?php echo esc_html(ab_t('pratitelja')); ?> · Apartmani &amp; Vikendice</small></span>
    </a>
    <?php return ob_get_clean();
}
