<?php
/** Izolirani test za mjerenje.php i skraćivanje naslova: php testovi/test-mjerenje.php */
define('ABSPATH', __DIR__); define('WP_CONTENT_DIR', sys_get_temp_dir() . '/abu-wpc-' . getmypid()); define('ABU_TABLE', 'abu_upiti');
function wp_mkdir_p($d) { return @mkdir($d, 0777, true) || is_dir($d); } function wp_json_encode($x, $f = 0) { return json_encode($x, $f); } function sanitize_text_field($s) { return trim(strip_tags((string) $s)); } function post_type_exists() { return false; }
class W { public $prefix = 'wp_'; function prepare($q) { return $q; } function get_results($q) { return array((object) array('izvor' => 'web', 'status' => 'novi', 'n' => 2)); } } $GLOBALS['wpdb'] = new W();
$GLOBALS['O'] = array(); $GLOBALS['F'] = array(); $GLOBALS['FP'] = false;
function add_action() {} function add_filter($h, $cb) { $GLOBALS['F'][$h][] = $cb; }
function get_option($k, $d = false) { return isset($GLOBALS['O'][$k]) ? $GLOBALS['O'][$k] : $d; }
function update_option($k, $v) { $GLOBALS['O'][$k] = $v; }
function current_time($f) { return gmdate($f); }
$GLOBALS['T'] = array(); function get_transient($k) { return $GLOBALS['T'][$k] ?? false; } function set_transient($k, $v, $e) { $GLOBALS['T'][$k] = $v; }
function wp_salt($s) { return 'sol'; } define('MINUTE_IN_SECONDS', 60);
function wp_parse_url($u, $c) { return parse_url($u, $c); }
function is_front_page() { return $GLOBALS['FP']; }
function wp_strip_all_tags($s) { return strip_tags($s); }
require dirname(__DIR__) . '/mjerenje.php';
require dirname(__DIR__) . '/seo-tehnika.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
$k = 'abu_mjerenje_' . gmdate('Y-m');
abu_mj_zabiljezi('wa', '', '/smjestaj/vila-enio/'); abu_mj_zabiljezi('wa', '', '/smjestaj/vila-enio/'); abu_mj_zabiljezi('tel', '', '/');
abu_mj_zabiljezi('ulaz', 'chatgpt'); abu_mj_zabiljezi('ulaz', 'zlonamjerno');
$z = $GLOBALS['O'][$k];
t('wa 2', $z['d:wa'] === 2); t('tel 1', $z['d:tel'] === 1); t('stranica', $z['p:/smjestaj/vila-enio/'] === 2);
t('ulaz 2', $z['d:ulaz'] === 2); t('chatgpt 1', $z['i:chatgpt'] === 1); t('nepoznat izvor se ne sprema', !isset($z['i:zlonamjerno']));
t('nepoznat događaj odbijen', abu_mj_zabiljezi('hack') === false);
t('put očišćen', abu_mj_put('/a b/<script>?x=1') === '/ab/script'); t('put prazan', abu_mj_put('') === '/');
for ($i = 0; $i < 600; $i++) { abu_mj_zabiljezi('wa', '', '/p' . $i . '/'); }
t('najviše 500 stranica', count(preg_grep('~^p:~', array_keys($GLOBALS['O'][$k]))) === 500);
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; bingbot/2.0)'; t('bot', abu_mj_bot());
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0) Safari/604.1'; t('čovjek', !abu_mj_bot());
$tp = $GLOBALS['F']['document_title_parts'][0];
t('naslov s Bihać bez imena sajta', !isset($tp(array('title' => 'Rafting na Uni: kako odabrati turu', 'site' => 'X'))['site']));
t('naslov bez Bihaća zadržava ime', isset($tp(array('title' => 'O nama', 'site' => 'X'))['site']));
t('"Unadom" nije "Una"', isset($tp(array('title' => 'Vikendica Unadom', 'site' => 'X'))['site']));
t('dug naslov bez Bihaća gubi ime sajta', !isset($tp(array('title' => 'Što ponijeti na rafting: praktičan popis za pripremu', 'site' => 'Apartmani i vikendice Bihać'))['site']));
t('kratak naslov bez Bihaća zadržava ime', isset($tp(array('title' => 'O nama', 'site' => 'Apartmani i vikendice Bihać'))['site']));
// kampanje
t('oznaka kampanje očišćena', abu_mj_k(' Boost Nova Godina!! ') === 'boost-nova-godina' && abu_mj_k(str_repeat('a', 80)) === str_repeat('a', 60));
abu_mj_kampanja_zabiljezi('fb-12', 'ulaz', 'facebook'); abu_mj_kampanja_zabiljezi('fb-12', 'ulaz', 'facebook'); abu_mj_kampanja_zabiljezi('fb-12', 'kontakt'); abu_mj_kampanja_zabiljezi('fb-12', 'upit');
$c = $GLOBALS['O']['abu_mj_kampanje']['fb-12'];
t('kampanja broji', $c['ulaz'] === 2 && $c['kontakt'] === 1 && $c['upit'] === 1 && $c['izvor'] === 'facebook');
t('nepoznata vrsta odbijena', abu_mj_kampanja_zabiljezi('fb-12', 'hack') === false && abu_mj_kampanja_zabiljezi('', 'ulaz') === false);
for ($i = 0; $i < 310; $i++) { abu_mj_kampanja_zabiljezi('k' . $i, 'ulaz'); }
t('najviše 300 kampanja', count($GLOBALS['O']['abu_mj_kampanje']) === 300);
t('cijena po upitu', abu_mj_po(30, 4) === '7,50 €' && abu_mj_po(0, 4) === '–' && abu_mj_po(30, 0) === '–');
$_SERVER['REMOTE_ADDR'] = '1.2.3.4'; $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone)';
t('prvi dolazak se broji', abu_mj_ponovljeno() === false); t('ponovljeni u 30 min ne', abu_mj_ponovljeno() === true);
$_SERVER['REMOTE_ADDR'] = '5.6.7.8'; t('drugi posjetitelj se broji', abu_mj_ponovljeno() === false);
$_SERVER['HTTP_USER_AGENT'] = 'facebookexternalhit/1.1'; t('facebook pregled je bot', abu_mj_bot());
t('google_ads je izvor', in_array('google_ads', abu_mj_izvori(), true));
// troškovi iz datoteke i stanje za zadatke
$GLOBALS['O']['abu_mj_kampanje'] = array(); abu_mj_kampanja_zabiljezi('fb-77', 'ulaz', 'facebook'); abu_mj_kampanja_zabiljezi('fb-77', 'ulaz'); abu_mj_kampanja_zabiljezi('fb-77', 'upit');
file_put_contents(abu_mj_mapa() . '/troskovi.json', json_encode(array('fb-77' => array('trosak' => 3, 'naziv' => 'Boost: test'), 'fb-88' => array('trosak' => 3))));
t('uvoz troškova', abu_mj_uvezi_troskove() === 2 && $GLOBALS['O']['abu_mj_kampanje']['fb-77']['trosak'] == 3 && $GLOBALS['O']['abu_mj_kampanje']['fb-88']['kanal'] === 'facebook');
$GLOBALS['O']['abu_mj_kampanje']['fb-77']['trosak'] = 5; t('ista datoteka ne gazi ručni unos', abu_mj_uvezi_troskove() === 0 && $GLOBALS['O']['abu_mj_kampanje']['fb-77']['trosak'] == 5);
abu_mj_izvezi_stanje(); $st = json_decode(file_get_contents(abu_mj_mapa() . '/stanje.json'), true);
t('stanje.json', $st['kampanje']['fb-77']['dolasci'] === 2 && $st['kampanje']['fb-77']['eur_po_upitu'] == 5 && $st['upiti_mjesec']['web'] === 2);
t('mapa zatvorena za web', strpos(file_get_contents(abu_mj_mapa() . '/.htaccess'), 'denied') !== false);
array_map('unlink', array_filter(glob(WP_CONTENT_DIR . '/abu-mjerenje/{,.}*', GLOB_BRACE) ?: array(), 'is_file')); @rmdir(WP_CONTENT_DIR . '/abu-mjerenje'); @rmdir(WP_CONTENT_DIR);
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
