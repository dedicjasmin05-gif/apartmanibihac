<?php
/**
 * LiteSpeed Cache: stranice s formama i živim podacima nikad ne idu u cache.
 * - stranice objekata (forma upita sa sigurnosnim ključem koji vrijedi ~12 h, kalendar slobodnih termina),
 * - pretraga /smjestaj/ (slobodni termini za datume), "Prijavite objekt" (ključ), team building (forma i poruka nakon slanja),
 * - stranice za potvrdu/odbijanje upita iz e-maila (?abu_a=).
 * Bez LiteSpeed Cache dodatka ovo ništa ne radi. Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

function abu_cache_iskljuci() {
    if (!empty($_GET['abu_a']) || !empty($_GET['abu_ok']) || !empty($_GET['abu_err']) || !empty($_GET['tb_status'])) { return 'poruka nakon slanja'; }
    if (is_singular('objekt')) { return 'objekt: forma upita i kalendar'; }
    if (get_query_var('ab_smjestaj')) { return 'pretraga slobodnih termina'; }
    if (is_page('prijavite-objekt')) { return 'forma prijave objekta'; }
    if (function_exists('abu_tb_tekuci') && abu_tb_tekuci()) { return 'team building forma'; }
    return '';
}

add_action('template_redirect', function () {
    $r = abu_cache_iskljuci();
    if ($r !== '') { do_action('litespeed_control_set_nocache', 'apartmani-upiti: ' . $r); }
}, 1);
