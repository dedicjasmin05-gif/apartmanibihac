<?php
/** Ciljana pretraga Commons za preostale izlete (CLI). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com'; $_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
add_filter('http_request_args', function ($a) { $a['user-agent'] = 'apartmanibihac.com-vodic/1.0 (+https://apartmanibihac.com/)'; $a['timeout'] = 25; return $a; });
$q = array(
 'lohovo' => array('Lohovo', 'Una Lohovo', 'Troslap Una', 'Dvoslap Una'),
 'cukovi' => array('Ćukovi', 'Cukovi Una', 'Una canyon Kulen Vakuf', 'Una river national park canyon'),
 'japodski' => array('Japodski otoci', 'Japod islands Una', 'Ripač Una river'),
 'kostela' => array('Kostelski buk', 'Kostela Bihać', 'Una Kostela', 'Kostela waterfall'),
 'sturlic' => array('Šturlić', 'Sturlic Cazin'),
 'pritoka' => array('Pritoka Bihać', 'Una river Bihać', 'Una Bihać'),
 'crkva' => array('Bihać crkva', 'Bihać zvonik', 'Bihac church tower', 'Bihać Sveti Ante'),
 'cazin' => array('Cazin', 'Cazin stari grad', 'Cazin mosque'),
 'bicikl' => array('Una river cycling', 'Una National Park', 'Una valley'),
);
foreach ($q as $k => $lista) {
    $nad = array();
    foreach ($lista as $s) {
        $r = wp_remote_get('https://commons.wikimedia.org/w/api.php?' . http_build_query(array('action' => 'query', 'format' => 'json', 'generator' => 'search', 'gsrsearch' => $s . ' filetype:bitmap', 'gsrnamespace' => 6, 'gsrlimit' => 15, 'prop' => 'imageinfo', 'iiprop' => 'size|mime')));
        $j = is_wp_error($r) ? null : json_decode(wp_remote_retrieve_body($r), true);
        foreach ((isset($j['query']['pages']) ? $j['query']['pages'] : array()) as $p) {
            $ii = isset($p['imageinfo'][0]) ? $p['imageinfo'][0] : null;
            if (!$ii || $ii['mime'] !== 'image/jpeg' || $ii['width'] < 900 || $ii['width'] < $ii['height']) { continue; }
            $nad[$p['title']] = 1;
        }
    }
    echo '## ' . $k . PHP_EOL . implode(PHP_EOL, array_slice(array_keys($nad), 0, 25)) . PHP_EOL;
}
