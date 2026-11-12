<?php
/** Test za webp.php na pravim slikama: php testovi/test-webp.php <direktorij s uploads/2026/11/*.jpg> */
define('ABSPATH', __DIR__);
function add_filter() {} function add_action() {}
$D = $argv[1];
function wp_get_upload_dir() { global $D; return array('baseurl' => 'https://apartmanibihac.com/wp-content/uploads', 'basedir' => $D . '/uploads'); }
function wp_parse_url($u, $c) { return parse_url($u, $c); }
require dirname(__DIR__) . '/webp.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
t('GD WebP dostupan', abu_webp_moguce());
foreach (glob($D . '/uploads/2026/11/*.webp') as $w) { unlink($w); }
$uk = 0; foreach (glob($D . '/uploads/2026/11/*.jpg') as $f) { $r = abu_webp_napravi($f); $uk += $r; printf("  %s: %d KB -> %s\n", basename($f), filesize($f) / 1024, is_file($f . '.webp') ? round(filesize($f . '.webp') / 1024) . ' KB webp' : 'bez webp (nije manje)'); }
t('ušteda > 0', $uk > 0);
t('drugi put se ne radi ponovno', abu_webp_napravi($D . '/uploads/2026/11/IMG_1581.jpg') === 0);
$html = '<head><meta property="og:image" content="https://apartmanibihac.com/wp-content/uploads/2026/11/IMG_1581.jpg"></head><body><img src="https://apartmanibihac.com/wp-content/uploads/2026/11/IMG_1581.jpg" srcset="https://apartmanibihac.com/wp-content/uploads/2026/11/IMG_1581.jpg 1200w, /wp-content/uploads/2026/11/IMG_0145-720x540.jpg 720w" style="background:url(/wp-content/uploads/2026/11/nema.jpg)"></body>';
$o = abu_webp_zamijeni($html);
t('og:image u head ostaje jpg', strpos($o, 'content="https://apartmanibihac.com/wp-content/uploads/2026/11/IMG_1581.jpg"') !== false);
t('src u tijelu webp', strpos($o, 'src="https://apartmanibihac.com/wp-content/uploads/2026/11/IMG_1581.jpg.webp"') !== false);
t('srcset oba webp', substr_count($o, '.jpg.webp') === 3);
t('slika bez webp ostaje', strpos($o, 'nema.jpg)') !== false);
t('bez dvostrukog .webp', strpos($o, '.webp.webp') === false && abu_webp_zamijeni($o) === $o);
t('html bez slika netaknut', abu_webp_zamijeni('<p>x</p>') === '<p>x</p>');
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
