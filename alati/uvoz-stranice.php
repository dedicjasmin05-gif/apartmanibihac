<?php
/** Jednokratno: prenesi odabrane stranice sa starog sajta (Leftor) na novi. php uvoz-stranice.php */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
$_SERVER['HTTP_HOST'] = 'apartmanibihac.com';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
function stari_json($put) {
    $c = curl_init('https://www.apartmanibihac.com' . $put);
    curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_RESOLVE => array('www.apartmanibihac.com:443:95.217.229.38')));
    $b = (string) curl_exec($c);
    curl_close($c);
    return json_decode($b, true);
}
$zamjene = json_decode((string) file_get_contents(__DIR__ . '/zamjene-placanje.json'), true) ?: array();
$slugovi = array('team-building-bihac', 'vikendice-s-bazenom', 'smjestaj-uz-unu', 'smjestaj-pritoka', 'smjestaj-lohovo', 'smjestaj-grmusa', 'o-nama');
foreach ($slugovi as $slug) {
    if (get_page_by_path($slug)) { echo $slug . ': već postoji' . PHP_EOL; continue; }
    $d = stari_json('/wp-json/wp/v2/pages?slug=' . $slug . '&_fields=title,content,excerpt');
    if (!is_array($d) || empty($d[0])) { echo $slug . ': nije dohvaćeno' . PHP_EOL; continue; }
    $html = $d[0]['content']['rendered'];
    $html = preg_replace('/\[\/?[a-z_]+[^\]]*\]/i', '', $html);              // ostaci shortcodova
    $html = preg_replace('/\s(class|style|id|data-[a-z-]+)="[^"]*"/i', '', $html); // stari stilovi teme
    $html = preg_replace('#https?://(www\.)?apartmanibihac\.com#i', home_url(), $html);
    $html = preg_replace('#<div>\s*</div>#', '', $html);
    foreach ($zamjene as $z) { $html = str_replace($z[0], $z[1], $html); }
    $html = preg_replace('/[^.<>]*kapar[^.<>]*30[^.<>]*\.\s*/iu', 'Plaćanje gotovinom pri dolasku; uz avans unaprijed moguć je popust. ', $html);
    $naslov = html_entity_decode(wp_strip_all_tags($d[0]['title']['rendered']));
    $izvod = wp_strip_all_tags($d[0]['excerpt']['rendered']);
    $id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $naslov, 'post_excerpt' => $izvod, 'post_content' => trim($html)), true);
    echo $slug . ': ' . (is_wp_error($id) ? 'GREŠKA ' . $id->get_error_message() : 'preneseno #' . $id . ', ' . str_word_count(wp_strip_all_tags($html)) . ' riječi, kapara: ' . substr_count(strtolower($html), 'kapar')) . PHP_EOL;
}
