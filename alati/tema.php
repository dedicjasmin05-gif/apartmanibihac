<?php
/** Aktivna tema, njene datoteke, logo u medijima (samo CLI). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
$t = wp_get_theme();
echo 'tema: ' . $t->get('Name') . ' ' . $t->get('Version') . ' | dir ' . get_stylesheet_directory() . ' | parent ' . get_template() . PHP_EOL;
echo 'git: ' . (is_dir(get_stylesheet_directory() . '/.git') ? 'DA' : 'ne') . PHP_EOL;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(get_stylesheet_directory(), FilesystemIterator::SKIP_DOTS)) as $f) {
    if (strpos($f, '/.git/') !== false) { continue; }
    echo '  ' . substr($f, strlen(get_stylesheet_directory())) . ' ' . filesize($f) . PHP_EOL;
}
echo 'custom_logo: ' . get_theme_mod('custom_logo') . ' ' . wp_get_attachment_url(get_theme_mod('custom_logo')) . PHP_EOL;
foreach (get_posts(array('post_type' => 'attachment', 'numberposts' => 30, 's' => 'logo')) as $a) { echo 'logo? #' . $a->ID . ' ' . $a->post_title . ' ' . wp_get_attachment_url($a->ID) . PHP_EOL; }
echo 'plugini: ' . implode(', ', get_option('active_plugins')) . PHP_EOL;
echo 'meni: ' . implode(', ', array_keys(get_nav_menu_locations())) . PHP_EOL;
