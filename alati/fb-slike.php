<?php
/**
 * Preuzmi fotografije iz jedne objave na Facebook stranici (link koji je vlasnik dao) u bazen za grafike:
 * wp-content/uploads/abu-grafike/foto/fb-<objava>-<n>.jpg. Koristi Meta token spremljen u WP (ne ispisuje ga).
 * Samo CLI: php alati/fb-slike.php "https://www.facebook.com/share/p/XXXX/"
 * (13. 11. 2026. nije trebalo: vlasnik je fotografije poslao izravno, u grafike/foto/.)
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';

$link = $argv[1] ?? '';
if (!$link) { echo "Nema linka.\n"; exit(1); }
$o = abu_meta_opt();
if (!$o['token'] || !$o['page_id']) { echo "Nema tokena ili ID-a stranice.\n"; exit(1); }

// 1. Gdje vodi kratki link (ID objave / pfbid).
$ch = curl_init($link);
curl_setopt_array($ch, array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 8, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36'));
$html = (string) curl_exec($ch);
$kraj = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);
$kraj = rawurldecode($kraj);
echo "Link vodi na: " . preg_replace('/[?&](__|mibextid|rdid|share_url)[^&]*/', '', $kraj) . "\n";
$kljucevi = array();
if (preg_match_all('/(pfbid[0-9A-Za-z]+)/', $kraj . ' ' . $html, $m)) { $kljucevi = array_merge($kljucevi, array_unique($m[1])); }
if (preg_match_all('/(?:posts|permalink|story_fbid=|fbid=|photos\/[^\/]+\/)\/?(\d{8,})/', $kraj, $m)) { $kljucevi = array_merge($kljucevi, array_unique($m[1])); }
echo "Ključevi: " . implode(', ', array_slice($kljucevi, 0, 5)) . "\n";

// 2. Zadnje objave stranice s privicima; nađi onu koja odgovara.
$polja = 'id,permalink_url,created_time,message,full_picture,attachments{media,subattachments.limit(50){media}}';
$r = wp_remote_get(ABU_GRAPH . rawurlencode($o['page_id']) . '/posts?limit=60&fields=' . rawurlencode($polja) . '&access_token=' . rawurlencode($o['token']), array('timeout' => 40));
if (is_wp_error($r)) { echo "Graph greška: " . $r->get_error_message() . "\n"; exit(1); }
$j = json_decode((string) wp_remote_retrieve_body($r), true);
if (!empty($j['error'])) { echo "Graph greška: " . ($j['error']['message'] ?? '') . "\n"; exit(1); }
$nadjena = null;
foreach ((array) ($j['data'] ?? array()) as $p) {
    $tekst = ($p['permalink_url'] ?? '') . ' ' . ($p['id'] ?? '');
    foreach ($kljucevi as $k) { if ($k && strpos($tekst, $k) !== false) { $nadjena = $p; break 2; } }
}
if (!$nadjena) {
    echo "Objava nije nađena među zadnjih 60. Zadnje objave sa slikama:\n";
    foreach (array_slice((array) ($j['data'] ?? array()), 0, 15) as $p) {
        echo '- ' . ($p['created_time'] ?? '') . ' ' . ($p['permalink_url'] ?? $p['id']) . ' | ' . mb_substr(str_replace("\n", ' ', (string) ($p['message'] ?? '')), 0, 70) . "\n";
    }
    exit(2);
}
echo "Objava: " . ($nadjena['created_time'] ?? '') . ' ' . ($nadjena['permalink_url'] ?? '') . "\n";

// 3. Sve slike iz objave.
$urls = array();
foreach ((array) ($nadjena['attachments']['data'] ?? array()) as $a) {
    if (!empty($a['subattachments']['data'])) { foreach ($a['subattachments']['data'] as $s) { if (!empty($s['media']['image']['src'])) { $urls[] = $s['media']['image']['src']; } } }
    elseif (!empty($a['media']['image']['src'])) { $urls[] = $a['media']['image']['src']; }
}
if (!$urls && !empty($nadjena['full_picture'])) { $urls[] = $nadjena['full_picture']; }
$up = wp_upload_dir();
$dir = trailingslashit($up['basedir']) . 'abu-grafike/foto';
wp_mkdir_p($dir);
$id = preg_replace('/\D/', '', substr((string) $nadjena['id'], strpos((string) $nadjena['id'], '_') + 1));
$n = 0;
foreach ($urls as $i => $u) {
    $g = wp_remote_get($u, array('timeout' => 40));
    if (is_wp_error($g) || wp_remote_retrieve_response_code($g) !== 200) { echo "Slika " . ($i + 1) . ": nije preuzeta\n"; continue; }
    $f = $dir . '/fb-' . $id . '-' . ($i + 1) . '.jpg';
    file_put_contents($f, wp_remote_retrieve_body($g));
    $s = @getimagesize($f);
    if (!$s) { @unlink($f); echo "Slika " . ($i + 1) . ": nije slika\n"; continue; }
    echo "Spremljeno: " . basename($f) . " ({$s[0]}x{$s[1]})\n";
    $n++;
}
echo "Ukupno: $n slika u " . str_replace(ABSPATH, '', $dir) . "\n";
