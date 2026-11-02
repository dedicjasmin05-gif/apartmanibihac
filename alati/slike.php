<?php
/**
 * Preuzimanje fotografija vodiča (sadrzaj/*.json) u medije. Pokreće se cronom svake minute
 * dok sve ne preuzme: php alati/slike.php [broj]. Mapiranje url => ID u opciji abu_vodic_slike.
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$max = isset($argv[1]) ? (int) $argv[1] : 20;
$start = time();
add_filter('http_request_args', function ($a) { $a['user-agent'] = 'apartmanibihac.com-vodic/1.0 (+https://apartmanibihac.com/)'; $a['timeout'] = 25; return $a; });

$map = get_option('abu_vodic_slike', array());
$pokusaji = get_option('abu_vodic_slike_pok', array());
$sve = abu_vodic_sve_slike();
$ok = 0; $greske = 0; $ostalo = 0;
foreach ($sve as $url => $info) {
    if (!empty($map[$url]) && $map[$url] > 0) { continue; }
    if ((isset($pokusaji[$url]) ? $pokusaji[$url] : 0) >= 2) { continue; }
    if ($ok + $greske >= $max || time() - $start > 50) { $ostalo++; continue; }
    list($ime, $izvor, $lic, $autor) = $info;
    $tmp = download_url($url);
    if (is_wp_error($tmp)) {
        $pokusaji[$url] = (isset($pokusaji[$url]) ? $pokusaji[$url] : 0) + 1; $greske++;
        echo 'GREŠKA ' . $url . ' ' . $tmp->get_error_message() . PHP_EOL;
        continue;
    }
    $tip = wp_check_filetype_and_ext($tmp, basename(parse_url($url, PHP_URL_PATH)));
    $ext = $tip['ext'] ? $tip['ext'] : 'jpg';
    $mime = function_exists('mime_content_type') ? mime_content_type($tmp) : '';
    if ($mime && strpos($mime, 'image/') !== 0) { @unlink($tmp); $pokusaji[$url] = 9; $greske++; echo 'NIJE SLIKA ' . $url . ' ' . $mime . PHP_EOL; continue; }
    if (!$tip['ext'] && $mime) { $ext = str_replace(array('image/', 'jpeg'), array('', 'jpg'), $mime); }
    $file = array('name' => sanitize_title($ime ? $ime : 'bihac') . '-' . substr(md5($url), 0, 6) . '.' . $ext, 'tmp_name' => $tmp);
    $id = media_handle_sideload($file, 0, $ime ? $ime . ', Bihać' : 'Bihać');
    if (is_wp_error($id)) { @unlink($tmp); $pokusaji[$url] = (isset($pokusaji[$url]) ? $pokusaji[$url] : 0) + 1; $greske++; echo 'GREŠKA ' . $url . ' ' . $id->get_error_message() . PHP_EOL; continue; }
    update_post_meta($id, '_wp_attachment_image_alt', $ime ? $ime . ', Bihać' : 'Bihać i rijeka Una');
    $kredit = trim($autor . ($lic ? ($autor ? ', ' : '') . $lic : ''));
    if ($kredit && stripos($lic, 'službena') === false) { update_post_meta($id, '_abu_kredit', $kredit); }
    update_post_meta($id, '_abu_izvor', $izvor ? $izvor : $url);
    $map[$url] = $id; $ok++;
    update_option('abu_vodic_slike', $map, false);
}
update_option('abu_vodic_slike', $map, false);
update_option('abu_vodic_slike_pok', $pokusaji, false);
$imaju = count(array_filter($map, function ($v) { return $v > 0; }));
echo 'preuzeto sad: ' . $ok . ', greške: ' . $greske . ', čeka: ' . $ostalo . ' | ukupno u medijima: ' . $imaju . ' / ' . count($sve) . PHP_EOL;
