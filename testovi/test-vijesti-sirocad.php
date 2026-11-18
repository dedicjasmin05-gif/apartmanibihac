<?php
/** Test za siročad prijevoda u vijesti-jezici.php: php testovi/test-vijesti-sirocad.php */
define('ABSPATH', __DIR__);
function add_action() {} function add_filter() {} function add_shortcode() {} function apply_filters($h, $v) { return $v; }
$META = array(1624 => array('_abu_vj_bs' => 1622, '_abu_vj_lang' => 'en'), 1633 => array('_abu_vj_bs' => 1631, '_abu_vj_lang' => 'de'),
  1638 => array('_abu_vj_bs' => 1637, '_abu_vj_lang' => 'en'), 1637 => array('_abu_vj_en' => 1638, '_abu_vj_de' => 1639));
$STATUS = array(1622 => 'draft', 1631 => 'draft', 1637 => 'publish', 1638 => 'publish', 1639 => 'publish');
function get_post_meta($id, $k, $s = true) { global $META; return isset($META[$id][$k]) ? $META[$id][$k] : ''; }
function get_post_status($id) { global $STATUS; return isset($STATUS[$id]) ? $STATUS[$id] : false; }
function get_permalink($id) { return 'https://x/' . $id . '/'; }
require dirname(__DIR__) . '/vijesti-jezici.php';
$ok = 0; $bad = 0;
function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
t('1624 je siroče (original 1622 skinut)', abu_vj_siroce(1624) === 1622);
t('1638 nije siroče', abu_vj_siroce(1638) === 0);
t('1633 (de, duplikat voza) -> de prijevod 1639', abu_vj_odrediste(1631, 'de') === 'https://x/1639/');
t('bs 1631 -> 1637', abu_vj_odrediste(1631, 'bs') === 'https://x/1637/');
t('1622 nema zamjenu -> 410', abu_vj_odrediste(1622, 'en') === '');
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
