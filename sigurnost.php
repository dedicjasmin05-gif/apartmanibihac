<?php
/**
 * Sigurnosna zaglavlja (standard 2026): HSTS, nosniff, Referrer-Policy, Permissions-Policy, zaštita od ugrađivanja u tuđe stranice.
 * Ne dira CSP (postojeći upgrade-insecure-requests ostaje), kolačiće ni sadržaj. Admin i AJAX ostaju kao prije.
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

function abu_sig_zaglavlja() {
    return array(
        'Strict-Transport-Security' => 'max-age=31536000',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        'X-Frame-Options' => 'SAMEORIGIN',
    );
}

add_action('send_headers', function () {
    if (headers_sent() || is_admin()) { return; }
    foreach (abu_sig_zaglavlja() as $k => $v) { header($k . ': ' . $v); }
});

/** llms.txt, llms-full.txt i robots.txt šalju se ranije (init), pa im zaglavlja dodajemo i tamo. */
add_action('init', function () {
    $p = isset($_SERVER['REQUEST_URI']) ? strtok(wp_unslash($_SERVER['REQUEST_URI']), '?') : '';
    if (in_array($p, array('/llms.txt', '/llms-full.txt'), true) && !headers_sent()) {
        foreach (abu_sig_zaglavlja() as $k => $v) { header($k . ': ' . $v); }
    }
}, 0);
