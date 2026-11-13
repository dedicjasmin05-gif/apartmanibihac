<?php
/** Test za stare adrese u preusmjeravanja.php: php testovi/test-preusmjeravanja.php */
define('ABSPATH', __DIR__);
function add_action() {} function add_filter() {} function apply_filters($h, $v) { return $v; }
require dirname(__DIR__) . '/preusmjeravanja.php';
$m = abu_stare_adrese(); $ok = 0; $bad = 0;
function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
$ocek = array('apartman-centar-bihac' => '/smjestaj/apartman-centar/', 'apartman-1-bihac' => '/apartmani-bihac/', 'apartman-2-bihac' => '/apartmani-bihac/',
  'galerija-slika' => '/smjestaj-bihac/', 'lokacija' => '/o-nama/', 'o-hotelu' => '/o-nama/', 'contacts' => '/o-nama/', 'our-services/npunabihac' => '/cesta-pitanja/nacionalni-park-una/',
  'our-services/riverbihac' => '/restorani-bihac/', 'our-services/sokolacbihac' => '/izleti-bihac/', 'our-services/domacahranabihac' => '/restorani-bihac/', 'our-services/pizzeriaasabihac' => '/restorani-bihac/',
  'ponuda' => '/smjestaj/', 'blog' => '/vodic/');
foreach ($ocek as $k => $v) { t("$k -> $v", ($m[$k] ?? '') === $v); }
foreach ($m as $k => $v) { t("cilj $v je unutarnji", $v[0] === '/'); }
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
