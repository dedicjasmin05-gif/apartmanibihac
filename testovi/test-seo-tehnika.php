<?php
/** Izolirani test za seo-tehnika.php bez WordPressa: php testovi/test-seo-tehnika.php */
define('ABSPATH', __DIR__);
$GLOBALS['F'] = array(); $GLOBALS['S'] = array('singular' => true, 'title' => 'Smještaj u Lohovu', 'page' => '', 'ab' => '', 'tax' => false);
function add_filter($h, $cb, $p = 10, $n = 1) { $GLOBALS['F'][$h][] = $cb; }
function add_action($h, $cb, $p = 10, $n = 1) { $GLOBALS['F'][$h][] = $cb; }
function is_admin() { return false; } function in_the_loop() { return true; } function is_main_query() { return true; }
function is_singular($t = '') { return $t ? ($t === 'post' && !empty($GLOBALS['S']['post'])) : $GLOBALS['S']['singular']; }
function get_the_title() { return $GLOBALS['S']['title']; }
function wp_strip_all_tags($s) { return strip_tags($s); }
function get_query_var($k) { return $GLOBALS['S']['ab']; }
function get_post_field($f, $id) { return $GLOBALS['S']['slug'] ?? ''; } function get_queried_object_id() { return 1; }
function is_tax($t) { return $GLOBALS['S']['tax']; }
function is_page($s) { return $GLOBALS['S']['page'] === $s; }
function home_url($p = '') { return 'https://apartmanibihac.com' . $p; } function untrailingslashit($s) { return rtrim($s, '/'); }
function esc_url($u) { return $u; } function get_permalink($p) { return 'https://apartmanibihac.com/2026/10/21/' . $p . '/'; }
function get_posts($a) { return $a['name'] === 'postoji' ? array('postoji') : array(); }
require dirname(__DIR__) . '/seo-tehnika.php';
$ok = 0; $bad = 0;
function t($naziv, $uvjet) { global $ok, $bad; if ($uvjet) { $ok++; } else { $bad++; echo "NEUSPJEH: $naziv\n"; } }
foreach (array(1 => '1 osoba', 2 => '2 osobe', 3 => '3 osobe', 4 => '4 osobe', 5 => '5 osoba', 12 => '12 osoba', 14 => '14 osoba', 20 => '20 osoba', 21 => '21 osoba', 22 => '22 osobe') as $n => $e) { t("osobe $n", abu_osobe($n) === $e); }
$h1 = $GLOBALS['F']['the_content'][0];
t('dupli h1 uklonjen', $h1("<h1>Smještaj u Lohovu</h1>\n<p>Tekst</p>") === "<p>Tekst</p>");
t('drugačiji h1 ostaje', $h1('<h1>Drugi naslov</h1><p>a</p>') === '<h1>Drugi naslov</h1><p>a</p>');
t('h1 u sredini ostaje', $h1('<p>a</p><h1>Smještaj u Lohovu</h1>') === '<p>a</p><h1>Smještaj u Lohovu</h1>');
t('bez h1 isto', $h1('<p>a</p>') === '<p>a</p>');
$GLOBALS['S']['singular'] = false; t('arhiva netaknuta', $h1('<h1>Smještaj u Lohovu</h1>') === '<h1>Smještaj u Lohovu</h1>');
$rob = $GLOBALS['F']['wp_robots'][0];
t('obična stranica indeks', empty($rob(array('max-image-preview' => 'large'))['noindex']));
$GLOBALS['S']['ab'] = '1'; $r = $rob(array('max-image-preview' => 'large')); t('pretraga noindex,follow', !empty($r['noindex']) && !empty($r['follow']));
$GLOBALS['S']['ab'] = ''; $GLOBALS['S']['tax'] = true; t('oprema noindex', !empty($rob(array())['noindex']));
$GLOBALS['S']['tax'] = false; $GLOBALS['S']['page'] = 'header-slike'; t('header-slike noindex', !empty($rob(array())['noindex']));
$GLOBALS['S']['page'] = ''; $GLOBALS['S']['post'] = true; $GLOBALS['S']['slug'] = 'naslov-pronadite-odlican-i-jeftin-smjestaj-u-bihacu-vodic-za-putnike'; t('članak iz 2024. noindex', !empty($rob(array())['noindex']));
$GLOBALS['S']['slug'] = 'rafting-na-uni-za-pocetnike'; t('ostali članci indeks', empty($rob(array())['noindex'])); $GLOBALS['S']['post'] = false;
$tx = $GLOBALS['F']['wp_sitemaps_taxonomies'][0]; t('oprema izvan mape', !isset($tx(array('category' => 1, 'oprema' => 1))['oprema']));
$vl = $GLOBALS['F']['the_content'][1];
t('vodic link zamijenjen', $vl('<a href="https://apartmanibihac.com/vodic/postoji/">x</a>') === '<a href="https://apartmanibihac.com/2026/10/21/postoji/">x</a>');
t('relativni vodic link', $vl("<a href='/vodic/postoji'>x</a>") === "<a href='https://apartmanibihac.com/2026/10/21/postoji/'>x</a>");
t('nepoznat ostaje', $vl('<a href="/vodic/nema/">x</a>') === '<a href="/vodic/nema/">x</a>');
t('paginacija ostaje', $vl('<a href="/vodic/page/2/">x</a>') === '<a href="/vodic/page/2/">x</a>');
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
