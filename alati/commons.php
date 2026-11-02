<?php
/** Za izlete bez fotografija: traži slike na Wikimedia Commons (naziv i lokacija). Ispis JSON u uploads/abu-tmp/commons.json (CLI). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com'; $_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
add_filter('http_request_args', function ($a) { $a['user-agent'] = 'apartmanibihac.com-vodic/1.0 (+https://apartmanibihac.com/)'; $a['timeout'] = 25; return $a; });
function c_api($q) {
    $r = wp_remote_get('https://commons.wikimedia.org/w/api.php?' . http_build_query(array_merge(array('action' => 'query', 'format' => 'json', 'prop' => 'imageinfo', 'iiprop' => 'url|size|mime', 'gsrnamespace' => 6, 'gsrlimit' => 12), $q)));
    $j = is_wp_error($r) ? null : json_decode(wp_remote_retrieve_body($r), true);
    $out = array();
    foreach ((isset($j['query']['pages']) ? $j['query']['pages'] : array()) as $p) {
        $ii = isset($p['imageinfo'][0]) ? $p['imageinfo'][0] : null;
        if (!$ii || $ii['mime'] !== 'image/jpeg' || $ii['width'] < 900 || $ii['width'] < $ii['height']) { continue; }
        $out[] = array('url' => $ii['url'], 'izvor' => $ii['descriptionurl'], 'title' => $p['title']);
    }
    return $out;
}
$d = json_decode(file_get_contents(dirname(__DIR__) . '/sadrzaj/izleti.json'), true);
$rez = array();
foreach ($d as $e) {
    if (!empty($e['slike'])) { continue; }
    $ime = trim(preg_split('/\s[–(-]\s?|\s\(/u', $e['ime'])[0]);
    $nadj = array();
    foreach (array($ime . ' Bihać', $ime) as $q) {
        foreach (c_api(array('generator' => 'search', 'gsrsearch' => $q . ' filetype:bitmap')) as $x) { $nadj[$x['url']] = $x; }
        if (count($nadj) >= 3) { break; }
    }
    if (count($nadj) < 2 && !empty($e['lat'])) {
        foreach (c_api(array('generator' => 'geosearch', 'ggscoord' => $e['lat'] . '|' . $e['lng'], 'ggsradius' => 1500, 'ggslimit' => 15, 'ggsnamespace' => 6)) as $x) { $nadj[$x['url']] = $x; }
    }
    $rez[$e['slug']] = array_slice(array_values($nadj), 0, 5);
    echo $e['slug'] . ': ' . count($rez[$e['slug']]) . PHP_EOL;
}
$dir = WP_CONTENT_DIR . '/uploads/abu-tmp';
@mkdir($dir);
file_put_contents($dir . '/commons.json', wp_json_encode($rez, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
