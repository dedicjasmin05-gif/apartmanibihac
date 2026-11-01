<?php
/**
 * Facebook i Instagram: traka u podnožju, blok povjerenja na naslovnici,
 * dijeljenje objekata (FB, WhatsApp, Viber, link), sameAs za Google i Open Graph.
 * Kratki kod za ručno postavljanje: [abu_drustvene].
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_FB', 'https://www.facebook.com/ApartmaniVikendice');
define('ABU_IG', 'https://www.instagram.com/apartmaniivikendice/');

function abu_dr_jezik() {
    if (function_exists('abu_jezik_stranice') && ($j = abu_jezik_stranice())) { return $j; }
    if (function_exists('abu_lang')) { $l = abu_lang(); if (in_array($l, array('en', 'de'), true)) { return $l; } }
    return 'bs';
}

function abu_dr_t($k) {
    $t = array(
        'bs' => array('prati' => 'Pratite nas', 'pratitelja' => 'pratitelja', 'osvrta' => 'osvrta', 'prep' => '100 % preporučuje', 'podijeli' => 'Podijelite ovaj smještaj', 'kopiraj' => 'Kopiraj link', 'kopirano' => 'Link kopiran', 'zajednica' => 'Najveća zajednica za smještaj u Bihaću', 'novo' => 'Nove fotografije, slobodni termini i akcije prvo objavljujemo na Facebooku i Instagramu.'),
        'en' => array('prati' => 'Follow us', 'pratitelja' => 'followers', 'osvrta' => 'reviews', 'prep' => '100% recommend', 'podijeli' => 'Share this property', 'kopiraj' => 'Copy link', 'kopirano' => 'Link copied', 'zajednica' => 'The largest accommodation community in Bihać', 'novo' => 'New photos, free dates and offers appear first on Facebook and Instagram.'),
        'de' => array('prati' => 'Folgen Sie uns', 'pratitelja' => 'Follower', 'osvrta' => 'Bewertungen', 'prep' => '100 % empfehlen', 'podijeli' => 'Unterkunft teilen', 'kopiraj' => 'Link kopieren', 'kopirano' => 'Link kopiert', 'zajednica' => 'Die größte Unterkunfts-Community in Bihać', 'novo' => 'Neue Fotos, freie Termine und Angebote zuerst auf Facebook und Instagram.'),
    );
    $l = abu_dr_jezik();
    return isset($t[$l][$k]) ? $t[$l][$k] : $t['bs'][$k];
}

function abu_dr_ikona($m) {
    if ($m === 'fb') { return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M14 8.5V6.8c0-.8.5-1 .9-1H17V2.2L14.1 2C11 2 10.2 4.3 10.2 6v2.5H8V12h2.2v10H14V12h2.8l.4-3.5z"/></svg>'; }
    if ($m === 'ig') { return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.3 5.5a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4zM21.9 7.1c-.1-1.6-.4-3-1.6-4.2S17.6 1.3 16 1.2C14.3 1.1 9.7 1.1 8 1.2c-1.6.1-3 .4-4.2 1.6S2.2 5.5 2.1 7.1C2 8.8 2 13.4 2.1 15.1c.1 1.6.4 3 1.6 4.2s2.6 1.5 4.2 1.6c1.7.1 6.3.1 8 0 1.6-.1 3-.4 4.2-1.6s1.5-2.6 1.6-4.2c.1-1.7.1-6.3.1-8zm-2.2 10.2a3.3 3.3 0 0 1-1.8 1.8c-1.3.5-4.3.4-5.7.4s-4.4.1-5.7-.4a3.3 3.3 0 0 1-1.8-1.8c-.5-1.3-.4-4.3-.4-5.7s-.1-4.4.4-5.7A3.3 3.3 0 0 1 6.5 4.1c1.3-.5 4.3-.4 5.7-.4s4.4-.1 5.7.4a3.3 3.3 0 0 1 1.8 1.8c.5 1.3.4 4.3.4 5.7s.1 4.4-.4 5.7z"/></svg>'; }
    if ($m === 'wa') { return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.8 14.1c-.2.7-1.4 1.3-2 1.4-.5.1-1.1.1-1.8-.1-.4-.1-1-.3-1.7-.6-3-1.3-5-4.3-5.1-4.5-.2-.2-1.2-1.6-1.2-3.1s.8-2.2 1-2.5c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.3.5-.4.5c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.1.1.7-.1 1.3z"/></svg>'; }
    if ($m === 'vb') { return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M11.4 1C8.6 1 3 1.6 3 9.2v2.6c0 3.4 1.1 5.6 3.5 6.6v3.3c0 .5.6.8 1 .4l2.7-2.9h1.3c2.8 0 9.5-.6 9.5-8.2V9.2C21 1.6 14.3 1 11.4 1zm4.7 13.8c-.3.6-1 1.2-1.6 1.1-.9-.2-3.8-1.6-5.6-3.7-1.8-2.1-2.4-4.3-2.4-4.9.1-.7.7-1.2 1.2-1.4.4-.1.7 0 .9.3l1 1.5c.2.4.1.8-.2 1l-.5.4c.4 1.2 1.6 2.7 3 3.2l.4-.5c.3-.3.7-.4 1-.2l1.6.9c.4.2.5.7.2 1.1z"/></svg>'; }
    return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M10.6 13.4a1 1 0 0 1 0-1.4l3-3a1 1 0 1 1 1.4 1.4l-3 3a1 1 0 0 1-1.4 0zM8.5 18.5a3.5 3.5 0 0 1-2.5-6l2-2a1 1 0 1 1 1.4 1.4l-2 2a1.5 1.5 0 1 0 2.1 2.1l2-2a1 1 0 1 1 1.4 1.4l-2 2a3.5 3.5 0 0 1-2.4 1.1zm6-6a1 1 0 0 1-.7-1.7l2-2a1.5 1.5 0 1 0-2.1-2.1l-2 2a1 1 0 1 1-1.4-1.4l2-2a3.5 3.5 0 1 1 5 5l-2 2a1 1 0 0 1-.8.2z"/></svg>';
}

function abu_dr_css() {
    static $ok = false;
    if ($ok) { return ''; }
    $ok = true;
    return '<style>
.abu-dr{--fb:#1877f2;--ig:#d62976;font:inherit}
.abu-dr a{text-decoration:none}
.abu-dr-gumbi{display:flex;flex-wrap:wrap;gap:10px}
.abu-dr-g{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:999px;color:#fff!important;font-weight:700;font-size:15px;line-height:1.2;transition:transform .15s,box-shadow .15s}
.abu-dr-g:hover{transform:translateY(-1px);box-shadow:0 6px 16px #0002}
.abu-dr-g small{font-weight:500;opacity:.9}
.abu-dr-fb{background:var(--fb)}
.abu-dr-ig{background:linear-gradient(45deg,#feda75,#fa7e1e 25%,#d62976 50%,#962fbf 75%,#4f5bd5)}
.abu-dr-wa{background:#25d366}.abu-dr-vb{background:#7360f2}.abu-dr-ln{background:#555;border:0;cursor:pointer;font:inherit}
.abu-dr-traka{background:#1f2a24;color:#fff;padding:28px 16px;text-align:center}
.abu-dr-traka h3{color:#fff;margin:0 0 6px;font-size:20px}
.abu-dr-traka p{margin:0 auto 16px;max-width:620px;opacity:.85;font-size:15px}
.abu-dr-traka .abu-dr-gumbi{justify-content:center}
.abu-dr-blok{background:#f6f8f4;border-radius:16px;padding:22px;margin:28px 0;display:grid;gap:14px}
.abu-dr-blok h3{margin:0;font-size:20px}
.abu-dr-brojke{display:flex;flex-wrap:wrap;gap:8px 22px;font-size:15px;color:#333}
.abu-dr-brojke b{font-size:22px;color:#111}
.abu-dr-dijeli{margin:26px 0;padding:18px;border:1px solid #e6e6e6;border-radius:14px}
.abu-dr-dijeli h4{margin:0 0 12px;font-size:16px}
@media(max-width:600px){.abu-dr-g{flex:1 1 calc(50% - 10px);justify-content:center;padding:11px 10px}}
</style>';
}

function abu_dr_brojke() {
    return '<div class="abu-dr-brojke"><span><b>139.000+</b> ' . esc_html(abu_dr_t('pratitelja')) . '</span><span><b>9.362</b> ' . esc_html(abu_dr_t('osvrta')) . '</span><span><b>★</b> ' . esc_html(abu_dr_t('prep')) . '</span></div>';
}

function abu_dr_profili() {
    return '<div class="abu-dr-gumbi">'
        . '<a class="abu-dr-g abu-dr-fb" href="' . esc_url(ABU_FB) . '" target="_blank" rel="noopener">' . abu_dr_ikona('fb') . 'Facebook <small>139K</small></a>'
        . '<a class="abu-dr-g abu-dr-ig" href="' . esc_url(ABU_IG) . '" target="_blank" rel="noopener">' . abu_dr_ikona('ig') . 'Instagram</a>'
        . '</div>';
}

/** [abu_drustvene] blok s brojkama i gumbima. */
function abu_dr_blok() {
    return abu_dr_css() . '<section class="abu-dr abu-dr-blok"><h3>' . esc_html(abu_dr_t('zajednica')) . '</h3>' . abu_dr_brojke() . '<p style="margin:0">' . esc_html(abu_dr_t('novo')) . '</p>' . abu_dr_profili() . '</section>';
}
add_shortcode('abu_drustvene', 'abu_dr_blok');

/** Naslovnica: blok ispod uvoda, ako ga već nema u sadržaju. Objekt: gumbi za dijeljenje. */
add_filter('the_content', function ($c) {
    if (is_admin() || !in_the_loop() || !is_main_query()) { return $c; }
    if ((is_front_page() || abu_jezik_stranice()) && strpos($c, 'abu-dr-blok') === false && strpos($c, '[abu_drustvene') === false) {
        return abu_dr_blok() . $c;
    }
    if (is_singular('objekt')) {
        $url = get_permalink();
        $txt = get_the_title() . ' – ' . $url;
        $c .= abu_dr_css() . '<div class="abu-dr abu-dr-dijeli"><h4>' . esc_html(abu_dr_t('podijeli')) . '</h4><div class="abu-dr-gumbi">'
            . '<a class="abu-dr-g abu-dr-fb" href="https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url) . '" target="_blank" rel="noopener">' . abu_dr_ikona('fb') . 'Facebook</a>'
            . '<a class="abu-dr-g abu-dr-wa" href="https://wa.me/?text=' . rawurlencode($txt) . '" target="_blank" rel="noopener">' . abu_dr_ikona('wa') . 'WhatsApp</a>'
            . '<a class="abu-dr-g abu-dr-vb" href="viber://forward?text=' . rawurlencode($txt) . '">' . abu_dr_ikona('vb') . 'Viber</a>'
            . '<button type="button" class="abu-dr-g abu-dr-ln" data-url="' . esc_attr($url) . '" data-ok="' . esc_attr(abu_dr_t('kopirano')) . '" onclick="navigator.clipboard&&navigator.clipboard.writeText(this.dataset.url).then(()=>{this.lastChild.textContent=this.dataset.ok})">' . abu_dr_ikona('ln') . '<span>' . esc_html(abu_dr_t('kopiraj')) . '</span></button>'
            . '</div></div>';
    }
    return $c;
}, 20);

/** Traka u podnožju svake stranice. */
add_action('wp_footer', function () {
    if (is_admin()) { return; }
    echo abu_dr_css() . '<section class="abu-dr abu-dr-traka"><h3>' . esc_html(abu_dr_t('prati')) . ' · 139.000+ ' . esc_html(abu_dr_t('pratitelja')) . '</h3><p>' . esc_html(abu_dr_t('novo')) . '</p>' . abu_dr_profili() . '</section>';
}, 5);

/** Google: veza sajta s profilima; Open Graph za lijep prikaz pri dijeljenju (ako nema SEO dodatka). */
add_action('wp_head', function () {
    if (is_front_page()) {
        $d = array('@context' => 'https://schema.org', '@type' => 'LodgingBusiness', 'name' => 'Apartmani & Vikendice Bihać', 'url' => home_url('/'),
            'telephone' => '+387 61 591 320', 'priceRange' => '25–100 €',
            'address' => array('@type' => 'PostalAddress', 'addressLocality' => 'Bihać', 'postalCode' => '77000', 'addressCountry' => 'BA'),
            'sameAs' => array(ABU_FB, ABU_IG));
        echo '<script type="application/ld+json">' . wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION') || !is_singular()) { return; }
    $id = get_queried_object_id();
    $opis = has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $id))), 30, '…');
    $img = get_the_post_thumbnail_url($id, 'large');
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:site_name" content="Apartmani & Vikendice Bihać">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(get_the_title($id)) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($opis) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink($id)) . '">' . "\n";
    if ($img) { echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n" . '<meta name="twitter:card" content="summary_large_image">' . "\n"; }
}, 3);
