<?php
/**
 * Grafike za objave: za svaki članak web sam složi sliku s logom (fotografija + naslov + oznaka + adresa + datum).
 * - 'ig' 1080×1350 (Instagram), 'fb' 1200×630 (Facebook i og:image pri dijeljenju linka).
 * - Spremaju se u uploads/abu-grafike/{id}-{vrsta}-{hash}.jpg; nova se pravi samo kad se promijeni naslov, slika, oznaka ili datum.
 * - Fontovi (OFL) i logo su u mapi grafike/. Ako server nema GD s FreeType, vraća '' i sve radi kao prije (obična fotografija).
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_GR_VER', '4');
define('ABU_GR_TEL', '+387 61 591 320');

function abu_gr_moze() {
    return function_exists('imagecreatetruecolor') && function_exists('imagettftext') && function_exists('imagettfbbox');
}

/** Objava vezana za smještaj (ponuda, apartmani): na sliku i u tekst ide broj telefona. Obične vijesti i vodiči: bez broja. */
function abu_je_smjestaj($id) {
    if (get_post_meta($id, '_abu_dnevna_vijest', true)) { return false; }
    if (get_post_meta($id, '_abu_smjestaj', true)) { return true; }
    $slug = (string) get_post_field('post_name', $id);
    if (preg_match('/(smjestaj|apartman|vikendic|ponud|gdje-odsjesti|nocenj|rezervacij)/', $slug)) { return true; }
    foreach ((array) get_the_category($id) as $c) { if (in_array($c->slug, array('ponude', 'ponuda'), true)) { return true; } }
    return (bool) apply_filters('abu_je_smjestaj', false, $id);
}

/** Oznaka na slici: dnevna vijest → Vijesti iz turizma; inače prva rubrika; bez rubrike → Vodič. */
function abu_gr_oznaka($id) {
    if (get_post_meta($id, '_abu_dnevna_vijest', true)) { return 'Vijesti iz turizma'; }
    $k = get_the_category($id);
    foreach ((array) $k as $c) {
        if (in_array($c->slug, array('uncategorized', 'nekategorizirano', 'bez-kategorije'), true)) { continue; }
        return $c->name;
    }
    return 'Vodič';
}

/** Putanja do izvorne fotografije (istaknuta slika). */
function abu_gr_foto($id) {
    $att = get_post_thumbnail_id($id);
    if (!$att) { return array(0, ''); }
    $f = get_attached_file($att);
    return array((int) $att, ($f && is_readable($f)) ? $f : '');
}

/** URL grafike ('ig' ili 'fb') za članak; napravi je ako ne postoji. Prazno ako nije moguće. */
function abu_grafika($id, $vrsta = 'fb') {
    $id = (int) $id;
    if (!abu_gr_moze() || !in_array($vrsta, array('ig', 'fb'), true) || get_post_type($id) !== 'post') { return ''; }
    list($att, $foto) = abu_gr_foto($id);
    if (!$foto) { return ''; }
    $naslov = html_entity_decode(wp_strip_all_tags(get_the_title($id)), ENT_QUOTES, 'UTF-8');
    $oznaka = abu_gr_oznaka($id);
    $datum = get_the_date('j. n. Y.', $id);
    $tel = abu_je_smjestaj($id);
    $hash = substr(md5(ABU_GR_VER . '|' . $vrsta . '|' . $naslov . '|' . $att . '|' . $oznaka . '|' . $datum . '|' . (int) $tel), 0, 10);
    $up = wp_upload_dir();
    if (!empty($up['error'])) { return ''; }
    $dir = trailingslashit($up['basedir']) . 'abu-grafike';
    $ime = $id . '-' . $vrsta . '-' . $hash . '.jpg';
    $url = trailingslashit($up['baseurl']) . 'abu-grafike/' . $ime;
    if (is_readable($dir . '/' . $ime)) { return $url; }
    if (!wp_mkdir_p($dir)) { return ''; }
    if (get_transient('abu_gr_lock_' . $id . $vrsta)) { return ''; }
    set_transient('abu_gr_lock_' . $id . $vrsta, 1, 60);
    $ok = abu_gr_nacrtaj($vrsta, $foto, $naslov, $oznaka, $datum, $dir . '/' . $ime, $tel);
    delete_transient('abu_gr_lock_' . $id . $vrsta);
    if (!$ok) { return ''; }
    foreach ((array) glob($dir . '/' . $id . '-' . $vrsta . '-*.jpg') as $stara) { if ($stara && basename($stara) !== $ime) { @unlink($stara); } }
    return $url;
}

function abu_gr_ucitaj($f) {
    $i = @getimagesize($f);
    if (!$i) { return null; }
    switch ($i[2]) {
        case IMAGETYPE_JPEG: return @imagecreatefromjpeg($f);
        case IMAGETYPE_PNG: return @imagecreatefrompng($f);
        case IMAGETYPE_WEBP: return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($f) : null;
    }
    return null;
}

/** Fotografija „cover“ u pravokutnik (x, y, w, h). */
function abu_gr_cover($c, $src, $x, $y, $w, $h) {
    $sw = imagesx($src); $sh = imagesy($src);
    $s = max($w / $sw, $h / $sh);
    $cw = (int) round($w / $s); $ch = (int) round($h / $s);
    $cx = (int) round(($sw - $cw) / 2); $cy = (int) round(($sh - $ch) / 2);
    imagecopyresampled($c, $src, $x, $y, $cx, $cy, $w, $h, $cw, $ch);
}

/** Preliv: $tocke = [[udio 0..1, r, g, b, prozirnost 0..1 (1 = puna boja)], ...]; smjer 'v' (po redovima) ili 'h' (po stupcima). */
function abu_gr_preliv($c, $x0, $y0, $w, $h, $tocke, $smjer = 'v') {
    $n = $smjer === 'v' ? $h : $w;
    for ($i = 0; $i < $n; $i++) {
        $t = $n > 1 ? $i / ($n - 1) : 0;
        for ($k = 0; $k < count($tocke) - 1; $k++) { if ($t <= $tocke[$k + 1][0]) { break; } }
        $a = $tocke[$k]; $b = $tocke[min($k + 1, count($tocke) - 1)];
        $u = ($b[0] - $a[0]) > 0 ? ($t - $a[0]) / ($b[0] - $a[0]) : 0;
        $u = max(0, min(1, $u));
        $r = (int) round($a[1] + ($b[1] - $a[1]) * $u); $g = (int) round($a[2] + ($b[2] - $a[2]) * $u); $bb = (int) round($a[3] + ($b[3] - $a[3]) * $u);
        $al = $a[4] + ($b[4] - $a[4]) * $u;
        if ($al <= 0.004) { continue; }
        $col = imagecolorallocatealpha($c, $r, $g, $bb, (int) round(127 - 127 * $al));
        if ($smjer === 'v') { imagefilledrectangle($c, $x0, $y0 + $i, $x0 + $w - 1, $y0 + $i, $col); }
        else { imagefilledrectangle($c, $x0 + $i, $y0, $x0 + $i, $y0 + $h - 1, $col); }
    }
}

function abu_gr_sirina($vel, $font, $tekst) {
    $b = imagettfbbox($vel, 0, $font, $tekst);
    return $b ? abs($b[2] - $b[0]) : 0;
}

/** Prelamanje u redove do najveće širine. */
function abu_gr_prelomi($vel, $font, $tekst, $max) {
    $rijeci = preg_split('/\s+/u', trim($tekst));
    $redovi = array(); $r = '';
    foreach ($rijeci as $w) {
        $p = $r === '' ? $w : $r . ' ' . $w;
        if ($r !== '' && abu_gr_sirina($vel, $font, $p) > $max) { $redovi[] = $r; $r = $w; } else { $r = $p; }
    }
    if ($r !== '') { $redovi[] = $r; }
    return $redovi;
}

/** Uravnoteženi redovi: najuža širina s istim brojem redova (kao text-wrap: balance). */
function abu_gr_uravnotezi($vel, $font, $tekst, $max) {
    $red = abu_gr_prelomi($vel, $font, $tekst, $max);
    $n = count($red);
    if ($n < 2) { return $red; }
    $lo = (int) ($max * 0.5); $hi = $max; $naj = $red;
    while ($hi - $lo > 4) {
        $m = (int) (($lo + $hi) / 2);
        $x = abu_gr_prelomi($vel, $font, $tekst, $m);
        $prevelik = false; foreach ($x as $l) { if (abu_gr_sirina($vel, $font, $l) > $m && strpos($l, ' ') !== false) { $prevelik = true; } }
        if (count($x) <= $n && !$prevelik) { $hi = $m; $naj = $x; } else { $lo = $m; }
    }
    return $naj;
}

/** Tekst s razmakom između slova (za oznaku). Vraća širinu. */
function abu_gr_razmaknuto($c, $vel, $font, $x, $y, $boja, $tekst, $razmak, $crtaj = true) {
    $z = preg_split('//u', $tekst, -1, PREG_SPLIT_NO_EMPTY);
    $pre = '';
    foreach ($z as $i => $slovo) {
        // položaj iz širine cijelog prethodnog niza (prava širina slova uz kerning), plus razmak
        $px = $pre === '' ? 0 : abu_gr_sirina($vel, $font, $pre . 'H') - abu_gr_sirina($vel, $font, 'H');
        if ($crtaj && $slovo !== ' ') { imagettftext($c, $vel, 0, (int) round($x + $px + $i * $razmak), (int) round($y), $boja, $font, $slovo); }
        $pre .= $slovo;
    }
    return abu_gr_sirina($vel, $font, $tekst) + (count($z) - 1) * $razmak;
}

function abu_gr_zaobljeno($c, $x1, $y1, $x2, $y2, $r, $boja) {
    imagefilledrectangle($c, $x1 + $r, $y1, $x2 - $r, $y2, $boja);
    imagefilledrectangle($c, $x1, $y1 + $r, $x2, $y2 - $r, $boja);
    foreach (array(array($x1 + $r, $y1 + $r), array($x2 - $r, $y1 + $r), array($x1 + $r, $y2 - $r), array($x2 - $r, $y2 - $r)) as $t) { imagefilledellipse($c, $t[0], $t[1], 2 * $r, 2 * $r, $boja); }
}

function abu_gr_png($c, $f, $x, $y, $w = 0, $h = 0) {
    $p = @imagecreatefrompng($f);
    if (!$p) { return; }
    $sw = imagesx($p); $sh = imagesy($p);
    if (!$w) { $w = $sw; } if (!$h) { $h = (int) round($sh * $w / $sw); }
    imagealphablending($c, true);
    imagecopyresampled($c, $p, $x, $y, 0, 0, $w, $h, $sw, $sh);
    imagedestroy($p);
}

/** Crtanje; $px u CSS pikselima → točke za GD (96 dpi): pt = px * 0.75. */
function abu_gr_nacrtaj($vrsta, $foto, $naslov, $oznaka, $datum, $izlaz, $tel = false) {
    $d = __DIR__ . '/grafike/';
    $F_NASLOV = $d . 'bricolage-800.ttf'; $F_OZN = $d . 'sourcesans-700.ttf'; $F_POD = $d . 'sourcesans-600.ttf';
    foreach (array($F_NASLOV, $F_OZN, $F_POD) as $f) { if (!is_readable($f)) { return false; } }
    $src = abu_gr_ucitaj($foto);
    if (!$src) { return false; }
    $ig = $vrsta === 'ig';
    $W = $ig ? 1080 : 1200; $H = $ig ? 1350 : 630;
    $c = imagecreatetruecolor($W, $H);
    imagealphablending($c, true);
    $una = imagecolorallocate($c, 14, 79, 76);
    imagefilledrectangle($c, 0, 0, $W, $H, $una);
    $bijela = imagecolorallocate($c, 255, 255, 255);
    $bijela2 = imagecolorallocate($c, 222, 232, 230);
    $sun = imagecolorallocate($c, 242, 183, 5);
    $tinta = imagecolorallocate($c, 23, 48, 46);
    $pt = 0.75;

    if ($ig) {
        abu_gr_cover($c, $src, 0, 0, $W, $H);
        abu_gr_preliv($c, 0, 0, $W, $H, array(
            array(0, 11, 55, 53, .85), array(.14, 11, 55, 53, .35), array(.26, 14, 79, 76, 0), array(.40, 14, 79, 76, 0),
            array(.68, 10, 45, 43, .88), array(1, 11, 55, 53, 1)), 'v');
        abu_gr_png($c, $d . 'brand.png', 46, 42);
        $L = 60; $maxw = $W - 120; $dno = $H - 64;
        $vel = 76; $lh = 1.02; $pod = 27; $valh = 22; $valw = $W - 120;
    } else {
        $px = (int) round($W * 0.52);
        abu_gr_cover($c, $src, $px, 0, $W - $px, $H);
        abu_gr_preliv($c, $px, 0, 150, $H, array(array(0, 14, 79, 76, .75), array(1, 14, 79, 76, 0)), 'h');
        abu_gr_png($c, $d . 'brand.png', 41, 33, (int) round(479 * 0.8125));
        $L = 52; $maxw = 560; $dno = $H - 46;
        $vel = 50; $lh = 1.02; $pod = 21; $valh = 16; $valw = 300;
    }

    // Naslov: najviše 4 reda (IG) ili 3 (FB); po potrebi manja slova.
    $red = abu_gr_uravnotezi($vel * $pt, $F_NASLOV, $naslov, $maxw);
    $granica = $ig ? 4 : 3;
    while (count($red) > $granica && $vel > ($ig ? 52 : 34)) { $vel -= 4; $red = abu_gr_uravnotezi($vel * $pt, $F_NASLOV, $naslov, $maxw); }
    $vis = (int) round($vel * $lh);

    // Odozdo prema gore: podnožje (broj telefona u prvom planu) → val → naslov → oznaka.
    $tv = $ig ? 46 : 34;                       // veličina broja (px)
    $mal = $ig ? 23 : 18;                      // sitni tekst
    $ikona = $ig ? 44 : 32;
    $sun_t = imagecolorallocate($c, 242, 183, 5);
    if ($tel && $ig) {
        $y_tel = $dno - (int) round($tv * 0.18);
        abu_gr_png($c, $d . 'tel.png', $L - 4, $y_tel - (int) round($tv * 0.78) - 2, $ikona, $ikona);
        imagettftext($c, $tv * $pt, 0, $L + $ikona + 10, $y_tel, $sun_t, $F_NASLOV, ABU_GR_TEL);
        $t1 = 'Poziv · WhatsApp · Viber'; $t2 = 'apartmanibihac.com';
        imagettftext($c, $mal * $pt, 0, $W - 60 - abu_gr_sirina($mal * $pt, $F_POD, $t2), $y_tel, $bijela, $F_POD, $t2);
        imagettftext($c, $mal * $pt, 0, $W - 60 - abu_gr_sirina($mal * $pt, $F_POD, $t1), $y_tel - (int) round($mal * 1.3), $bijela2, $F_POD, $t1);
        $vrh_pod = $y_tel - (int) round($tv * 0.8);
    } elseif ($tel) {
        $y_mal = $dno - (int) round($mal * 0.25);
        imagettftext($c, $mal * $pt, 0, $L, $y_mal, $bijela2, $F_POD, 'Poziv · WhatsApp · Viber · apartmanibihac.com');
        $y_tel = $y_mal - (int) round($mal * 1.45);
        abu_gr_png($c, $d . 'tel.png', $L - 3, $y_tel - (int) round($tv * 0.78) - 1, $ikona, $ikona);
        imagettftext($c, $tv * $pt, 0, $L + $ikona + 8, $y_tel, $sun_t, $F_NASLOV, ABU_GR_TEL);
        $vrh_pod = $y_tel - (int) round($tv * 0.8);
    } else {
        // Vijesti i vodiči: adresa i datum (bez broja).
        $y_pod = $dno - (int) round($pod * 0.28);
        imagettftext($c, $pod * $pt, 0, $L, $y_pod, $bijela, $F_POD, 'apartmanibihac.com');
        if ($ig) { $sw = abu_gr_sirina($pod * $pt, $F_POD, $datum); imagettftext($c, $pod * $pt, 0, $W - 60 - $sw, $y_pod, $bijela2, $F_POD, $datum); }
        else { imagettftext($c, $pod * $pt, 0, $L + abu_gr_sirina($pod * $pt, $F_POD, 'apartmanibihac.com') + 24, $y_pod, $bijela2, $F_POD, $datum); }
        $vrh_pod = $dno - (int) round($pod * 1.25);
    }
    $y_val = $vrh_pod - ($ig ? 28 : 18) - $valh;
    abu_gr_png($c, $d . 'val.png', $L, $y_val, $valw, $valh);

    $y_zadnji = $y_val - ($ig ? 22 : 18) - (int) round($vel * 0.22);
    $n = count($red);
    foreach ($red as $i => $l) {
        imagettftext($c, $vel * $pt, 0, $L, $y_zadnji - ($n - 1 - $i) * $vis, $bijela, $F_NASLOV, $l);
    }
    $y_vrh_naslova = $y_zadnji - ($n - 1) * $vis - (int) round($vel * 0.80);

    // Oznaka (žuta pločica).
    $ov = $ig ? 22 : 17; $padx = $ig ? 18 : 14; $pady = $ig ? 9 : 7;
    $tekst = function_exists('mb_strtoupper') ? mb_strtoupper($oznaka, 'UTF-8') : strtoupper($oznaka);
    $raz = $ov * 0.08;
    $sir = abu_gr_razmaknuto($c, $ov * $pt, $F_OZN, 0, 0, $tinta, $tekst, $raz, false);
    $ph = (int) round($ov * 1.3) + 2 * $pady;
    $y2 = $y_vrh_naslova - ($ig ? 26 : 20); $y1 = $y2 - $ph;
    abu_gr_zaobljeno($c, $L, $y1, $L + (int) round($sir) + 2 * $padx, $y2, 6, $sun);
    abu_gr_razmaknuto($c, $ov * $pt, $F_OZN, $L + $padx, $y1 + $pady + (int) round($ov * 1.0), $tinta, $tekst, $raz);

    $ok = imagejpeg($c, $izlaz, 88);
    imagedestroy($c); imagedestroy($src);
    return $ok;
}

/** Odmah nakon objave članka napravi obje grafike (da su spremne za Facebook i Instagram). */
add_action('transition_post_status', function ($novi, $stari, $post) {
    if ($novi !== 'publish' || $post->post_type !== 'post') { return; }
    if (!wp_next_scheduled('abu_gr_napravi', array((int) $post->ID))) { wp_schedule_single_event(time() + 30, 'abu_gr_napravi', array((int) $post->ID)); }
}, 10, 3);
add_action('abu_gr_napravi', function ($id) { abu_grafika($id, 'fb'); abu_grafika($id, 'ig'); });
