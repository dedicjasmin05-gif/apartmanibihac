<?php
/**
 * Komentari isključeni na cijelom sajtu (stizao je samo spam). Neodobreni komentari idu u spam.
 */
if (!defined('ABSPATH')) { exit; }

add_filter('comments_open', '__return_false', 99);
add_filter('pings_open', '__return_false', 99);
add_filter('comments_array', function ($c) { return is_admin() ? $c : array(); }, 99);
add_filter('xmlrpc_methods', function ($m) { unset($m['pingback.ping'], $m['pingback.extensions.getPingbacks']); return $m; });
add_action('wp_headers', function ($h) { unset($h['X-Pingback']); return $h; });

add_action('init', function () {
    if (get_option('abu_komentari_ver') === ABU_VER) { return; }
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');
    global $wpdb;
    $wpdb->query("UPDATE {$wpdb->posts} SET comment_status='closed', ping_status='closed' WHERE comment_status<>'closed' OR ping_status<>'closed'");
    foreach (get_comments(array('status' => 'hold', 'number' => 500, 'fields' => 'ids')) as $id) { wp_spam_comment($id); }
    update_option('abu_komentari_ver', ABU_VER);
}, 40);
