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
        // 13.11.2026.: važne adrese starog sajta (arhiva 2023.–2026.) koje su vraćale 404
        'apartman-centar-bihac'          => '/smjestaj/apartman-centar/',
        'apartman-1-bihac'               => '/apartmani-bihac/',
        'apartman-2-bihac'               => '/apartmani-bihac/',
        'galerija-slika'                 => '/smjestaj-bihac/',
        'galerija'                       => '/smjestaj-bihac/',
        'lokacija'                       => '/o-nama/',
        'o-hotelu'                       => '/o-nama/',
        'contacts'                       => '/o-nama/',
        'our-services'                   => '/izleti-bihac/',
        'our-services/npunabihac'        => '/cesta-pitanja/nacionalni-park-una/',
        'our-services/riverbihac'        => '/restorani-bihac/',
        'our-services/sokolacbihac'      => '/izleti-bihac/',
        'our-services/domacahranabihac'  => '/restorani-bihac/',
        'our-services/pizzeriaasabihac'  => '/restorani-bihac/',
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
    // Ista pravila za sve; AI botovi imaju vlastitu grupu, pa im se pravila ponavljaju (grupa * za njih ne vrijedi).
    $pravila = "Allow: /\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nDisallow: /?s=\nDisallow: /*?abu_a=\n";
    $ai = array('GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended', 'OAI-SearchBot', 'ChatGPT-User', 'Claude-SearchBot', 'Claude-User', 'Perplexity-User');
    return "User-agent: *\n" . $pravila . "\n# AI pretraživači su dobrodošli\n" . implode('', array_map(function ($b) { return 'User-agent: ' . $b . "\n"; }, $ai)) . $pravila . "\nSitemap: " . home_url('/wp-sitemap.xml') . "\n# llms: " . home_url('/llms.txt') . "\n# llms-full: " . home_url('/llms-full.txt') . "\n";
}, 10, 2);
