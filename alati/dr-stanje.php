<?php
/**
 * Stanje objava na Facebook/Instagram za zadnje članke (samo čitanje, samo CLI). Ne ispisuje token.
 * Pokretanje: php alati/dr-stanje.php
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
$o = abu_meta_opt();
echo 'auto=' . (int) $o['auto'] . ' od=' . $o['od'] . ' token=' . ($o['token'] ? 'da' : 'ne') . ' page=' . ($o['page_id'] ? 'da' : 'ne') . ' ig=' . ($o['ig_id'] ? 'da' : 'ne') . "\n";
echo 'zadnja objava na mreže: ' . (string) get_option('abu_meta_zadnji') . ' | sada ' . current_time('mysql') . "\n";
echo 'dnevne vijesti: ' . (string) get_option('abu_dv_stanje') . "\n";
foreach (get_posts(array('numberposts' => 5, 'post_status' => 'publish')) as $p) {
    $m = function ($k) use ($p) { return (string) get_post_meta($p->ID, $k, true); };
    $sl = wp_next_scheduled('abu_dr_auto', array($p->ID));
    echo "\n#{$p->ID} {$p->post_date} {$p->post_name}\n";
    echo '  FB: ' . ($m('_abu_fb_id') ? 'objavljeno ' . $m('_abu_fb_id') : 'ne') . ($m('_abu_fb_greska') ? ' | greška: ' . $m('_abu_fb_greska') : '') . "\n";
    echo '  IG: ' . ($m('_abu_ig_id') ? 'objavljeno ' . $m('_abu_ig_id') : 'ne') . ($m('_abu_ig_greska') ? ' | greška: ' . $m('_abu_ig_greska') : '') . "\n";
    echo '  zakazano: ' . ($sl ? wp_date('Y-m-d H:i:s', $sl) : '-') . ' | grafika: ' . (function_exists('abu_grafika') ? basename(abu_grafika($p->ID, 'ig')) : '-') . "\n";
}
