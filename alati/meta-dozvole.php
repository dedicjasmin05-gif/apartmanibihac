<?php
/** Koje dozvole ima spremljeni Meta token i ima li pristup reklamnom računu (samo CLI, samo čitanje; token se ne ispisuje). */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com'; $_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
$t = abu_meta_opt('token');
if (!$t) { echo "nema tokena\n"; exit; }
$g = function ($put) use ($t) { $r = wp_remote_get(ABU_GRAPH . $put . (strpos($put, '?') ? '&' : '?') . 'access_token=' . rawurlencode($t), array('timeout' => 30)); return is_wp_error($r) ? array('error' => $r->get_error_message()) : json_decode((string) wp_remote_retrieve_body($r), true); };
$d = wp_remote_get('https://graph.facebook.com/debug_token?input_token=' . rawurlencode($t) . '&access_token=' . rawurlencode($t), array('timeout' => 30));
$d = is_wp_error($d) ? array() : json_decode((string) wp_remote_retrieve_body($d), true);
$x = $d['data'] ?? array();
echo 'vrsta: ' . ($x['type'] ?? '?') . ' | istječe: ' . (!empty($x['expires_at']) ? gmdate('Y-m-d', $x['expires_at']) : 'ne') . "\n";
echo 'dozvole: ' . implode(', ', (array) ($x['scopes'] ?? array())) . "\n";
$a = $g('me/adaccounts?fields=name,account_status,currency');
echo 'reklamni računi: ' . (isset($a['data']) ? count($a['data']) . ' ' . implode('; ', array_map(function ($r) { return ($r['name'] ?? '') . ' (' . ($r['currency'] ?? '') . ', status ' . ($r['account_status'] ?? '') . ')'; }, $a['data'])) : ('greška: ' . ($a['error']['message'] ?? ''))) . "\n";
