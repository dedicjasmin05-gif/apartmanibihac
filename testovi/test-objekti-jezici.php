<?php
/** Test za objekti-jezici.php na pravom prijevodu: php testovi/test-objekti-jezici.php */
define('ABSPATH', __DIR__);
function add_action() {} function add_filter() {} function add_shortcode() {}
function esc_html($s) { return htmlspecialchars($s); } function esc_url($s) { return $s; } function esc_attr($s) { return htmlspecialchars($s, ENT_QUOTES); }
function get_post_meta($id, $k, $s) { return array('_abu_mjesto' => 'Lohovo', '_abu_kapacitet' => 6, '_abu_min_noci' => 2)[$k] ?? ''; }
function get_permalink($id) { return 'https://apartmanibihac.com/smjestaj/vila-enio/'; } function home_url($p) { return 'https://apartmanibihac.com' . $p; }
require dirname(__DIR__) . '/objekti-jezici.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
$d = abu_oj_podaci();
t('19 objekata', count($d['objekti']) === 19);
foreach (array('en', 'de') as $l) {
    $h = abu_oj_html('vila-enio', 13, $l);
    t("$l kalendar i upit", strpos($h, '[abu_kalendar id="13"') !== false && strpos($h, '[abu_upit id="13"]') !== false);
    t("$l galerija", strpos($h, '[abu_galerija id="13"]') !== false);
    t("$l lokacija Lohovo", strpos($h, htmlspecialchars($d['lokacije']['Lohovo'][$l])) !== false);
    t("$l min noći", strpos($h, $l === 'de' ? 'mindestens 2 Nächte' : 'minimum 2 nights') !== false);
    t("$l bez bosanskog", strpos($h, 'Kako rezervirati') === false);
}
foreach ($d['objekti'] as $s => $x) { foreach (array('en', 'de') as $l) { t("$s $l title<=60", mb_strlen($x[$l]['title']) <= 60); } }
echo "OK $ok, neuspjeh $bad\n"; echo substr(abu_oj_html('vila-enio', 13, 'de'), 0, 600), "\n"; exit($bad ? 1 : 0);
