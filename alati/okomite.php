<?php
/** Popis okomitih fotografija vodiča (CLI). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com'; $_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
$n = 0; $o = 0;
foreach (get_option('abu_vodic_slike', array()) as $url => $id) {
    if ($id <= 0) { continue; } $n++;
    $m = wp_get_attachment_metadata($id);
    if (!empty($m['height']) && $m['height'] > $m['width'] * 1.05) { $o++; echo 'okomita #' . $id . ' ' . $m['width'] . 'x' . $m['height'] . ' ' . get_the_title($id) . PHP_EOL; }
}
echo "ukupno $n, okomitih $o" . PHP_EOL;
