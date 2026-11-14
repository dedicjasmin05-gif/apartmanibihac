<?php
/** Izolirani test za mjerenje.php i skraćivanje naslova: php testovi/test-mjerenje.php */
define('ABSPATH', __DIR__);
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
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
