<?php
/** Kratki izvještaj o sadržaju novog sajta (samo CLI). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
foreach (array('objekt', 'page', 'post', 'attachment') as $t) {
    $c = wp_count_posts($t);
    echo $t . ': ' . (int) ($t === 'attachment' ? $c->inherit : $c->publish) . ' objavljeno, ' . (int) ($c->draft ?? 0) . " skica\n";
}
$bez = get_posts(array('post_type' => 'objekt', 'numberposts' => 100, 'meta_query' => array(array('key' => '_thumbnail_id', 'compare' => 'NOT EXISTS')), 'fields' => 'ids'));
echo 'objekti bez slike: ' . count($bez) . "\n";
echo 'naslovnica: ' . get_option('show_on_front') . ' #' . get_option('page_on_front') . ', blog #' . get_option('page_for_posts') . "\n";
echo 'permalinks: ' . get_option('permalink_structure') . ' | seed: ' . get_option('abu_seed') . "\n";
foreach (array('/smjestaj/vila-enio/', '/en/', '/de/', '/vodic/') as $u) { echo $u . ' -> ' . url_to_postid(home_url($u)) . "\n"; }
