<?php
/** Izolirani test za povezivanje.php na stvarnim podacima objekata (sadrzaj-objekti.php): php testovi/test-povezivanje.php [-v] */
define('ABSPATH', __DIR__);
function add_filter() {} function add_action() {}
function untrailingslashit($s) { return rtrim($s, '/'); }
require dirname(__DIR__) . '/sadrzaj-objekti.php';
require dirname(__DIR__) . '/povezivanje.php';
$opr = array('Strogi centar' => 'strogi-centar', 'Izlaz na Unu' => 'izlaz-na-unu', 'Bazen' => 'bazen');
$obj = array(); $i = 100;
foreach (abu_seed_objekti() as $slug => $o) {
    $obj[] = array('id' => $i++, 'slug' => $slug, 'mjesto' => $o[1], 'kap' => $o[3], 'oprema' => array_values(array_intersect_key($opr, array_flip($o[7]))), 'url' => "https://apartmanibihac.com/smjestaj/$slug/");
}
$ok = 0; $bad = 0; $v = in_array('-v', $argv, true);
function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
$po = array();
$clanci = array('strbacki-buk-izlet-iz-bihaca', 'rafting-na-uni-za-pocetnike', 'smjestaj-za-grupe-bihac', 'vikendica-s-bazenom-bihac-provjera', 'bihac-s-djecom-izbor-smjestaja',
  'kupanje-na-uni', '10-restorana-uz-unu-koje-preporucujemo', 'plitvicka-jezera-iz-bihaca', 'smjestaj-sa-psom-bihac-sto-pitati', 'cijene-smjestaja-u-bihacu', 'team-building-program-za-2-dana-na-uni');
foreach ($clanci as $j => $s) {
    $pr = abu_pov_clanak($s);
    $iz = $pr ? abu_pov_odaberi($pr, (string) $j, $obj) : array();
    if ($v) { echo str_pad($s, 42), str_pad($pr ?: '-', 9), implode(', ', array_column($iz, 'slug')), "\n"; }
    foreach ($iz as $x) { $po[$x['slug']] = 1; }
}
t('pas bez bloka', abu_pov_clanak('smjestaj-sa-psom-bihac-sto-pitati') === '');
t('team building = grupe', abu_pov_clanak('team-building-program-za-2-dana-na-uni') === 'grupe');
t('restorani = grad', abu_pov_clanak('10-restorana-uz-unu-koje-preporucujemo') === 'grad');
t('usporedba = mix', abu_pov_clanak('apartman-centar-ili-vikendica-bihac') === 'mix');
t('smještaj uz Unu = rijeka', abu_pov_clanak('smjestaj-uz-unu-kako-odabrati') === 'rijeka');
t('štrbački = park', abu_pov_clanak('strbacki-buk-izlet-iz-bihaca') === 'park');
$park = abu_pov_odaberi('park', 'x', $obj); t('park: Lohovo prvi', $park[0]['mjesto'] === 'Lohovo' && count($park) === 3);
foreach (abu_pov_odaberi('rafting', 'y', $obj) as $j => $x) { if ($j < 2) { t('rafting: Grmuša prva dva', $x['mjesto'] === 'Grmuša'); } }
foreach (abu_pov_odaberi('grupe', 'z', $obj) as $x) { t('grupe: kapacitet >= 9', $x['kap'] >= 9); }
foreach (abu_pov_odaberi('grad', 'w', $obj) as $x) { t('grad: u Bihaću', $x['mjesto'] === 'Bihać'); }
foreach (abu_pov_odaberi('rijeka', 'r', $obj) as $x) { t('rijeka: izlaz na Unu', in_array('izlaz-na-unu', $x['oprema'], true)); }
$b = abu_pov_odaberi('bazen', 'b', $obj); t('bazen: Vila Enio prva', $b[0]['slug'] === 'vila-enio' && count($b) === 3);
$m = abu_pov_odaberi('mix', 'm', $obj); t('mix: 1 grad + 2 uz Unu', $m[0]['mjesto'] === 'Bihać' && in_array('izlaz-na-unu', $m[1]['oprema'], true) && count($m) === 3);
$sad = '<a href="https://apartmanibihac.com/smjestaj/vila-enio/">Vila Enio</a>';
t('već linkan objekt se preskače', !in_array('vila-enio', array_column(abu_pov_odaberi('park', 'x', $obj, $sad), 'slug'), true));
foreach (abu_pov_teme() as $k => $pr) { t("tema $k ima 3 objekta", count(abu_pov_odaberi($pr, $k, $obj)) === 3); foreach (abu_pov_odaberi($pr, $k, $obj) as $x) { $po[$x['slug']] = 1; } }
t('tema ljubimci bez bloka', !isset(abu_pov_teme()['21-kucni-ljubimci']));
t('različite stranice, različiti objekti', abu_pov_odaberi('rijeka', '1', $obj) != abu_pov_odaberi('rijeka', '2', $obj));
if ($v) { echo 'Linkani objekti (teme bs + uzorak članaka): ', count($po), '/', count($obj), "\n"; }
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
