<?php
/** Test za dnevne-vijesti.php: php testovi/test-dnevne-vijesti.php */
define('ABSPATH', __DIR__); define('OBJECT', 'OBJECT'); define('DAY_IN_SECONDS', 86400);
$GLOBALS['o'] = array(); $GLOBALS['p'] = array(); $GLOBALS['m'] = array();
function add_action() {} function add_filter() {}
function get_option($k, $d = false) { return $GLOBALS['o'][$k] ?? $d; } function update_option($k, $v) { $GLOBALS['o'][$k] = $v; }
function wp_date($f, $t) { return gmdate($f, $t); } function get_term_by() { return (object) array('term_id' => 9); }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9-]/i', '-', $s)); }
function get_page_by_path($s) { return isset($GLOBALS['p'][$s]) ? (object) array() : null; }
function wp_strip_all_tags($s) { return strip_tags($s); } function wp_kses_post($s) { return preg_replace('#<script.*?</script>#s', '', $s); }
function wp_insert_post($a) { $GLOBALS['p'][$a['post_name']] = $a; return count($GLOBALS['p']); }
function is_wp_error($x) { return false; } function update_post_meta($id, $k, $v) { $GLOBALS['m'][$id][$k] = $v; } function esc_url_raw($u) { return $u; }
require dirname(__DIR__) . '/dnevne-vijesti.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
$sada = strtotime('2026-11-14T14:00:00+01:00');
$v = function ($s, $kad) { return array('slug' => $s, 'naslov' => 'N ' . $s, 'izvod' => 'i', 'html' => '<p>x</p><script>alert(1)</script>', 'objavi' => $kad, 'izvor_url' => 'https://klix.ba/x', 'fb' => 'fb', 'ig' => 'ig'); };
$l = array($v('a', '2026-11-14T08:30:00+01:00'), $v('b', '2026-11-14T13:30:00+01:00'), $v('c', '2026-11-14T19:30:00+01:00'), $v('stara', '2026-11-10T08:30:00+01:00'), array('slug' => 'los'));
t('objavljene 2 (8:30 i 13:30)', abu_dv_obradi($l, $sada) === 2);
t('19:30 još nije', !isset($GLOBALS['p']['c']));
t('stara se preskače', !isset($GLOBALS['p']['stara']));
t('script uklonjen', strpos($GLOBALS['p']['a']['post_content'], 'script') === false);
t('kategorija turizam', $GLOBALS['p']['a']['post_category'] === array(9));
t('fb tekst spremljen', $GLOBALS['m'][1]['_abu_fb_tekst'] === 'fb');
t('drugi prolaz ne duplira', abu_dv_obradi($l, $sada) === 0);
t('navečer izlazi treća', abu_dv_obradi($l, strtotime('2026-11-14T19:45:00+01:00')) === 1);
$puno = array(); for ($i = 0; $i < 8; $i++) { $puno[] = $v('d' . $i, '2026-11-15T08:00:00+01:00'); }
t('najviše 4 dnevno', abu_dv_obradi($puno, strtotime('2026-11-15T09:00:00+01:00')) === 4);
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
