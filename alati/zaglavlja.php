<?php
/** Ispis HTTP zaglavlja naslovnice gledano sa servera (samo CLI, samo čitanje). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
foreach (array('https://apartmanibihac.com/', 'https://apartmanibihac.com/?c=' . mt_rand()) as $u) {
    $ch = curl_init($u);
    curl_setopt_array($ch, array(CURLOPT_NOBODY => true, CURLOPT_HEADER => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30));
    $h = (string) curl_exec($ch); curl_close($ch);
    echo "== $u\n";
    foreach (explode("\n", $h) as $l) { if (preg_match('/^(HTTP|strict-transport|x-content-type|referrer-policy|permissions-policy|x-frame|x-litespeed-cache:)/i', $l)) { echo trim($l) . "\n"; } }
}
