<?php
/**
 * Kontakt u prvom planu: traka na vrhu svake stranice (telefon, poziv, WhatsApp, Viber, e-mail)
 * i stalna traka s gumbima pri dnu ekrana na mobitelu.
 */
if (!defined('ABSPATH')) { exit; }

define('ABU_TEL', '+38761591320');
define('ABU_TEL_PRIKAZ', '+387 61 591 320');
define('ABU_MAIL', 'apartmaniubihacu@gmail.com');

function abu_kt_t($k) {
    $l = function_exists('abu_dr_jezik') ? abu_dr_jezik() : 'bs';
    $t = array(
        'bs' => array('nazovi' => 'Nazovi', 'uvod' => 'Rezervacije i upiti:', 'mail' => 'E-mail', 'jezici' => 'Govorimo bosanski, engleski i njemački', 'cijena' => 'Cijeli apartman od 25 € (50 KM)'),
        'en' => array('nazovi' => 'Call', 'uvod' => 'Bookings and enquiries:', 'mail' => 'E-mail', 'jezici' => 'We speak English and German', 'cijena' => 'Whole apartment from €25'),
        'de' => array('nazovi' => 'Anrufen', 'uvod' => 'Buchung und Anfragen:', 'mail' => 'E-Mail', 'jezici' => 'Wir sprechen Deutsch und Englisch', 'cijena' => 'Ganze Wohnung ab 25 €'),
    );
    return isset($t[$l][$k]) ? $t[$l][$k] : $t['bs'][$k];
}

function abu_kt_ikona($m) {
    if ($m === 'tel') { return '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z"/></svg>'; }
    if ($m === 'mail') { return '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/></svg>'; }
    return function_exists('abu_dr_ikona') ? abu_dr_ikona($m) : '';
}

function abu_kt_gumbi($klasa) {
    return '<a class="' . $klasa . ' abu-kt-tel" href="tel:' . ABU_TEL . '">' . abu_kt_ikona('tel') . '<span>' . esc_html(abu_kt_t('nazovi')) . '</span></a>'
        . '<a class="' . $klasa . ' abu-kt-wa" href="https://wa.me/' . ltrim(ABU_TEL, '+') . '" target="_blank" rel="noopener">' . abu_kt_ikona('wa') . '<span>WhatsApp</span></a>'
        . '<a class="' . $klasa . ' abu-kt-vb" href="viber://chat?number=%2B' . ltrim(ABU_TEL, '+') . '">' . abu_kt_ikona('vb') . '<span>Viber</span></a>'
        . '<a class="' . $klasa . ' abu-kt-ml" href="mailto:' . ABU_MAIL . '">' . abu_kt_ikona('mail') . '<span>' . esc_html(abu_kt_t('mail')) . '</span></a>';
}

function abu_kt_css() {
    return '<style>
.abu-kt{background:#14532d;color:#fff;font:600 15px/1.3 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;position:fixed;top:0;left:0;right:0;z-index:100000}
.abu-kt-in{max-width:1200px;margin:0 auto;padding:8px 16px;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:8px 14px}
.abu-kt-broj{color:#fff!important;text-decoration:none;font-size:18px;font-weight:800;letter-spacing:.3px;white-space:nowrap}
.abu-kt-uv{opacity:.85;font-weight:500}
.abu-kt-sp{opacity:.75;font-weight:500;font-size:13px}
.abu-kt-g{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;color:#fff!important;text-decoration:none;font-size:14px;line-height:1}
.abu-kt-tel{background:#16a34a}.abu-kt-wa{background:#25d366}.abu-kt-vb{background:#7360f2}.abu-kt-ml{background:#334155}
.abu-kt-g:hover{filter:brightness(1.1)}
.abu-kt-dno{display:none}
@media(max-width:760px){
 .abu-kt-uv,.abu-kt-sp,.abu-kt .abu-kt-g{display:none}
 .abu-kt-in{padding:7px 12px}
 .abu-kt-dno{display:grid;grid-template-columns:repeat(4,1fr);position:fixed;left:0;right:0;bottom:0;z-index:99999;background:#fff;box-shadow:0 -4px 16px #0002;padding:6px 6px calc(6px + env(safe-area-inset-bottom));gap:6px}
 .abu-kt-d{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:8px 2px;border-radius:12px;color:#fff!important;text-decoration:none;font:700 12px/1 system-ui,-apple-system,Roboto,Arial,sans-serif}
 .abu-kt-d svg{width:22px;height:22px}
 body{padding-bottom:76px}
}
</style>';
}

function abu_kt_traka() {
    return abu_kt_css() . '<div class="abu-kt" role="region" aria-label="Kontakt"><div class="abu-kt-in">'
        . '<span class="abu-kt-uv">' . esc_html(abu_kt_t('uvod')) . '</span>'
        . '<a class="abu-kt-broj" href="tel:' . ABU_TEL . '">' . abu_kt_ikona('tel') . ' ' . ABU_TEL_PRIKAZ . '</a>'
        . abu_kt_gumbi('abu-kt-g')
        . '<span class="abu-kt-sp">' . esc_html(abu_kt_t('cijena')) . ' · ' . esc_html(abu_kt_t('jezici')) . '</span>'
        . '</div></div>'
        . '<nav class="abu-kt-dno" aria-label="Kontakt">' . abu_kt_gumbi('abu-kt-d') . '</nav>'
        . '<script>' . abu_kt_js() . '</script>';
}

/** Pomakni stranicu i zaglavlje teme ispod fiksne trake (tema ima zaglavlje preko vrha). */
function abu_kt_js() {
    return '(function(){function f(){var b=document.querySelector(".abu-kt");if(!b)return;var h=b.offsetHeight;var ab=document.getElementById("wpadminbar");var o=ab?ab.offsetHeight:0;b.style.top=o+"px";document.documentElement.style.setProperty("--abu-kt-h",h+"px");document.body.style.paddingTop=h+"px";var el=document.querySelectorAll("body *");for(var i=0;i<el.length;i++){var e=el[i];if(e===b||b.contains(e)||e.classList.contains("abu-kt-dno")||e.closest(".abu-kt-dno"))continue;var cs=getComputedStyle(e);var hd=e.tagName==="HEADER"||/(^|[ _-])(header|navbar|site-nav|masthead|topbar)/i.test(typeof e.className==="string"?e.className:"");if(!(cs.position==="fixed"||cs.position==="sticky"||(cs.position==="absolute"&&hd&&e.offsetParent===document.body)))continue;var r=e.getBoundingClientRect();if(r.top>h+o+5||r.height>window.innerHeight*0.6)continue;if(e.dataset.abuTop===undefined){e.dataset.abuTop=parseFloat(cs.top)||0;}e.style.top=(parseFloat(e.dataset.abuTop)+h+o)+"px";}}if(document.readyState!=="loading")f();else document.addEventListener("DOMContentLoaded",f);window.addEventListener("load",f);window.addEventListener("resize",f);setTimeout(f,800);})();';
}

/** Na vrhu stranice (tema s wp_body_open), inače na dnu s pomakom na vrh. */
add_action('wp_body_open', function () {
    if (is_admin()) { return; }
    $GLOBALS['abu_kt_ispisano'] = true;
    echo abu_kt_traka(); // phpcs:ignore
}, 1);
add_action('wp_footer', function () {
    if (is_admin() || !empty($GLOBALS['abu_kt_ispisano'])) { return; }
    echo abu_kt_traka() . '<script>(function(){var t=document.querySelector(".abu-kt");if(t&&document.body.firstChild!==t){document.body.insertBefore(t,document.body.firstChild);}})();</script>'; // phpcs:ignore
}, 1);

/** Kontakt i za Google. */
add_action('wp_head', function () {
    echo '<meta name="format-detection" content="telephone=yes">' . "\n";
}, 1);
