<?php
/**
 * Preuzima slike objekata i stare članke sa starog sajta (samo čitanje).
 * Pokreće se iz komandne linije na serveru:  php uvoz-stari.php [https://stari-host] [ip]
 * Dnevnik: wp-content/uploads/abu-uvoz.log
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
@set_time_limit(0);
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$STARI = isset($argv[1]) ? rtrim($argv[1], '/') : 'https://www.apartmanibihac.com';
$IP = isset($argv[2]) ? $argv[2] : '';
$KORAK = isset($argv[3]) ? $argv[3] : 'sve';
$LOG = WP_CONTENT_DIR . '/uploads/abu-uvoz.log';
function L($s) { global $LOG; file_put_contents($LOG, date('H:i:s') . ' ' . $s . "\n", FILE_APPEND); echo $s . "\n"; }

if ($IP) {
    add_action('http_api_curl', function ($h, $r, $url) use ($IP) {
        $host = parse_url($url, PHP_URL_HOST);
        if (strpos($host, 'apartmanibihac.com') !== false) {
            curl_setopt($h, CURLOPT_RESOLVE, array($host . ':443:' . $IP, $host . ':80:' . $IP));
        }
    }, 10, 3);
}

function G($url, $json = false) {
    $r = wp_remote_get($url, array('timeout' => 40, 'redirection' => 3, 'user-agent' => 'Mozilla/5.0 (uvoz apartmanibihac)'));
    if (is_wp_error($r)) { L('GREŠKA ' . $url . ' ' . $r->get_error_message()); return null; }
    $c = wp_remote_retrieve_response_code($r);
    if ($c !== 200) { L('HTTP ' . $c . ' ' . $url); return null; }
    $b = wp_remote_retrieve_body($r);
    return $json ? json_decode($b, true) : $b;
}

function R($stari, $put) {
    static $nacin = null;
    $put = ltrim($put, '/');
    if ($nacin === null) {
        $r = wp_remote_get($stari . '/wp-json/', array('timeout' => 30));
        $nacin = (!is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) ? 'lijep' : 'route';
    }
    if ($nacin === 'lijep') { return $stari . '/wp-json/' . $put; }
    $dij = explode('?', $put, 2);
    return $stari . '/?rest_route=/' . rtrim($dij[0], '/') . (isset($dij[1]) ? '&' . $dij[1] : '');
}
if ($KORAK === 'provjera') {
    foreach (array('/', '/?rest_route=/', '/smjestaj/vila-enio/', '/feed/') as $u) {
        $r = wp_remote_get($STARI . $u, array('timeout' => 30, 'redirection' => 0));
        if (is_wp_error($r)) { echo $u . ' GREŠKA ' . $r->get_error_message() . "\n"; continue; }
        $b = wp_remote_retrieve_body($r);
        echo $u . ' -> ' . wp_remote_retrieve_response_code($r) . ' ' . strlen($b) . 'B server=' . wp_remote_retrieve_header($r, 'server') . ' loc=' . wp_remote_retrieve_header($r, 'location') . ' | ' . substr(preg_replace('/\s+/', ' ', strip_tags(substr($b, 0, 600))), 0, 160) . "\n";
    }
    echo 'ip=' . gethostbyname(parse_url($STARI, PHP_URL_HOST)) . "\n";
}
L('=== start ' . $STARI . ($IP ? ' preko ' . $IP : '') . ' korak=' . $KORAK);
$root = G(R($STARI, ''), true);
if ($root && isset($root['namespaces']) && in_array('abu/v1', $root['namespaces'], true)) {
    L('STOP: ' . $STARI . ' pokazuje na NOVI sajt (Hostinger), a ne na stari. Treba IP starog servera.');
    exit(1);
}
L('stari sajt: ' . ($root ? ($root['name'] ?? '?') . ' | ' . implode(',', $root['namespaces'] ?? array()) : 'REST nedostupan'));

/** Preuzmi jednu sliku (izvorna veličina) i zakači je na post. */
function uvezi_sliku($url, $parent, $alt = '') {
    $url = preg_replace('/-\d+x\d+(?=\.(jpe?g|png|webp)$)/i', '', strtok($url, '?'));
    $ima = get_posts(array('post_type' => 'attachment', 'meta_key' => '_abu_izvor', 'meta_value' => $url, 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'any'));
    if ($ima) {
        if ($parent && !wp_get_post_parent_id($ima[0])) { wp_update_post(array('ID' => $ima[0], 'post_parent' => $parent)); }
        return $ima[0];
    }
    $tmp = download_url($url, 60);
    if (is_wp_error($tmp)) { L('  slika greška ' . $url . ' ' . $tmp->get_error_message()); return 0; }
    if (filesize($tmp) < 15000) { @unlink($tmp); return 0; } // ikone, logotipi
    $id = media_handle_sideload(array('name' => basename($url), 'tmp_name' => $tmp), $parent, $alt ?: null);
    if (is_wp_error($id)) { @unlink($tmp); L('  sideload greška ' . $url . ' ' . $id->get_error_message()); return 0; }
    update_post_meta($id, '_abu_izvor', $url);
    if ($alt) { update_post_meta($id, '_wp_attachment_image_alt', $alt); }
    return $id;
}

function slike_iz_html($html, $stari) {
    $host = preg_quote(parse_url($stari, PHP_URL_HOST), '#');
    preg_match_all('#https?://(?:www\.)?apartmanibihac\.com/wp-content/uploads/[^"\'\s)]+?\.(?:jpe?g|png|webp)#i', $html, $m);
    $out = array();
    foreach ($m[0] as $u) {
        $k = preg_replace('/-\d+x\d+(?=\.(jpe?g|png|webp)$)/i', '', $u);
        if (preg_match('/logo|icon|favicon|avatar|placeholder|banner-ad|flag/i', $k)) { continue; }
        $out[$k] = $k;
    }
    return array_values($out);
}

/* ---------- 1. Slike objekata ---------- */
if ($KORAK === 'sve' || $KORAK === 'slike') {
    // Pokušaj preko REST-a: pronađi tip objava za smještaj
    $tipovi = G(R($STARI, 'wp/v2/types'), true);
    $rest_tip = '';
    if (is_array($tipovi)) {
        foreach ($tipovi as $slug => $t) {
            if (in_array($slug, array('post', 'page', 'attachment', 'wp_block'), true)) { continue; }
            if (!empty($t['rest_base'])) { $rest_tip = $t['rest_base']; L('tip smještaja na starom sajtu: ' . $slug . ' (' . $rest_tip . ')'); break; }
        }
    }
    $sve_slike_drugih = array();
    $objekti = get_posts(array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => 100));
    // prvo skupi sve kandidate po objektu (HTML), da izbacimo slike koje se ponavljaju na više stranica (sidebar)
    $kand = array();
    foreach ($objekti as $o) {
        $urls = array();
        if ($rest_tip) {
            $stari_o = G(R($STARI, 'wp/v2/' . $rest_tip . '?slug=' . $o->post_name), true);
            if ($stari_o && !empty($stari_o[0]['id'])) {
                $med = G(R($STARI, 'wp/v2/media?parent=' . $stari_o[0]['id'] . '&per_page=100'), true);
                if (is_array($med)) { foreach ($med as $md) { if (!empty($md['source_url'])) { $urls[] = $md['source_url']; } } }
                if (!empty($stari_o[0]['featured_media'])) {
                    $fm = G(R($STARI, 'wp/v2/media/' . $stari_o[0]['featured_media']), true);
                    if ($fm && !empty($fm['source_url'])) { array_unshift($urls, $fm['source_url']); }
                }
            }
        }
        if (!$urls) {
            $html = G($STARI . '/smjestaj/' . $o->post_name . '/');
            if ($html) { $urls = slike_iz_html($html, $STARI); }
        }
        $kand[$o->ID] = array_values(array_unique($urls));
        foreach ($kand[$o->ID] as $u) { $sve_slike_drugih[$u] = isset($sve_slike_drugih[$u]) ? $sve_slike_drugih[$u] + 1 : 1; }
    }
    foreach ($objekti as $o) {
        $urls = array_filter($kand[$o->ID], function ($u) use ($sve_slike_drugih) { return $sve_slike_drugih[$u] < 3; });
        $urls = array_slice(array_values($urls), 0, 25);
        L($o->post_name . ': ' . count($urls) . ' slika');
        $n = 0;
        foreach ($urls as $i => $u) {
            $id = uvezi_sliku($u, $o->ID, $o->post_title . ' – ' . get_post_meta($o->ID, '_abu_mjesto', true));
            if ($id) { $n++; if (!has_post_thumbnail($o->ID)) { set_post_thumbnail($o->ID, $id); } }
        }
        L('  uvezeno ' . $n);
    }
    // Slike za izmjenjivač na naslovnici (stranica header-slike), ako je prazna
    $hp = get_page_by_path('header-slike');
    if ($hp && strpos($hp->post_content, '<img') === false) {
        $html = '';
        foreach (array('vila-enio', 'green-river', 'vikendica-rekic', 'farah-superior-apartments') as $s) {
            $p = get_page_by_path($s, OBJECT, 'objekt');
            if ($p && ($t = get_post_thumbnail_id($p->ID))) {
                $html .= '<img class="wp-image-' . $t . '" src="' . esc_url(wp_get_attachment_image_url($t, 'full')) . '" alt="' . esc_attr($p->post_title . ', uz rijeku Unu') . '">' . "\n";
            }
        }
        if ($html) { wp_update_post(array('ID' => $hp->ID, 'post_content' => $html)); }
        L('naslovnica: ' . substr_count($html, '<img') . ' slika');
    }
}

/* ---------- 2. Stari članci ---------- */
if ($KORAK === 'sve' || $KORAK === 'clanci') {
    $cat = get_term_by('slug', 'vodic-kategorija', 'category');
    $cat_id = $cat ? $cat->term_id : 1;
    $str = 1; $ukupno = 0;
    do {
        $posts = G(R($STARI, 'wp/v2/posts?per_page=50&page=' . $str), true);
        if (!is_array($posts) || !$posts) { break; }
        foreach ($posts as $p) {
            $slug = $p['slug'];
            if (get_posts(array('name' => $slug, 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1))) { L('članak postoji: ' . $slug); continue; }
            $content = $p['content']['rendered'];
            $id = wp_insert_post(array('post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug,
                'post_title' => html_entity_decode($p['title']['rendered'], ENT_QUOTES, 'UTF-8'),
                'post_excerpt' => wp_strip_all_tags(html_entity_decode($p['excerpt']['rendered'], ENT_QUOTES, 'UTF-8')),
                'post_content' => $content, 'post_date' => $p['date'], 'post_date_gmt' => $p['date_gmt'], 'post_category' => array($cat_id)), true);
            if (is_wp_error($id)) { L('greška članak ' . $slug); continue; }
            // slike iz teksta
            foreach (slike_iz_html($content, $STARI) as $u) {
                $a = uvezi_sliku($u, $id, get_the_title($id));
                if ($a) {
                    $novi = wp_get_attachment_url($a);
                    $content = preg_replace('#' . preg_quote(preg_replace('/\.(jpe?g|png|webp)$/i', '', $u), '#') . '(-\d+x\d+)?\.(jpe?g|png|webp)#i', $novi, $content);
                    if (!has_post_thumbnail($id)) { set_post_thumbnail($id, $a); }
                }
            }
            if (!empty($p['featured_media'])) {
                $fm = G(R($STARI, 'wp/v2/media/' . $p['featured_media']), true);
                if ($fm && !empty($fm['source_url'])) { $a = uvezi_sliku($fm['source_url'], $id, get_the_title($id)); if ($a) { set_post_thumbnail($id, $a); } }
            }
            // linkovi na stari sajt ostaju relativni na isti domen
            $content = str_replace(array('http://www.apartmanibihac.com', 'http://apartmanibihac.com'), 'https://www.apartmanibihac.com', $content);
            wp_update_post(array('ID' => $id, 'post_content' => $content));
            $ukupno++;
            L('članak: ' . get_permalink($id));
        }
        $str++;
    } while (count($posts) === 50);
    L('članaka uvezeno: ' . $ukupno);
}
L('=== kraj');
