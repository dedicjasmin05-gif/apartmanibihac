<?php
/** Izolirani test za drustvene-objave.php sa simuliranim Graph API-jem: php testovi/test-drustvene-objave.php */
define('ABSPATH', __DIR__); define('DAY_IN_SECONDS', 86400);
$GLOBALS['opt'] = array(); $GLOBALS['meta'] = array(); $GLOBALS['poz'] = array(); $GLOBALS['odg'] = array();
function add_action() {} function add_filter() {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function wp_parse_args($a, $d) { return array_merge($d, (array) $a); }
function get_post_meta($id, $k, $s) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k]); }
function get_permalink($id) { return 'https://apartmanibihac.com/clanak-' . $id . '/'; }
function has_excerpt($id) { return true; } function get_the_excerpt($id) { return 'Kratak opis.'; } function get_the_title($id) { return 'Naslov ' . $id; }
function wp_strip_all_tags($s) { return strip_tags($s); }
function get_post_thumbnail_id($id) { return $id === 2 ? 0 : 50; } function wp_get_attachment_image_url($a, $v) { return 'https://apartmanibihac.com/wp-content/uploads/a.jpg'; }
function is_wp_error($x) { return false; }
function wp_remote_post($u, $a) { $GLOBALS['poz'][] = array($u, $a['body']); return array_shift($GLOBALS['odg']); }
function wp_remote_retrieve_body($r) { return $r; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; }
function wp_date($f) { return date($f); }
function wp_schedule_single_event($t, $h, $a) { $GLOBALS['zak'][] = array($t, $a[0]); }
function wp_remote_get($u, $a = array()) { return '{"status_code":"FINISHED"}'; }
function add_query_arg($a, $u) { return $u . (strpos($u, '?') === false ? '?' : '&') . http_build_query($a); }
require dirname(__DIR__) . '/drustvene-objave.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
t('bez tokena ne objavljuje', abu_dr_objavi_jedan(1, 'fb') === 'Nema tokena.' && !$GLOBALS['poz']);
$GLOBALS['opt']['abu_meta'] = array('page_id' => '123', 'token' => 'TAJNA', 'ig_id' => '456', 'auto' => 1);
$GLOBALS['odg'] = array('{"id":"123_999"}');
t('facebook objava', abu_dr_objavi_jedan(1, 'fb') === 'Objavljeno na Facebooku.' && $GLOBALS['meta'][1]['_abu_fb_id'] === '123_999');
t('facebook poziv ide na /123/feed s linkom', strpos($GLOBALS['poz'][0][0], '/123/feed') !== false && $GLOBALS['poz'][0][1]['link'] === 'https://apartmanibihac.com/clanak-1/?utm_source=facebook&utm_medium=social&utm_campaign=fb-1');
t('ne objavljuje dvaput', abu_dr_objavi_jedan(1, 'fb') === 'Već objavljeno na Facebooku.' && count($GLOBALS['poz']) === 1);
$GLOBALS['odg'] = array('{"id":"C1"}', '{"id":"IG9"}');
t('instagram: kontejner pa objava', abu_dr_objavi_jedan(1, 'ig') === 'Objavljeno na Instagramu.' && strpos($GLOBALS['poz'][2][0], '/456/media_publish') !== false && $GLOBALS['poz'][2][1]['creation_id'] === 'C1');
t('instagram slika je jpg', substr($GLOBALS['poz'][1][1]['image_url'], -4) === '.jpg');
t('instagram bez slike', strpos(abu_dr_objavi_jedan(2, 'ig'), 'nema istaknutu sliku') !== false);
$GLOBALS['odg'] = array('{"error":{"message":"Invalid token"}}');
t('greška se sprema', abu_dr_objavi_jedan(3, 'fb') === 'Facebook: Invalid token' && $GLOBALS['meta'][3]['_abu_fb_greska'] === 'Invalid token');
$GLOBALS['meta'][4]['_abu_fb_tekst'] = 'Moj tekst #Bihać';
t('vlastiti tekst iz vijesti', abu_dr_tekst(4, 'fb') === 'Moj tekst #Bihać');
t('složeni tekst ima hashtagove', strpos(abu_dr_tekst(5, 'ig'), '#Bihać') !== false && strpos(abu_dr_tekst(5, 'ig'), 'apartmanibihac.com') !== false);
// Storyji
$GLOBALS['poz'] = array(); $GLOBALS['odg'] = array('{"id":"SC1"}', '{"id":"IGS1"}');
t('ig story: kontejner STORIES pa objava', abu_dr_story_jedan(1, 'ig') === 'Instagram story objavljen.' && strpos($GLOBALS['poz'][0][0], '/456/media') !== false && $GLOBALS['poz'][0][1]['media_type'] === 'STORIES' && !isset($GLOBALS['poz'][0][1]['caption']) && $GLOBALS['poz'][1][1]['creation_id'] === 'SC1' && $GLOBALS['meta'][1]['_abu_igs_id'] === 'IGS1');
t('ig story ne dira običnu IG objavu', $GLOBALS['meta'][1]['_abu_ig_id'] === 'IG9');
t('ig story ne objavljuje dvaput', abu_dr_story_jedan(1, 'ig') === 'Instagram story: već objavljen.' && count($GLOBALS['poz']) === 2);
$GLOBALS['poz'] = array(); $GLOBALS['odg'] = array('{"id":"PH7"}', '{"success":true,"post_id":"123_777"}');
t('fb story: neobjavljena fotografija pa photo_stories', abu_dr_story_jedan(1, 'fb') === 'Facebook story objavljen.' && strpos($GLOBALS['poz'][0][0], '/123/photos') !== false && $GLOBALS['poz'][0][1]['published'] === 'false' && strpos($GLOBALS['poz'][1][0], '/123/photo_stories') !== false && $GLOBALS['poz'][1][1]['photo_id'] === 'PH7' && $GLOBALS['meta'][1]['_abu_fbs_id'] === '123_777');
$GLOBALS['poz'] = array(); $GLOBALS['odg'] = array('{"id":"PH8"}', '{"error":{"message":"No permission"}}');
t('fb story greška se sprema', abu_dr_story_jedan(6, 'fb') === 'Facebook story: No permission' && $GLOBALS['meta'][6]['_abu_fbs_greska'] === 'No permission' && empty($GLOBALS['meta'][6]['_abu_fbs_id']));
t('story bez slike', strpos(abu_dr_story_jedan(2, 'ig'), 'nema istaknutu sliku') !== false);
t('storyji uključeni po zadanom', abu_meta_opt('story') == 1);

// Zaštita od rafala: 7 novih članaka odjednom -> najviše 5 dnevno, razmak 90 min; članak s datumom unazad se preskače.
$GLOBALS['zak'] = array(); $GLOBALS['opt']['abu_meta_zadnji'] = 0; $GLOBALS['opt']['abu_meta_dan'] = array();
$sad = gmdate('Y-m-d H:i:s');
for ($i = 101; $i <= 107; $i++) { abu_dr_auto_zakazi('publish', 'draft', (object) array('ID' => $i, 'post_type' => 'post', 'post_date_gmt' => $sad)); }
t('rafal: najviše 5 dnevno', count($GLOBALS['zak']) === 5 && ($GLOBALS['meta'][106]['_abu_auto_preskoceno'] ?? '') === 'dnevni limit');
t('rafal: razmak 90 min', $GLOBALS['zak'][1][0] - $GLOBALS['zak'][0][0] === 5400 && $GLOBALS['zak'][4][0] - $GLOBALS['zak'][3][0] === 5400);
$GLOBALS['opt']['abu_meta_dan'] = array();
abu_dr_auto_zakazi('publish', 'draft', (object) array('ID' => 120, 'post_type' => 'post', 'post_date_gmt' => gmdate('Y-m-d H:i:s', time() - 2 * 86400)));
t('datum unazad ne ide na mreže', count($GLOBALS['zak']) === 5 && ($GLOBALS['meta'][120]['_abu_auto_preskoceno'] ?? '') === 'stari datum');
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
