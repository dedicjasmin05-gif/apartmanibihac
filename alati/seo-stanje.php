<?php
/**
 * Stanje sajta za SEO (samo čitanje, samo CLI). Ne ispisuje osobne podatke: ni gostiju ni vlasnika
 * (imena, e-mailove, telefone, adrese), nego samo zbrojeve i podatke koji su ionako javni na webu.
 * Pokretanje: php alati/seo-stanje.php
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
global $wpdb;

echo '== verzije: dodatak ' . ABU_VER . ', tema ' . wp_get_theme()->get('Version') . ', WP ' . get_bloginfo('version') . ', PHP ' . PHP_VERSION . PHP_EOL;
echo '== aktivni dodaci: ' . implode(', ', array_map(function ($p) { return dirname($p) !== '.' ? dirname($p) : $p; }, (array) get_option('active_plugins'))) . PHP_EOL;
echo '== naziv sajta: ' . get_bloginfo('name') . ' | opis: ' . get_bloginfo('description') . PHP_EOL;

echo PHP_EOL . '== objekti (javni podaci)' . PHP_EOL;
foreach (get_posts(array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC')) as $o) {
    $m = function ($k) use ($o) { return get_post_meta($o->ID, '_abu_' . $k, true); };
    $op = wp_get_post_terms($o->ID, 'oprema', array('fields' => 'names'));
    $ical = trim((string) $m('ical_uvoz'));
    $slike = count(get_attached_media('image', $o->ID)) + (has_post_thumbnail($o->ID) ? 1 : 0);
    echo $o->post_name . ' | ' . $m('mjesto') . ' | kap ' . $m('kapacitet') . ' | cijena ' . ($m('cijena') !== '' ? $m('cijena') : '-') . ' | min noći ' . ($m('min_noci') ?: '-')
        . ' | grupa ' . $m('grupa') . ' | slike ' . $slike . ' | iCal izvora ' . ($ical === '' ? 0 : count(preg_split('/\s+/', $ical)))
        . ' | vlasnik e-mail ' . ($m('vlasnik_email') ? 'DA' : 'ne') . ' | oprema: ' . (is_wp_error($op) ? '' : implode(', ', $op))
        . ' | riječi ' . str_word_count(wp_strip_all_tags($o->post_content)) . PHP_EOL;
}

echo PHP_EOL . '== postavke upita (bez e-mailova i ključeva)' . PHP_EOL;
$p = (array) get_option('abu_postavke', array());
foreach (array('kapara_posto', 'drzi_sati', 'google_recenzija', 'telefon_javni', 'link_placanja') as $k) { echo $k . ': ' . (isset($p[$k]) && $p[$k] !== '' ? $p[$k] : '(prazno)') . PHP_EOL; }
echo 'tema: facebook ' . get_theme_mod('ab_facebook', '-') . ' | instagram ' . get_theme_mod('ab_instagram', '-') . ' | cijena od ' . get_theme_mod('ab_cijena_od', '-') . PHP_EOL;

echo PHP_EOL . '== upiti po mjesecu i statusu (samo zbrojevi)' . PHP_EOL;
$t = $wpdb->prefix . 'abu_upiti';
foreach ((array) $wpdb->get_results("SELECT DATE_FORMAT(kreiran,'%Y-%m') mj, izvor, status, COUNT(*) n FROM $t GROUP BY mj, izvor, status ORDER BY mj") as $r) { echo $r->mj . ' ' . $r->izvor . ' ' . $r->status . ': ' . $r->n . PHP_EOL; }
echo 'team building upita: ' . (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='tb_upit'") . PHP_EOL;
echo 'iCal blokada ukupno: ' . (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}abu_ical_blocks") . PHP_EOL;

echo PHP_EOL . '== tehničko' . PHP_EOL;
foreach (array('abu_rw_popravljeno', 'abu_mig_placanje3', 'abu_indexnow_zadnje', 'abu_seo_ver', 'abu_pitanja_ver') as $k) { echo $k . ': ' . var_export(get_option($k), true) . PHP_EOL; }
$r = get_option('rewrite_rules');
echo 'pravilo ^smjestaj/?$: ' . (isset($r['^smjestaj/?$']) ? 'DA' : 'NE') . ' | oprema: ' . (isset($r['oprema/([^/]+)/?$']) ? 'DA' : 'NE') . PHP_EOL;
echo 'mjerenje ' . current_time('Y-m') . ': ' . wp_json_encode(array_filter((array) get_option('abu_mjerenje_' . current_time('Y-m'), array()), function ($k) { return strpos($k, 'p:') !== 0; }, ARRAY_FILTER_USE_KEY)) . PHP_EOL;
echo 'revizije o-nama: ' . count(wp_get_post_revisions(get_page_by_path('o-nama'))) . PHP_EOL;
echo 'cron dodatka: ' . (wp_next_scheduled('abu_cron') ? gmdate('c', wp_next_scheduled('abu_cron')) : 'NIJE ZAKAZAN') . PHP_EOL;
