<?php
/** Jednokratni uvoz sadržaja, obrazac "Prijavite objekt" i llms.txt. */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/sadrzaj-objekti.php';
require_once __DIR__ . '/sadrzaj-stranice.php';
require_once __DIR__ . '/jezici.php';

define('ABU_SEED', '2026-11-01b');

add_action('init', function () {
    if (get_option('abu_seed') === ABU_SEED) { return; }
    if (get_transient('abu_seed_lock')) { return; }
    set_transient('abu_seed_lock', 1, 120);
    abu_seed_run();
    update_option('abu_seed', ABU_SEED);
    delete_transient('abu_seed_lock');
}, 30);

function abu_seed_post_by_slug($slug, $type) {
    $p = get_posts(array('name' => $slug, 'post_type' => $type, 'post_status' => array('publish', 'draft', 'pending', 'private'), 'numberposts' => 1));
    return $p ? $p[0] : null;
}

function abu_seed_run() {
    // Osnovne postavke (samo ako su još tvorničke)
    if (get_option('blogname') === 'apartmanibihac.com' || get_option('blogname') === 'My WordPress Website') {
        update_option('blogname', 'Apartmani i vikendice Bihać');
    }
    update_option('blogdescription', 'Smještaj u Bihaću i uz rijeku Unu');
    if (!get_option('timezone_string')) { update_option('timezone_string', 'Europe/Sarajevo'); }
    update_option('date_format', 'j. n. Y.');
    update_option('time_format', 'H:i');
    update_option('start_of_week', 1);
    if (get_option('permalink_structure') === '' || get_option('permalink_structure') === '/%postname%/') {
        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/'); // kao na starom sajtu
    }
    if (!get_option('abu_postavke')) {
        $d = abu_defaults();
        $d['admin_emails'] = get_option('admin_email');
        update_option('abu_postavke', $d);
    }

    // Objekti
    foreach (abu_seed_objekti() as $slug => $o) {
        if (abu_seed_post_by_slug($slug, 'objekt')) { continue; }
        $id = wp_insert_post(array('post_type' => 'objekt', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $o[0],
            'post_excerpt' => $o[5][0], 'post_content' => abu_seed_objekt_html($o)), true);
        if (is_wp_error($id)) { continue; }
        update_post_meta($id, '_abu_mjesto', $o[1]);
        update_post_meta($id, '_abu_kapacitet', $o[3]);
        update_post_meta($id, '_abu_grupa', $o[4]);
        update_post_meta($id, '_abu_min_noci', $o[8]);
        update_post_meta($id, '_abu_ical_key', wp_generate_password(20, false));
        if (taxonomy_exists('oprema')) { wp_set_object_terms($id, $o[7], 'oprema'); }
        else { update_post_meta($id, '_abu_oprema_cekanje', $o[7]); }
    }

    // Stranice
    $ids = array();
    foreach (abu_seed_stranice() as $slug => $s) {
        $p = abu_seed_post_by_slug($slug, 'page');
        if ($p) { $ids[$slug] = $p->ID; continue; }
        $ids[$slug] = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $s[0], 'post_excerpt' => $s[1], 'post_content' => $s[2]));
    }
    foreach (abu_seed_jezici() as $slug => $s) {
        if (abu_seed_post_by_slug($slug, 'page')) { continue; }
        wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $s[0], 'post_excerpt' => $s[1], 'post_content' => $s[2]));
    }
    $poc = abu_seed_post_by_slug('pocetna', 'page');
    $poc_id = $poc ? $poc->ID : wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'pocetna', 'post_title' => 'Početna', 'post_content' => ''));
    update_option('show_on_front', 'page');
    update_option('page_on_front', $poc_id);
    if (!empty($ids['vodic'])) { update_option('page_for_posts', $ids['vodic']); }

    // Tvornički sadržaj u smeće (može se vratiti)
    foreach (array(array('hello-world', 'post'), array('sample-page', 'page'), array('primjer-stranice', 'page'), array('pozdrav-svijete', 'post')) as $d) {
        $p = abu_seed_post_by_slug($d[0], $d[1]);
        if ($p) { wp_trash_post($p->ID); }
    }

    // Blog
    $cat = term_exists('Vodič', 'category');
    $cat_id = $cat ? (int) $cat['term_id'] : (int) wp_insert_term('Vodič', 'category', array('slug' => 'vodic-kategorija'))['term_id'];
    foreach (abu_seed_clanci() as $slug => $c) {
        if (abu_seed_post_by_slug($slug, 'post')) { continue; }
        wp_insert_post(array('post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $c[0], 'post_excerpt' => $c[1], 'post_content' => $c[2], 'post_category' => array($cat_id)));
    }
    flush_rewrite_rules(false);
}

// Oprema koja je čekala da tema registrira taksonomiju
add_action('init', function () {
    if (!taxonomy_exists('oprema')) { return; }
    $c = get_posts(array('post_type' => 'objekt', 'meta_key' => '_abu_oprema_cekanje', 'numberposts' => 50, 'fields' => 'ids'));
    foreach ($c as $id) { wp_set_object_terms($id, (array) get_post_meta($id, '_abu_oprema_cekanje', true), 'oprema'); delete_post_meta($id, '_abu_oprema_cekanje'); }
}, 40);

/* ---------- Obrazac "Prijavite svoj objekt" ---------- */
add_shortcode('abu_prijava', function () {
    $ok = isset($_GET['prijava']) && $_GET['prijava'] === 'ok';
    $err = isset($_GET['prijava']) && $_GET['prijava'] === 'greska';
    ob_start();
    if ($ok) { echo '<div class="abu-ok">Hvala! Prijava je stigla. Javit ćemo vam se u roku od 24 sata.</div>'; }
    if ($err) { echo '<div class="abu-err">Provjerite ime, mobitel, e-mail i naziv objekta pa pošaljite ponovo.</div>'; } ?>
    <form class="abu-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <?php wp_nonce_field('abu_prijava', 'abu_pn'); ?>
      <input type="hidden" name="action" value="abu_prijava">
      <label>Naziv objekta<input name="naziv" required maxlength="120" placeholder="npr. Vikendica Una"></label>
      <div class="abu-r">
        <label>Mjesto<select name="mjesto"><option>Bihać</option><option>Lohovo</option><option>Pritoka</option><option>Grmuša</option><option>Bihać i okolica</option><option>Drugo</option></select></label>
        <label>Kapacitet (osoba)<input type="number" name="kapacitet" min="1" max="60" required></label>
        <label>Cijena po noći (€)<input type="number" name="cijena" min="0" max="2000"></label>
      </div>
      <label>Opis i oprema (bazen, izlaz na Unu, klima, parking…)<textarea name="opis" rows="4" maxlength="4000"></textarea></label>
      <label>iCal link s Bookinga ili Airbnba (ako ga imate)<input type="url" name="ical" maxlength="500" placeholder="https://admin.booking.com/…/ical…"></label>
      <label>Fotografije (do 10, JPG/PNG)<input type="file" name="slike[]" accept="image/jpeg,image/png,image/webp" multiple></label>
      <div class="abu-r">
        <label>Vaše ime i prezime<input name="ime" required maxlength="120" autocomplete="name"></label>
        <label>Mobitel (WhatsApp/Viber)<input type="tel" name="tel" required maxlength="40" autocomplete="tel"></label>
        <label>E-mail<input type="email" name="email" required maxlength="180" autocomplete="email"></label>
      </div>
      <label class="abu-trap" aria-hidden="true">Web<input name="web" tabindex="-1" autocomplete="off"></label>
      <button class="btn" type="submit">Pošalji prijavu</button>
    </form>
    <?php return abu_css() . ob_get_clean();
});
add_action('admin_post_nopriv_abu_prijava', 'abu_prijava_submit');
add_action('admin_post_abu_prijava', 'abu_prijava_submit');
function abu_prijava_submit() {
    $back = home_url('/prijavite-objekt/');
    if (!isset($_POST['abu_pn']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abu_pn'])), 'abu_prijava') || !empty($_POST['web'])) { wp_safe_redirect(add_query_arg('prijava', 'greska', $back)); exit; }
    $g = function ($k) { return isset($_POST[$k]) ? sanitize_text_field(wp_unslash($_POST[$k])) : ''; };
    $naziv = $g('naziv'); $ime = $g('ime'); $tel = $g('tel'); $email = sanitize_email($g('email'));
    if (!$naziv || !$ime || !$tel || !is_email($email)) { wp_safe_redirect(add_query_arg('prijava', 'greska', $back)); exit; }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    if (get_transient('abu_pr_' . md5($ip))) { wp_safe_redirect(add_query_arg('prijava', 'ok', $back)); exit; }
    set_transient('abu_pr_' . md5($ip), 1, 300);
    $opis = isset($_POST['opis']) ? sanitize_textarea_field(wp_unslash($_POST['opis'])) : '';
    $id = wp_insert_post(array('post_type' => 'objekt', 'post_status' => 'draft', 'post_title' => $naziv, 'post_content' => wpautop(esc_html($opis))));
    if (!is_wp_error($id)) {
        update_post_meta($id, '_abu_mjesto', $g('mjesto'));
        update_post_meta($id, '_abu_kapacitet', (int) $g('kapacitet'));
        update_post_meta($id, '_abu_cijena', (int) $g('cijena'));
        update_post_meta($id, '_abu_grupa', 'posrednicki');
        update_post_meta($id, '_abu_vlasnik_ime', $ime);
        update_post_meta($id, '_abu_vlasnik_tel', $tel);
        update_post_meta($id, '_abu_vlasnik_email', $email);
        update_post_meta($id, '_abu_min_noci', 1);
        $ical = isset($_POST['ical']) ? esc_url_raw(wp_unslash($_POST['ical'])) : '';
        if ($ical) { update_post_meta($id, '_abu_ical_uvoz', $ical); }
        update_post_meta($id, '_abu_ical_key', wp_generate_password(20, false));
        if (!empty($_FILES['slike']['name'][0])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $f = $_FILES['slike']; // phpcs:ignore
            $n = min(10, count($f['name']));
            for ($i = 0; $i < $n; $i++) {
                if ($f['error'][$i] !== UPLOAD_ERR_OK || $f['size'][$i] > 12 * MB_IN_BYTES) { continue; }
                $type = wp_check_filetype_and_ext($f['tmp_name'][$i], $f['name'][$i]);
                if (!in_array($type['type'], array('image/jpeg', 'image/png', 'image/webp'), true)) { continue; }
                $_FILES['abu_one'] = array('name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]);
                $att = media_handle_upload('abu_one', $id);
                if (!is_wp_error($att) && !has_post_thumbnail($id)) { set_post_thumbnail($id, $att); }
            }
        }
        $link = admin_url('post.php?post=' . $id . '&action=edit');
        foreach (abu_admin_emails() as $to) {
            abu_mail($to, 'Nova prijava objekta: ' . $naziv, "Vlasnik: $ime\nMobitel: $tel\nE-mail: $email\nMjesto: " . $g('mjesto') . "\nKapacitet: " . $g('kapacitet') . "\n\nPregledaj i objavi:\n$link");
        }
        foreach (abu_admin_telefoni() as $t) { abu_sms($t, 'Nova prijava objekta: ' . $naziv . ', ' . $ime . ' ' . $tel); }
    }
    wp_safe_redirect(add_query_arg('prijava', 'ok', $back));
    exit;
}

/* ---------- llms.txt za AI pretraživače ---------- */
add_action('init', function () {
    $path = isset($_SERVER['REQUEST_URI']) ? strtok(wp_unslash($_SERVER['REQUEST_URI']), '?') : '';
    if ($path !== '/llms.txt') { return; }
    header('Content-Type: text/plain; charset=utf-8');
    $o = "# Apartmani i vikendice Bihać (apartmanibihac.com)\n\n> Smještaj u Bihaću i uz rijeku Unu, Bosna i Hercegovina: apartmani u gradu i kuće za odmor u Lohovu, Pritoci i Grmuši. Cijene od 25 do 100 € po noći za cijeli objekt. Direktan upit, vlasnik potvrđuje u roku od 12 sati, kapara 30 %. Kontakt: +387 61 591 320 (WhatsApp/Viber).\n\n## Smještaj\n";
    foreach (get_posts(array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC')) as $p) {
        $o .= '- [' . $p->post_title . '](' . get_permalink($p) . '): ' . get_post_meta($p->ID, '_abu_mjesto', true) . ', do ' . (int) get_post_meta($p->ID, '_abu_kapacitet', true) . ' osoba. ' . wp_strip_all_tags($p->post_excerpt) . "\n";
    }
    $o .= "\n## Vodiči\n";
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 100)) as $p) {
        $o .= '- [' . $p->post_title . '](' . get_permalink($p) . '): ' . wp_strip_all_tags(get_the_excerpt($p)) . "\n";
    }
    $o .= "\n## Stranice\n";
    foreach (array('rafting-na-uni', 'restorani-bihac', 'nocni-zivot-bihac', 'uvjeti-i-privatnost', 'prijavite-objekt') as $s) {
        $p = get_page_by_path($s);
        if ($p) { $o .= '- [' . $p->post_title . '](' . get_permalink($p) . '): ' . wp_strip_all_tags($p->post_excerpt) . "\n"; }
    }
    echo $o; // phpcs:ignore
    exit;
}, 1);
