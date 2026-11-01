<?php
/** SEO provjera živog sajta iz komandne linije. */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
function dohvati($u) {
    $c = curl_init($u);
    curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => 'Mozilla/5.0 (seo-provjera)'));
    $b = (string) curl_exec($c);
    $k = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $t = round(curl_getinfo($c, CURLINFO_TOTAL_TIME), 2);
    curl_close($c);
    return array($k, $b, $t);
}
foreach (array('/', '/smjestaj-bihac/', '/cesta-pitanja/kako-doci-u-bihac/', '/en/accommodation-bihac/', '/de/faq/', '/smjestaj/vila-enio/') as $p) {
    list($k, $b, $t) = dohvati('https://apartmanibihac.com' . $p);
    preg_match('/<title>(.*?)<\/title>/si', $b, $ti);
    preg_match('/<meta name="description" content="([^"]*)"/i', $b, $md);
    $h1 = preg_match_all('/<h1[\s>]/i', $b);
    $ld = preg_match_all('/application\/ld\+json/i', $b);
    $hl = preg_match_all('/hreflang=/i', $b);
    $can = preg_match('/rel=["\']canonical["\'][^>]*href=["\']([^"\']+)/i', $b, $cm) ? $cm[1] : '-';
    $robots = preg_match('/<meta name=["\']robots["\'] content=["\']([^"\']+)/i', $b, $rm) ? $rm[1] : '-';
    $img = preg_match_all('/<img /i', $b);
    $alt = preg_match_all('/<img [^>]*alt=["\'][^"\']+["\']/i', $b);
    echo $p . ' | ' . $k . ' | ' . $t . 's | ' . (strlen($b) >> 10) . 'KB | title: ' . (isset($ti[1]) ? trim(html_entity_decode($ti[1])) : '-') . ' | desc: ' . (isset($md[1]) ? strlen(html_entity_decode($md[1])) : 0) . ' zn | h1: ' . $h1 . ' | json-ld: ' . $ld . ' | hreflang: ' . $hl . ' | canonical: ' . $can . ' | robots: ' . $robots . ' | img ' . $img . ' (alt ' . $alt . ') | kontakt traka: ' . (strpos($b, 'abu-kt-broj') !== false ? 'DA' : 'NE') . ' | desc tekst: ' . (isset($md[1]) ? html_entity_decode($md[1]) : '') . PHP_EOL;
}
list($k, $b) = dohvati('https://apartmanibihac.com/robots.txt');
echo 'robots.txt ' . $k . ': ' . preg_replace('/\s+/', ' ', substr($b, 0, 300)) . PHP_EOL;
list($k, $b) = dohvati('https://apartmanibihac.com/wp-sitemap.xml');
echo 'sitemap ' . $k . ': ' . preg_match_all('/<loc>/', $b) . ' podsitemapa' . PHP_EOL;
$uk = 0;
if (preg_match_all('/<loc>([^<]+)<\/loc>/', $b, $m)) { foreach ($m[1] as $s) { list(, $sb) = dohvati($s); $n = preg_match_all('/<loc>/', $sb); $uk += $n; echo '  ' . basename($s) . ': ' . $n . PHP_EOL; } }
echo 'URL-ova u sitemapu: ' . $uk . PHP_EOL;
list($k, $b) = dohvati('https://apartmanibihac.com/llms-full.txt');
echo 'llms-full.txt ' . $k . ': ' . (strlen($b) >> 10) . ' KB, pitanja: ' . substr_count($b, "\n### ") . PHP_EOL;
list($k, $b) = dohvati('https://www.apartmanibihac.com/');
echo 'www -> ' . $k . PHP_EOL;
list($k, $b) = dohvati('https://apartmanibihac.com/ponuda/');
echo '/ponuda/ (stara adresa) -> ' . $k . PHP_EOL;
