<?php
/**
 * Osvježi stranice čestih pitanja iz pitanja/*.json bez vremenskog ograničenja web zahtjeva (samo CLI).
 * Pokretanje: php alati/pitanja-osvjezi.php
 */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
set_time_limit(0);
require dirname(__DIR__, 4) . '/wp-load.php';
$pot = ABU_VER;
foreach (array_keys(abu_pitanja_jezici()) as $l) {
    foreach (array_keys(abu_pitanja_teme_sve()) as $k) {
        $f = dirname(__DIR__) . '/pitanja/' . abu_pitanja_jezici()[$l]['dir'] . $k . '.json';
        $pot .= is_readable($f) ? md5_file($f) : '-';
    }
}
set_transient('abu_pitanja_lock', 1, 900);
$t = microtime(true);
foreach (array_keys(abu_pitanja_jezici()) as $l) { abu_pitanja_osvjezi_jezik($l); echo "$l gotovo " . round(microtime(true) - $t, 1) . " s\n"; }
update_option('abu_pitanja_ver', md5($pot));
delete_transient('abu_pitanja_lock');
do_action('litespeed_purge_all');
echo "OK\n";
