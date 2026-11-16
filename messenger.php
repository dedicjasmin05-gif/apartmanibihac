<?php
/**
 * Messenger (FB stranica Apartmani & Vikendice) – POVEZIVANJE, bez automatskih odgovora.
 * - Webhook: https://apartmanibihac.com/wp-json/abu/v1/messenger (GET provjera Mete, POST poruke).
 * - Postavke su u wp-content/abu-messenger/postavke.json (izvan gita, mapa zatvorena za web):
 *   {"verify_token":"...","app_secret":"...","pretplati":true}
 *   Bez app_secret webhook ne prima ništa (provjera potpisa X-Hub-Signature-256 je obavezna).
 * - Primljene poruke se bilježe u wp-content/abu-messenger/poruke.jsonl (zadnjih ~2000).
 * - Svakih 15 min (abu_cron) piše stanje.json: aplikacija tokena, dozvole, pretplata stranice;
 *   uz "pretplati": true sama pretplati stranicu na poruke. Token stranice je onaj iz modula Društvene mreže.
 * - Gostima se NIŠTA ne odgovara dok se ne doda logika odgovora (Jasmin je daje naknadno).
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

function abu_ms_mapa() {
    $d = WP_CONTENT_DIR . '/abu-messenger';
    if (!is_dir($d)) { wp_mkdir_p($d); }
    if (!file_exists($d . '/.htaccess')) { @file_put_contents($d . '/.htaccess', "Require all denied\nDeny from all\n"); }
    if (!file_exists($d . '/index.php')) { @file_put_contents($d . '/index.php', "<?php // Silence is golden.\n"); }
    return $d;
}

function abu_ms_cfg($k = null) {
    $f = abu_ms_mapa() . '/postavke.json';
    $c = file_exists($f) ? json_decode((string) file_get_contents($f), true) : array();
    $c = array_merge(array('verify_token' => '', 'app_secret' => '', 'pretplati' => false), is_array($c) ? $c : array());
    return $k === null ? $c : $c[$k];
}

/** Potpis Mete: X-Hub-Signature-256 = "sha256=" . HMAC-SHA256(tijelo, app_secret). */
function abu_ms_potpis_ok($tijelo, $zaglavlje, $tajna) {
    if ($tajna === '' || strpos((string) $zaglavlje, 'sha256=') !== 0) { return false; }
    return hash_equals('sha256=' . hash_hmac('sha256', $tijelo, $tajna), (string) $zaglavlje);
}

/** Iz tijela webhooka izvuci događaje (poruke gostiju i odgovore stranice). */
function abu_ms_dogadaji($tijelo) {
    $j = json_decode($tijelo, true);
    $out = array();
    if (!is_array($j) || ($j['object'] ?? '') !== 'page') { return $out; }
    foreach ((array) ($j['entry'] ?? array()) as $e) {
        foreach ((array) ($e['messaging'] ?? array()) as $m) {
            $echo = !empty($m['message']['is_echo']);
            if (empty($m['message']) && empty($m['postback'])) { continue; }
            $out[] = array(
                'vrijeme' => isset($m['timestamp']) ? gmdate('c', (int) ($m['timestamp'] / 1000)) : gmdate('c'),
                'vrsta' => $echo ? 'stranica' : (empty($m['postback']) ? 'gost' : 'gumb'),
                'gost' => (string) ($echo ? ($m['recipient']['id'] ?? '') : ($m['sender']['id'] ?? '')),
                'tekst' => (string) ($m['message']['text'] ?? ($m['postback']['title'] ?? '')),
                'privitci' => count((array) ($m['message']['attachments'] ?? array())),
                'mid' => (string) ($m['message']['mid'] ?? ''),
            );
        }
    }
    return $out;
}

function abu_ms_zapisi($dogadaji) {
    if (!$dogadaji) { return; }
    $f = abu_ms_mapa() . '/poruke.jsonl';
    $red = '';
    foreach ($dogadaji as $d) { $red .= wp_json_encode($d, JSON_UNESCAPED_UNICODE) . "\n"; }
    @file_put_contents($f, $red, FILE_APPEND | LOCK_EX);
    if (@filesize($f) > 1500000) { // zadrži zadnjih 2000 redaka
        $l = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        @file_put_contents($f, implode("\n", array_slice($l, -2000)) . "\n", LOCK_EX);
    }
}

add_action('rest_api_init', function () {
    register_rest_route('abu/v1', '/messenger', array(
        array('methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => function ($r) {
            $vt = (string) abu_ms_cfg('verify_token');
            if ($r->get_param('hub_mode') === 'subscribe' && $vt !== '' && hash_equals($vt, (string) $r->get_param('hub_verify_token'))) {
                header('Content-Type: text/plain; charset=utf-8');
                echo preg_replace('/[^0-9A-Za-z_\-]/', '', (string) $r->get_param('hub_challenge'));
                exit;
            }
            return new WP_Error('zabranjeno', 'Zabranjeno', array('status' => 403));
        }),
        array('methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function ($r) {
            $tijelo = (string) $r->get_body();
            if (!abu_ms_potpis_ok($tijelo, $r->get_header('x_hub_signature_256'), (string) abu_ms_cfg('app_secret'))) {
                return new WP_Error('potpis', 'Neispravan potpis', array('status' => 403));
            }
            $d = abu_ms_dogadaji($tijelo);
            abu_ms_zapisi($d);
            do_action('abu_ms_poruke', $d); // ovdje će se kasnije spojiti logika odgovora
            return new WP_REST_Response('EVENT_RECEIVED', 200);
        }),
    ));
});

function abu_ms_get($put, $polja) {
    $r = wp_remote_get(ABU_GRAPH . $put . '?' . http_build_query($polja), array('timeout' => 20));
    if (is_wp_error($r)) { return array(null, $r->get_error_message()); }
    $j = json_decode((string) wp_remote_retrieve_body($r), true);
    if (!empty($j['error'])) { return array(null, (string) ($j['error']['message'] ?? 'greška')); }
    return array($j, '');
}

/** Stanje veze (bez tokena) u stanje.json; uz pretplati=true pretplati stranicu na poruke. */
function abu_ms_provjeri_vezu() {
    $o = function_exists('abu_meta_opt') ? abu_meta_opt() : array('token' => '', 'page_id' => '');
    $s = array('azurirano' => current_time('c'), 'webhook' => rest_url('abu/v1/messenger'),
        'verify_token' => abu_ms_cfg('verify_token') !== '' ? 'postavljen' : 'NEMA', 'app_secret' => abu_ms_cfg('app_secret') !== '' ? 'postavljen' : 'NEMA',
        'token_stranice' => $o['token'] ? 'da' : 'NEMA', 'stranica' => $o['page_id']);
    if ($o['token'] && $o['page_id']) {
        list($a, $e) = abu_ms_get('app', array('access_token' => $o['token']));
        $s['aplikacija'] = $a ? array('id' => $a['id'] ?? '', 'ime' => $a['name'] ?? '') : 'greška: ' . $e;
        list($d, $e) = abu_ms_get('debug_token', array('input_token' => $o['token'], 'access_token' => $o['token']));
        $s['dozvole'] = $d ? array_values((array) ($d['data']['scopes'] ?? array())) : 'greška: ' . $e;
        $s['pages_messaging'] = is_array($s['dozvole']) && in_array('pages_messaging', $s['dozvole'], true);
        list($p, $e) = abu_ms_get(rawurlencode($o['page_id']) . '/subscribed_apps', array('access_token' => $o['token']));
        $s['pretplacene_aplikacije'] = $p ? array_map(function ($x) { return array('id' => $x['id'] ?? '', 'ime' => $x['name'] ?? '', 'polja' => $x['subscribed_fields'] ?? array()); }, (array) ($p['data'] ?? array())) : 'greška: ' . $e;
        $nasa = is_array($s['aplikacija']) ? $s['aplikacija']['id'] : '';
        $pretplacena = false;
        foreach ((array) (is_array($s['pretplacene_aplikacije']) ? $s['pretplacene_aplikacije'] : array()) as $x) {
            if ($x['id'] === $nasa && in_array('messages', (array) $x['polja'], true)) { $pretplacena = true; }
        }
        if (!$pretplacena && abu_ms_cfg('pretplati') && $s['pages_messaging']) {
            list($j, $e) = abu_dr_graph(rawurlencode($o['page_id']) . '/subscribed_apps', array('subscribed_fields' => 'messages,messaging_postbacks,message_echoes', 'access_token' => $o['token']));
            $s['pretplata_pokusaj'] = $e ? 'greška: ' . $e : 'uspjelo';
            $pretplacena = !$e && !empty($j['success']);
        }
        $s['stranica_pretplacena_na_poruke'] = $pretplacena;
    }
    $f = abu_ms_mapa() . '/poruke.jsonl';
    $s['primljeno_dogadaja'] = file_exists($f) ? count(file($f, FILE_SKIP_EMPTY_LINES)) : 0;
    @file_put_contents(abu_ms_mapa() . '/stanje.json', wp_json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return $s;
}
add_action('abu_cron', 'abu_ms_provjeri_vezu', 40);
