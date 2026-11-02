<?php
/**
 * IndexNow: svaka nova ili promijenjena stranica odmah se javlja Bingu, Yandexu, Seznamu, Naveru (i preko Binga ChatGPT/Copilot pretrazi).
 * Ključ je u opciji abu_indexnow_kljuc i dostupan na /{ključ}.txt. Pri nadogradnji dodatka jednom se pošalju svi URL-ovi iz mape sajta.
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
        header('Content-Type: text/plain; charset=utf-8');
        echo abu_in_kljuc(); // phpcs:ignore
        exit;
    }
}, 1);

function abu_in_posalji($urls) {
    $urls = array_values(array_unique(array_filter((array) $urls)));
    if (!$urls) { return; }
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    foreach (array_chunk($urls, 9000) as $dio) {
        wp_remote_post('https://api.indexnow.org/indexnow', array(
            'timeout' => 15, 'blocking' => false, 'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => wp_json_encode(array('host' => $host, 'key' => abu_in_kljuc(), 'keyLocation' => home_url('/' . abu_in_kljuc() . '.txt'), 'urlList' => $dio)),
        ));
    }
    update_option('abu_indexnow_zadnje', current_time('mysql') . ' (' . count($urls) . ')');
}

/** Objava ili izmjena javnog sadržaja => IndexNow. */
add_action('transition_post_status', function ($novi, $stari, $post) {
    if ($novi !== 'publish' || wp_is_post_revision($post) || !in_array($post->post_type, array('post', 'page', 'objekt'), true)) { return; }
    $u = get_permalink($post);
    if ($u) { abu_in_posalji(array($u)); }
}, 10, 3);

/** Jednom po verziji dodatka: svi URL-ovi iz mape sajta. */
add_action('init', function () {
    if (get_option('abu_indexnow_ver') === ABU_VER || get_transient('abu_indexnow_lock')) { return; }
    set_transient('abu_indexnow_lock', 1, 600);
    $urls = array(home_url('/'));
    foreach (array('page', 'post', 'objekt') as $t) {
        foreach (get_posts(array('post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids')) as $id) { $urls[] = get_permalink($id); }
    }
    abu_in_posalji($urls);
    update_option('abu_indexnow_ver', ABU_VER);
}, 50);
