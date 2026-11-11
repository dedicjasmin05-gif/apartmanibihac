<?php
/**
 * Mjerenje bez kolačića i bez vanjske usluge: klikovi na telefon, WhatsApp, Viber i e-mail te izvor dolaska
 * (Google, Bing, ChatGPT, Perplexity, Copilot…). Ne sprema se IP, ime ni bilo kakav identifikator posjetitelja,
 * nego samo mjesečni zbroj. Upiti i potvrđene rezervacije čitaju se iz postojećih tablica.
 * Izvještaj: Upiti → Mjerenje. Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

/** Jednokratno: brojač za 2026-11 sadržavao je automatske provjere s dana objave (11. 11.), pa se briše; mjerenje kreće od nule. */
add_action('init', function () {
    if (get_option('abu_mj_ponisteno_2026_11')) { return; }
    delete_option('abu_mjerenje_2026-11');
    update_option('abu_mj_ponisteno_2026_11', current_time('mysql'), false);
}, 50);

function abu_mj_izvori() {
    return array('google', 'bing', 'chatgpt', 'perplexity', 'copilot', 'gemini', 'claude', 'duckduckgo', 'yandex', 'facebook', 'instagram', 'booking', 'ostalo', 'direktno');
}
function abu_mj_dogadjaji() { return array('tel', 'wa', 'viber', 'mail', 'ulaz'); }

/** Je li zahtjev od bota (ne broji se). */
function abu_mj_bot() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower((string) $_SERVER['HTTP_USER_AGENT']) : '';
    return $ua === '' || (bool) preg_match('~bot|crawl|spider|slurp|preview|headless|lighthouse|python|curl|wget~', $ua);
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
    abu_mj_zabiljezi($d, $i, $p);
    wp_die('', '', array('response' => 204));
}

/** Mala skripta: izvor dolaska (samo kad posjetitelj dođe izvana) i klikovi na kontakt. Bez kolačića i spremanja u preglednik. */
add_action('wp_footer', function () {
    if (is_admin() || current_user_can('edit_posts')) { return; }
    $url = esc_url(admin_url('admin-ajax.php'));
    ?>
<script>(function(){var u="<?php echo $url; // phpcs:ignore ?>";function s(d,i){try{var f=new FormData();f.append("action","abu_mj");f.append("d",d);f.append("p",location.pathname);if(i){f.append("i",i)}if(navigator.sendBeacon){navigator.sendBeacon(u,f)}else{fetch(u,{method:"POST",body:f,keepalive:true})}}catch(e){}}
function izvor(){var q=(new URLSearchParams(location.search).get("utm_source")||"").toLowerCase(),r="";try{r=document.referrer?new URL(document.referrer).hostname.toLowerCase():""}catch(e){}if(r===location.hostname)return"";var h=q+" "+r,m=[["chatgpt","chatgpt|openai"],["perplexity","perplexity"],["copilot","copilot"],["gemini","gemini"],["claude","claude"],["bing","bing"],["google","google"],["duckduckgo","duckduckgo"],["yandex","yandex"],["facebook","facebook|fb\\.|fb$"],["instagram","instagram"],["booking","booking"]];for(var k=0;k<m.length;k++){if(new RegExp(m[k][1]).test(h))return m[k][0]}return r||q?"ostalo":"direktno"}
var i=izvor();if(i)s("ulaz",i);
document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest("a[href]");if(!a)return;var h=a.getAttribute("href")||"";var d=/^tel:/i.test(h)?"tel":/wa\.me|whatsapp\.com/i.test(h)?"wa":/^viber:/i.test(h)?"viber":/^mailto:/i.test(h)?"mail":"";if(d)s(d)},true)})();</script>
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

function abu_mj_izvjestaj() {
    $mj = array(current_time('Y-m'), gmdate('Y-m', strtotime(current_time('Y-m') . '-01 -1 month')), gmdate('Y-m', strtotime(current_time('Y-m') . '-01 -2 month')));
    $z = array(); $u = array();
    foreach ($mj as $m) { $z[$m] = (array) get_option('abu_mjerenje_' . $m, array()); $u[$m] = abu_mj_upiti($m); }
    $g = function ($m, $k) use ($z) { return isset($z[$m][$k]) ? (int) $z[$m][$k] : 0; };
    $red = function ($naziv, $fn) use ($mj) { echo '<tr><th style="text-align:left">' . esc_html($naziv) . '</th>'; foreach ($mj as $m) { echo '<td>' . (int) $fn($m) . '</td>'; } echo '</tr>'; };
    echo '<div class="wrap"><h1>Mjerenje</h1><p>Zbroj po mjesecu, bez kolačića i bez osobnih podataka. Prijavljeni urednici i botovi se ne broje. <b>Upit</b> je poslan obrazac; <b>potvrđeno</b> su upiti koje je vlasnik potvrdio.</p>';
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
    foreach (abu_mj_izvori() as $i) { $red(ucfirst($i), function ($m) use ($g, $i) { return $g($m, 'i:' . $i); }); }
    echo '</tbody></table>';
    $p = array();
    foreach ($z[$mj[0]] as $k => $n) { if (strpos($k, 'p:') === 0) { $p[substr($k, 2)] = $n; } }
    arsort($p);
    echo '<h2>Stranice s najviše klikova na kontakt (' . esc_html($mj[0]) . ')</h2><table class="widefat striped" style="max-width:760px"><tbody>';
    foreach (array_slice($p, 0, 20, true) as $put => $n) { echo '<tr><td><a href="' . esc_url(home_url($put)) . '" target="_blank">' . esc_html($put) . '</a></td><td>' . (int) $n . '</td></tr>'; }
    if (!$p) { echo '<tr><td>Još nema podataka.</td></tr>'; }
    echo '</tbody></table></div>';
}
