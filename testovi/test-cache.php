<?php
/** Izolirani test za cache.php: php testovi/test-cache.php */
define('ABSPATH', __DIR__);
$GLOBALS['S'] = array(); $GLOBALS['A'] = array();
function add_action($h, $cb) { $GLOBALS['cb'] = $cb; }
function do_action($h, $r) { $GLOBALS['A'][] = $h . ':' . $r; }
function is_singular($t) { return !empty($GLOBALS['S']['objekt']); }
function get_query_var($k) { return !empty($GLOBALS['S']['trazi']) ? '1' : ''; }
function is_page($s) { return ($GLOBALS['S']['page'] ?? '') === $s; }
function abu_tb_tekuci() { return !empty($GLOBALS['S']['tb']) ? 'bs' : ''; }
require dirname(__DIR__) . '/cache.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
function prolaz($s, $get = array()) { $GLOBALS['S'] = $s; $_GET = $get; $GLOBALS['A'] = array(); ($GLOBALS['cb'])(); return $GLOBALS['A']; }
t('naslovnica ide u cache', prolaz(array()) === array());
t('objekt bez cachea', count(prolaz(array('objekt' => 1))) === 1);
t('pretraga bez cachea', count(prolaz(array('trazi' => 1))) === 1);
t('prijava objekta bez cachea', count(prolaz(array('page' => 'prijavite-objekt'))) === 1);
t('team building bez cachea', count(prolaz(array('tb' => 1))) === 1);
t('potvrda iz e-maila bez cachea', count(prolaz(array(), array('abu_a' => 'potvrdi'))) === 1);
t('poruka nakon upita bez cachea', count(prolaz(array(), array('abu_ok' => '5'))) === 1);
t('naziv akcije', strpos(prolaz(array('objekt' => 1))[0], 'litespeed_control_set_nocache:') === 0);
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
