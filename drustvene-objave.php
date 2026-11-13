<?php
/**
 * Objave na Facebook stranicu i Instagram (Meta Graph API).
 * - Upiti → Društvene mreže: ID stranice, token stranice, Instagram Business ID, uključivanje automatike,
 *   popis članaka sa stanjem objave, gumbi "Objavi na Facebook/Instagram" i gotovi tekstovi za ručno kopiranje.
 * - Kad je automatika uključena, svaki NOVI članak objavljen nakon uključivanja ide na FB i IG 10 minuta nakon objave.
 * - Token se čuva u bazi (opcija abu_meta), nikad u kodu ni u gitu.
 * Bez tokena modul samo priprema tekstove. Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_GRAPH', 'https://graph.facebook.com/v22.0/');

function abu_meta_opt($k = null) {
    $o = wp_parse_args((array) get_option('abu_meta', array()), array('page_id' => '', 'token' => '', 'ig_id' => '', 'auto' => 0, 'od' => ''));
    return $k === null ? $o : $o[$k];
}

/** Tekst objave: iz članka (meta _abu_fb_tekst / _abu_ig_tekst) ili složen iz naslova i sažetka. */
function abu_dr_tekst($id, $mreza) {
    $t = trim((string) get_post_meta($id, $mreza === 'ig' ? '_abu_ig_tekst' : '_abu_fb_tekst', true));
    if ($t === '') {
        $izvod = has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $id))), 30, '…');
        $t = get_the_title($id) . "\n\n" . wp_strip_all_tags($izvod) . "\n\n" . ($mreza === 'ig' ? '#Bihać #Una #rijekaUna #BiH #BosnaiHercegovina #apartmani #vikendice' : '#Bihać #Una #BiH');
        if ($mreza === 'ig') { $t .= "\n\nViše na apartmanibihac.com"; }
    }
    // Objave o smještaju: broj telefona u tekstu (vijesti i vodiči bez broja).
    if (function_exists('abu_je_smjestaj') && abu_je_smjestaj($id) && strpos(preg_replace('/\s+/', '', $t), '61591320') === false) {
        $t .= "\n\n📞 Poziv, WhatsApp, Viber: +387 61 591 320";
    }
    return $t;
}

/** Slika za Instagram: originalna JPG istaknuta slika (Instagram ne prima WebP). */
function abu_dr_slika($id) {
    if (function_exists('abu_grafika') && ($g = abu_grafika($id, 'ig'))) { return $g; } // grafika s logom (grafike.php)
    $att = get_post_thumbnail_id($id);
    if (!$att) { return ''; }
    $u = wp_get_attachment_image_url($att, 'large');
    return $u ? $u : (string) wp_get_attachment_url($att);
}

function abu_dr_graph($put, $polja) {
    $r = wp_remote_post(ABU_GRAPH . $put, array('timeout' => 30, 'body' => $polja));
    if (is_wp_error($r)) { return array(null, $r->get_error_message()); }
    $j = json_decode((string) wp_remote_retrieve_body($r), true);
    if (!empty($j['error'])) { return array(null, (string) ($j['error']['message'] ?? 'greška')); }
    return array($j, '');
}

/** Objavi jedan članak na odabranu mrežu; sprema ID objave ili grešku u meta polja. */
function abu_dr_objavi_jedan($id, $mreza) {
    $o = abu_meta_opt();
    if (!$o['token']) { return 'Nema tokena.'; }
    $link = get_permalink($id);
    if ($mreza === 'fb') {
        if (!$o['page_id']) { return 'Nema ID-a Facebook stranice.'; }
        if (get_post_meta($id, '_abu_fb_id', true)) { return 'Već objavljeno na Facebooku.'; }
        list($j, $e) = abu_dr_graph(rawurlencode($o['page_id']) . '/feed', array('message' => abu_dr_tekst($id, 'fb'), 'link' => $link, 'access_token' => $o['token']));
        if ($e) { update_post_meta($id, '_abu_fb_greska', $e); return 'Facebook: ' . $e; }
        update_post_meta($id, '_abu_fb_id', $j['id'] ?? '1'); delete_post_meta($id, '_abu_fb_greska');
        return 'Objavljeno na Facebooku.';
    }
    if (!$o['ig_id']) { return 'Nema Instagram Business ID-a.'; }
    if (get_post_meta($id, '_abu_ig_id', true)) { return 'Već objavljeno na Instagramu.'; }
    $img = abu_dr_slika($id);
    if (!$img) { update_post_meta($id, '_abu_ig_greska', 'Članak nema istaknutu sliku.'); return 'Instagram: članak nema istaknutu sliku.'; }
    list($j, $e) = abu_dr_graph(rawurlencode($o['ig_id']) . '/media', array('image_url' => $img, 'caption' => abu_dr_tekst($id, 'ig'), 'access_token' => $o['token']));
    if (!$e) { list($j, $e) = abu_dr_graph(rawurlencode($o['ig_id']) . '/media_publish', array('creation_id' => $j['id'] ?? '', 'access_token' => $o['token'])); }
    if ($e) { update_post_meta($id, '_abu_ig_greska', $e); return 'Instagram: ' . $e; }
    update_post_meta($id, '_abu_ig_id', $j['id'] ?? '1'); delete_post_meta($id, '_abu_ig_greska');
    return 'Objavljeno na Instagramu.';
}

/** Automatika: novi članak (objavljen nakon uključivanja) → FB i IG nakon 10 minuta. */
add_action('transition_post_status', function ($novi, $stari, $post) {
    if ($novi !== 'publish' || $stari === 'publish' || $post->post_type !== 'post') { return; }
    $o = abu_meta_opt();
    if (empty($o['auto']) || !$o['token'] || ($o['od'] && $post->post_date_gmt && strtotime($post->post_date_gmt) < strtotime($o['od']) - DAY_IN_SECONDS)) { return; }
    // najmanje 10 minuta nakon objave i 10 minuta razmaka između dvije automatske objave
    $kad = max(time() + 600, (int) get_option('abu_meta_zadnji', 0) + 600);
    update_option('abu_meta_zadnji', $kad, false);
    wp_schedule_single_event($kad, 'abu_dr_auto', array((int) $post->ID));
}, 20, 3);
add_action('abu_dr_auto', function ($id) {
    abu_dr_objavi_jedan($id, 'fb');
    abu_dr_objavi_jedan($id, 'ig');
});

/* ---------- Admin ---------- */
add_action('admin_menu', function () {
    add_submenu_page('abu-upiti', 'Društvene mreže', 'Društvene mreže', 'edit_posts', 'abu-drustvene', 'abu_dr_admin');
}, 21);

function abu_dr_admin() {
    $poruka = '';
    if (isset($_POST['abu_meta_spremi']) && current_user_can('manage_options') && check_admin_referer('abu_meta')) {
        $o = abu_meta_opt();
        $novo = array('page_id' => sanitize_text_field(wp_unslash($_POST['page_id'] ?? '')), 'ig_id' => sanitize_text_field(wp_unslash($_POST['ig_id'] ?? '')),
            'auto' => empty($_POST['auto']) ? 0 : 1, 'token' => $o['token'], 'od' => $o['od']);
        $tok = trim((string) wp_unslash($_POST['token'] ?? ''));
        if ($tok !== '') { $novo['token'] = sanitize_text_field($tok); }
        if (!empty($_POST['token_brisi'])) { $novo['token'] = ''; }
        if ($novo['auto'] && !$o['auto']) { $novo['od'] = current_time('mysql', true); }
        update_option('abu_meta', $novo, false);
        $poruka = 'Spremljeno.';
        if ($novo['token'] && $novo['page_id']) {
            $r = wp_remote_get(ABU_GRAPH . rawurlencode($novo['page_id']) . '?fields=name,followers_count&access_token=' . rawurlencode($novo['token']), array('timeout' => 20));
            $jj = is_wp_error($r) ? null : json_decode((string) wp_remote_retrieve_body($r), true);
            $poruka .= !empty($jj['name']) ? ' Facebook stranica: ' . esc_html($jj['name']) . (isset($jj['followers_count']) ? ' (' . number_format_i18n($jj['followers_count']) . ' pratitelja)' : '') . '.' : ' Provjera stranice nije uspjela: ' . esc_html($jj['error']['message'] ?? 'nepoznato') . '.';
        }
    }
    if (isset($_POST['abu_dr_objavi'], $_POST['id'], $_POST['mreza']) && check_admin_referer('abu_dr_objavi')) {
        $poruka = abu_dr_objavi_jedan((int) $_POST['id'], sanitize_key(wp_unslash($_POST['mreza'])) === 'ig' ? 'ig' : 'fb');
    }
    $o = abu_meta_opt();
    echo '<div class="wrap"><h1>Društvene mreže</h1>';
    if ($poruka) { echo '<div class="notice notice-info"><p>' . esc_html($poruka) . '</p></div>'; }
    echo '<p>Kad su uneseni ID Facebook stranice, token stranice (s pravima <code>pages_manage_posts</code>, <code>pages_read_engagement</code>, <code>instagram_basic</code>, <code>instagram_content_publish</code>) i Instagram Business ID, članci se mogu objaviti jednim klikom, a uz uključenu automatiku svaki novi članak ide na obje mreže 10 minuta nakon objave. Bez tokena ispod su gotovi tekstovi za kopiranje.</p>';
    if (current_user_can('manage_options')) {
        echo '<form method="post"><table class="form-table">';
        wp_nonce_field('abu_meta');
        echo '<tr><th>ID Facebook stranice</th><td><input class="regular-text" name="page_id" value="' . esc_attr($o['page_id']) . '"></td></tr>';
        echo '<tr><th>Token stranice</th><td><input class="regular-text" type="password" name="token" placeholder="' . ($o['token'] ? 'spremljen (unesite novi za zamjenu)' : '') . '" autocomplete="off"> ' . ($o['token'] ? '<label><input type="checkbox" name="token_brisi" value="1"> obriši</label>' : '') . '</td></tr>';
        echo '<tr><th>Instagram Business ID</th><td><input class="regular-text" name="ig_id" value="' . esc_attr($o['ig_id']) . '"></td></tr>';
        echo '<tr><th>Automatika</th><td><label><input type="checkbox" name="auto" value="1"' . checked($o['auto'], 1, false) . '> novi članci automatski na Facebook i Instagram</label>' . ($o['od'] ? ' <small>(uključeno od ' . esc_html($o['od']) . ' UTC)</small>' : '') . '</td></tr>';
        echo '</table><p><button class="button button-primary" name="abu_meta_spremi" value="1">Spremi</button></p></form>';
    }
    echo '<h2>Članci</h2><table class="widefat striped"><thead><tr><th>Članak</th><th>Facebook</th><th>Instagram</th><th>Tekst za kopiranje (Facebook)</th></tr></thead><tbody>';
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 40)) as $p) {
        $st = function ($m) use ($p, $o) {
            $id = get_post_meta($p->ID, '_abu_' . $m . '_id', true);
            if ($id) { return '✓ objavljeno'; }
            $g = get_post_meta($p->ID, '_abu_' . $m . '_greska', true);
            $b = $o['token'] ? '<form method="post" style="display:inline">' . wp_nonce_field('abu_dr_objavi', '_wpnonce', true, false) . '<input type="hidden" name="id" value="' . (int) $p->ID . '"><input type="hidden" name="mreza" value="' . $m . '"><button class="button" name="abu_dr_objavi" value="1">Objavi</button></form>' : '—';
            return ($g ? '<span style="color:#b32d2e">' . esc_html($g) . '</span><br>' : '') . $b;
        };
        echo '<tr><td><a href="' . esc_url(get_permalink($p)) . '" target="_blank">' . esc_html($p->post_title) . '</a><br><small>' . esc_html(get_the_date('', $p)) . '</small></td><td>' . $st('fb') . '</td><td>' . $st('ig') . '</td>'
            . '<td><textarea readonly rows="4" style="width:100%" onclick="this.select()">' . esc_textarea(abu_dr_tekst($p->ID, 'fb') . "\n\n" . get_permalink($p)) . '</textarea></td></tr>';
    }
    echo '</tbody></table></div>';
}
