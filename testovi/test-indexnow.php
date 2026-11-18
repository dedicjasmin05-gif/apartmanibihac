<?php
/** Test za indexnow.php (red i javna adresa): php testovi/test-indexnow.php */
define('ABSPATH', __DIR__);
function add_action() {} function add_filter() {}
$OPT = array();
function get_option($k, $d = false) { global $OPT; return isset($OPT[$k]) ? $OPT[$k] : $d; }
function update_option($k, $v) { global $OPT; $OPT[$k] = $v; return true; }
function home_url($p = '') { return 'https://apartmanibihac.com' . $p; }
function wp_parse_url($u, $c = -1) { return parse_url($u, $c); }
function get_permalink($p) { return $p->post_status === 'publish' ? 'https://apartmanibihac.com/2026/11/16/' . $p->post_name . '/' : 'https://apartmanibihac.com/?p=' . $p->ID; }
require dirname(__DIR__) . '/indexnow.php';
$ok = 0; $bad = 0;
function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
abu_in_u_red(array('https://apartmanibihac.com/a/', 'https://apartmanibihac.com/a/', 'https://apartmanibihac.com/smjestaj/?mjesto=x', 'https://drugi.com/b/', ''));
$r = get_option('abu_in_red');
t('red bez duplikata i parametara', array_keys($r) === array('https://apartmanibihac.com/a/'));
$p = (object) array('ID' => 5, 'post_status' => 'trash', 'post_name' => 'vijest__trashed');
t('javna adresa smeća', abu_in_javna_adresa($p) === 'https://apartmanibihac.com/2026/11/16/vijest/');
t('original ostaje u smeću', $p->post_status === 'trash');
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
