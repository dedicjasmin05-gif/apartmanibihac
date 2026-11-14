<?php
/** Zakazane objave: naslov, vrijeme, početak teksta i FB/IG teksta (samo CLI, samo čitanje). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com'; $_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
foreach (get_posts(array('post_type' => 'post', 'post_status' => 'future', 'numberposts' => 10)) as $p) {
    echo "#{$p->ID} {$p->post_date} {$p->post_name}\n  naslov: {$p->post_title}\n  izvod: {$p->post_excerpt}\n";
    echo '  tekst: ' . mb_substr(wp_strip_all_tags($p->post_content), 0, 220) . "\n";
    echo '  fb: ' . mb_substr((string) get_post_meta($p->ID, '_abu_fb_tekst', true), 0, 160) . "\n";
    echo '  ig: ' . mb_substr((string) get_post_meta($p->ID, '_abu_ig_tekst', true), 0, 120) . "\n";
}
echo "ispravke: " . get_option('abu_mig_ispravke') . "\n";
