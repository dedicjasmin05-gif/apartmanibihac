<?php
/** Provjera domene iz komandne linije: što vraća apartmanibihac.com preko javnog DNS-a i izravno na IP. */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
echo 'DNS A: ' . implode(',', gethostbynamel('apartmanibihac.com') ?: array('-')) . ' | www: ' . implode(',', gethostbynamel('www.apartmanibihac.com') ?: array('-')) . PHP_EOL;
foreach (array('http://apartmanibihac.com/llms.txt', 'http://www.apartmanibihac.com/llms.txt', 'https://apartmanibihac.com/llms.txt') as $u) {
    foreach (array('', '46.202.156.141') as $ip) {
        $c = curl_init($u);
        $o = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HEADER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0);
        if ($ip) { $h = parse_url($u, PHP_URL_HOST); $p = parse_url($u, PHP_URL_SCHEME) === 'https' ? 443 : 80; $o[CURLOPT_RESOLVE] = array("$h:$p:$ip"); }
        curl_setopt_array($c, $o);
        $r = (string) curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $err = curl_error($c);
        curl_close($c);
        preg_match('/^location:\s*(\S+)/mi', $r, $m);
        $tijelo = substr($r, strpos($r, "\r\n\r\n") + 4, 70);
        echo $u . ($ip ? ' @' . $ip : ' @dns') . ' -> ' . $code . ($m ? ' loc=' . $m[1] : '') . ($err ? ' err=' . $err : '') . ' | ' . preg_replace('/\s+/', ' ', $tijelo) . PHP_EOL;
    }
}
