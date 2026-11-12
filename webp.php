<?php
/**
 * WebP bez vanjskih usluga: uz svaku JPG/PNG sliku u uploads nastaje "slika.jpg.webp" (GD, kvaliteta 80),
 * ali samo ako je manja od originala. Stranice tada umjesto JPG/PNG poslužuju WebP (original ostaje netaknut).
 * Postojeće slike pretvara alati/webp-sve.php (jednokratno, cron), nove se pretvaraju pri učitavanju.
 * Povrat: ukloniti require_once u apartmani-upiti.php (stranice se vraćaju na JPG; .webp datoteke mogu ostati).
 */
if (!defined('ABSPATH')) { exit; }

function abu_webp_moguce() { return function_exists('imagewebp') && function_exists('imagecreatefromjpeg'); }

/** Napravi .webp uz datoteku; vrati broj ušteđenih bajtova (0 ako nije isplativo ili nije moguće). */
function abu_webp_napravi($put, $kvaliteta = 80) {
    if (!abu_webp_moguce() || !preg_match('~\.(jpe?g|png)$~i', $put) || !is_file($put)) { return 0; }
    $cilj = $put . '.webp';
    if (is_file($cilj)) { return 0; }
    $tip = strtolower(pathinfo($put, PATHINFO_EXTENSION));
    $im = $tip === 'png' ? @imagecreatefrompng($put) : @imagecreatefromjpeg($put);
    if (!$im) { return 0; }
    if ($tip === 'png') { imagepalettetotruecolor($im); imagealphablending($im, true); imagesavealpha($im, true); }
    $ok = @imagewebp($im, $cilj, $kvaliteta);
    imagedestroy($im);
    if (!$ok || !is_file($cilj)) { return 0; }
    $razlika = filesize($put) - filesize($cilj);
    if ($razlika <= 0) { @unlink($cilj); return 0; }
    return $razlika;
}

/** Nove slike: WebP za original i sve generirane veličine. */
add_filter('wp_generate_attachment_metadata', function ($meta, $id) {
    $f = get_attached_file($id);
    if (!$f) { return $meta; }
    abu_webp_napravi($f);
    $dir = dirname($f);
    foreach ((array) ($meta['sizes'] ?? array()) as $v) { if (!empty($v['file'])) { abu_webp_napravi($dir . '/' . $v['file']); } }
    return $meta;
}, 20, 2);

/** Brisanje slike briše i njene .webp kopije. */
add_action('delete_attachment', function ($id) {
    $f = get_attached_file($id);
    if (!$f) { return; }
    foreach (glob(preg_replace('~\.(jpe?g|png)$~i', '', $f) . '*.webp') ?: array() as $w) { @unlink($w); }
});

/** Ispis stranice: URL-ovi slika iz uploads zamjenjuju se .webp inačicom kad ona postoji. */
function abu_webp_zamijeni($html) {
    if (stripos($html, '/wp-content/uploads/') === false) { return $html; }
    // <head> (og:image za Facebook/WhatsApp) ostaje JPG; mijenja se samo tijelo stranice.
    $i = stripos($html, '</head>');
    if ($i !== false) { return substr($html, 0, $i + 7) . abu_webp_zamijeni(substr($html, $i + 7)); }
    $up = wp_get_upload_dir();
    $baza = $up['baseurl']; $dir = $up['basedir'];
    $rel = (string) wp_parse_url($baza, PHP_URL_PATH);
    static $ima = array();
    return preg_replace_callback('~(?:https?://[^\s"\'(),]+)?' . preg_quote($rel, '~') . '/([^\s"\'(),?#]+\.(?:jpe?g|png))(?=[\s"\'(),?#])~i', function ($m) use ($dir, &$ima) {
        $f = $m[1];
        if (!isset($ima[$f])) { $ima[$f] = is_file($dir . '/' . rawurldecode($f) . '.webp'); }
        return $ima[$f] ? $m[0] . '.webp' : $m[0];
    }, $html);
}
add_action('template_redirect', function () {
    if (is_admin() || is_feed() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    ob_start('abu_webp_zamijeni');
}, 2);
