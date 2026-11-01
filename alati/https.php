<?php
/** Jednokratno: adresa sajta na https://apartmanibihac.com i ispravak http poveznica u sadržaju. */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
$novi = 'https://apartmanibihac.com';
foreach (array('home', 'siteurl') as $o) { if (get_option($o) !== $novi) { update_option($o, $novi); } }
global $wpdb;
$n = 0;
foreach (array('http://apartmanibihac.com', 'http://www.apartmanibihac.com', 'https://www.apartmanibihac.com') as $stari) {
    $n += (int) $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s", $stari, $novi, '%' . $wpdb->esc_like($stari) . '%'));
}
wp_cache_flush();
echo 'home=' . get_option('home') . ' siteurl=' . get_option('siteurl') . ' ispravljenih objava: ' . $n . PHP_EOL;
