<?php
/**
 * Dnevne vijesti iz turizma (Bihać → USK → BiH), 2–3 dnevno.
 * Zakazani zadatak (Claude) svako jutro piše kratke VLASTITE vijesti s linkom na izvor u javnu datoteku
 * sadrzaj/dnevne-vijesti.json na grani "dnevne-vijesti" repozitorija dedicjasmin05-gif/apartmanibihac.
 * Web je čita svakih 15 minuta (WP cron) i objavljuje svaku vijest kad dođe njeno vrijeme ("objavi"), u rubriku
 * "Vijesti iz turizma". Modul Društvene mreže je zatim dijeli na Facebook i Instagram (ako je automatika uključena).
 * Zaštite: samo poznata polja, wp_kses_post, najviše 4 objave dnevno, slug jedinstven, vrijeme ne u prošlosti dulje od 2 dana.
 * Povrat: ukloniti require_once u apartmani-upiti.php (objavljene vijesti ostaju kao obični članci).
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_DV_URL', 'https://raw.githubusercontent.com/dedicjasmin05-gif/apartmanibihac/dnevne-vijesti/sadrzaj/dnevne-vijesti.json');
define('ABU_DV_DNEVNO', 4);

add_filter('cron_schedules', function ($s) { $s['abu_15min_dv'] = array('interval' => 900, 'display' => 'Svakih 15 minuta (vijesti)'); return $s; });
add_action('init', function () {
    if (!wp_next_scheduled('abu_dv_provjeri')) { wp_schedule_event(time() + 120, 'abu_15min_dv', 'abu_dv_provjeri'); }
    if (!term_exists('vijesti-turizam', 'category')) {
        $rod = term_exists('vijesti', 'category');
        wp_insert_term('Vijesti iz turizma', 'category', array('slug' => 'vijesti-turizam', 'parent' => is_array($rod) ? (int) $rod['term_id'] : 0));
    }
}, 45);

/** Dohvati JSON (uz ETag), vrati listu vijesti ili null. */
function abu_dv_dohvati() {
    $h = array('User-Agent' => 'apartmanibihac.com dnevne-vijesti');
    $etag = get_option('abu_dv_etag');
    if ($etag) { $h['If-None-Match'] = $etag; }
    $r = wp_remote_get(ABU_DV_URL . '?t=' . floor(time() / 300), array('timeout' => 20, 'headers' => $h));
    if (is_wp_error($r)) { update_option('abu_dv_stanje', 'greška: ' . $r->get_error_message(), false); return null; }
    $kod = wp_remote_retrieve_response_code($r);
    if ($kod === 304) { $c = get_option('abu_dv_cache'); return is_array($c) ? $c : null; }
    if ($kod !== 200) { update_option('abu_dv_stanje', 'HTTP ' . $kod, false); return null; }
    $d = json_decode((string) wp_remote_retrieve_body($r), true);
    if (!is_array($d)) { update_option('abu_dv_stanje', 'neispravan JSON', false); return null; }
    update_option('abu_dv_etag', (string) wp_remote_retrieve_header($r, 'etag'), false);
    update_option('abu_dv_cache', $d, false);
    return $d;
}

/** Objavi vijesti kojima je došlo vrijeme. */
function abu_dv_obradi($lista, $sada = null) {
    $sada = $sada ?: time();
    $objavljeno = 0;
    $danas = wp_date('Y-m-d', $sada);
    $brojac = get_option('abu_dv_dan', array());
    $n_danas = (isset($brojac['d']) && $brojac['d'] === $danas) ? (int) $brojac['n'] : 0;
    $kat = get_term_by('slug', 'vijesti-turizam', 'category');
    foreach ((array) $lista as $v) {
        if ($n_danas >= ABU_DV_DNEVNO) { break; }
        if (!is_array($v) || empty($v['slug']) || empty($v['naslov']) || empty($v['html']) || empty($v['objavi'])) { continue; }
        $slug = sanitize_title($v['slug']);
        $kad = strtotime($v['objavi']);
        if (!$kad || $kad > $sada || $kad < $sada - 2 * DAY_IN_SECONDS) { continue; }
        if (get_page_by_path($slug, OBJECT, 'post')) { continue; }
        $id = wp_insert_post(array('post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug,
            'post_title' => wp_strip_all_tags($v['naslov']), 'post_excerpt' => wp_strip_all_tags((string) ($v['izvod'] ?? '')),
            'post_content' => wp_kses_post($v['html']), 'post_category' => $kat ? array($kat->term_id) : array()));
        if (!$id || is_wp_error($id)) { continue; }
        update_post_meta($id, '_abu_dnevna_vijest', 1);
        if (!empty($v['izvor_url'])) { update_post_meta($id, '_abu_izvor_url', esc_url_raw($v['izvor_url'])); }
        if (!empty($v['fb'])) { update_post_meta($id, '_abu_fb_tekst', wp_strip_all_tags($v['fb'])); }
        if (!empty($v['ig'])) { update_post_meta($id, '_abu_ig_tekst', wp_strip_all_tags($v['ig'])); }
        if (function_exists('abu_vijesti_slika_za')) { $img = abu_vijesti_slika_za($slug, (string) ($v['slika'] ?? '')); if ($img) { set_post_thumbnail($id, $img); } }
        $objavljeno++; $n_danas++;
    }
    update_option('abu_dv_dan', array('d' => $danas, 'n' => $n_danas), false);
    return $objavljeno;
}

add_action('abu_dv_provjeri', function () {
    if (get_transient('abu_dv_lock')) { return; }
    set_transient('abu_dv_lock', 1, 600);
    $l = abu_dv_dohvati();
    if ($l !== null) {
        $n = abu_dv_obradi($l);
        update_option('abu_dv_stanje', current_time('mysql') . ': u datoteci ' . count($l) . ', objavljeno sada ' . $n, false);
        if ($n && function_exists('do_action')) { do_action('litespeed_purge_all'); }
    }
    delete_transient('abu_dv_lock');
});

/** Na kraju dnevne vijesti: izvor (vlastiti sažetak, original kod izvora). */
add_filter('the_content', function ($c) {
    if (!is_singular('post') || !get_post_meta(get_the_ID(), '_abu_dnevna_vijest', true)) { return $c; }
    $u = get_post_meta(get_the_ID(), '_abu_izvor_url', true);
    if (!$u || strpos($c, $u) !== false) { return $c; }
    return $c . '<p><small>Izvor: <a href="' . esc_url($u) . '" target="_blank" rel="noopener">' . esc_html(wp_parse_url($u, PHP_URL_HOST)) . '</a></small></p>';
}, 30);
