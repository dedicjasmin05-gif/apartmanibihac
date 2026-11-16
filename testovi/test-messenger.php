<?php
/** Izolirani test za messenger.php: php testovi/test-messenger.php */
define('ABSPATH', __DIR__); define('WP_CONTENT_DIR', sys_get_temp_dir() . '/abu-ms-test-' . getmypid());
function add_action() {} function wp_mkdir_p($d) { return mkdir($d, 0777, true); }
function wp_json_encode($x, $f = 0) { return json_encode($x, $f); }
require dirname(__DIR__) . '/messenger.php';
$ok = 0; $bad = 0; function t($n, $u) { global $ok, $bad; if ($u) { $ok++; } else { $bad++; echo "NEUSPJEH: $n\n"; } }
$b = '{"object":"page","entry":[{"messaging":[{"sender":{"id":"G1"},"recipient":{"id":"P"},"timestamp":1763297520000,"message":{"mid":"m1","text":"Imate li stan za 4 osobe?"}},{"sender":{"id":"P"},"recipient":{"id":"G1"},"message":{"mid":"m2","is_echo":true,"text":"Imamo"}},{"sender":{"id":"G1"},"read":{"watermark":1}}]}]}';
t('potpis ispravan', abu_ms_potpis_ok($b, 'sha256=' . hash_hmac('sha256', $b, 'TAJNA'), 'TAJNA'));
t('potpis kriv', !abu_ms_potpis_ok($b, 'sha256=' . hash_hmac('sha256', $b, 'DRUGA'), 'TAJNA'));
t('bez tajne odbija', !abu_ms_potpis_ok($b, 'sha256=' . hash_hmac('sha256', $b, ''), ''));
$d = abu_ms_dogadaji($b);
t('dva događaja (read se preskače)', count($d) === 2);
t('poruka gosta', $d[0]['vrsta'] === 'gost' && $d[0]['gost'] === 'G1' && $d[0]['tekst'] === 'Imate li stan za 4 osobe?');
t('odgovor stranice prepoznat', $d[1]['vrsta'] === 'stranica' && $d[1]['gost'] === 'G1');
t('nije stranica', abu_ms_dogadaji('{"object":"instagram","entry":[]}') === array());
abu_ms_zapisi($d);
t('zapis u poruke.jsonl', count(file(WP_CONTENT_DIR . '/abu-messenger/poruke.jsonl')) === 2 && file_exists(WP_CONTENT_DIR . '/abu-messenger/.htaccess'));
t('postavke bez datoteke', abu_ms_cfg('app_secret') === '' && abu_ms_cfg('pretplati') === false);
file_put_contents(WP_CONTENT_DIR . '/abu-messenger/postavke.json', '{"verify_token":"V","app_secret":"S","pretplati":true}');
t('postavke iz datoteke', abu_ms_cfg('verify_token') === 'V' && abu_ms_cfg('pretplati') === true);
echo "OK $ok, neuspjeh $bad\n"; exit($bad ? 1 : 0);
