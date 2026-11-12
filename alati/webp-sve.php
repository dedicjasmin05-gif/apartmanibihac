<?php
/**
 * Jednokratno: WebP kopije za sve postojeće JPG/PNG slike u uploads (samo CLI, preko cron posla).
 * Može se pokretati više puta: preskače gotove, staje nakon ~240 s i nastavlja pri sljedećem pokretanju.
 * Originalne slike se ne mijenjaju. Ispis: broj datoteka, ušteda i preostalo.
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
@set_time_limit(0);
if (!function_exists('abu_webp_napravi')) { echo "webp.php nije učitan\n"; exit(1); }
echo 'GD WebP: ' . (abu_webp_moguce() ? 'DA' : 'NE') . ' | PHP ' . PHP_VERSION . PHP_EOL;
if (!abu_webp_moguce()) { exit(1); }
$dir = wp_get_upload_dir()['basedir'];
$start = time(); $novo = 0; $usteda = 0; $preskoceno = 0; $ostalo = 0; $ukupno = 0; $orig = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
    $p = $f->getPathname();
    if (!preg_match('~\.(jpe?g|png)$~i', $p) || strpos($p, '/abu-tmp/') !== false) { continue; }
    $ukupno++;
    if (is_file($p . '.webp')) { $preskoceno++; continue; }
    if (time() - $start > 240) { $ostalo++; continue; }
    $orig += filesize($p);
    $r = abu_webp_napravi($p);
    if ($r > 0) { $novo++; $usteda += $r; }
}
printf("slika ukupno: %d | novih webp: %d | već gotovih: %d | preostalo za sljedeće pokretanje: %d | ušteda u ovom pokretanju: %.1f MB od %.1f MB\n", $ukupno, $novo, $preskoceno, $ostalo, $usteda / 1048576, $orig / 1048576);
