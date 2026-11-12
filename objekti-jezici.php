<?php
/**
 * Stranice objekata na engleskom i njemačkom: /en/stays/<slug>/ i /de/unterkuenfte/<slug>/.
 * Tekst iz seo/objekti-prijevod.json (prevedeni opis, oprema, lokacija, SEO), a galerija, kalendar i forma upita
 * su živi (shortcodovi), pa se podaci objekta uzimaju iz baze. hreflang bs/en/de, LodgingBusiness schema na jeziku stranice.
 * Povrat: ukloniti require_once u apartmani-upiti.php (stranice ostaju u WP-u kao obične stranice).
 */
if (!defined('ABSPATH')) { exit; }

function abu_oj_putevi() { return array('en' => 'en/stays', 'de' => 'de/unterkuenfte'); }

function abu_oj_podaci() {
    static $d = null;
    if ($d !== null) { return $d; }
    $f = __DIR__ . '/seo/objekti-prijevod.json';
    $d = is_readable($f) ? (array) json_decode((string) file_get_contents($f), true) : array();
    return $d;
}

/** URL stranice objekta na jeziku (ili '' ako ne postoji). */
function abu_objekt_url($id, $l) {
    $put = abu_oj_putevi();
    if (!isset($put[$l])) { return ''; }
    $p = get_page_by_path($put[$l] . '/' . get_post_field('post_name', $id));
    return ($p && $p->post_status === 'publish') ? get_permalink($p) : '';
}

function abu_oj_t($l) {
    $t = array(
        'en' => array('hub_t' => 'Places to stay in Bihać and on the Una', 'hub_h' => 'All places to stay', 'opis' => 'About the property', 'oprema' => 'What the property offers', 'gdje' => 'Location', 'foto' => 'Photos', 'kal' => 'Available dates', 'upit' => 'Send an enquiry',
            'kako' => 'How to book', 'kako_p' => 'Choose your dates in the calendar and send an enquiry. The owner confirms within 12 hours and you receive a confirmation with the total price by e-mail. The exact address and the owner’s contact are sent with the booking confirmation. You pay in cash on arrival; no deposit is required, and if a property asks for an advance payment, this is stated in the confirmation. Dogs and cats on request.',
            'osobe' => 'up to %d guests', 'min' => 'minimum %d nights', 'bs' => 'This page in Bosnian', 'pitanja' => 'Questions? WhatsApp or Viber +387 61 591 320 (we speak English).', 'svi' => 'All accommodation'),
        'de' => array('hub_t' => 'Unterkünfte in Bihać und an der Una', 'hub_h' => 'Alle Unterkünfte', 'opis' => 'Über die Unterkunft', 'oprema' => 'Ausstattung', 'gdje' => 'Lage', 'foto' => 'Fotos', 'kal' => 'Freie Termine', 'upit' => 'Anfrage senden',
            'kako' => 'So buchen Sie', 'kako_p' => 'Wählen Sie Ihre Daten im Kalender und senden Sie eine Anfrage. Der Vermieter bestätigt innerhalb von 12 Stunden, Sie erhalten die Bestätigung mit dem Gesamtpreis per E-Mail. Die genaue Adresse und den Kontakt des Vermieters schicken wir mit der Buchungsbestätigung. Bezahlt wird bar bei Ankunft; eine Anzahlung ist nicht Pflicht, und wenn eine Unterkunft eine Vorauszahlung verlangt, steht das in der Bestätigung. Hunde und Katzen auf Anfrage.',
            'osobe' => 'bis %d Personen', 'min' => 'mindestens %d Nächte', 'bs' => 'Diese Seite auf Bosnisch', 'pitanja' => 'Fragen? WhatsApp oder Viber +387 61 591 320 (wir sprechen Deutsch).', 'svi' => 'Alle Unterkünfte'),
    );
    return $t[$l];
}

/** Sadržaj stranice (spremljen u bazu; shortcodovi se izvršavaju pri prikazu). */
function abu_oj_html($slug, $id, $l) {
    $d = abu_oj_podaci(); $x = $d['objekti'][$slug][$l]; $T = abu_oj_t($l);
    $mj = (string) get_post_meta($id, '_abu_mjesto', true);
    $kap = (int) get_post_meta($id, '_abu_kapacitet', true);
    $min = max(1, (int) get_post_meta($id, '_abu_min_noci', true));
    $lok = $d['lokacije'][$mj][$l] ?? '';
    $h = '<p class="lead"><strong>' . esc_html($mj) . '</strong> · ' . esc_html(sprintf($T['osobe'], $kap)) . ($min > 1 ? ' · ' . esc_html(sprintf($T['min'], $min)) : '') . '</p>' . "\n";
    $h .= '[abu_galerija id="' . $id . '"]' . "\n";
    $h .= '<h2>' . esc_html($T['opis']) . '</h2>' . "\n";
    foreach ((array) $x['uvod'] as $p) { $h .= '<p>' . esc_html($p) . '</p>' . "\n"; }
    $h .= '<h2>' . esc_html($T['oprema']) . '</h2>' . "\n<ul>";
    foreach ((array) $x['oprema'] as $o) { $h .= '<li>' . esc_html($o) . '</li>'; }
    $h .= "</ul>\n";
    if ($lok) { $h .= '<h2>' . esc_html($T['gdje']) . '</h2>' . "\n<p>" . esc_html($lok) . "</p>\n"; }
    $h .= '<h2 id="upit">' . esc_html($T['kal']) . '</h2>' . "\n" . '[abu_kalendar id="' . $id . '" mjeseci="3"]' . "\n";
    $h .= '<h2>' . esc_html($T['upit']) . '</h2>' . "\n" . '[abu_upit id="' . $id . '"]' . "\n";
    $h .= '<h2>' . esc_html($T['kako']) . '</h2>' . "\n<p>" . esc_html($T['kako_p']) . "</p>\n<p>" . esc_html($T['pitanja']) . "</p>\n";
    $h .= '<p><a href="' . esc_url(get_permalink($id)) . '" hreflang="bs">' . esc_html($T['bs']) . '</a> · <a href="' . esc_url(home_url($l === 'de' ? '/de/unterkunft-bihac/' : '/en/accommodation-bihac/')) . '">' . esc_html($T['svi']) . '</a></p>';
    return $h;
}

/** Stvori ili osvježi stranice kad se promijeni prijevod ili verzija dodatka. */
add_action('init', function () {
    $f = __DIR__ . '/seo/objekti-prijevod.json';
    if (!is_readable($f)) { return; }
    $ver = md5_file($f) . ABU_VER;
    if (get_option('abu_oj_ver') === $ver || get_transient('abu_oj_lock')) { return; }
    set_transient('abu_oj_lock', 1, 300);
    $d = abu_oj_podaci();
    foreach (abu_oj_putevi() as $l => $put) {
        $T = abu_oj_t($l);
        $rod_l = get_page_by_path($l);
        if (!$rod_l) { continue; }
        $hub = get_page_by_path($put);
        $hd = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $rod_l->ID, 'post_name' => basename($put), 'post_title' => $T['hub_t'],
            'post_content' => '<p class="lead">' . esc_html($T['hub_t']) . '.</p>' . "\n" . '[abu_lista lang="' . $l . '"]');
        if ($hub) { $hd['ID'] = $hub->ID; wp_update_post($hd); $hid = $hub->ID; } else { $hid = wp_insert_post($hd); }
        update_post_meta($hid, '_abu_oj_hub', $l);
        foreach ((array) ($d['objekti'] ?? array()) as $slug => $x) {
            $o = get_page_by_path($slug, OBJECT, 'objekt');
            if (!$o || $o->post_status !== 'publish' || empty($x[$l])) { continue; }
            $p = get_page_by_path($put . '/' . $slug);
            $pd = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $hid, 'post_name' => $slug, 'post_title' => $x[$l]['h1'],
                'post_excerpt' => $x[$l]['meta'], 'post_content' => abu_oj_html($slug, $o->ID, $l));
            if ($p) { $pd['ID'] = $p->ID; wp_update_post($pd); $pid = $p->ID; } else { $pid = wp_insert_post($pd); }
            if ($pid && !is_wp_error($pid)) { update_post_meta($pid, '_abu_oj_objekt', $o->ID); update_post_meta($pid, '_abu_oj_lang', $l); }
            if ($o->post_name && has_post_thumbnail($o->ID) && !has_post_thumbnail($pid)) { set_post_thumbnail($pid, get_post_thumbnail_id($o->ID)); }
        }
    }
    update_option('abu_oj_ver', $ver);
    delete_transient('abu_oj_lock');
}, 41);

/** Tekuća stranica objekta na jeziku: array(objekt_id, lang, slug) ili null. */
function abu_oj_tekuca() {
    if (!is_page()) { return null; }
    $pid = get_queried_object_id();
    $oid = (int) get_post_meta($pid, '_abu_oj_objekt', true);
    if (!$oid) { return null; }
    return array($oid, (string) get_post_meta($pid, '_abu_oj_lang', true), get_post_field('post_name', $oid));
}

add_filter('pre_get_document_title', function ($t) {
    $c = abu_oj_tekuca();
    if (!$c) { return $t; }
    $d = abu_oj_podaci();
    return $d['objekti'][$c[2]][$c[1]]['title'] ?? $t;
}, 25);

/** Meta opis, hreflang i schema; hub stranice nisu za indeks (isti popis je na /en/accommodation-bihac/). */
add_action('wp_head', function () {
    if (is_page() && get_post_meta(get_queried_object_id(), '_abu_oj_hub', true)) { return; }
    $oid = 0; $l = 'bs';
    if (is_singular('objekt')) { $oid = get_queried_object_id(); }
    elseif ($c = abu_oj_tekuca()) { list($oid, $l) = $c; }
    if (!$oid) { return; }
    $u = array('bs' => get_permalink($oid), 'en' => abu_objekt_url($oid, 'en'), 'de' => abu_objekt_url($oid, 'de'));
    $u = array_filter($u);
    if (count($u) > 1) {
        foreach ($u as $k => $url) { echo '<link rel="alternate" hreflang="' . esc_attr($k) . '" href="' . esc_url($url) . '">' . "\n"; }
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($u['bs']) . '">' . "\n";
    }
    if ($l === 'bs') { return; }
    $d = abu_oj_podaci(); $slug = get_post_field('post_name', $oid); $x = $d['objekti'][$slug][$l] ?? null;
    if (!$x) { return; }
    // meta opis ispisuje seo.php iz sažetka stranice (= $x['meta'])
    $img = array();
    if (function_exists('ab_slike')) { foreach (ab_slike($oid, 6) as $i) { $img[] = wp_get_attachment_image_url($i, 'large'); } }
    $s = array('@context' => 'https://schema.org', '@type' => 'LodgingBusiness', 'name' => get_the_title($oid), 'url' => get_permalink(), 'inLanguage' => $l,
        'description' => $x['meta'], 'image' => array_values(array_filter($img)),
        'address' => array('@type' => 'PostalAddress', 'addressLocality' => (string) get_post_meta($oid, '_abu_mjesto', true), 'addressCountry' => 'BA'),
        'telephone' => '+387 61 591 320', 'maximumAttendeeCapacity' => (int) get_post_meta($oid, '_abu_kapacitet', true),
        'amenityFeature' => array_map(function ($n) { return array('@type' => 'LocationFeatureSpecification', 'name' => $n, 'value' => true); }, (array) $x['oprema']),
        'petsAllowed' => $l === 'de' ? 'Hunde und Katzen auf Anfrage' : 'Dogs and cats on request', 'containedInPlace' => array('@id' => home_url('/#org')));
    echo '<script type="application/ld+json">' . wp_json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 3);

add_filter('wp_robots', function ($r) {
    if (is_page() && get_post_meta(get_queried_object_id(), '_abu_oj_hub', true)) { $r['noindex'] = true; $r['follow'] = true; unset($r['max-image-preview']); }
    return $r;
});

/** Hub stranice izvan sitemapa (noindex). */
add_filter('wp_sitemaps_posts_query_args', function ($a, $tip) {
    if ($tip !== 'page') { return $a; }
    $ids = get_posts(array('post_type' => 'page', 'meta_key' => '_abu_oj_hub', 'fields' => 'ids', 'numberposts' => 5));
    if ($ids) { $a['post__not_in'] = array_merge(isset($a['post__not_in']) ? (array) $a['post__not_in'] : array(), $ids); }
    return $a;
}, 11, 2);

/** Forma upita i kalendar: ove stranice nikad u cache. */
add_action('template_redirect', function () {
    if (abu_oj_tekuca()) { do_action('litespeed_control_set_nocache', 'apartmani-upiti: objekt na jeziku, forma upita'); }
}, 1);

/** [abu_galerija id=""]: fotografije objekta (istaknuta + priložene), klik otvara punu veličinu. */
add_shortcode('abu_galerija', function ($a) {
    $a = shortcode_atts(array('id' => 0, 'broj' => 12), $a);
    $id = (int) $a['id'];
    if (!$id || !function_exists('ab_slike')) { return ''; }
    $sl = ab_slike($id, (int) $a['broj']);
    if (!$sl) { return ''; }
    $h = '<style>.abu-gal{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;margin:16px 0}.abu-gal a{display:block;border-radius:10px;overflow:hidden}.abu-gal img{width:100%;aspect-ratio:4/3;object-fit:cover;display:block}.abu-gal a:first-child{grid-column:1/-1}.abu-gal a:first-child img{aspect-ratio:16/9}</style><div class="abu-gal">';
    foreach ($sl as $i => $sid) {
        $h .= '<a href="' . esc_url(wp_get_attachment_image_url($sid, 'full')) . '" target="_blank" rel="noopener">' . wp_get_attachment_image($sid, $i === 0 ? 'ab-wide' : 'ab-card', false, array('loading' => $i === 0 ? 'eager' : 'lazy', 'alt' => get_the_title($id) . ', ' . ($i + 1))) . '</a>';
    }
    return $h . '</div>';
});

/** llms.txt: stranice objekata na engleskom i njemačkom. */
add_filter('abu_llms_dodatak', function ($o) {
    foreach (abu_oj_putevi() as $l => $put) {
        $hub = get_page_by_path($put);
        if (!$hub) { continue; }
        $o .= "\n## " . ($l === 'de' ? 'Unterkünfte (Deutsch)' : 'Places to stay (English)') . "\n";
        foreach (get_pages(array('parent' => $hub->ID, 'sort_column' => 'post_title', 'post_status' => 'publish')) as $p) {
            $o .= '- [' . $p->post_title . '](' . get_permalink($p) . '): ' . wp_strip_all_tags($p->post_excerpt) . "\n";
        }
    }
    return $o;
}, 20);
