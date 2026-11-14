<?php
// Test: crtanje grafika (bez WordPressa). php testovi/test-grafike.php [izlazna mapa] [fotografija]
define('ABSPATH', __DIR__);
function add_action() {} function add_filter() {}
$META = array(); $SLUG = array();
function get_post_meta($id, $k) { global $META; return $META[$id][$k] ?? ''; }
function get_post_field($f, $id) { global $SLUG; return $SLUG[$id] ?? ''; }
function get_the_category($id) { return array(); }
function apply_filters($n, $v) { return $v; }
require __DIR__ . '/../grafike.php';
$ok = 0; $ne = 0;
function t($u, $p) { global $ok, $ne; if ($u) { $ok++; } else { $ne++; echo "NEUSPJEH: $p\n"; } }
$out = $argv[1] ?? sys_get_temp_dir();
$foto = $argv[2] ?? '';
if (!$foto) { $foto = $out . '/proba.jpg'; $i = imagecreatetruecolor(1200, 900); imagefill($i, 0, 0, imagecolorallocate($i, 60, 120, 90)); imagejpeg($i, $foto); }
t(abu_gr_moze(), 'GD s FreeType');
foreach (array('ig' => array(1080, 1350), 'fb' => array(1200, 630), 'story' => array(1080, 1920)) as $v => $dim) {
    $f = "$out/t-$v.jpg";
    t(abu_gr_nacrtaj($v, $foto, 'Nakon olujnog vjetra: hladan vikend i snijeg na planinama', 'Vijesti iz turizma', '13. 11. 2026.', $f), "crtanje $v");
    $s = @getimagesize($f); t($s && $s[0] === $dim[0] && $s[1] === $dim[1], "dimenzije $v");
    $f2 = "$out/t-$v-dugi.jpg";
    t(abu_gr_nacrtaj($v, $foto, 'Vrlo dugačak naslov vijesti koji ima mnogo riječi i mora se prelomiti u više redova da stane na sliku čšćđž', 'Događaji', '1. 1. 2027.', $f2), "dugi naslov $v");
}
t(abu_gr_nacrtaj('ig', $foto, 'Cijeli apartman od 25 € u Bihaću', 'Smještaj', '1. 1. 2027.', "$out/t-ig-tel.jpg", true), 'crtanje s brojem');
$SLUG = array(1 => 'smjestaj-u-bihacu-cijeli-apartman', 2 => 'ees-granica-bih-hrvatska', 3 => 'apartmani-za-novu-godinu'); $META = array(3 => array('_abu_dnevna_vijest' => 1));
t(abu_je_smjestaj(1) === true, 'smještaj → broj'); t(abu_je_smjestaj(2) === false, 'vijest → bez broja'); t(abu_je_smjestaj(3) === false, 'dnevna vijest → bez broja');
$r = abu_gr_uravnotezi(76 * .75, __DIR__ . '/../grafike/bricolage-800.ttf', 'Wanderlust: BiH četvrta među destinacijama u usponu', 960);
t(count($r) === 3, 'uravnoteženi redovi (3)');
echo "OK $ok, neuspjeh $ne\n";
