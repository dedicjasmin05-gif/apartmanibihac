<?php
/**
 * Plugin Name: Apartmani Upiti
 * Description: Upiti za smještaj s potvrdom vlasnika jednim klikom, kalendarom, iCal sinkronizacijom (Booking/Airbnb), kaparom i automatskim porukama.
 * Version: 1.0.3
 * Author: Digital Puls
 * Text Domain: apartmani-upiti
 */

if (!defined('ABSPATH')) { exit; }

define('ABU_VER', '1.0.3');
define('ABU_TABLE', 'abu_upiti');
define('ABU_ICAL_TABLE', 'abu_ical_blocks');

/* ============================================================
 * 1. INSTALACIJA: tablice i cron
 * ============================================================ */
register_activation_hook(__FILE__, 'abu_activate');
function abu_activate() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $c = $wpdb->get_charset_collate();
    $t = $wpdb->prefix . ABU_TABLE;
    $i = $wpdb->prefix . ABU_ICAL_TABLE;
    dbDelta("CREATE TABLE $t (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        objekt_id bigint(20) unsigned NOT NULL,
        dolazak date NOT NULL,
        odlazak date NOT NULL,
        osobe smallint(5) unsigned NOT NULL DEFAULT 1,
        ime varchar(190) NOT NULL,
        email varchar(190) NOT NULL,
        telefon varchar(60) NOT NULL DEFAULT '',
        poruka text NULL,
        jezik varchar(5) NOT NULL DEFAULT 'bs',
        izvor varchar(20) NOT NULL DEFAULT 'web',
        status varchar(20) NOT NULL DEFAULT 'novi',
        token varchar(64) NOT NULL,
        iznos decimal(10,2) NULL,
        kapara decimal(10,2) NULL,
        kreiran datetime NOT NULL,
        drzi_do datetime NULL,
        potvrden datetime NULL,
        kapara_do datetime NULL,
        placeno datetime NULL,
        podsjetnik_admin tinyint(1) NOT NULL DEFAULT 0,
        podsjetnik_kapara tinyint(1) NOT NULL DEFAULT 0,
        poruka_7d tinyint(1) NOT NULL DEFAULT 0,
        poruka_1d tinyint(1) NOT NULL DEFAULT 0,
        poruka_recenzija tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        KEY objekt (objekt_id, status),
        KEY datumi (dolazak, odlazak)
    ) $c;");
    dbDelta("CREATE TABLE $i (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        objekt_id bigint(20) unsigned NOT NULL,
        izvor varchar(190) NOT NULL DEFAULT '',
        od date NOT NULL,
        do date NOT NULL,
        PRIMARY KEY  (id),
        KEY objekt (objekt_id)
    ) $c;");
    if (!wp_next_scheduled('abu_cron')) {
        wp_schedule_event(time() + 60, 'abu_15min', 'abu_cron');
    }
    abu_register_cpt();
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('abu_cron');
});
add_filter('cron_schedules', function ($s) {
    $s['abu_15min'] = array('interval' => 900, 'display' => 'Svakih 15 minuta');
    return $s;
});

/* ============================================================
 * 2. POSTAVKE
 * ============================================================ */
function abu_opt($key, $default = '') {
    $o = get_option('abu_postavke', array());
    return isset($o[$key]) && $o[$key] !== '' ? $o[$key] : $default;
}
function abu_defaults() {
    return array(
        'admin_emails'   => get_option('admin_email'),
        'admin_telefoni' => '',
        'drzi_sati'      => 12,
        'podsjetnik_sati'=> 3,
        'kapara_posto'   => 30,
        'kapara_sati'    => 24,
        'iban'           => '',
        'primatelj'      => '',
        'link_placanja'  => '',
        'whatsapp'       => '38761591320',
        'telefon_javni'  => '+387 61 591 320',
        'google_recenzija' => '',
        'sms_url'        => '',
        'sms_kljuc'      => '',
        'sms_posiljatelj'=> 'ApartBihac',
        'uvjeti_otkaza'  => 'Do 14 dana prije dolaska kapara se vraća umanjena za trošak plaćanja. Kasnije se kapara ne vraća.',
    );
}

/* ============================================================
 * 3. OBJEKTI (CPT) + polja vlasnika
 * ============================================================ */
add_action('init', 'abu_register_cpt');
// Nakon nadogradnje dodatka ili teme osvježi adrese (npr. /smjestaj/naziv/).
add_action('init', function () {
    $v = ABU_VER . '|' . wp_get_theme()->get('Version') . '|' . get_template();
    if (get_option('abu_rw') !== $v) { flush_rewrite_rules(false); update_option('abu_rw', $v); }
}, 99);
function abu_register_cpt() {
    if (post_type_exists('objekt')) { return; }
    register_post_type('objekt', array(
        'labels' => array('name' => 'Objekti', 'singular_name' => 'Objekt', 'add_new_item' => 'Dodaj objekt', 'edit_item' => 'Uredi objekt'),
        'public' => true,
        'has_archive' => false,
        'rewrite' => array('slug' => 'smjestaj', 'with_front' => false),
        'menu_icon' => 'dashicons-admin-home',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true,
    ));
}

function abu_meta_fields() {
    return array(
        'grupa'          => array('Grupa', 'select', array('vlastiti' => 'Vlastiti', 'posrednicki' => 'Posrednički')),
        'vlasnik_ime'    => array('Vlasnik — ime', 'text'),
        'vlasnik_email'  => array('Vlasnik — e-mail', 'email'),
        'vlasnik_tel'    => array('Vlasnik — mobitel (+387…)', 'text'),
        'mjesto'         => array('Mjesto', 'text'),
        'adresa'         => array('Adresa (šalje se gostu tek nakon uplate)', 'text'),
        'kapacitet'      => array('Kapacitet (osoba)', 'number'),
        'cijena'         => array('Cijena po noći (€)', 'number'),
        'min_noci'       => array('Minimalno noćenja', 'number'),
        'ical_uvoz'      => array('iCal linkovi za uvoz (Booking/Airbnb), jedan po retku', 'textarea'),
        'upute'          => array('Upute za dolazak (šalju se 7 dana prije)', 'textarea'),
    );
}

add_action('add_meta_boxes', function () {
    add_meta_box('abu_objekt', 'Podaci objekta i vlasnika', 'abu_meta_box', 'objekt', 'normal', 'high');
});
function abu_meta_box($post) {
    wp_nonce_field('abu_save_objekt', 'abu_nonce');
    echo '<table class="form-table">';
    foreach (abu_meta_fields() as $k => $f) {
        $v = get_post_meta($post->ID, '_abu_' . $k, true);
        echo '<tr><th><label for="abu_' . esc_attr($k) . '">' . esc_html($f[0]) . '</label></th><td>';
        if ($f[1] === 'textarea') {
            echo '<textarea class="large-text" rows="3" id="abu_' . esc_attr($k) . '" name="abu[' . esc_attr($k) . ']">' . esc_textarea($v) . '</textarea>';
        } elseif ($f[1] === 'select') {
            echo '<select id="abu_' . esc_attr($k) . '" name="abu[' . esc_attr($k) . ']">';
            foreach ($f[2] as $ov => $ol) {
                echo '<option value="' . esc_attr($ov) . '"' . selected($v, $ov, false) . '>' . esc_html($ol) . '</option>';
            }
            echo '</select>';
        } else {
            echo '<input class="regular-text" type="' . esc_attr($f[1]) . '" id="abu_' . esc_attr($k) . '" name="abu[' . esc_attr($k) . ']" value="' . esc_attr($v) . '">';
        }
        echo '</td></tr>';
    }
    $export = abu_ical_export_url($post->ID);
    echo '<tr><th>iCal izvoz (zalijepiti u Booking → Uvezi kalendar)</th><td><input class="large-text" readonly value="' . esc_attr($export) . '" onclick="this.select()"></td></tr>';
    echo '<tr><th>Kratki kodovi</th><td><code>[abu_kalendar id="' . intval($post->ID) . '"]</code> &nbsp; <code>[abu_upit id="' . intval($post->ID) . '"]</code></td></tr>';
    echo '</table>';
}
add_action('save_post_objekt', function ($post_id) {
    if (!isset($_POST['abu_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abu_nonce'])), 'abu_save_objekt')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    $in = isset($_POST['abu']) && is_array($_POST['abu']) ? wp_unslash($_POST['abu']) : array();
    foreach (abu_meta_fields() as $k => $f) {
        $v = isset($in[$k]) ? $in[$k] : '';
        $v = $f[1] === 'textarea' ? sanitize_textarea_field($v) : ($f[1] === 'email' ? sanitize_email($v) : sanitize_text_field($v));
        update_post_meta($post_id, '_abu_' . $k, $v);
    }
    if (!get_post_meta($post_id, '_abu_ical_key', true)) {
        update_post_meta($post_id, '_abu_ical_key', wp_generate_password(20, false));
    }
    wp_schedule_single_event(time() + 5, 'abu_sync_one', array($post_id));
});
function abu_m($objekt_id, $k) { return get_post_meta($objekt_id, '_abu_' . $k, true); }

/* ============================================================
 * 4. DOSTUPNOST
 * ============================================================ */
/** Statusi koji zauzimaju dane. 'novi' zauzima samo dok traje drzi_do. */
function abu_zauzeto($objekt_id, $od, $do, $osim_id = 0) {
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    $now = current_time('mysql');
    $n = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $t WHERE objekt_id=%d AND id<>%d AND dolazak < %s AND odlazak > %s
         AND (status IN ('potvrden','placeno') OR (status='novi' AND drzi_do > %s))",
        $objekt_id, $osim_id, $do, $od, $now));
    if ($n > 0) { return true; }
    $i = $wpdb->prefix . ABU_ICAL_TABLE;
    $m = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $i WHERE objekt_id=%d AND od < %s AND do > %s", $objekt_id, $do, $od));
    return $m > 0;
}

/** Svi zauzeti dani (Y-m-d) od danas do +$dana. */
function abu_zauzeti_dani($objekt_id, $dana = 400) {
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    $i = $wpdb->prefix . ABU_ICAL_TABLE;
    $today = current_time('Y-m-d');
    $kraj = gmdate('Y-m-d', strtotime($today . ' +' . intval($dana) . ' days'));
    $now = current_time('mysql');
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT dolazak AS od, odlazak AS do FROM $t WHERE objekt_id=%d AND odlazak > %s AND dolazak < %s
         AND (status IN ('potvrden','placeno') OR (status='novi' AND drzi_do > %s))
         UNION ALL SELECT od, do FROM $i WHERE objekt_id=%d AND do > %s AND od < %s",
        $objekt_id, $today, $kraj, $now, $objekt_id, $today, $kraj), ARRAY_A);
    $dani = array();
    foreach ($rows as $r) {
        $d = strtotime($r['od']);
        $e = strtotime($r['do']);
        while ($d < $e) { $dani[gmdate('Y-m-d', $d)] = 1; $d += 86400; }
    }
    return array_keys($dani);
}

add_action('rest_api_init', function () {
    register_rest_route('abu/v1', '/dostupnost/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($r) {
            $id = intval($r['id']);
            if (get_post_type($id) !== 'objekt') { return new WP_Error('nema', 'Nema objekta', array('status' => 404)); }
            return array('zauzeto' => abu_zauzeti_dani($id), 'min_noci' => max(1, intval(abu_m($id, 'min_noci'))));
        },
    ));
});

/* ============================================================
 * 5. iCal UVOZ (Booking/Airbnb → nas) i IZVOZ (nas → Booking)
 * ============================================================ */
add_action('abu_sync_one', 'abu_ical_sync_objekt');
function abu_ical_sync_objekt($objekt_id) {
    global $wpdb;
    $i = $wpdb->prefix . ABU_ICAL_TABLE;
    $urls = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) abu_m($objekt_id, 'ical_uvoz'))));
    $novi = array();
    $uspjeh = true;
    foreach ($urls as $url) {
        if (!wp_http_validate_url($url)) { continue; }
        $res = wp_remote_get($url, array('timeout' => 20));
        if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) { $uspjeh = false; continue; }
        foreach (abu_ical_parse(wp_remote_retrieve_body($res)) as $ev) {
            $novi[] = array($url, $ev[0], $ev[1]);
        }
    }
    if (!$uspjeh && empty($novi)) { return; } // ne brišemo stare blokade ako izvor ne radi
    $wpdb->delete($i, array('objekt_id' => $objekt_id));
    foreach ($novi as $n) {
        $wpdb->insert($i, array('objekt_id' => $objekt_id, 'izvor' => substr($n[0], 0, 190), 'od' => $n[1], 'do' => $n[2]));
    }
    update_post_meta($objekt_id, '_abu_ical_zadnje', current_time('mysql'));
}
/** Vraća parove [od, do) kao Y-m-d. */
function abu_ical_parse($txt) {
    $txt = preg_replace("/\r\n[ \t]/", '', (string) $txt);
    $out = array();
    if (!preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $txt, $m)) { return $out; }
    foreach ($m[1] as $ev) {
        if (!preg_match('/DTSTART[^:]*:(\d{8})/', $ev, $s)) { continue; }
        $e = array();
        if (!preg_match('/DTEND[^:]*:(\d{8})/', $ev, $e)) { $e = array(1 => gmdate('Ymd', strtotime($s[1] . ' +1 day'))); }
        $od = substr($s[1], 0, 4) . '-' . substr($s[1], 4, 2) . '-' . substr($s[1], 6, 2);
        $do = substr($e[1], 0, 4) . '-' . substr($e[1], 4, 2) . '-' . substr($e[1], 6, 2);
        if ($do <= $od) { $do = gmdate('Y-m-d', strtotime($od . ' +1 day')); }
        $out[] = array($od, $do);
    }
    return $out;
}
function abu_ical_export_url($objekt_id) {
    $key = get_post_meta($objekt_id, '_abu_ical_key', true);
    if (!$key) { $key = wp_generate_password(20, false); update_post_meta($objekt_id, '_abu_ical_key', $key); }
    return add_query_arg(array('abu_ical' => $objekt_id, 'k' => $key), home_url('/'));
}
add_action('template_redirect', function () {
    if (empty($_GET['abu_ical'])) { return; }
    $id = intval($_GET['abu_ical']);
    $k = isset($_GET['k']) ? sanitize_text_field(wp_unslash($_GET['k'])) : '';
    if (get_post_type($id) !== 'objekt' || !hash_equals((string) get_post_meta($id, '_abu_ical_key', true), $k)) { status_header(404); exit; }
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, dolazak, odlazak FROM $t WHERE objekt_id=%d AND odlazak >= %s AND status IN ('potvrden','placeno')",
        $id, current_time('Y-m-d')), ARRAY_A);
    nocache_headers();
    header('Content-Type: text/calendar; charset=utf-8');
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    $o = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apartmani Upiti//BS\r\nCALSCALE:GREGORIAN\r\n";
    foreach ($rows as $r) {
        $o .= "BEGIN:VEVENT\r\nUID:abu-" . intval($r['id']) . '@' . $host . "\r\nDTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\nDTSTART;VALUE=DATE:" . str_replace('-', '', $r['dolazak']) . "\r\nDTEND;VALUE=DATE:" . str_replace('-', '', $r['odlazak']) . "\r\nSUMMARY:Rezervirano\r\nEND:VEVENT\r\n";
    }
    echo $o . "END:VCALENDAR\r\n"; // phpcs:ignore
    exit;
});

/* ============================================================
 * 6. TEKSTOVI (BS / DE / EN)
 * ============================================================ */
function abu_t($key, $lang = 'bs') {
    static $T = null;
    if ($T === null) {
        $T = array(
            'bs' => array(
                'f_ime' => 'Ime i prezime', 'f_email' => 'E-mail', 'f_tel' => 'Mobitel (WhatsApp/Viber)', 'f_dol' => 'Dolazak', 'f_odl' => 'Odlazak', 'f_osobe' => 'Broj osoba', 'f_poruka' => 'Poruka (nije obavezno)', 'f_uvjeti' => 'Prihvaćam uvjete otkaza', 'f_posalji' => 'Pošalji upit',
                'ok' => 'Hvala! Upit je poslan vlasniku. Odgovor s potvrdom dobivate u roku od %d sati na e-mail i SMS.',
                'err_dat' => 'Provjerite datume dolaska i odlaska.', 'err_zauz' => 'Odabrani datumi više nisu slobodni. Odaberite druge datume.', 'err_pol' => 'Ispunite ime, ispravan e-mail i mobitel.', 'err_min' => 'Minimalni boravak je %d noći.', 'err_kap' => 'Objekt prima najviše %d osoba.', 'err_uvj' => 'Potrebno je prihvatiti uvjete otkaza.', 'err_spam' => 'Pričekajte minutu prije novog upita.',
                's_primljen' => 'Primili smo vaš upit — %s',
                'b_primljen' => "Poštovani %1\$s,\n\nhvala na upitu za %2\$s, %3\$s – %4\$s, %5\$d osoba.\nVlasnik potvrđuje termin u roku od %6\$d sati, a potvrdu dobivate na ovaj e-mail i SMS.\n\nPitanja: WhatsApp/Viber %7\$s",
                's_potvrden' => 'Termin je slobodan — uplatite kaparu (%s)',
                'b_potvrden' => "Poštovani %1\$s,\n\ntermin %2\$s – %3\$s u objektu %4\$s je slobodan i čuvamo ga za vas.\n\nUkupno: %5\$s €\nKapara (%6\$d %%): %7\$s €\nRok za uplatu: %8\$s\n\n%9\$s\n\nNakon uplate dobivate adresu i kontakt vlasnika.\n\nUvjeti otkaza: %10\$s",
                'placanje_link' => 'Plaćanje karticom: %s',
                'placanje_iban' => "Uplata na račun:\nPrimatelj: %1\$s\nIBAN: %2\$s\nPoziv na broj / opis: Upit %3\$d",
                's_odbijen' => 'Termin nije dostupan — %s',
                'b_odbijen' => "Poštovani %1\$s,\n\nnažalost, %2\$s nije slobodan za %3\$s – %4\$s.\n\n%5\$sJavite nam se i pronaći ćemo vam smještaj: WhatsApp/Viber %6\$s",
                'alternative' => "Slobodni slični objekti za vaše datume:\n%s\n\n",
                's_podsj_kap' => 'Podsjetnik: rok za kaparu ističe — %s',
                'b_podsj_kap' => "Poštovani %1\$s,\n\nrok za uplatu kapare za %2\$s ističe %3\$s. Bez uplate se termin oslobađa za druge goste.\n\n%4\$s",
                's_otkazan' => 'Termin oslobođen — %s',
                'b_otkazan' => "Poštovani %1\$s,\n\nkapara za %2\$s nije uplaćena u roku pa je termin oslobođen. Ako i dalje želite doći, pošaljite novi upit ili nam se javite: %3\$s",
                's_placeno' => 'Rezervacija potvrđena — %s',
                'b_placeno' => "Poštovani %1\$s,\n\nvaša rezervacija je potvrđena.\n\nObjekt: %2\$s\nAdresa: %3\$s\nDolazak: %4\$s\nOdlazak: %5\$s\nOsoba: %6\$d\nPreostalo za platiti pri dolasku: %7\$s €\n\nVlasnik: %8\$s, %9\$s\n\nUvjeti otkaza: %10\$s",
                's_7d' => 'Za 7 dana dolazite u Bihać — %s',
                'b_7d' => "Poštovani %1\$s,\n\nvidimo se %2\$s u objektu %3\$s.\n\n%4\$s\n\nRafting na Uni, team building i restoran uz rijeku: Una Olympic Rafting, +387 67 103 7177.\n\nVlasnik: %5\$s",
                's_1d' => 'Sutra dolazite — %s',
                'b_1d' => 'Sutra je vaš dolazak u %1$s. Vlasnik: %2$s. Sretan put!',
                's_rec' => 'Hvala na boravku — %s',
                'b_rec' => "Poštovani %1\$s,\n\nhvala što ste bili naši gosti. Ostavite nam recenziju, puno nam znači:\n%2\$s\n\nZa idući dolazak javite se direktno i dobivate 10 %% popusta.",
            ),
            'de' => array(
                'f_ime' => 'Vor- und Nachname', 'f_email' => 'E-Mail', 'f_tel' => 'Handy (WhatsApp/Viber)', 'f_dol' => 'Anreise', 'f_odl' => 'Abreise', 'f_osobe' => 'Personen', 'f_poruka' => 'Nachricht (optional)', 'f_uvjeti' => 'Ich akzeptiere die Stornobedingungen', 'f_posalji' => 'Anfrage senden',
                'ok' => 'Danke! Ihre Anfrage wurde an den Gastgeber gesendet. Sie erhalten innerhalb von %d Stunden eine Bestätigung per E-Mail und SMS.',
                'err_dat' => 'Bitte prüfen Sie An- und Abreise.', 'err_zauz' => 'Die gewählten Tage sind nicht mehr frei.', 'err_pol' => 'Bitte Name, gültige E-Mail und Handynummer angeben.', 'err_min' => 'Mindestaufenthalt: %d Nächte.', 'err_kap' => 'Maximal %d Personen.', 'err_uvj' => 'Bitte Stornobedingungen akzeptieren.', 'err_spam' => 'Bitte eine Minute warten.',
                's_primljen' => 'Anfrage erhalten — %s',
                'b_primljen' => "Hallo %1\$s,\n\ndanke für Ihre Anfrage für %2\$s, %3\$s – %4\$s, %5\$d Personen.\nDer Gastgeber bestätigt innerhalb von %6\$d Stunden per E-Mail und SMS.\n\nFragen: WhatsApp/Viber %7\$s",
                's_potvrden' => 'Termin frei — bitte Anzahlung leisten (%s)',
                'b_potvrden' => "Hallo %1\$s,\n\nder Termin %2\$s – %3\$s in %4\$s ist frei und für Sie reserviert.\n\nGesamt: %5\$s €\nAnzahlung (%6\$d %%): %7\$s €\nFrist: %8\$s\n\n%9\$s\n\nNach Zahlung erhalten Sie Adresse und Kontakt des Gastgebers.\n\nStornobedingungen: %10\$s",
                'placanje_link' => 'Kartenzahlung: %s',
                'placanje_iban' => "Überweisung:\nEmpfänger: %1\$s\nIBAN: %2\$s\nVerwendungszweck: Anfrage %3\$d",
                's_odbijen' => 'Termin nicht verfügbar — %s',
                'b_odbijen' => "Hallo %1\$s,\n\nleider ist %2\$s für %3\$s – %4\$s nicht frei.\n\n%5\$sMelden Sie sich, wir finden eine Unterkunft: WhatsApp/Viber %6\$s",
                'alternative' => "Freie ähnliche Unterkünfte:\n%s\n\n",
                's_podsj_kap' => 'Erinnerung: Anzahlungsfrist — %s',
                'b_podsj_kap' => "Hallo %1\$s,\n\ndie Frist für die Anzahlung für %2\$s endet am %3\$s. Ohne Zahlung wird der Termin freigegeben.\n\n%4\$s",
                's_otkazan' => 'Termin freigegeben — %s',
                'b_otkazan' => "Hallo %1\$s,\n\ndie Anzahlung für %2\$s ist nicht eingegangen, der Termin wurde freigegeben. Kontakt: %3\$s",
                's_placeno' => 'Buchung bestätigt — %s',
                'b_placeno' => "Hallo %1\$s,\n\nIhre Buchung ist bestätigt.\n\nUnterkunft: %2\$s\nAdresse: %3\$s\nAnreise: %4\$s\nAbreise: %5\$s\nPersonen: %6\$d\nRestbetrag bei Anreise: %7\$s €\n\nGastgeber: %8\$s, %9\$s\n\nStornobedingungen: %10\$s",
                's_7d' => 'In 7 Tagen in Bihać — %s',
                'b_7d' => "Hallo %1\$s,\n\nwir sehen uns am %2\$s in %3\$s.\n\n%4\$s\n\nRafting auf der Una: Una Olympic Rafting, +387 67 103 7177.\n\nGastgeber: %5\$s",
                's_1d' => 'Morgen ist Anreise — %s',
                'b_1d' => 'Morgen reisen Sie nach %1$s an. Gastgeber: %2$s. Gute Reise!',
                's_rec' => 'Danke für Ihren Aufenthalt — %s',
                'b_rec' => "Hallo %1\$s,\n\ndanke für Ihren Besuch. Eine Bewertung hilft uns sehr:\n%2\$s\n\nBei direkter Buchung beim nächsten Mal erhalten Sie 10 %% Rabatt.",
            ),
            'en' => array(
                'f_ime' => 'Full name', 'f_email' => 'E-mail', 'f_tel' => 'Mobile (WhatsApp/Viber)', 'f_dol' => 'Arrival', 'f_odl' => 'Departure', 'f_osobe' => 'Guests', 'f_poruka' => 'Message (optional)', 'f_uvjeti' => 'I accept the cancellation terms', 'f_posalji' => 'Send request',
                'ok' => 'Thank you! Your request was sent to the host. You will get a confirmation within %d hours by e-mail and SMS.',
                'err_dat' => 'Please check arrival and departure dates.', 'err_zauz' => 'These dates are no longer available.', 'err_pol' => 'Please enter your name, a valid e-mail and mobile number.', 'err_min' => 'Minimum stay is %d nights.', 'err_kap' => 'Maximum %d guests.', 'err_uvj' => 'Please accept the cancellation terms.', 'err_spam' => 'Please wait a minute.',
                's_primljen' => 'Request received — %s',
                'b_primljen' => "Dear %1\$s,\n\nthank you for your request for %2\$s, %3\$s – %4\$s, %5\$d guests.\nThe host will confirm within %6\$d hours by e-mail and SMS.\n\nQuestions: WhatsApp/Viber %7\$s",
                's_potvrden' => 'Dates available — please pay the deposit (%s)',
                'b_potvrden' => "Dear %1\$s,\n\n%2\$s – %3\$s at %4\$s is available and held for you.\n\nTotal: %5\$s €\nDeposit (%6\$d %%): %7\$s €\nDeadline: %8\$s\n\n%9\$s\n\nAfter payment you receive the address and the host's contact.\n\nCancellation terms: %10\$s",
                'placanje_link' => 'Pay by card: %s',
                'placanje_iban' => "Bank transfer:\nBeneficiary: %1\$s\nIBAN: %2\$s\nReference: Request %3\$d",
                's_odbijen' => 'Dates not available — %s',
                'b_odbijen' => "Dear %1\$s,\n\nunfortunately %2\$s is not available for %3\$s – %4\$s.\n\n%5\$sContact us and we will find you a place: WhatsApp/Viber %6\$s",
                'alternative' => "Available similar places:\n%s\n\n",
                's_podsj_kap' => 'Reminder: deposit deadline — %s',
                'b_podsj_kap' => "Dear %1\$s,\n\nthe deposit deadline for %2\$s is %3\$s. Without payment the dates are released.\n\n%4\$s",
                's_otkazan' => 'Dates released — %s',
                'b_otkazan' => "Dear %1\$s,\n\nthe deposit for %2\$s was not received in time, so the dates were released. Contact: %3\$s",
                's_placeno' => 'Booking confirmed — %s',
                'b_placeno' => "Dear %1\$s,\n\nyour booking is confirmed.\n\nProperty: %2\$s\nAddress: %3\$s\nArrival: %4\$s\nDeparture: %5\$s\nGuests: %6\$d\nBalance due on arrival: %7\$s €\n\nHost: %8\$s, %9\$s\n\nCancellation terms: %10\$s",
                's_7d' => 'See you in Bihać in 7 days — %s',
                'b_7d' => "Dear %1\$s,\n\nsee you on %2\$s at %3\$s.\n\n%4\$s\n\nRafting on the Una: Una Olympic Rafting, +387 67 103 7177.\n\nHost: %5\$s",
                's_1d' => 'Arrival tomorrow — %s',
                'b_1d' => 'Tomorrow is your arrival at %1$s. Host: %2$s. Safe travels!',
                's_rec' => 'Thank you for staying — %s',
                'b_rec' => "Dear %1\$s,\n\nthank you for staying with us. A review helps us a lot:\n%2\$s\n\nBook directly next time and get 10 %% off.",
            ),
        );
    }
    $lang = isset($T[$lang]) ? $lang : 'bs';
    return isset($T[$lang][$key]) ? $T[$lang][$key] : (isset($T['bs'][$key]) ? $T['bs'][$key] : $key);
}
function abu_lang() {
    $l = substr(get_locale(), 0, 2);
    if (isset($_REQUEST['lang'])) { $l = substr(sanitize_key(wp_unslash($_REQUEST['lang'])), 0, 2); }
    return in_array($l, array('de', 'en'), true) ? $l : 'bs';
}
function abu_dat($ymd) { return date_i18n('d.m.Y.', strtotime($ymd)); }
function abu_novac($n) { return number_format((float) $n, 2, ',', '.'); }

/* ============================================================
 * 7. SLANJE: e-mail, SMS, WhatsApp link
 * ============================================================ */
function abu_mail($to, $subj, $body) {
    if (!$to) { return false; }
    $from = get_bloginfo('name');
    $headers = array('Content-Type: text/plain; charset=UTF-8', 'From: ' . $from . ' <' . get_option('admin_email') . '>');
    return wp_mail($to, $subj, $body, $headers);
}
/**
 * SMS preko bilo kojeg HTTP SMS servisa (npr. Infobip, Twilio preko proxyja).
 * Postavke: sms_url (POST endpoint), sms_kljuc (Authorization header), sms_posiljatelj.
 * Šalje JSON {from, to, text}. Prazan sms_url = SMS isključen.
 */
function abu_sms($to, $text) {
    $url = abu_opt('sms_url');
    $to = preg_replace('/[^0-9+]/', '', (string) $to);
    if (!$url || !$to) { return false; }
    $res = wp_remote_post($url, array(
        'timeout' => 15,
        'headers' => array('Content-Type' => 'application/json', 'Authorization' => abu_opt('sms_kljuc')),
        'body' => wp_json_encode(array('from' => abu_opt('sms_posiljatelj', 'ApartBihac'), 'to' => $to, 'text' => $text)),
    ));
    return !is_wp_error($res) && wp_remote_retrieve_response_code($res) < 300;
}
function abu_wa_link($tel, $text) {
    return 'https://wa.me/' . preg_replace('/[^0-9]/', '', (string) $tel) . '?text=' . rawurlencode($text);
}
function abu_admin_emails() {
    return array_filter(array_map('trim', explode(',', (string) abu_opt('admin_emails', get_option('admin_email')))));
}
function abu_admin_telefoni() {
    return array_filter(array_map('trim', explode(',', (string) abu_opt('admin_telefoni'))));
}

/* ============================================================
 * 8. UPIT: spremanje, linkovi za akcije, obavijesti
 * ============================================================ */
function abu_get($id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . ABU_TABLE . ' WHERE id=%d', $id));
}
function abu_set($id, $data) {
    global $wpdb;
    return $wpdb->update($wpdb->prefix . ABU_TABLE, $data, array('id' => $id));
}
function abu_action_url($u, $akcija) {
    $sig = hash_hmac('sha256', $u->id . '|' . $akcija . '|' . $u->token, wp_salt('auth'));
    return add_query_arg(array('abu_a' => $akcija, 'u' => $u->id, 's' => $sig), home_url('/'));
}
function abu_noci($u) { return max(1, (int) round((strtotime($u->odlazak) - strtotime($u->dolazak)) / 86400)); }

function abu_kreiraj_upit($d) {
    global $wpdb;
    $objekt = get_post($d['objekt_id']);
    $cijena = (float) abu_m($objekt->ID, 'cijena');
    $now = current_time('timestamp');
    $u = array(
        'objekt_id' => $objekt->ID,
        'dolazak' => $d['dolazak'],
        'odlazak' => $d['odlazak'],
        'osobe' => $d['osobe'],
        'ime' => $d['ime'],
        'email' => $d['email'],
        'telefon' => $d['telefon'],
        'poruka' => $d['poruka'],
        'jezik' => $d['jezik'],
        'izvor' => $d['izvor'],
        'status' => 'novi',
        'token' => wp_generate_password(32, false),
        'kreiran' => current_time('mysql'),
        'drzi_do' => gmdate('Y-m-d H:i:s', $now + HOUR_IN_SECONDS * intval(abu_opt('drzi_sati', 12))),
    );
    $noci = max(1, (int) round((strtotime($d['odlazak']) - strtotime($d['dolazak'])) / 86400));
    if ($cijena > 0) {
        $u['iznos'] = round($cijena * $noci, 2);
        $u['kapara'] = round($u['iznos'] * intval(abu_opt('kapara_posto', 30)) / 100, 2);
    }
    $wpdb->insert($wpdb->prefix . ABU_TABLE, $u);
    $id = (int) $wpdb->insert_id;
    $row = abu_get($id);
    abu_obavijesti_novi($row);
    return $id;
}

function abu_obavijesti_novi($u) {
    $o = get_post($u->objekt_id);
    $naziv = $o ? $o->post_title : ('#' . $u->objekt_id);
    $L = $u->jezik;
    $sati = intval(abu_opt('drzi_sati', 12));
    // gost
    abu_mail($u->email, sprintf(abu_t('s_primljen', $L), $naziv),
        sprintf(abu_t('b_primljen', $L), $u->ime, $naziv, abu_dat($u->dolazak), abu_dat($u->odlazak), $u->osobe, $sati, abu_opt('telefon_javni')));
    // vlasnik + admini: isti sadržaj s gumbima
    $potvrdi = abu_action_url($u, 'potvrdi');
    $odbij = abu_action_url($u, 'odbij');
    $iznos = $u->iznos ? abu_novac($u->iznos) . ' €' : 'po dogovoru';
    $txt = "NOVI UPIT #{$u->id} — {$naziv}\n\n"
        . 'Datumi: ' . abu_dat($u->dolazak) . ' – ' . abu_dat($u->odlazak) . ' (' . abu_noci($u) . " noći)\n"
        . "Osoba: {$u->osobe}\nGost: {$u->ime}\nMobitel: {$u->telefon}\nE-mail: {$u->email}\nJezik: " . strtoupper($u->jezik) . "\nIznos: {$iznos}\n"
        . ($u->poruka ? "Poruka: {$u->poruka}\n" : '')
        . "\nTermin je držan {$sati} sati.\n\n✔ POTVRDI (slobodno je):\n{$potvrdi}\n\n✖ ODBIJ (nije slobodno):\n{$odbij}\n";
    $prima = abu_admin_emails();
    $vl = abu_m($u->objekt_id, 'vlasnik_email');
    if ($vl) { $prima[] = $vl; }
    foreach (array_unique($prima) as $to) {
        abu_mail($to, "Novi upit #{$u->id}: {$naziv}, " . abu_dat($u->dolazak), $txt);
    }
    $sms = "Upit #{$u->id} {$naziv} " . abu_dat($u->dolazak) . '-' . abu_dat($u->odlazak) . ", {$u->osobe} os. Potvrdi: {$potvrdi} Odbij: {$odbij}";
    $tel = abu_admin_telefoni();
    $vt = abu_m($u->objekt_id, 'vlasnik_tel');
    if ($vt) { $tel[] = $vt; }
    foreach (array_unique($tel) as $t) { abu_sms($t, $sms); }
    do_action('abu_novi_upit', $u);
}

/** Akcije s jednim klikom iz e-maila/SMS-a: potvrdi, odbij, placeno. */
add_action('template_redirect', function () {
    if (empty($_GET['abu_a']) || empty($_GET['u']) || empty($_GET['s'])) { return; }
    $akcija = sanitize_key(wp_unslash($_GET['abu_a']));
    $u = abu_get(intval($_GET['u']));
    $sig = sanitize_text_field(wp_unslash($_GET['s']));
    nocache_headers();
    if (!$u || !in_array($akcija, array('potvrdi', 'odbij', 'placeno'), true) || !hash_equals(hash_hmac('sha256', $u->id . '|' . $akcija . '|' . $u->token, wp_salt('auth')), $sig)) {
        abu_poruka_stranica('Link nije ispravan ili je istekao.');
    }
    // Prvi korak je potvrdni ekran (zaštita od preglednika koji sami otvaraju linkove iz e-maila).
    if (empty($_POST['abu_ok'])) {
        $o = get_post($u->objekt_id);
        $natpis = array('potvrdi' => 'Potvrđujem da je termin slobodan', 'odbij' => 'Termin nije slobodan — odbij', 'placeno' => 'Kapara je uplaćena');
        abu_poruka_stranica(
            '<p><strong>Upit #' . intval($u->id) . '</strong> — ' . esc_html($o ? $o->post_title : '') . '<br>' . esc_html(abu_dat($u->dolazak) . ' – ' . abu_dat($u->odlazak)) . ', ' . intval($u->osobe) . ' osoba, ' . esc_html($u->ime) . '</p>'
            . '<p>Trenutni status: <strong>' . esc_html(abu_status_naziv($u->status)) . '</strong></p>'
            . '<form method="post"><input type="hidden" name="abu_ok" value="1"><button style="font-size:18px;padding:14px 22px;background:#ffda26;border:0;border-radius:6px;font-weight:700;cursor:pointer">' . esc_html($natpis[$akcija]) . '</button></form>',
            false);
    }
    $rez = abu_izvrsi($u, $akcija);
    abu_poruka_stranica($rez);
});

function abu_status_naziv($s) {
    $n = array('novi' => 'Novi — čeka potvrdu', 'potvrden' => 'Potvrđen — čeka kaparu', 'placeno' => 'Plaćeno — rezervacija', 'odbijen' => 'Odbijen', 'istekao' => 'Istekao bez odgovora', 'otkazan' => 'Otkazan (nema kapare)');
    return isset($n[$s]) ? $n[$s] : $s;
}

function abu_izvrsi($u, $akcija) {
    if ($akcija === 'potvrdi') {
        if ($u->status !== 'novi') { return 'Ovaj upit je već obrađen: ' . abu_status_naziv($u->status) . '.'; }
        if (abu_zauzeto($u->objekt_id, $u->dolazak, $u->odlazak, $u->id)) {
            abu_odbij($u, true);
            return 'Termin je u međuvremenu zauzet (Booking ili drugi gost). Gostu je poslan drugi prijedlog.';
        }
        abu_potvrdi($u);
        return 'Potvrđeno. Gost je dobio upute za kaparu. Hvala!';
    }
    if ($akcija === 'odbij') {
        if (!in_array($u->status, array('novi', 'potvrden'), true)) { return 'Ovaj upit je već obrađen: ' . abu_status_naziv($u->status) . '.'; }
        abu_odbij($u, true);
        return 'Odbijeno. Gostu je poslan drugi prijedlog.';
    }
    if ($akcija === 'placeno') {
        if ($u->status !== 'potvrden') { return 'Status je: ' . abu_status_naziv($u->status) . '.'; }
        abu_placeno($u);
        return 'Označeno kao plaćeno. Gost i vlasnik su dobili kontakte.';
    }
    return '';
}

function abu_placanje_upute($u) {
    $L = $u->jezik;
    $p = array();
    if (abu_opt('link_placanja')) { $p[] = sprintf(abu_t('placanje_link', $L), add_query_arg(array('client_reference_id' => 'upit-' . $u->id), abu_opt('link_placanja'))); }
    if (abu_opt('iban')) { $p[] = sprintf(abu_t('placanje_iban', $L), abu_opt('primatelj'), abu_opt('iban'), $u->id); }
    return implode("\n\n", $p);
}

function abu_potvrdi($u) {
    $sati = intval(abu_opt('kapara_sati', 24));
    abu_set($u->id, array('status' => 'potvrden', 'potvrden' => current_time('mysql'), 'kapara_do' => gmdate('Y-m-d H:i:s', current_time('timestamp') + $sati * HOUR_IN_SECONDS)));
    $u = abu_get($u->id);
    $o = get_post($u->objekt_id);
    $L = $u->jezik;
    $rok = date_i18n('d.m.Y. H:i', strtotime($u->kapara_do));
    abu_mail($u->email, sprintf(abu_t('s_potvrden', $L), $o->post_title), sprintf(abu_t('b_potvrden', $L),
        $u->ime, abu_dat($u->dolazak), abu_dat($u->odlazak), $o->post_title,
        $u->iznos ? abu_novac($u->iznos) : '—', intval(abu_opt('kapara_posto', 30)), $u->kapara ? abu_novac($u->kapara) : '—', $rok,
        abu_placanje_upute($u), abu_opt('uvjeti_otkaza')));
    abu_sms($u->telefon, $o->post_title . ' ' . abu_dat($u->dolazak) . ': ' . ($L === 'bs' ? 'termin je slobodan. Upute za kaparu su na vašem e-mailu, rok ' : 'available. Deposit details sent by e-mail, deadline ') . $rok);
    // admini dobivaju link "plaćeno" za ručno označavanje (uplata na račun)
    foreach (abu_admin_emails() as $to) {
        abu_mail($to, "Upit #{$u->id} potvrđen — čeka kaparu", "Kad kapara stigne na račun, kliknite:\n" . abu_action_url($u, 'placeno'));
    }
    // ostali upiti za iste dane: odbij
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    $drugi = $wpdb->get_col($wpdb->prepare("SELECT id FROM $t WHERE objekt_id=%d AND id<>%d AND status='novi' AND dolazak < %s AND odlazak > %s",
        $u->objekt_id, $u->id, $u->odlazak, $u->dolazak));
    foreach ($drugi as $did) { abu_odbij(abu_get((int) $did), true); }
}

function abu_alternative($u) {
    $q = new WP_Query(array('post_type' => 'objekt', 'post_status' => 'publish', 'posts_per_page' => 50, 'post__not_in' => array($u->objekt_id), 'fields' => 'ids'));
    $out = array();
    foreach ($q->posts as $pid) {
        $kap = intval(abu_m($pid, 'kapacitet'));
        if ($kap && $kap < $u->osobe) { continue; }
        if (abu_zauzeto($pid, $u->dolazak, $u->odlazak)) { continue; }
        $out[] = '• ' . get_the_title($pid) . ' — ' . get_permalink($pid);
        if (count($out) >= 3) { break; }
    }
    return $out ? sprintf(abu_t('alternative', $u->jezik), implode("\n", $out)) : '';
}

function abu_odbij($u, $obavijesti_gosta = true, $status = 'odbijen') {
    abu_set($u->id, array('status' => $status));
    if (!$obavijesti_gosta) { return; }
    $o = get_post($u->objekt_id);
    $L = $u->jezik;
    abu_mail($u->email, sprintf(abu_t('s_odbijen', $L), $o->post_title),
        sprintf(abu_t('b_odbijen', $L), $u->ime, $o->post_title, abu_dat($u->dolazak), abu_dat($u->odlazak), abu_alternative($u), abu_opt('telefon_javni')));
}

function abu_placeno($u) {
    abu_set($u->id, array('status' => 'placeno', 'placeno' => current_time('mysql')));
    $u = abu_get($u->id);
    $o = get_post($u->objekt_id);
    $L = $u->jezik;
    $ostatak = ($u->iznos && $u->kapara) ? abu_novac($u->iznos - $u->kapara) : '—';
    $vl_ime = abu_m($u->objekt_id, 'vlasnik_ime');
    $vl_tel = abu_m($u->objekt_id, 'vlasnik_tel');
    abu_mail($u->email, sprintf(abu_t('s_placeno', $L), $o->post_title), sprintf(abu_t('b_placeno', $L),
        $u->ime, $o->post_title, abu_m($u->objekt_id, 'adresa'), abu_dat($u->dolazak), abu_dat($u->odlazak), $u->osobe, $ostatak, $vl_ime, $vl_tel, abu_opt('uvjeti_otkaza')));
    abu_sms($u->telefon, ($L === 'bs' ? 'Rezervacija potvrđena: ' : 'Booking confirmed: ') . $o->post_title . ' ' . abu_dat($u->dolazak) . '. ' . $vl_ime . ' ' . $vl_tel);
    $txt = "POTVRĐENA REZERVACIJA #{$u->id} — {$o->post_title}\n\n" . abu_dat($u->dolazak) . ' – ' . abu_dat($u->odlazak) . ", {$u->osobe} osoba\nGost: {$u->ime}, {$u->telefon}, {$u->email}\nZa naplatu pri dolasku: {$ostatak} €\n";
    $vl = abu_m($u->objekt_id, 'vlasnik_email');
    if ($vl) { abu_mail($vl, "Rezervacija #{$u->id}: {$o->post_title}, " . abu_dat($u->dolazak), $txt); }
    if ($vl_tel) { abu_sms($vl_tel, "Rezervacija #{$u->id} {$o->post_title} " . abu_dat($u->dolazak) . '-' . abu_dat($u->odlazak) . ": {$u->ime} {$u->telefon}, naplata {$ostatak} EUR"); }
    foreach (abu_admin_emails() as $to) { abu_mail($to, "Plaćeno #{$u->id}: {$o->post_title}", $txt); }
    do_action('abu_placeno', $u);
}

function abu_poruka_stranica($html, $escape = true) {
    status_header(200);
    $body = $escape ? '<p>' . esc_html($html) . '</p>' : $html;
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>' . esc_html(get_bloginfo('name')) . '</title></head><body style="font:17px/1.5 Roboto,Arial,sans-serif;background:#f6f6f6;margin:0"><div style="max-width:560px;margin:40px auto;background:#fff;border-radius:14px;padding:28px;border-top:6px solid #ffda26"><h2 style="margin-top:0">' . esc_html(get_bloginfo('name')) . '</h2>' . $body . '</div></body></html>'; // phpcs:ignore
    exit;
}

/* ============================================================
 * 9. CRON: istek upita, podsjetnici, otkaz bez kapare, poruke gostu
 * ============================================================ */
add_action('abu_cron', 'abu_cron_run');
function abu_cron_run() {
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    $now = current_time('mysql');
    $ts = current_time('timestamp');

    // 1) iCal sinkronizacija svih objekata s linkovima
    $ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_abu_ical_uvoz' AND meta_value<>''");
    foreach ($ids as $pid) { abu_ical_sync_objekt((int) $pid); }

    // 2) podsjetnik adminima: novi upit bez odgovora nakon X sati
    $h = intval(abu_opt('podsjetnik_sati', 3));
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE status='novi' AND podsjetnik_admin=0 AND kreiran < %s", gmdate('Y-m-d H:i:s', $ts - $h * HOUR_IN_SECONDS)));
    foreach ($rows as $u) {
        $o = get_post($u->objekt_id);
        $txt = "Upit #{$u->id} ({$o->post_title}, " . abu_dat($u->dolazak) . ") čeka odgovor već {$h} h. Vlasnik: " . abu_m($u->objekt_id, 'vlasnik_ime') . ' ' . abu_m($u->objekt_id, 'vlasnik_tel') . "\n\nPotvrdi: " . abu_action_url($u, 'potvrdi') . "\nOdbij: " . abu_action_url($u, 'odbij');
        foreach (abu_admin_emails() as $to) { abu_mail($to, "Bez odgovora: upit #{$u->id}", $txt); }
        foreach (abu_admin_telefoni() as $tel) { abu_sms($tel, "Upit #{$u->id} {$o->post_title} bez odgovora {$h}h"); }
        abu_set($u->id, array('podsjetnik_admin' => 1));
    }

    // 3) istek novih upita → gost dobiva alternative
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE status='novi' AND drzi_do < %s", $now));
    foreach ($rows as $u) { abu_odbij($u, true, 'istekao'); }

    // 4) podsjetnik za kaparu kad ostane 1/3 roka, pa otkaz kad rok istekne
    $rows = $wpdb->get_results("SELECT * FROM $t WHERE status='potvrden'");
    $rok_s = intval(abu_opt('kapara_sati', 24)) * HOUR_IN_SECONDS;
    foreach ($rows as $u) {
        $do = strtotime($u->kapara_do);
        $o = get_post($u->objekt_id);
        if ($do < $ts) {
            abu_set($u->id, array('status' => 'otkazan'));
            abu_mail($u->email, sprintf(abu_t('s_otkazan', $u->jezik), $o->post_title), sprintf(abu_t('b_otkazan', $u->jezik), $u->ime, $o->post_title, abu_opt('telefon_javni')));
            foreach (abu_admin_emails() as $to) { abu_mail($to, "Otkazano bez kapare #{$u->id}", "{$o->post_title}, " . abu_dat($u->dolazak) . ' — termin oslobođen.'); }
        } elseif (!$u->podsjetnik_kapara && ($do - $ts) < $rok_s / 3) {
            $rok = date_i18n('d.m.Y. H:i', $do);
            abu_mail($u->email, sprintf(abu_t('s_podsj_kap', $u->jezik), $o->post_title), sprintf(abu_t('b_podsj_kap', $u->jezik), $u->ime, $o->post_title, $rok, abu_placanje_upute($u)));
            abu_sms($u->telefon, $o->post_title . ': ' . ($u->jezik === 'bs' ? 'rok za kaparu ističe ' : 'deposit deadline ') . $rok);
            abu_set($u->id, array('podsjetnik_kapara' => 1));
        }
    }

    // 5) poruke gostu: 7 dana prije, 1 dan prije, dan nakon odlaska
    $danas = current_time('Y-m-d');
    $za7 = gmdate('Y-m-d', strtotime($danas . ' +7 days'));
    $za1 = gmdate('Y-m-d', strtotime($danas . ' +1 day'));
    $jucer = gmdate('Y-m-d', strtotime($danas . ' -1 day'));
    foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE status='placeno' AND poruka_7d=0 AND dolazak <= %s AND dolazak > %s", $za7, $za1)) as $u) {
        $o = get_post($u->objekt_id);
        abu_mail($u->email, sprintf(abu_t('s_7d', $u->jezik), $o->post_title), sprintf(abu_t('b_7d', $u->jezik), $u->ime, abu_dat($u->dolazak), $o->post_title, abu_m($u->objekt_id, 'upute'), abu_m($u->objekt_id, 'vlasnik_ime') . ' ' . abu_m($u->objekt_id, 'vlasnik_tel')));
        abu_set($u->id, array('poruka_7d' => 1));
    }
    foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE status='placeno' AND poruka_1d=0 AND dolazak = %s", $za1)) as $u) {
        $o = get_post($u->objekt_id);
        $vl = abu_m($u->objekt_id, 'vlasnik_ime') . ' ' . abu_m($u->objekt_id, 'vlasnik_tel');
        abu_sms($u->telefon, sprintf(abu_t('b_1d', $u->jezik), $o->post_title, $vl));
        abu_mail($u->email, sprintf(abu_t('s_1d', $u->jezik), $o->post_title), sprintf(abu_t('b_1d', $u->jezik), $o->post_title, $vl));
        abu_set($u->id, array('poruka_1d' => 1));
    }
    $rec = abu_opt('google_recenzija');
    if ($rec) {
        foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE status='placeno' AND poruka_recenzija=0 AND izvor='web' AND odlazak = %s", $jucer)) as $u) {
            $o = get_post($u->objekt_id);
            abu_mail($u->email, sprintf(abu_t('s_rec', $u->jezik), $o->post_title), sprintf(abu_t('b_rec', $u->jezik), $u->ime, $rec));
            abu_set($u->id, array('poruka_recenzija' => 1));
        }
    }
}

/* ============================================================
 * 10. FRONTEND: kalendar + obrazac za upit
 * ============================================================ */
add_shortcode('abu_kalendar', function ($a) {
    $a = shortcode_atts(array('id' => get_the_ID(), 'mjeseci' => 2), $a);
    $id = intval($a['id']);
    if (get_post_type($id) !== 'objekt') { return ''; }
    $zauz = array_flip(abu_zauzeti_dani($id));
    $L = abu_lang();
    $dani_n = $L === 'de' ? array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So') : ($L === 'en' ? array('Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su') : array('Po', 'Ut', 'Sr', 'Če', 'Pe', 'Su', 'Ne'));
    $o = '<div class="abu-kal" data-objekt="' . $id . '">';
    $start = strtotime(current_time('Y-m-01'));
    $danas = current_time('Y-m-d');
    for ($k = 0; $k < max(1, min(12, intval($a['mjeseci']))); $k++) {
        $m = strtotime('+' . $k . ' month', $start);
        $o .= '<div class="abu-mj"><div class="abu-mj-n">' . esc_html(date_i18n('F Y', $m)) . '</div><div class="abu-g">';
        foreach ($dani_n as $dn) { $o .= '<span class="abu-h">' . esc_html($dn) . '</span>'; }
        $pomak = (int) date('N', $m) - 1;
        for ($p = 0; $p < $pomak; $p++) { $o .= '<span></span>'; }
        $dim = (int) date('t', $m);
        for ($d = 1; $d <= $dim; $d++) {
            $ymd = date('Y-m', $m) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            $cls = $ymd < $danas ? 'abu-pr' : (isset($zauz[$ymd]) ? 'abu-z' : 'abu-s');
            $o .= '<span class="abu-d ' . $cls . '" data-d="' . $ymd . '">' . $d . '</span>';
        }
        $o .= '</div></div>';
    }
    $leg = $L === 'de' ? array('frei', 'belegt') : ($L === 'en' ? array('available', 'booked') : array('slobodno', 'zauzeto'));
    $o .= '<div class="abu-leg"><i class="abu-s"></i>' . esc_html($leg[0]) . ' <i class="abu-z"></i>' . esc_html($leg[1]) . '</div></div>';
    return abu_css() . $o;
});

add_shortcode('abu_upit', function ($a) {
    $a = shortcode_atts(array('id' => get_the_ID()), $a);
    $id = intval($a['id']);
    if (get_post_type($id) !== 'objekt') { return ''; }
    $L = abu_lang();
    $poruka = '';
    if (isset($_GET['abu_ok']) && intval($_GET['abu_ok']) === $id) {
        $poruka = '<div class="abu-ok">' . esc_html(sprintf(abu_t('ok', $L), intval(abu_opt('drzi_sati', 12)))) . '</div>';
    } elseif (isset($_GET['abu_err'])) {
        $poruka = '<div class="abu-err">' . esc_html(urldecode(sanitize_text_field(wp_unslash($_GET['abu_err'])))) . '</div>';
    }
    $kap = intval(abu_m($id, 'kapacitet'));
    $o = $poruka . '<form class="abu-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">'
        . wp_nonce_field('abu_upit_' . $id, 'abu_n', true, false)
        . '<input type="hidden" name="action" value="abu_upit"><input type="hidden" name="objekt" value="' . $id . '"><input type="hidden" name="lang" value="' . esc_attr($L) . '">'
        . '<input type="hidden" name="nazad" value="' . esc_url(get_permalink($id)) . '">'
        . '<div class="abu-r"><label>' . esc_html(abu_t('f_dol', $L)) . '<input type="date" name="dolazak" required min="' . esc_attr(current_time('Y-m-d')) . '"></label>'
        . '<label>' . esc_html(abu_t('f_odl', $L)) . '<input type="date" name="odlazak" required min="' . esc_attr(current_time('Y-m-d')) . '"></label>'
        . '<label>' . esc_html(abu_t('f_osobe', $L)) . '<input type="number" name="osobe" min="1" max="' . ($kap ? $kap : 50) . '" value="2" required></label></div>'
        . '<label>' . esc_html(abu_t('f_ime', $L)) . '<input name="ime" required maxlength="120" autocomplete="name"></label>'
        . '<div class="abu-r"><label>' . esc_html(abu_t('f_email', $L)) . '<input type="email" name="email" required maxlength="180" autocomplete="email"></label>'
        . '<label>' . esc_html(abu_t('f_tel', $L)) . '<input type="tel" name="telefon" required maxlength="40" autocomplete="tel"></label></div>'
        . '<label>' . esc_html(abu_t('f_poruka', $L)) . '<textarea name="poruka" rows="3" maxlength="2000"></textarea></label>'
        . '<label class="abu-trap" aria-hidden="true">Web<input name="web" tabindex="-1" autocomplete="off"></label>'
        . '<label class="abu-c"><input type="checkbox" name="uvjeti" value="1" required> ' . esc_html(abu_t('f_uvjeti', $L)) . ' <small>(' . esc_html(abu_opt('uvjeti_otkaza', abu_defaults()['uvjeti_otkaza'])) . ')</small></label>'
        . '<button type="submit" class="btn">' . esc_html(abu_t('f_posalji', $L)) . '</button></form>';
    return abu_css() . $o;
});

add_action('admin_post_nopriv_abu_upit', 'abu_handle_upit');
add_action('admin_post_abu_upit', 'abu_handle_upit');
function abu_handle_upit() {
    $id = isset($_POST['objekt']) ? intval($_POST['objekt']) : 0;
    $L = isset($_POST['lang']) ? substr(sanitize_key(wp_unslash($_POST['lang'])), 0, 2) : 'bs';
    $nazad = isset($_POST['nazad']) ? esc_url_raw(wp_unslash($_POST['nazad'])) : home_url('/');
    $fail = function ($msg) use ($nazad) { wp_safe_redirect(add_query_arg('abu_err', rawurlencode($msg), $nazad) . '#upit'); exit; };
    if (!$id || get_post_type($id) !== 'objekt' || !isset($_POST['abu_n']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abu_n'])), 'abu_upit_' . $id)) { $fail(abu_t('err_pol', $L)); }
    if (!empty($_POST['web'])) { $fail(abu_t('err_pol', $L)); }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $rk = 'abu_rl_' . md5($ip);
    if (get_transient($rk)) { $fail(abu_t('err_spam', $L)); }
    $dol = isset($_POST['dolazak']) ? sanitize_text_field(wp_unslash($_POST['dolazak'])) : '';
    $odl = isset($_POST['odlazak']) ? sanitize_text_field(wp_unslash($_POST['odlazak'])) : '';
    $ok_d = function ($s) { $d = DateTime::createFromFormat('!Y-m-d', $s); return $d && $d->format('Y-m-d') === $s; };
    if (!$ok_d($dol) || !$ok_d($odl) || $dol < current_time('Y-m-d') || $odl <= $dol) { $fail(abu_t('err_dat', $L)); }
    $noci = (int) round((strtotime($odl) - strtotime($dol)) / 86400);
    $min = max(1, intval(abu_m($id, 'min_noci')));
    if ($noci < $min) { $fail(sprintf(abu_t('err_min', $L), $min)); }
    $osobe = isset($_POST['osobe']) ? intval($_POST['osobe']) : 0;
    $kap = intval(abu_m($id, 'kapacitet'));
    if ($osobe < 1 || ($kap && $osobe > $kap)) { $fail(sprintf(abu_t('err_kap', $L), $kap)); }
    $ime = isset($_POST['ime']) ? sanitize_text_field(wp_unslash($_POST['ime'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $tel = isset($_POST['telefon']) ? sanitize_text_field(wp_unslash($_POST['telefon'])) : '';
    if (!$ime || !is_email($email) || strlen(preg_replace('/\D/', '', $tel)) < 6) { $fail(abu_t('err_pol', $L)); }
    if (empty($_POST['uvjeti'])) { $fail(abu_t('err_uvj', $L)); }
    if (abu_zauzeto($id, $dol, $odl)) { $fail(abu_t('err_zauz', $L)); }
    set_transient($rk, 1, 60);
    abu_kreiraj_upit(array(
        'objekt_id' => $id, 'dolazak' => $dol, 'odlazak' => $odl, 'osobe' => $osobe, 'ime' => $ime, 'email' => $email, 'telefon' => $tel,
        'poruka' => isset($_POST['poruka']) ? sanitize_textarea_field(wp_unslash($_POST['poruka'])) : '',
        'jezik' => in_array($L, array('de', 'en'), true) ? $L : 'bs', 'izvor' => 'web',
    ));
    wp_safe_redirect(add_query_arg('abu_ok', $id, $nazad) . '#upit');
    exit;
}

function abu_css() {
    static $done = false;
    if ($done) { return ''; }
    $done = true;
    return '<style>.abu-kal{display:flex;flex-wrap:wrap;gap:22px;margin:18px 0}.abu-mj{flex:1 1 260px;max-width:340px}.abu-mj-n{font-weight:700;margin-bottom:8px;text-transform:capitalize}.abu-g{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}.abu-g span{text-align:center;padding:7px 0;border-radius:6px;font-size:14px}.abu-h{font-size:12px!important;color:#777}.abu-s{background:#eaf6ea}.abu-z{background:#f3d6d6;color:#999;text-decoration:line-through}.abu-pr{color:#bbb}.abu-leg{width:100%;font-size:13px;color:#555}.abu-leg i{display:inline-block;width:14px;height:14px;border-radius:4px;vertical-align:-2px;margin:0 6px 0 12px}.abu-form{display:grid;gap:12px;background:#fff;padding:20px;border-radius:14px;box-shadow:0 4px 18px #0000000f}.abu-form label{display:grid;gap:4px;font-size:14px;font-weight:600}.abu-form input,.abu-form textarea{font:inherit;font-weight:400;padding:10px;border:1px solid #ccc;border-radius:6px}.abu-r{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}.abu-c{display:block!important;font-weight:400!important}.abu-c small{color:#666}.abu-trap{position:absolute;left:-9999px}.abu-form .btn{background:#ffda26;border:0;padding:13px 18px;border-radius:5px;font-weight:700;font-size:16px;cursor:pointer}.abu-ok{background:#eaf6ea;padding:14px;border-radius:10px;margin:10px 0}.abu-err{background:#fbe4e4;padding:14px;border-radius:10px;margin:10px 0}</style>';
}

/* ============================================================
 * 11. ADMIN: popis upita, ručni upit (telefon), postavke
 * ============================================================ */
add_action('admin_menu', function () {
    add_menu_page('Upiti', 'Upiti', 'edit_posts', 'abu-upiti', 'abu_admin_lista', 'dashicons-calendar-alt', 25);
    add_submenu_page('abu-upiti', 'Novi upit (telefon)', 'Novi upit (telefon)', 'edit_posts', 'abu-novi', 'abu_admin_novi');
    add_submenu_page('abu-upiti', 'Postavke upita', 'Postavke', 'manage_options', 'abu-postavke', 'abu_admin_postavke');
});

function abu_admin_lista() {
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    if (isset($_GET['abu_do'], $_GET['id'], $_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'abu_do')) {
        $u = abu_get(intval($_GET['id']));
        if ($u) { echo '<div class="notice notice-info"><p>' . esc_html(abu_izvrsi($u, sanitize_key(wp_unslash($_GET['abu_do'])))) . '</p></div>'; }
    }
    $f = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
    $sql = "SELECT * FROM $t" . ($f ? $wpdb->prepare(' WHERE status=%s', $f) : '') . ' ORDER BY id DESC LIMIT 300';
    $rows = $wpdb->get_results($sql); // phpcs:ignore
    echo '<div class="wrap"><h1>Upiti <a class="page-title-action" href="' . esc_url(admin_url('admin.php?page=abu-novi')) . '">Novi upit (telefon)</a></h1><p>';
    foreach (array('' => 'Svi', 'novi' => 'Novi', 'potvrden' => 'Čeka kaparu', 'placeno' => 'Plaćeno', 'odbijen' => 'Odbijeni', 'istekao' => 'Istekli', 'otkazan' => 'Otkazani') as $k => $n) {
        echo '<a style="margin-right:12px;' . ($f === $k ? 'font-weight:700' : '') . '" href="' . esc_url(admin_url('admin.php?page=abu-upiti&status=' . $k)) . '">' . esc_html($n) . '</a>';
    }
    echo '</p><table class="widefat striped"><thead><tr><th>#</th><th>Objekt</th><th>Datumi</th><th>Os.</th><th>Gost</th><th>Iznos / kapara</th><th>Status</th><th>Akcije</th></tr></thead><tbody>';
    foreach ($rows as $u) {
        $act = '';
        $mk = function ($a, $n) use ($u) { return '<a class="button button-small" href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=abu-upiti&abu_do=' . $a . '&id=' . $u->id), 'abu_do')) . '">' . esc_html($n) . '</a> '; };
        if ($u->status === 'novi') { $act .= $mk('potvrdi', 'Potvrdi') . $mk('odbij', 'Odbij'); }
        if ($u->status === 'potvrden') { $act .= $mk('placeno', 'Kapara plaćena') . $mk('odbij', 'Otkaži'); }
        $wa = abu_wa_link($u->telefon, 'Pozdrav ' . $u->ime . ', javljamo se vezano za vaš upit #' . $u->id . '.');
        $act .= '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url($wa) . '">WhatsApp</a>';
        echo '<tr><td>' . intval($u->id) . '</td><td>' . esc_html(get_the_title($u->objekt_id)) . '</td><td>' . esc_html(abu_dat($u->dolazak) . ' – ' . abu_dat($u->odlazak)) . '</td><td>' . intval($u->osobe) . '</td><td>' . esc_html($u->ime) . '<br><small>' . esc_html($u->telefon . ' · ' . $u->email) . '</small></td><td>' . esc_html(($u->iznos ? abu_novac($u->iznos) : '—') . ' / ' . ($u->kapara ? abu_novac($u->kapara) : '—')) . '</td><td>' . esc_html(abu_status_naziv($u->status)) . '</td><td>' . $act . '</td></tr>'; // phpcs:ignore
    }
    echo '</tbody></table></div>';
}

function abu_admin_novi() {
    if (isset($_POST['abu_novi']) && check_admin_referer('abu_novi')) {
        $d = array(
            'objekt_id' => intval($_POST['objekt']), 'dolazak' => sanitize_text_field(wp_unslash($_POST['dolazak'])), 'odlazak' => sanitize_text_field(wp_unslash($_POST['odlazak'])),
            'osobe' => intval($_POST['osobe']), 'ime' => sanitize_text_field(wp_unslash($_POST['ime'])), 'email' => sanitize_email(wp_unslash($_POST['email'])),
            'telefon' => sanitize_text_field(wp_unslash($_POST['telefon'])), 'poruka' => sanitize_textarea_field(wp_unslash($_POST['poruka'])),
            'jezik' => sanitize_key(wp_unslash($_POST['jezik'])), 'izvor' => 'telefon',
        );
        if (get_post_type($d['objekt_id']) !== 'objekt' || $d['odlazak'] <= $d['dolazak']) {
            echo '<div class="notice notice-error"><p>Provjerite objekt i datume.</p></div>';
        } elseif (abu_zauzeto($d['objekt_id'], $d['dolazak'], $d['odlazak'])) {
            echo '<div class="notice notice-error"><p>Termin je zauzet.</p></div>';
        } else {
            $nid = abu_kreiraj_upit($d);
            echo '<div class="notice notice-success"><p>Upit #' . intval($nid) . ' upisan. Vlasnik i admini su obaviješteni.</p></div>';
        }
    }
    $objekti = get_posts(array('post_type' => 'objekt', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC'));
    echo '<div class="wrap"><h1>Novi upit (telefon / WhatsApp)</h1><form method="post">';
    wp_nonce_field('abu_novi');
    echo '<table class="form-table"><tr><th>Objekt</th><td><select name="objekt">';
    foreach ($objekti as $p) { echo '<option value="' . intval($p->ID) . '">' . esc_html($p->post_title) . '</option>'; }
    echo '</select></td></tr><tr><th>Dolazak / odlazak</th><td><input type="date" name="dolazak" required> <input type="date" name="odlazak" required></td></tr>'
        . '<tr><th>Osoba</th><td><input type="number" name="osobe" min="1" value="2"></td></tr><tr><th>Ime</th><td><input class="regular-text" name="ime" required></td></tr>'
        . '<tr><th>Mobitel</th><td><input class="regular-text" name="telefon" required></td></tr><tr><th>E-mail</th><td><input class="regular-text" type="email" name="email"></td></tr>'
        . '<tr><th>Jezik gosta</th><td><select name="jezik"><option value="bs">BS</option><option value="de">DE</option><option value="en">EN</option></select></td></tr>'
        . '<tr><th>Napomena</th><td><textarea class="large-text" name="poruka" rows="3"></textarea></td></tr></table>'
        . '<p><button class="button button-primary" name="abu_novi" value="1">Upiši upit i obavijesti vlasnika</button></p></form></div>';
}

function abu_admin_postavke() {
    $def = abu_defaults();
    if (isset($_POST['abu_spremi']) && check_admin_referer('abu_postavke')) {
        $in = isset($_POST['p']) && is_array($_POST['p']) ? wp_unslash($_POST['p']) : array();
        $o = array();
        foreach ($def as $k => $v) { $o[$k] = isset($in[$k]) ? ($k === 'uvjeti_otkaza' ? sanitize_textarea_field($in[$k]) : sanitize_text_field($in[$k])) : ''; }
        update_option('abu_postavke', $o);
        echo '<div class="notice notice-success"><p>Spremljeno.</p></div>';
    }
    $lab = array(
        'admin_emails' => 'E-mailovi admina (zarezom)', 'admin_telefoni' => 'Mobiteli admina za SMS (zarezom, +387…)', 'drzi_sati' => 'Koliko sati se termin drži za upit', 'podsjetnik_sati' => 'Podsjetnik adminima nakon (sati)',
        'kapara_posto' => 'Kapara (%)', 'kapara_sati' => 'Rok za kaparu (sati)', 'iban' => 'IBAN za uplatu kapare', 'primatelj' => 'Primatelj uplate', 'link_placanja' => 'Link za plaćanje karticom (Stripe Payment Link)',
        'whatsapp' => 'WhatsApp broj (bez +)', 'telefon_javni' => 'Telefon za goste', 'google_recenzija' => 'Link za Google recenziju', 'sms_url' => 'SMS API URL (prazno = bez SMS-a)', 'sms_kljuc' => 'SMS API Authorization', 'sms_posiljatelj' => 'SMS pošiljatelj', 'uvjeti_otkaza' => 'Uvjeti otkaza',
    );
    echo '<div class="wrap"><h1>Postavke upita</h1><form method="post">';
    wp_nonce_field('abu_postavke');
    echo '<table class="form-table">';
    foreach ($lab as $k => $n) {
        $v = abu_opt($k, $def[$k]);
        echo '<tr><th>' . esc_html($n) . '</th><td>' . ($k === 'uvjeti_otkaza' ? '<textarea class="large-text" rows="3" name="p[' . esc_attr($k) . ']">' . esc_textarea($v) . '</textarea>' : '<input class="regular-text" name="p[' . esc_attr($k) . ']" value="' . esc_attr($v) . '">') . '</td></tr>';
    }
    echo '</table><p><button class="button button-primary" name="abu_spremi" value="1">Spremi</button></p></form></div>';
}

require_once __DIR__ . '/uvoz.php';
