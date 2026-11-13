<?php
/**
 * Vijesti na engleskom i njemačkom + puni SEO za vijesti.
 * - Svaka vijest (sadrzaj/vijesti.json i dnevne vijesti) može imati polja "en" i "de" {slug, naslov, izvod, html}.
 *   Prijevod postaje stranica /en/news/<slug>/ ili /de/nachrichten/<slug>/ (ista fotografija, izvor, datum).
 * - hreflang bs/en/de + x-default na sve tri verzije; NewsArticle schema (izvor, slika s logom, jezik); breadcrumbs.
 * - Popis vijesti na /en/news/ i /de/nachrichten/ ([abu_vijesti_jezik lang]); IndexNow šalje nove stranice (indexnow.php).
 * Povrat: ukloniti require_once u apartmani-upiti.php (stranice ostaju kao obične stranice).
 */
if (!defined('ABSPATH')) { exit; }

function abu_vj_putevi() { return array('en' => 'en/news', 'de' => 'de/nachrichten'); }
function abu_vj_t($l) {
    $t = array(
        'en' => array('hub' => 'News from Bihać and the Una river', 'hub_e' => 'Latest tourism news from Bihać, Una National Park and the Una-Sana Canton: events, travel, border, weather and tips for guests.', 'izvor' => 'Source', 'pocetna' => 'Home', 'sve' => 'All news'),
        'de' => array('hub' => 'Nachrichten aus Bihać und von der Una', 'hub_e' => 'Aktuelle Tourismusnachrichten aus Bihać, dem Nationalpark Una und dem Una-Sana-Kanton: Events, Reisen, Grenze, Wetter und Tipps für Gäste.', 'izvor' => 'Quelle', 'pocetna' => 'Startseite', 'sve' => 'Alle Nachrichten'),
    );
    return $t[$l];
}

/** Stranica-središte /en/news/ ili /de/nachrichten/ (stvori ako ne postoji). */
function abu_vj_hub($l) {
    $put = abu_vj_putevi()[$l];
    $h = get_page_by_path($put);
    if ($h) { return (int) $h->ID; }
    $rod = get_page_by_path($l);
    if (!$rod) { return 0; }
    $T = abu_vj_t($l);
    $id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $rod->ID, 'post_name' => basename($put),
        'post_title' => $T['hub'], 'post_excerpt' => $T['hub_e'], 'post_content' => '<p class="lead">' . esc_html($T['hub_e']) . '</p>' . "\n" . '[abu_vijesti_jezik lang="' . $l . '"]'));
    return ($id && !is_wp_error($id)) ? (int) $id : 0;
}

/**
 * Spremi prijevod vijesti $bs_id na jezik $l. $d = {slug, naslov, izvod, html}. Ažurira se samo kad se tekst promijeni.
 * Vraća ID stranice ili 0.
 */
function abu_vj_spremi($bs_id, $l, $d) {
    if (!in_array($l, array('en', 'de'), true) || !is_array($d) || empty($d['slug']) || empty($d['naslov']) || empty($d['html'])) { return 0; }
    $bs = get_post($bs_id);
    if (!$bs || $bs->post_status !== 'publish') { return 0; }
    $hub = abu_vj_hub($l);
    if (!$hub) { return 0; }
    $slug = sanitize_title($d['slug']);
    $hash = md5(wp_json_encode($d));
    $pid = (int) get_post_meta($bs_id, '_abu_vj_' . $l, true);
    $p = $pid ? get_post($pid) : get_page_by_path(abu_vj_putevi()[$l] . '/' . $slug);
    if ($p && get_post_meta($p->ID, '_abu_vj_hash', true) === $hash) { return (int) $p->ID; }
    $pd = array('post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $hub, 'post_name' => $slug,
        'post_title' => wp_strip_all_tags($d['naslov']), 'post_excerpt' => wp_strip_all_tags((string) ($d['izvod'] ?? '')),
        'post_content' => wp_kses_post($d['html']), 'post_date' => $bs->post_date, 'post_date_gmt' => $bs->post_date_gmt);
    if ($p) { $pd['ID'] = $p->ID; $id = wp_update_post($pd); } else { $id = wp_insert_post($pd); }
    if (!$id || is_wp_error($id)) { return 0; }
    update_post_meta($id, '_abu_vj_bs', (int) $bs_id);
    update_post_meta($id, '_abu_vj_lang', $l);
    update_post_meta($id, '_abu_vj_hash', $hash);
    update_post_meta($bs_id, '_abu_vj_' . $l, (int) $id);
    $iz = get_post_meta($bs_id, '_abu_izvor_url', true);
    if ($iz) { update_post_meta($id, '_abu_izvor_url', $iz); }
    if (has_post_thumbnail($bs_id)) { set_post_thumbnail($id, get_post_thumbnail_id($bs_id)); }
    return (int) $id;
}

/** Sve tri verzije vijesti za tekuću stranicu: array('bs' => url, 'en' => url, 'de' => url) ili prazno. */
function abu_vj_verzije($id) {
    $bs = get_post_type($id) === 'post' ? (int) $id : (int) get_post_meta($id, '_abu_vj_bs', true);
    if (!$bs || get_post_status($bs) !== 'publish') { return array(); }
    $o = array('bs' => get_permalink($bs));
    foreach (array('en', 'de') as $l) {
        $p = (int) get_post_meta($bs, '_abu_vj_' . $l, true);
        if ($p && get_post_status($p) === 'publish') { $o[$l] = get_permalink($p); }
    }
    return count($o) > 1 ? $o : array();
}

/** hreflang na bs vijesti i na prijevodima. */
add_action('wp_head', function () {
    if (!is_singular(array('post', 'page'))) { return; }
    $id = get_queried_object_id();
    $v = abu_vj_verzije($id);
    if (!$v) { return; }
    foreach ($v as $l => $u) { echo '<link rel="alternate" hreflang="' . esc_attr($l) . '" href="' . esc_url($u) . '">' . "\n"; }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($v['bs']) . '">' . "\n";
}, 4);

/** NewsArticle + breadcrumbs za prijevode (bs vijesti dobivaju NewsArticle u seo-tehnika.php §4). */
add_action('wp_head', function () {
    if (!is_page()) { return; }
    $id = get_queried_object_id();
    $bs = (int) get_post_meta($id, '_abu_vj_bs', true);
    if (!$bs) { return; }
    $l = (string) get_post_meta($id, '_abu_vj_lang', true);
    $T = abu_vj_t($l === 'de' ? 'de' : 'en');
    $d = array('@context' => 'https://schema.org', '@type' => 'NewsArticle', 'headline' => wp_strip_all_tags(get_the_title($id)),
        'url' => get_permalink($id), 'mainEntityOfPage' => get_permalink($id), 'inLanguage' => $l,
        'datePublished' => get_the_date('c', $id), 'dateModified' => get_the_modified_date('c', $id),
        'author' => array('@type' => 'Organization', 'name' => 'Apartmani & Vikendice Bihać', 'url' => home_url('/')),
        'publisher' => array('@id' => home_url('/#org')), 'translationOfWork' => array('@id' => get_permalink($bs)));
    $img = function_exists('abu_grafika') ? abu_grafika($bs, 'fb') : '';
    if (!$img) { $img = get_the_post_thumbnail_url($id, 'large'); }
    if ($img) { $d['image'] = $img; }
    if (has_excerpt($id)) { $d['description'] = wp_strip_all_tags(get_the_excerpt($id)); }
    $iz = get_post_meta($id, '_abu_izvor_url', true);
    if ($iz) { $d['isBasedOn'] = $iz; }
    $hub = wp_get_post_parent_id($id);
    $b = array('@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => array(
        array('@type' => 'ListItem', 'position' => 1, 'name' => $T['pocetna'], 'item' => get_permalink(get_page_by_path($l))),
        array('@type' => 'ListItem', 'position' => 2, 'name' => get_the_title($hub), 'item' => get_permalink($hub)),
        array('@type' => 'ListItem', 'position' => 3, 'name' => wp_strip_all_tags(get_the_title($id)), 'item' => get_permalink($id))));
    echo '<script type="application/ld+json">' . wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($b, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 6);

/** Prijevod: datum objave iznad teksta, izvor ispod (ako ga tekst već nema), poveznice na ostale jezike i popis vijesti. */
add_filter('the_content', function ($c) {
    if (!is_page() || !in_the_loop()) { return $c; }
    $id = get_the_ID();
    $bs = (int) get_post_meta($id, '_abu_vj_bs', true);
    if (!$bs) { return $c; }
    $l = (string) get_post_meta($id, '_abu_vj_lang', true) === 'de' ? 'de' : 'en';
    $T = abu_vj_t($l);
    $ts = (int) get_post_time('U', true, $id);
    $mj = $l === 'de' ? array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember')
        : array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $n = (int) wp_date('n', $ts);
    $dat = wp_date('j', $ts) . ($l === 'de' ? '. ' : ' ') . $mj[$n - 1] . ' ' . wp_date('Y', $ts);
    $pre = '<p class="abu-vj-d"><time datetime="' . esc_attr(get_the_date('c', $id)) . '">' . esc_html($dat) . '</time></p>';
    $iz = get_post_meta($id, '_abu_izvor_url', true);
    $post = ($iz && strpos($c, $iz) === false) ? '<p><small>' . esc_html($T['izvor']) . ': <a href="' . esc_url($iz) . '" target="_blank" rel="noopener">' . esc_html(wp_parse_url($iz, PHP_URL_HOST)) . '</a></small></p>' : '';
    $hub = wp_get_post_parent_id($id);
    $post .= '<p><a href="' . esc_url(get_permalink($hub)) . '">← ' . esc_html($T['sve']) . '</a> · <a href="' . esc_url(get_permalink($bs)) . '" hreflang="bs" lang="bs">Bosanski</a></p>';
    return $pre . $c . $post;
}, 30);

/** [abu_vijesti_jezik lang="en"]: najnovije vijesti na jeziku (kartice s datumom i sažetkom). */
add_shortcode('abu_vijesti_jezik', function ($a) {
    $a = shortcode_atts(array('lang' => 'en', 'broj' => 30), $a);
    $l = $a['lang'] === 'de' ? 'de' : 'en';
    $hub = get_page_by_path(abu_vj_putevi()[$l]);
    if (!$hub) { return ''; }
    $p = get_pages(array('parent' => $hub->ID, 'sort_column' => 'post_date', 'sort_order' => 'DESC', 'post_status' => 'publish', 'number' => (int) $a['broj']));
    if (!$p) { return ''; }
    $h = '<ul class="abu-vodici">';
    foreach ($p as $x) {
        $h .= '<li><a href="' . esc_url(get_permalink($x)) . '"><strong>' . esc_html($x->post_title) . '</strong></a> <small>' . esc_html(get_the_date('j. n. Y.', $x)) . '</small>'
            . ($x->post_excerpt ? '<br>' . esc_html($x->post_excerpt) : '') . '</li>';
    }
    return $h . '</ul>';
});

/** Uvoz prijevoda iz sadrzaj/vijesti.json (kad se datoteka promijeni) za već objavljene vijesti. */
add_action('init', function () {
    $f = __DIR__ . '/sadrzaj/vijesti.json';
    if (!is_readable($f)) { return; }
    $ver = md5_file($f) . ABU_VER;
    if (get_option('abu_vj_ver') === $ver || get_transient('abu_vj_lock')) { return; }
    set_transient('abu_vj_lock', 1, 300);
    foreach ((array) json_decode((string) file_get_contents($f), true) as $v) {
        if (empty($v['slug']) || (empty($v['en']) && empty($v['de']))) { continue; }
        $bs = get_page_by_path($v['slug'], OBJECT, 'post');
        if (!$bs || $bs->post_status !== 'publish') { continue; }
        foreach (array('en', 'de') as $l) { if (!empty($v[$l])) { abu_vj_spremi($bs->ID, $l, $v[$l]); } }
    }
    update_option('abu_vj_ver', $ver);
    delete_transient('abu_vj_lock');
}, 46);

/** Zakazana vijest iz vijesti.json kad izađe: odmah napravi prijevode. */
add_action('transition_post_status', function ($novi, $stari, $post) {
    if ($novi !== 'publish' || $stari === 'publish' || $post->post_type !== 'post') { return; }
    $f = __DIR__ . '/sadrzaj/vijesti.json';
    if (!is_readable($f)) { return; }
    foreach ((array) json_decode((string) file_get_contents($f), true) as $v) {
        if (empty($v['slug']) || $v['slug'] !== $post->post_name) { continue; }
        if (!has_post_thumbnail($post->ID) && function_exists('abu_vijesti_slika_za')) { $img = abu_vijesti_slika_za($post->post_name, (string) ($v['slika'] ?? '')); if ($img) { set_post_thumbnail($post->ID, $img); } }
        foreach (array('en', 'de') as $l) { if (!empty($v[$l])) { abu_vj_spremi($post->ID, $l, $v[$l]); } }
    }
}, 30, 3);

/** Središta vijesti nisu u popisu „vodiča“ i ne smetaju; prijevodi idu u llms.txt. */
add_filter('abu_llms_dodatak', function ($o) {
    foreach (abu_vj_putevi() as $l => $put) {
        $hub = get_page_by_path($put);
        if (!$hub) { continue; }
        $p = get_pages(array('parent' => $hub->ID, 'sort_column' => 'post_date', 'sort_order' => 'DESC', 'post_status' => 'publish', 'number' => 20));
        if (!$p) { continue; }
        $o .= "\n## " . ($l === 'de' ? 'Nachrichten (Deutsch)' : 'News (English)') . "\n";
        foreach ($p as $x) { $o .= '- [' . $x->post_title . '](' . get_permalink($x) . '): ' . wp_strip_all_tags($x->post_excerpt) . "\n"; }
    }
    return $o;
});
