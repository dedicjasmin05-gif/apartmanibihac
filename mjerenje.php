<?php
/**
 * Mjerenje bez kolačića i bez vanjske usluge: klikovi na telefon, WhatsApp, Viber i e-mail, izvor dolaska
 * (Google, Google Ads, Facebook, Instagram, ChatGPT…) i kampanje (utm_campaign) s troškom koji se unese ručno,
 * pa se vidi cijena po dolasku, po kontaktu i po upitu. Ne sprema se IP, ime ni bilo kakav trajni identifikator:
 * za odbacivanje ponovljenih dolazaka koristi se kratkotrajni sažetak (30 min), a u preglednik se sprema samo
 * naziv kampanje za tu karticu (sessionStorage). Izvještaj: Upiti → Mjerenje. Povrat: ukloniti require_once.
 */
if (!defined('ABSPATH')) { exit; }

/** Jednokratno: brojač za 2026-11 sadržavao je automatske provjere s dana objave (11. 11.), pa se briše; mjerenje kreće od nule. */
add_action('init', function () {
    if (get_option('abu_mj_ponisteno_2026_11')) { return; }
    delete_option('abu_mjerenje_2026-11');
    update_option('abu_mj_ponisteno_2026_11', current_time('mysql'), false);
}, 50);

/** Od ovog trenutka dolasci se broje s filtrom botova i ponavljanja (za izvještaj). */
add_action('init', function () {
    if (!get_option('abu_mj_v2_od')) { update_option('abu_mj_v2_od', current_time('mysql'), false); }
}, 51);

function abu_mj_izvori() {
    return array('google', 'google_ads', 'bing', 'chatgpt', 'perplexity', 'copilot', 'gemini', 'claude', 'duckduckgo', 'yandex', 'facebook', 'instagram', 'booking', 'ostalo', 'direktno');
}
function abu_mj_dogadjaji() { return array('tel', 'wa', 'viber', 'mail', 'ulaz'); }
function abu_mj_kanali() { return array('facebook' => 'Facebook / Instagram', 'google_ads' => 'Google Ads', 'ostalo' => 'Ostalo'); }

/** Je li zahtjev od bota (ne broji se). */
function abu_mj_bot() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower((string) $_SERVER['HTTP_USER_AGENT']) : '';
    return $ua === '' || (bool) preg_match('~bot|crawl|spider|slurp|preview|headless|lighthouse|python|curl|wget|externalhit|monitor|uptime|pingdom|phantom|selenium|puppeteer|playwright~', $ua);
}

/** Naziv kampanje: mala slova, brojevi, crtica i podvlaka, najviše 60 znakova. */
function abu_mj_k($k) {
    $k = strtolower(trim((string) $k));
    $k = preg_replace('~[^a-z0-9_-]+~', '-', $k);
    return substr(trim($k, '-'), 0, 60);
}

/**
 * Je li ovo ponovljeni dolazak istog preglednika u zadnjih 30 minuta. Ključ je sažetak (IP + preglednik + dan + tajna),
 * vrijedi 30 minuta i ne može se vratiti u IP.
 */
function abu_mj_ponovljeno() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
    $h = 'abu_mj_v_' . substr(hash_hmac('sha256', $ip . '|' . $ua . '|' . gmdate('Y-m-d'), wp_salt('nonce')), 0, 24);
    if (get_transient($h)) { return true; }
    set_transient($h, 1, 30 * MINUTE_IN_SECONDS);
    return false;
}

/** Dodaj događaj u zbroj tekućeg mjeseca. */
function abu_mj_zabiljezi($d, $izvor = '', $put = '') {
    if (!in_array($d, abu_mj_dogadjaji(), true)) { return false; }
    $k = 'abu_mjerenje_' . current_time('Y-m');
    $z = get_option($k, array());
    if (!is_array($z)) { $z = array(); }
    $z['d:' . $d] = (isset($z['d:' . $d]) ? $z['d:' . $d] : 0) + 1;
    if ($d === 'ulaz' && in_array($izvor, abu_mj_izvori(), true)) { $z['i:' . $izvor] = (isset($z['i:' . $izvor]) ? $z['i:' . $izvor] : 0) + 1; }
    if ($d !== 'ulaz' && $put !== '') {
        $pk = 'p:' . $put;
        $broj_p = count(preg_grep('~^p:~', array_keys($z)));
        if (isset($z[$pk]) || $broj_p < 500) { $z[$pk] = (isset($z[$pk]) ? $z[$pk] : 0) + 1; }
    }
    update_option($k, $z, false);
    return true;
}

/** Brojač po kampanji: ulaz (dolazak), kontakt (klik na telefon/WhatsApp/Viber/e-mail), upit (poslan obrazac). */
function abu_mj_kampanja_zabiljezi($kamp, $vrsta, $izvor = '') {
    $kamp = abu_mj_k($kamp);
    if ($kamp === '' || !in_array($vrsta, array('ulaz', 'kontakt', 'upit'), true)) { return false; }
    $sve = get_option('abu_mj_kampanje', array());
    if (!is_array($sve)) { $sve = array(); }
    if (!isset($sve[$kamp])) {
        if (count($sve) >= 300) { return false; }
        $sve[$kamp] = array('ulaz' => 0, 'kontakt' => 0, 'upit' => 0, 'prvi' => current_time('Y-m-d'), 'zadnji' => '', 'izvor' => '', 'trosak' => 0, 'kanal' => '', 'naziv' => '');
    }
    $sve[$kamp][$vrsta] = (int) $sve[$kamp][$vrsta] + 1;
    $sve[$kamp]['zadnji'] = current_time('Y-m-d');
    if ($izvor !== '' && empty($sve[$kamp]['izvor']) && in_array($izvor, abu_mj_izvori(), true)) { $sve[$kamp]['izvor'] = $izvor; }
    update_option('abu_mj_kampanje', $sve, false);
    return true;
}

function abu_mj_put($p) {
    $p = (string) wp_parse_url('/' . ltrim((string) $p, '/'), PHP_URL_PATH);
    $p = preg_replace('~[^a-z0-9/_-]~i', '', $p);
    return substr($p !== '' ? $p : '/', 0, 120);
}

add_action('wp_ajax_nopriv_abu_mj', 'abu_mj_primi');
add_action('wp_ajax_abu_mj', 'abu_mj_primi');
function abu_mj_primi() {
    // Prijavljeni urednici i botovi se ne broje. Vrijednosti su s popisa dopuštenih, ništa drugo se ne sprema.
    if (abu_mj_bot() || current_user_can('edit_posts')) { wp_die('', '', array('response' => 204)); }
    $d = isset($_POST['d']) ? sanitize_key(wp_unslash($_POST['d'])) : '';
    $i = isset($_POST['i']) ? sanitize_key(wp_unslash($_POST['i'])) : '';
    $p = isset($_POST['p']) ? abu_mj_put(wp_unslash($_POST['p'])) : '';
    $kamp = isset($_POST['k']) ? abu_mj_k(wp_unslash($_POST['k'])) : '';
    if ($d === 'ulaz' && abu_mj_ponovljeno()) { wp_die('', '', array('response' => 204)); }
    if (abu_mj_zabiljezi($d, $i, $p) && $kamp !== '') {
        abu_mj_kampanja_zabiljezi($kamp, $d === 'ulaz' ? 'ulaz' : 'kontakt', $i);
    }
    wp_die('', '', array('response' => 204));
}

/** Upit za smještaj s weba ili team building upit: ako je posjetitelj došao iz kampanje, pribroji upit toj kampanji. */
function abu_mj_upit_iz_kampanje() {
    if (empty($_POST['abu_k']) || current_user_can('edit_posts')) { return; }
    abu_mj_kampanja_zabiljezi(wp_unslash($_POST['abu_k']), 'upit');
}
add_action('abu_novi_upit', function ($u) { if (isset($u->izvor) && $u->izvor === 'web') { abu_mj_upit_iz_kampanje(); } });
add_action('save_post_tb_upit', function ($id, $post, $update) { if (!$update && !is_admin()) { abu_mj_upit_iz_kampanje(); } }, 10, 3);

/**
 * Mala skripta: izvor dolaska (samo kad posjetitelj dođe izvana), kampanja iz utm_campaign i klikovi na kontakt.
 * Dolazak se šalje tek kad je stranica vidljiva barem 1,5 s i ne šalje ga automatizirani preglednik.
 */
add_action('wp_footer', function () {
    if (is_admin() || current_user_can('edit_posts')) { return; }
    $url = esc_url(admin_url('admin-ajax.php'));
    ?>
<script>(function(){if(navigator.webdriver)return;var u="<?php echo $url; // phpcs:ignore ?>",Q=new URLSearchParams(location.search);
function K(){var k=(Q.get("utm_campaign")||"").toLowerCase().replace(/[^a-z0-9_-]+/g,"-").slice(0,60);try{if(k){sessionStorage.setItem("abu_k",k)}else{k=sessionStorage.getItem("abu_k")||""}}catch(e){}return k}
var k=K();function s(d,i){try{var f=new FormData();f.append("action","abu_mj");f.append("d",d);f.append("p",location.pathname);if(i){f.append("i",i)}if(k){f.append("k",k)}if(navigator.sendBeacon){navigator.sendBeacon(u,f)}else{fetch(u,{method:"POST",body:f,keepalive:true})}}catch(e){}}
function izvor(){var q=(Q.get("utm_source")||"").toLowerCase(),r="";try{r=document.referrer?new URL(document.referrer).hostname.toLowerCase():""}catch(e){}if(r===location.hostname)return"";if(Q.get("gclid")||Q.get("gbraid")||Q.get("wbraid"))return"google_ads";if(Q.get("fbclid")&&!q)return/instagram/.test(r)?"instagram":"facebook";var h=q+" "+r,m=[["chatgpt","chatgpt|openai"],["perplexity","perplexity"],["copilot","copilot"],["gemini","gemini"],["claude","claude"],["bing","bing"],["google","google"],["duckduckgo","duckduckgo"],["yandex","yandex"],["instagram","instagram|^ig$| ig$"],["facebook","facebook|fb\\.|fb$|^fb |meta"],["booking","booking"]];for(var x=0;x<m.length;x++){if(new RegExp(m[x][1]).test(h))return m[x][0]}return r||q?"ostalo":"direktno"}
var i=izvor();if(i){var t=setTimeout(function(){if(document.visibilityState==="visible")s("ulaz",i)},1500);document.addEventListener("visibilitychange",function(){if(document.visibilityState!=="visible")clearTimeout(t)})}
document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest("a[href]");if(!a)return;var h=a.getAttribute("href")||"";var d=/^tel:/i.test(h)?"tel":/wa\.me|whatsapp\.com/i.test(h)?"wa":/^viber:/i.test(h)?"viber":/^mailto:/i.test(h)?"mail":"";if(d)s(d)},true);
document.addEventListener("submit",function(e){var f=e.target;if(!k||!f||!f.action||f.action.indexOf("admin-post.php")<0||f.querySelector("input[name=abu_k]"))return;var x=document.createElement("input");x.type="hidden";x.name="abu_k";x.value=k;f.appendChild(x)},true)})();</script>
    <?php
}, 99);

/* ---------- Izvještaj u adminu ---------- */
add_action('admin_menu', function () {
    add_submenu_page('abu-upiti', 'Mjerenje', 'Mjerenje', 'edit_posts', 'abu-mjerenje', 'abu_mj_izvjestaj');
}, 20);

/** Upiti s weba, telefonski upiti, potvrđene rezervacije i team building upiti po mjesecu (iz postojećih tablica). */
function abu_mj_upiti($mj) {
    global $wpdb;
    $t = $wpdb->prefix . ABU_TABLE;
    $od = $mj . '-01 00:00:00';
    $do = gmdate('Y-m-d H:i:s', strtotime($od . ' +1 month'));
    $r = $wpdb->get_results($wpdb->prepare("SELECT izvor, status, COUNT(*) n FROM $t WHERE kreiran >= %s AND kreiran < %s GROUP BY izvor, status", $od, $do)); // phpcs:ignore
    $o = array('web' => 0, 'telefon' => 0, 'potvrdeno' => 0, 'tb' => 0);
    foreach ((array) $r as $x) {
        if ($x->izvor === 'web') { $o['web'] += $x->n; } else { $o['telefon'] += $x->n; }
        if (in_array($x->status, array('potvrden', 'placeno'), true)) { $o['potvrdeno'] += $x->n; }
    }
    if (post_type_exists('tb_upit')) {
        $o['tb'] = count(get_posts(array('post_type' => 'tb_upit', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids',
            'date_query' => array(array('after' => $od, 'before' => $do, 'inclusive' => true)))));
    }
    return $o;
}

/** Cijena po jedinici (trošak / broj), ili crtica. */
function abu_mj_po($trosak, $n) { return ($trosak > 0 && $n > 0) ? number_format($trosak / $n, 2, ',', '.') . ' €' : '–'; }

/** Spremanje troška, naziva i kanala kampanje te nove kampanje (samo administrator). */
function abu_mj_spremi_kampanje() {
    if (empty($_POST['abu_mj_kamp']) || !current_user_can('manage_options') || !check_admin_referer('abu_mj_kamp')) { return ''; }
    $sve = get_option('abu_mj_kampanje', array());
    if (!is_array($sve)) { $sve = array(); }
    $in = isset($_POST['kp']) && is_array($_POST['kp']) ? wp_unslash($_POST['kp']) : array();
    foreach ($in as $kamp => $v) {
        $kamp = abu_mj_k($kamp);
        if (!isset($sve[$kamp]) || !is_array($v)) { continue; }
        if (!empty($v['obrisi'])) { unset($sve[$kamp]); continue; }
        $sve[$kamp]['trosak'] = max(0, round((float) str_replace(',', '.', (string) ($v['trosak'] ?? 0)), 2));
        $sve[$kamp]['naziv'] = sanitize_text_field((string) ($v['naziv'] ?? ''));
        $sve[$kamp]['kanal'] = array_key_exists((string) ($v['kanal'] ?? ''), abu_mj_kanali()) ? (string) $v['kanal'] : '';
    }
    $nova = isset($_POST['nova_k']) ? abu_mj_k(wp_unslash($_POST['nova_k'])) : '';
    if ($nova !== '' && !isset($sve[$nova]) && count($sve) < 300) {
        $kanal = isset($_POST['nova_kanal']) ? sanitize_key(wp_unslash($_POST['nova_kanal'])) : '';
        $sve[$nova] = array('ulaz' => 0, 'kontakt' => 0, 'upit' => 0, 'prvi' => current_time('Y-m-d'), 'zadnji' => '', 'izvor' => '',
            'trosak' => isset($_POST['nova_trosak']) ? max(0, round((float) str_replace(',', '.', sanitize_text_field(wp_unslash($_POST['nova_trosak']))), 2)) : 0,
            'kanal' => array_key_exists($kanal, abu_mj_kanali()) ? $kanal : '',
            'naziv' => isset($_POST['nova_naziv']) ? sanitize_text_field(wp_unslash($_POST['nova_naziv'])) : '');
    }
    update_option('abu_mj_kampanje', $sve, false);
    return 'Spremljeno.';
}

/** Link za kampanju s UTM oznakama. */
function abu_mj_link($kamp, $kanal, $put = '/') {
    $src = $kanal === 'google_ads' ? 'google' : ($kanal === 'facebook' ? 'facebook' : 'kampanja');
    $med = $kanal === 'google_ads' ? 'cpc' : 'paid';
    return add_query_arg(array('utm_source' => $src, 'utm_medium' => $med, 'utm_campaign' => $kamp), home_url($put));
}

function abu_mj_izvjestaj() {
    $poruka = abu_mj_spremi_kampanje();
    $mj = array(current_time('Y-m'), gmdate('Y-m', strtotime(current_time('Y-m') . '-01 -1 month')), gmdate('Y-m', strtotime(current_time('Y-m') . '-01 -2 month')));
    $z = array(); $u = array();
    foreach ($mj as $m) { $z[$m] = (array) get_option('abu_mjerenje_' . $m, array()); $u[$m] = abu_mj_upiti($m); }
    $g = function ($m, $k) use ($z) { return isset($z[$m][$k]) ? (int) $z[$m][$k] : 0; };
    $red = function ($naziv, $fn) use ($mj) { echo '<tr><th style="text-align:left">' . esc_html($naziv) . '</th>'; foreach ($mj as $m) { echo '<td>' . (int) $fn($m) . '</td>'; } echo '</tr>'; };
    echo '<div class="wrap"><h1>Mjerenje</h1>';
    if ($poruka) { echo '<div class="notice notice-success"><p>' . esc_html($poruka) . '</p></div>'; }

    /* Kampanje */
    $sve = get_option('abu_mj_kampanje', array());
    if (!is_array($sve)) { $sve = array(); }
    uasort($sve, function ($a, $b) { return strcmp((string) $b['zadnji'], (string) $a['zadnji']); });
    echo '<h2>Kampanje: koliko je došlo ljudi za uloženi novac</h2>';
    echo '<p>Svaki plaćeni oglas (Facebook boost, Google Ads…) neka vodi na link s oznakom kampanje. Upiši koliko si platio, a ovdje se vidi cijena po dolasku, po kontaktu i po upitu. '
        . 'Automatske objave na Facebooku same dobivaju oznaku <code>fb-ID</code>, pa kad boostaš takvu objavu, samo upiši trošak u njen red. '
        . '<b>Dolazak</b> = posjetitelj otvorio stranicu (isti preglednik broji se jednom u 30 min). <b>Kontakt</b> = klik na telefon, WhatsApp, Viber ili e-mail. <b>Upit</b> = poslan obrazac.</p>';
    echo '<form method="post">';
    wp_nonce_field('abu_mj_kamp');
    echo '<input type="hidden" name="abu_mj_kamp" value="1"><table class="widefat striped" style="max-width:1100px"><thead><tr>'
        . '<th>Kampanja</th><th>Opis</th><th>Kanal</th><th>Trošak €</th><th>Dolasci</th><th>Kontakti</th><th>Upiti</th><th>€ / dolazak</th><th>€ / kontakt</th><th>€ / upit</th><th>Aktivna</th><th>Obriši</th></tr></thead><tbody>';
    $ukT = 0; $ukU = 0; $ukK = 0; $ukQ = 0;
    foreach ($sve as $kamp => $c) {
        $tr = (float) $c['trosak']; $ukT += $tr; $ukU += (int) $c['ulaz']; $ukK += (int) $c['kontakt']; $ukQ += (int) $c['upit'];
        $kanal = $c['kanal'] !== '' ? $c['kanal'] : ($c['izvor'] === 'google_ads' ? 'google_ads' : (in_array($c['izvor'], array('facebook', 'instagram'), true) || strpos($kamp, 'fb-') === 0 ? 'facebook' : 'ostalo'));
        $n = 'kp[' . esc_attr($kamp) . ']';
        echo '<tr><td><code>' . esc_html($kamp) . '</code><br><small><a href="' . esc_url(abu_mj_link($kamp, $kanal)) . '" target="_blank">link</a></small></td>'
            . '<td><input name="' . $n . '[naziv]" value="' . esc_attr($c['naziv']) . '" style="width:160px"></td><td><select name="' . $n . '[kanal]">';
        foreach (abu_mj_kanali() as $kk => $kn) { echo '<option value="' . esc_attr($kk) . '"' . selected($kanal, $kk, false) . '>' . esc_html($kn) . '</option>'; }
        echo '</select></td><td><input name="' . $n . '[trosak]" value="' . esc_attr($tr ? number_format($tr, 2, ',', '') : '') . '" style="width:80px" inputmode="decimal"></td>'
            . '<td>' . (int) $c['ulaz'] . '</td><td>' . (int) $c['kontakt'] . '</td><td>' . (int) $c['upit'] . '</td>'
            . '<td>' . esc_html(abu_mj_po($tr, (int) $c['ulaz'])) . '</td><td>' . esc_html(abu_mj_po($tr, (int) $c['kontakt'])) . '</td><td>' . esc_html(abu_mj_po($tr, (int) $c['upit'])) . '</td>'
            . '<td><small>' . esc_html($c['prvi'] . ($c['zadnji'] ? ' – ' . $c['zadnji'] : '')) . '</small></td><td><input type="checkbox" name="' . $n . '[obrisi]" value="1"></td></tr>';
    }
    if (!$sve) { echo '<tr><td colspan="12">Još nema kampanja. Prva se pojavi čim netko dođe preko linka s oznakom kampanje ili preko nove automatske objave na Facebooku.</td></tr>'; }
    else {
        echo '<tr><th>Ukupno</th><td></td><td></td><td><b>' . esc_html(number_format($ukT, 2, ',', '.')) . '</b></td><td><b>' . (int) $ukU . '</b></td><td><b>' . (int) $ukK . '</b></td><td><b>' . (int) $ukQ . '</b></td>'
            . '<td>' . esc_html(abu_mj_po($ukT, $ukU)) . '</td><td>' . esc_html(abu_mj_po($ukT, $ukK)) . '</td><td>' . esc_html(abu_mj_po($ukT, $ukQ)) . '</td><td></td><td></td></tr>';
    }
    echo '</tbody></table>';
    echo '<h3>Nova kampanja (prije nego platiš oglas)</h3><p>Oznaka (npr. <code>boost-nova-godina</code>) <input name="nova_k" placeholder="boost-nova-godina" style="width:200px"> '
        . 'Opis <input name="nova_naziv" style="width:180px"> Kanal <select name="nova_kanal">';
    foreach (abu_mj_kanali() as $kk => $kn) { echo '<option value="' . esc_attr($kk) . '">' . esc_html($kn) . '</option>'; }
    echo '</select> Trošak € <input name="nova_trosak" style="width:80px" inputmode="decimal"></p><p>Nakon spremanja u redu kampanje klikni <b>link</b>, kopiraj adresu i nju stavi u oglas.</p>';
    if (current_user_can('manage_options')) { submit_button('Spremi kampanje'); }
    echo '</form>';

    /* Mjesečni zbroj */
    echo '<h2>Po mjesecima</h2><p>Zbroj po mjesecu, bez kolačića i bez osobnih podataka. Prijavljeni urednici i botovi se ne broje. <b>Upit</b> je poslan obrazac; <b>potvrđeno</b> su upiti koje je vlasnik potvrdio.</p>';
    $od = (string) get_option('abu_mj_v2_od', '');
    if ($od !== '' && substr($od, 0, 7) === $mj[0] && substr($od, 8, 2) !== '01') {
        echo '<p style="color:#8a5a00"><b>Napomena:</b> do ' . esc_html(date_i18n('j. n. Y. H:i', strtotime($od))) . ' brojali su se i automatski posjeti bez izvora (botovi), pa je „Direktno“ za ' . esc_html($mj[0]) . ' previsoko. Od tada se ponovljeni i automatski posjeti ne broje.</p>';
    }
    echo '<table class="widefat striped" style="max-width:760px"><thead><tr><th></th>'; foreach ($mj as $m) { echo '<th>' . esc_html($m) . '</th>'; } echo '</tr></thead><tbody>';
    echo '<tr><td colspan="4"><b>Upiti</b></td></tr>';
    $red('Upiti s weba (obrazac)', function ($m) use ($u) { return $u[$m]['web']; });
    $red('Upiti unijeti ručno (telefon/WhatsApp)', function ($m) use ($u) { return $u[$m]['telefon']; });
    $red('Potvrđeno (potvrđen ili plaćeno)', function ($m) use ($u) { return $u[$m]['potvrdeno']; });
    $red('Team building upiti', function ($m) use ($u) { return $u[$m]['tb']; });
    echo '<tr><td colspan="4"><b>Klikovi na kontakt</b></td></tr>';
    foreach (array('tel' => 'Telefon', 'wa' => 'WhatsApp', 'viber' => 'Viber', 'mail' => 'E-mail') as $k => $n) { $red($n, function ($m) use ($g, $k) { return $g($m, 'd:' . $k); }); }
    echo '<tr><td colspan="4"><b>Dolasci po izvoru</b></td></tr>';
    $red('Ukupno dolazaka', function ($m) use ($g) { return $g($m, 'd:ulaz'); });
    $nazivi = array('google_ads' => 'Google Ads (plaćeno)', 'chatgpt' => 'ChatGPT', 'duckduckgo' => 'DuckDuckGo');
    foreach (abu_mj_izvori() as $i) { $red(isset($nazivi[$i]) ? $nazivi[$i] : ucfirst($i), function ($m) use ($g, $i) { return $g($m, 'i:' . $i); }); }
    echo '</tbody></table>';
    $p = array();
    foreach ($z[$mj[0]] as $k => $n) { if (strpos($k, 'p:') === 0) { $p[substr($k, 2)] = $n; } }
    arsort($p);
    echo '<h2>Stranice s najviše klikova na kontakt (' . esc_html($mj[0]) . ')</h2><table class="widefat striped" style="max-width:760px"><tbody>';
    foreach (array_slice($p, 0, 20, true) as $put => $n) { echo '<tr><td><a href="' . esc_url(home_url($put)) . '" target="_blank">' . esc_html($put) . '</a></td><td>' . (int) $n . '</td></tr>'; }
    if (!$p) { echo '<tr><td>Još nema podataka.</td></tr>'; }
    echo '</tbody></table></div>';
}
