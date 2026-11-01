<?php
/** Njemačka (/de/) i engleska (/en/) ulazna stranica, hreflang i jezik obrasca. */
if (!defined('ABSPATH')) { exit; }

function abu_jezik_stranice() {
    if (!is_page()) { return ''; }
    $p = get_queried_object();
    while ($p && !empty($p->post_parent)) { $p = get_post($p->post_parent); }
    return ($p && in_array($p->post_name, array('de', 'en'), true)) ? $p->post_name : '';
}
add_filter('locale', function ($l) {
    if (is_admin() || !did_action('wp')) { return $l; }
    $j = abu_jezik_stranice();
    return $j === 'de' ? 'de_DE' : ($j === 'en' ? 'en_GB' : $l);
});
add_filter('language_attributes', function ($out) {
    $j = did_action('wp') ? abu_jezik_stranice() : '';
    return $j ? 'lang="' . $j . '"' : 'lang="bs"';
});
add_action('wp_head', function () {
    if (!is_front_page() && !abu_jezik_stranice()) { return; }
    $de = get_page_by_path('de'); $en = get_page_by_path('en');
    echo '<link rel="alternate" hreflang="bs" href="' . esc_url(home_url('/')) . '">' . "\n";
    echo '<link rel="alternate" hreflang="hr" href="' . esc_url(home_url('/')) . '">' . "\n";
    if ($de) { echo '<link rel="alternate" hreflang="de" href="' . esc_url(get_permalink($de)) . '">' . "\n"; }
    if ($en) { echo '<link rel="alternate" hreflang="en" href="' . esc_url(get_permalink($en)) . '">' . "\n"; }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/')) . '">' . "\n";
}, 2);

/** [abu_lista lang="en" mjesto="" broj="30"] kartice objekata s jezikom u linku. */
add_shortcode('abu_lista', function ($a) {
    $a = shortcode_atts(array('lang' => 'bs', 'mjesto' => '', 'broj' => 30), $a);
    $q = array('post_type' => 'objekt', 'post_status' => 'publish', 'numberposts' => (int) $a['broj'], 'orderby' => 'menu_order title', 'order' => 'ASC');
    if ($a['mjesto']) { $q['meta_key'] = '_abu_mjesto'; $q['meta_value'] = $a['mjesto']; }
    $ids = wp_list_pluck(get_posts($q), 'ID');
    usort($ids, function ($x, $y) { return (get_post_meta($x, '_abu_grupa', true) === 'vlastiti' ? 0 : 1) - (get_post_meta($y, '_abu_grupa', true) === 'vlastiti' ? 0 : 1); });
    $osobe = array('en' => 'guests', 'de' => 'Personen', 'bs' => 'osoba');
    $l = isset($osobe[$a['lang']]) ? $a['lang'] : 'bs';
    $h = '<div class="grid grid--cards">';
    foreach ($ids as $id) {
        if (function_exists('ab_kartica')) { $h .= ab_kartica($id, null, $l === 'bs' ? array() : array('lang' => $l)); continue; }
        $h .= '<a class="card" href="' . esc_url(add_query_arg('lang', $l, get_permalink($id))) . '">' . get_the_post_thumbnail($id, 'medium_large') . '<strong>' . esc_html(get_the_title($id)) . '</strong> · ' . esc_html(get_post_meta($id, '_abu_mjesto', true)) . ', ' . (int) get_post_meta($id, '_abu_kapacitet', true) . ' ' . $osobe[$l] . '</a>';
    }
    return $h . '</div>';
});

function abu_seed_jezici() {
    $wa = 'https://wa.me/38761591320';
    return array(
        'en' => array('Apartments and holiday homes in Bihać on the Una river',
            'Apartments in Bihać and riverside holiday homes in Lohovo, Pritoka and Grmuša. From €25 to €100 per night for the whole property. Book directly with the owner.',
            '<p class="lead">Stay in Bihać, Bosnia and Herzegovina: city-centre apartments and holiday homes right on the Una river, 15 minutes from Una National Park and 35 minutes from Plitvice Lakes.</p>
<h2>Why book with us</h2>
<ul>
<li><strong>Direct with the owner</strong>: no booking fees, prices from €25 to €100 per night for the whole property.</li>
<li><strong>Answer within 12 hours</strong>: send an inquiry, the owner confirms availability and you get a confirmation by e-mail.</li>
<li><strong>30 % deposit</strong>: the rest is paid on arrival in cash or by card.</li>
<li><strong>Local help</strong>: rafting, restaurants, trips to Štrbački buk and Plitvice. Ask us on WhatsApp or Viber: <a href="' . $wa . '">+387 61 591 320</a>.</li>
</ul>
<h2>Where to stay</h2>
<p><strong>Bihać centre</strong>: apartments within walking distance of the old town, cafés and the Una promenade. <strong>Lohovo</strong>: quiet village south of Bihać with houses directly on the river, many with pools. <strong>Pritoka and Grmuša</strong>: holiday homes with their own access to the Una, ideal for families and groups.</p>
<h2>All properties</h2>
[abu_lista lang="en"]
<h2>Frequently asked questions</h2>
<h3>How far is Bihać from Plitvice Lakes?</h3><p>About 35 km, a 35–40 minute drive via the Izačić border crossing.</p>
<h3>Can we swim in the Una?</h3><p>Yes. The river is clean and clear; most riverside houses have their own bathing spot. The water is cool even in summer (around 18–20 °C).</p>
<h3>Do you organise rafting?</h3><p>Yes, with Una Olympic Rafting in Grmuša. See <a href="/rafting-na-uni/">rafting on the Una</a>.</p>
<h3>Which currency is used?</h3><p>Convertible mark (BAM); euros are accepted everywhere. Our prices are in euros.</p>'),
        'de' => array('Ferienwohnungen und Ferienhäuser in Bihać an der Una',
            'Ferienwohnungen in Bihać und Ferienhäuser direkt an der Una in Lohovo, Pritoka und Grmuša. 25 bis 100 € pro Nacht für das ganze Objekt. Direkt beim Vermieter buchen.',
            '<p class="lead">Urlaub in Bihać, Bosnien und Herzegowina: Ferienwohnungen im Stadtzentrum und Ferienhäuser direkt am Fluss Una, 15 Minuten vom Nationalpark Una und 35 Minuten von den Plitvicer Seen entfernt.</p>
<h2>Warum direkt bei uns buchen</h2>
<ul>
<li><strong>Direkt beim Vermieter</strong>: keine Buchungsgebühren, 25 bis 100 € pro Nacht für das ganze Objekt.</li>
<li><strong>Antwort innerhalb von 12 Stunden</strong>: Anfrage senden, der Vermieter bestätigt die Verfügbarkeit, Sie erhalten eine Bestätigung per E-Mail.</li>
<li><strong>30 % Anzahlung</strong>: der Rest wird bei Ankunft bar oder mit Karte bezahlt.</li>
<li><strong>Hilfe vor Ort</strong>: Rafting, Restaurants, Ausflüge zum Štrbački buk und nach Plitvice. Schreiben Sie uns auf WhatsApp oder Viber: <a href="' . $wa . '">+387 61 591 320</a>. Wir sprechen Deutsch.</li>
</ul>
<h2>Wo übernachten</h2>
<p><strong>Zentrum von Bihać</strong>: Wohnungen zu Fuß zur Altstadt, zu Cafés und zur Una-Promenade. <strong>Lohovo</strong>: ruhiges Dorf südlich von Bihać mit Häusern direkt am Fluss, viele mit Pool. <strong>Pritoka und Grmuša</strong>: Ferienhäuser mit eigenem Zugang zur Una, ideal für Familien und Gruppen.</p>
<h2>Alle Unterkünfte</h2>
[abu_lista lang="de"]
<h2>Häufige Fragen</h2>
<h3>Wie weit ist es von Bihać zu den Plitvicer Seen?</h3><p>Etwa 35 km, 35–40 Minuten mit dem Auto über den Grenzübergang Izačić.</p>
<h3>Kann man in der Una baden?</h3><p>Ja. Das Wasser ist sauber und klar, die meisten Häuser am Fluss haben eine eigene Badestelle. Auch im Sommer ist es frisch (etwa 18–20 °C).</p>
<h3>Organisieren Sie Rafting?</h3><p>Ja, mit Una Olympic Rafting in Grmuša. Mehr unter <a href="/rafting-na-uni/">Rafting auf der Una</a>.</p>
<h3>Welche Währung gilt?</h3><p>Konvertible Mark (BAM); Euro wird überall akzeptiert. Unsere Preise sind in Euro.</p>'),
    );
}
