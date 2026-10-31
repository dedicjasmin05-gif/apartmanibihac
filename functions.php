<?php
if (!defined('ABSPATH')) { exit; }

define('AB_VER', '1.0.0');

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('custom-logo');
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
function ab_cijena_txt($id) {
    $c = (float) ab_m($id, 'cijena');
    return $c > 0 ? 'od ' . number_format($c, 0, ',', '.') . ' € / noć' : 'cijena na upit';
}
function ab_osobe($n) {
    $n = (int) $n; $m10 = $n % 10; $m100 = $n % 100;
    return $n . ' ' . (($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) ? 'osobe' : 'osoba');
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
    ob_start(); ?>
    <article class="card">
      <a class="card__media" href="<?php echo esc_url($url); ?>" tabindex="-1" aria-hidden="true"><?php echo $img ? $img : '<span class="card__noimg"></span>'; // phpcs:ignore ?></a>
      <div class="card__body">
        <p class="card__place"><?php echo esc_html(ab_m($id, 'mjesto')); ?><?php if ($slobodno === true) : ?> <span class="tag tag--free">slobodno za vaše datume</span><?php endif; ?></p>
        <h3 class="card__title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($id)); ?></a></h3>
        <ul class="card__facts">
          <?php if ($kap) : ?><li>do <?php echo esc_html(ab_osobe($kap)); ?></li><?php endif; ?>
          <?php foreach (array_slice((array) $op, 0, 2) as $o) : ?><li><?php echo esc_html($o); ?></li><?php endforeach; ?>
        </ul>
        <p class="card__price"><?php echo esc_html(ab_cijena_txt($id)); ?></p>
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
    $org = array('@type' => 'TravelAgency', '@id' => home_url('/#org'), 'name' => get_bloginfo('name'), 'url' => home_url('/'), 'telephone' => ab_tel(), 'email' => ab_mod('ab_email', ''),
        'address' => array('@type' => 'PostalAddress', 'addressLocality' => 'Bihać', 'addressCountry' => 'BA'),
        'areaServed' => array('Bihać', 'Lohovo', 'Grmuša', 'Pritoka', 'Nacionalni park Una'),
        'sameAs' => array_values(array_filter(array(ab_mod('ab_facebook'), ab_mod('ab_instagram')))));
    $g[] = $site; $g[] = $org;
    if (is_singular('objekt')) {
        $id = get_queried_object_id();
        $sl = ab_slike($id, 6);
        $l = array('@type' => 'LodgingBusiness', 'name' => get_the_title($id), 'url' => get_permalink($id), 'description' => wp_strip_all_tags(get_the_excerpt($id)),
            'image' => array_values(array_filter(array_map(function ($i) { return wp_get_attachment_image_url($i, 'large'); }, $sl))),
            'address' => array('@type' => 'PostalAddress', 'addressLocality' => ab_m($id, 'mjesto') ? ab_m($id, 'mjesto') : 'Bihać', 'addressCountry' => 'BA'),
            'telephone' => ab_tel(), 'containedInPlace' => array('@id' => home_url('/#org')));
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
        array('Kako rezervirati?', 'Odaberite objekt i datume i pošaljite upit. Vlasnik potvrđuje u roku od 12 sati. Nakon potvrde uplaćujete kaparu od 30 %, a ostatak plaćate pri dolasku.'),
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
