<?php
/** Provjera stanja sajta iz komandne linije: php provjera.php */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';

echo 'verzija ' . ABU_VER . PHP_EOL;
$r = get_page_by_path('cesta-pitanja');
if ($r) {
    $dj = get_pages(array('child_of' => $r->ID, 'post_status' => 'publish'));
    $n = 0;
    foreach ($dj as $p) { $n += substr_count($p->post_content, '<h3>'); }
    echo 'cesta-pitanja: ' . count($dj) . ' tema, ' . $n . ' pitanja, ' . get_permalink($r) . PHP_EOL;
} else { echo 'cesta-pitanja: NEMA' . PHP_EOL; }
foreach (get_posts(array('post_type' => 'page', 'meta_key' => '_abu_seo', 'numberposts' => 50)) as $p) {
    echo 'seo: ' . get_permalink($p) . ' | ' . $p->post_title . PHP_EOL;
}
foreach (array('zdravo-svijete', 'hello-world-2') as $s) {
    $x = get_posts(array('name' => $s, 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1));
    echo $s . ': ' . ($x ? $x[0]->post_status : 'nema') . PHP_EOL;
}
echo 'objekata: ' . count(get_posts(array('post_type' => 'objekt', 'numberposts' => 100))) . ', clanaka: ' . count(get_posts(array('post_type' => 'post', 'numberposts' => 200))) . PHP_EOL;
$o = get_option('abu_postavke', array());
echo 'kapara_posto: ' . (isset($o['kapara_posto']) ? $o['kapara_posto'] : '?') . ', upiti na: ' . (isset($o['admin_emails']) ? $o['admin_emails'] : '?') . PHP_EOL;
global $wpdb;
echo 'objava s "kapar": ' . (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='publish' AND post_content LIKE '%kapar%'") . PHP_EOL;
