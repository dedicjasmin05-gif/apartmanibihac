<?php
/**
 * IndexNow: nove, izmijenjene i uklonjene javne stranice (BS/EN/DE) javljaju se Bingu i ostalim IndexNow
 * pretraživačima (Yandex, Seznam, Naver, Yep…).
 * - Ključ je u opciji abu_indexnow_kljuc i dostupan na /{ključ}.txt.
 * - URL-ovi idu u red (bez duplikata), šalju se na abu_cron (15 min) i odgovor se zapisuje u
 *   wp-content/abu-indexnow/stanje.json (zadnjih 30 slanja).
 * - Šalju se samo javni indeksabilni URL-ovi: bez nacrta, admina, noindex stranica, filtera i parametara.
 *   Uklonjeni URL (objava → nacrt/smeće, prijevod skinute vijesti) također se javlja – Bing tada vidi 404/410/301.
 */
if (!defined('ABSPATH')) { exit; }

function abu_in_kljuc() {
    $k = get_option('abu_indexnow_kljuc');
    if (!$k) { $k = strtolower(wp_generate_password(32, false, false)); update_option('abu_indexnow_kljuc', $k); }
    return $k;
}

/** /{ključ}.txt vraća ključ (potvrda vlasništva za IndexNow). */
add_action('init', function () {
    $put = isset($_SERVER['REQUEST_URI']) ? strtok((string) $_SERVER['REQUEST_URI'], '?') : '';
    if ($put === '/' . abu_in_kljuc() . '.txt') {
        do_action('litespeed_control_set_nocache', 'apartmani-upiti: indexnow kljuc');
        header('Content-Type: text/plain; charset=utf-8');
        echo abu_in_kljuc(); // phpcs:ignore
        exit;
    }
}, 1);

/** Je li objava javna i indeksabilna (ista pravila kao noindex i sitemap u ostalim modulima). */
function abu_in_javan($post) {
    $post = get_post($post);
    if (!$post || $post->post_status !== 'publish' || $post->post_password !== '' || wp_is_post_revision($post)) { return false; }
    if (!in_array($post->post_type, array('post', 'page', 'objekt'), true)) { return false; }
    if ($post->post_type === 'page' && ($post->post_name === 'header-slike' || get_post_meta($post->ID, '_abu_oj_hub', true))) { return false; }
    if ($post->post_type === 'post' && function_exists('abu_seo_skriveni_clanci') && in_array($post->post_name, abu_seo_skriveni_clanci(), true)) { return false; }
    if (function_exists('abu_vj_siroce') && abu_vj_siroce($post->ID)) { return false; }
    return true;
}

/** Javna adresa koju je objava imala dok je bila objavljena (i nakon prelaska u nacrt/smeće). */
function abu_in_javna_adresa($post) {
    $p = clone $post;
    $p->post_status = 'publish';
    $p->post_name = preg_replace('/__trashed(-\d+)?$/', '', $p->post_name);
    $u = get_permalink($p);
    return ($u && strpos($u, '?') === false) ? $u : '';
}

/** Dodaj URL-ove u red (samo naš host, bez parametara). */
function abu_in_u_red($urls) {
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    $red = (array) get_option('abu_in_red', array());
    foreach ((array) $urls as $u) {
        if (!$u || strpos($u, '?') !== false || wp_parse_url($u, PHP_URL_HOST) !== $host) { continue; }
        $red[$u] = time();
    }
    update_option('abu_in_red', $red, false);
}

/** Pošalji URL-ove (blokirajuće, s odgovorom) i zapiši rezultat. */
function abu_in_posalji($urls, $razlog = '') {
    $urls = array_values(array_unique(array_filter((array) $urls)));
    if (!$urls) { return null; }
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    $kod = 0;
    foreach (array_chunk($urls, 9000) as $dio) {
        $r = wp_remote_post('https://api.indexnow.org/indexnow', array(
            'timeout' => 20, 'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => wp_json_encode(array('host' => $host, 'key' => abu_in_kljuc(), 'keyLocation' => home_url('/' . abu_in_kljuc() . '.txt'), 'urlList' => $dio)),
        ));
        $kod = is_wp_error($r) ? 'greska: ' . $r->get_error_message() : (int) wp_remote_retrieve_response_code($r);
        abu_in_zapisi(array('vrijeme' => current_time('mysql'), 'razlog' => $razlog, 'broj' => count($dio), 'odgovor' => $kod,
            'tijelo' => is_wp_error($r) ? '' : mb_substr((string) wp_remote_retrieve_body($r), 0, 300), 'primjeri' => array_slice($dio, 0, 5)));
    }
    update_option('abu_indexnow_zadnje', current_time('mysql') . ' (' . count($urls) . ') ' . $kod);
    return $kod;
}

function abu_in_zapisi($z) {
    $dir = WP_CONTENT_DIR . '/abu-indexnow';
    if (!is_dir($dir)) { wp_mkdir_p($dir); @file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n"); }
    $f = $dir . '/stanje.json';
    $s = is_file($f) ? json_decode((string) file_get_contents($f), true) : array();
    if (!is_array($s)) { $s = array(); }
    $s['kljuc_url'] = home_url('/' . abu_in_kljuc() . '.txt');
    $s['red'] = count((array) get_option('abu_in_red', array()));
    $s['slanja'] = array_slice(array_merge(array($z), isset($s['slanja']) ? (array) $s['slanja'] : array()), 0, 30);
    @file_put_contents($f, wp_json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/** Objava, izmjena ili uklanjanje javnog sadržaja => u red. */
add_action('transition_post_status', function ($novi, $stari, $post) {
    if (wp_is_post_revision($post) || wp_is_post_autosave($post) || !in_array($post->post_type, array('post', 'page', 'objekt'), true)) { return; }
    if ($novi === 'publish') {
        if (abu_in_javan($post)) { abu_in_u_red(array(get_permalink($post))); }
        return;
    }
    if ($stari !== 'publish') { return; }
    // uklonjeno s weba: stara javna adresa + prijevodi (koji sada vraćaju 410/301)
    $urls = array(abu_in_javna_adresa($post));
    if ($post->post_type === 'post') {
        foreach (array('en', 'de') as $l) { $p = (int) get_post_meta($post->ID, '_abu_vj_' . $l, true); if ($p) { $urls[] = get_permalink($p); } }
    }
    abu_in_u_red($urls);
}, 20, 3);

/** Red se šalje na abu_cron (svakih 15 min), najviše jednom po URL-u u 6 sati. */
add_action('abu_cron', function () {
    $red = (array) get_option('abu_in_red', array());
    if (!$red) { return; }
    $poslano = (array) get_option('abu_in_poslano', array());
    $sad = time();
    $za = array();
    foreach ($red as $u => $t) { if (empty($poslano[$u]) || $sad - $poslano[$u] > 6 * HOUR_IN_SECONDS) { $za[] = $u; } }
    update_option('abu_in_red', array(), false);
    if (!$za) { return; }
    $kod = abu_in_posalji($za, 'promjene');
    if ($kod === 200 || $kod === 202) {
        foreach ($za as $u) { $poslano[$u] = $sad; }
        arsort($poslano);
        update_option('abu_in_poslano', array_slice($poslano, 0, 3000, true), false);
    } else {
        abu_in_u_red($za); // ponovi na sljedećem cronu
    }
}, 60);

/** Jednokratno (verzija 2 modula): svi javni indeksabilni URL-ovi, BS/EN/DE. Ne ponavlja se pri svakoj verziji dodatka. */
add_action('abu_cron', function () {
    if (get_option('abu_indexnow_puni') === '2' || get_transient('abu_indexnow_lock')) { return; }
    set_transient('abu_indexnow_lock', 1, 600);
    $urls = array(home_url('/'));
    foreach (get_posts(array('post_type' => array('page', 'post', 'objekt'), 'post_status' => 'publish', 'numberposts' => -1, 'no_found_rows' => true)) as $p) {
        if (abu_in_javan($p)) { $urls[] = get_permalink($p); }
    }
    $urls = array_values(array_unique(array_filter($urls, function ($u) { return strpos($u, '?') === false; })));
    $kod = abu_in_posalji($urls, 'puni popis v2');
    if ($kod === 200 || $kod === 202) {
        update_option('abu_indexnow_puni', '2');
        $sad = time(); $poslano = array();
        foreach ($urls as $u) { $poslano[$u] = $sad; }
        update_option('abu_in_poslano', $poslano, false);
    }
    delete_transient('abu_indexnow_lock');
}, 55);
