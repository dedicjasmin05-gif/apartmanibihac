<?php
/**
 * Sav mail weba ide na adresu apartmana, a ne na adresu agencije iz instalacije.
 * Pošiljatelj je s domene weba, jer ona ima SPF za Hostinger.
 */
if (!defined('ABSPATH')) { exit; }

add_filter('pre_option_admin_email', function () { return ABU_ODGOVOR_MAIL; });
add_filter('comment_moderation_recipients', function () { return array(ABU_ODGOVOR_MAIL); });
add_filter('comment_notification_recipients', function () { return array(ABU_ODGOVOR_MAIL); });

// Domena ima DMARC p=quarantine. Tehnička adresa slanja mora biti s iste domene, inače Gmail mail šalje u spam.
add_action('phpmailer_init', function ($m) {
    $m->Sender = ABU_OD_MAIL;
    if ($m->From && strpos($m->From, '@apartmanibihac.com') === false) { $m->From = ABU_OD_MAIL; }
});
add_filter('wp_mail_from', function () { return ABU_OD_MAIL; });
