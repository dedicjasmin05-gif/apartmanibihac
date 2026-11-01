<?php
/** Popis stranica i članaka sa starog sajta (Leftor, 95.217.229.38) i usporedba s novim. php stari.php [pojam] */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
function stari($put) {
    $c = curl_init('https://www.apartmanibihac.com' . $put);
    curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_RESOLVE => array('www.apartmanibihac.com:443:95.217.229.38')));
    $b = (string) curl_exec($c);
    curl_close($c);
    return json_decode($b, true);
}
$pojam = isset($argv[1]) ? $argv[1] : '';
foreach (array('pages', 'posts') as $tip) {
    for ($str = 1; $str <= 3; $str++) {
        $d = stari('/wp-json/wp/v2/' . $tip . '?per_page=100&page=' . $str . '&_fields=slug,title,status,link,content' . ($pojam ? '&search=' . rawurlencode($pojam) : ''));
        if (!is_array($d) || !$d || isset($d['code'])) { break; }
        foreach ($d as $p) {
            $slug = $p['slug'];
            $novi = get_page_by_path($slug, OBJECT, array('page', 'post', 'objekt'));
            $rijeci = str_word_count(wp_strip_all_tags($p['content']['rendered']));
            echo $tip . ' | ' . $slug . ' | ' . html_entity_decode($p['title']['rendered']) . ' | ' . $rijeci . ' r. | novi: ' . ($novi ? $novi->post_status : 'NEMA') . PHP_EOL;
        }
    }
}
