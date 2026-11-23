<?php
/** Ponovi objavu na Instagramu za članke kojima je IG pao (greška, a nema ID-a), zadnjih 7 dana, najviše 2 (samo CLI). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com'; $_SERVER['REQUEST_URI'] = '/';
set_time_limit(300);
require dirname(__DIR__, 4) . '/wp-load.php';
$n = 0;
foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 20, 'date_query' => array(array('after' => '7 days ago')))) as $p) {
    if ($n >= 2 || get_post_meta($p->ID, '_abu_ig_id', true) || !get_post_meta($p->ID, '_abu_ig_greska', true)) { continue; }
    echo "#{$p->ID} {$p->post_name}: " . abu_dr_objavi_jedan($p->ID, 'ig') . "\n"; $n++;
}
echo "gotovo ($n)\n";
