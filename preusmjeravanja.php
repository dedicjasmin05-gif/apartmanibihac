<?php
/** 301 sa starih adresa (stari sajt na Leftoru) na nove + robots.txt. */
if (!defined('ABSPATH')) { exit; }

function abu_stare_adrese() {
    return array(
        'ponuda'             => '/smjestaj/',
        'ponuda-vikendica'   => '/smjestaj/',
        'ponuda-apartmana'   => '/smjestaj/?mjesto=Biha%C4%87',
        'apartmani'          => '/smjestaj/?mjesto=Biha%C4%87',
        'vikendice'          => '/smjestaj/',
        'smjestaj-bihac'     => '/smjestaj/',
        'properties'         => '/smjestaj/',
        'property'           => '/smjestaj/',
        'listings'           => '/smjestaj/',
        'blog'               => '/vodic/',
        'novosti'            => '/vodic/',
        'kontakt'            => '/#kontakt',
        'contact'            => '/#kontakt',
        'o-nama'             => '/',
        'about'              => '/',
        'rafting'            => '/rafting-na-uni/',
        'una-rafting'        => '/rafting-na-uni/',
        'restorani'          => '/restorani-bihac/',
        'nocni-zivot'        => '/nocni-zivot-bihac/',
        'izlasci'            => '/nocni-zivot-bihac/',
        'dodaj-objekt'       => '/prijavite-objekt/',
        'add-listing'        => '/prijavite-objekt/',
        'uvjeti'             => '/uvjeti-i-privatnost/',
        'privacy-policy'     => '/uvjeti-i-privatnost/',
        'politika-privatnosti' => '/uvjeti-i-privatnost/',
    );
}

add_action('template_redirect', function () {
    if (!is_404()) { return; }
    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = trim((string) parse_url($uri, PHP_URL_PATH), '/');
    if ($path === '') { return; }
    $path = rawurldecode($path);
    $map = apply_filters('abu_stare_adrese', abu_stare_adrese());
    $dest = '';
    if (isset($map[$path])) {
        $dest = $map[$path];
    } else {
        $dijelovi = explode('/', $path);
        $zadnji = sanitize_title(end($dijelovi));
        // /property/naziv/, /listing/naziv/, /apartmani/naziv/ … → objekt istog naziva
        foreach (array('objekt', 'post', 'page') as $t) {
            $p = get_posts(array('name' => $zadnji, 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => 1));
            if ($p) { $dest = get_permalink($p[0]); break; }
        }
        if (!$dest && in_array($dijelovi[0], array('category', 'tag', 'author', 'kategorija'), true)) { $dest = '/vodic/'; }
        if (!$dest && in_array($dijelovi[0], array('wp-content'), true)) { return; }
    }
    if ($dest) {
        if (strpos($dest, 'http') !== 0) { $dest = home_url($dest); }
        wp_redirect($dest, 301, 'apartmanibihac');
        exit;
    }
}, 5);

add_filter('robots_txt', function ($out, $public) {
    if (!$public) { return $out; }
    return "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nDisallow: /?s=\nDisallow: /*?abu_a=\n\n# AI pretraživači su dobrodošli\nUser-agent: GPTBot\nAllow: /\nUser-agent: ClaudeBot\nAllow: /\nUser-agent: PerplexityBot\nAllow: /\nUser-agent: Google-Extended\nAllow: /\n\nSitemap: " . home_url('/wp-sitemap.xml') . "\n# llms: " . home_url('/llms.txt') . "\n";
}, 10, 2);
