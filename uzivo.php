<?php
/**
 * [abu_uzivo lang="bs"]: "Bihać uživo" — vrijeme sada i 3 dana, vjetar, UV, kvaliteta zraka, izlazak/zalazak sunca, tečaj KM.
 * Podatke dohvaća preglednik posjetitelja iz Open-Meteo (besplatno, bez ključa, CC BY 4.0, izvor naveden), pa stranica
 * smije ići u cache. Bez JavaScripta prikazuje se samo poveznica na izvor.
 * [abu_kamere lang="bs"]: kamere iz seo/kamere.json (slika uživo samo gdje izvor to dopušta, inače poveznica).
 * Povrat: ukloniti require_once u apartmani-upiti.php.
 */
if (!defined('ABSPATH')) { exit; }

function abu_uzivo_t($l) {
    $t = array(
        'bs' => array('naslov' => 'Bihać uživo', 'sada' => 'Sada', 'osjet' => 'osjećaj', 'vjetar' => 'Vjetar', 'vlaga' => 'Vlaga', 'uv' => 'UV indeks', 'zrak' => 'Kvaliteta zraka', 'izlazak' => 'Izlazak sunca', 'zalazak' => 'Zalazak sunca', 'tecaj' => 'Tečaj', 'danas' => 'Danas', 'sutra' => 'Sutra', 'kisa' => 'kiša', 'izvor' => 'Vrijeme i zrak: Open-Meteo (osvježava se pri otvaranju stranice). Tečaj je fiksni: 1 € = 1,95583 KM.', 'ucitava' => 'Učitavam podatke…', 'greska' => 'Podaci trenutno nisu dostupni.', 'aqi' => array('dobra', 'prihvatljiva', 'umjerena', 'loša', 'vrlo loša', 'izrazito loša'), 'dani' => array('ned', 'pon', 'uto', 'sri', 'čet', 'pet', 'sub')),
        'en' => array('naslov' => 'Bihać live', 'sada' => 'Now', 'osjet' => 'feels like', 'vjetar' => 'Wind', 'vlaga' => 'Humidity', 'uv' => 'UV index', 'zrak' => 'Air quality', 'izlazak' => 'Sunrise', 'zalazak' => 'Sunset', 'tecaj' => 'Exchange rate', 'danas' => 'Today', 'sutra' => 'Tomorrow', 'kisa' => 'rain', 'izvor' => 'Weather and air: Open-Meteo (updated when the page opens). The rate is fixed: €1 = 1.95583 KM.', 'ucitava' => 'Loading…', 'greska' => 'Data currently unavailable.', 'aqi' => array('good', 'fair', 'moderate', 'poor', 'very poor', 'extremely poor'), 'dani' => array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat')),
        'de' => array('naslov' => 'Bihać live', 'sada' => 'Jetzt', 'osjet' => 'gefühlt', 'vjetar' => 'Wind', 'vlaga' => 'Luftfeuchte', 'uv' => 'UV-Index', 'zrak' => 'Luftqualität', 'izlazak' => 'Sonnenaufgang', 'zalazak' => 'Sonnenuntergang', 'tecaj' => 'Wechselkurs', 'danas' => 'Heute', 'sutra' => 'Morgen', 'kisa' => 'Regen', 'izvor' => 'Wetter und Luft: Open-Meteo (beim Öffnen der Seite aktualisiert). Der Kurs ist fest: 1 € = 1,95583 KM.', 'ucitava' => 'Daten werden geladen…', 'greska' => 'Daten derzeit nicht verfügbar.', 'aqi' => array('gut', 'ausreichend', 'mäßig', 'schlecht', 'sehr schlecht', 'extrem schlecht'), 'dani' => array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa')),
    );
    return isset($t[$l]) ? $t[$l] : $t['bs'];
}

add_shortcode('abu_uzivo', function ($a) {
    $a = shortcode_atts(array('lang' => 'bs'), $a);
    $T = abu_uzivo_t($a['lang']);
    $css = '<style>.abu-uz{margin:24px 0;padding:20px;border-radius:16px;background:#0b3b39;color:#fff}.abu-uz h2{color:#fff;margin:0 0 12px;font-size:1.3rem}.abu-uz-g{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}.abu-uz-k{background:#ffffff14;border-radius:12px;padding:12px}.abu-uz-k small{display:block;color:#cfe6e2;font-size:.8rem}.abu-uz-k b{font-size:1.25rem}.abu-uz-d{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:10px}.abu-uz p.abu-uz-i{margin:12px 0 0;font-size:.8rem;color:#cfe6e2}.abu-uz a{color:#ffda26}</style>';
    $h = $css . '<section class="abu-uz" data-l="' . esc_attr(wp_json_encode($T)) . '"><h2>' . esc_html($T['naslov']) . '</h2><div class="abu-uz-g"><div class="abu-uz-k">' . esc_html($T['ucitava']) . '</div></div>'
        . '<p class="abu-uz-i">' . esc_html($T['izvor']) . ' <a href="https://open-meteo.com/" target="_blank" rel="noopener">open-meteo.com</a></p></section>';
    $js = <<<'JS'
<script>(function(){var s=document.currentScript.previousElementSibling;if(!s||!s.dataset.l)return;var T=JSON.parse(s.dataset.l),g=s.querySelector('.abu-uz-g');
var W={0:'☀️',1:'🌤️',2:'⛅',3:'☁️',45:'🌫️',48:'🌫️',51:'🌦️',53:'🌦️',55:'🌧️',61:'🌧️',63:'🌧️',65:'🌧️',66:'🌧️',67:'🌧️',71:'🌨️',73:'🌨️',75:'❄️',77:'❄️',80:'🌦️',81:'🌧️',82:'⛈️',85:'🌨️',86:'❄️',95:'⛈️',96:'⛈️',99:'⛈️'};
function k(n,v){return '<div class="abu-uz-k"><small>'+n+'</small><b>'+v+'</b></div>'}
function hm(x){return x?x.slice(11,16):'–'}
var u='https://api.open-meteo.com/v1/forecast?latitude=44.8169&longitude=15.8708&current=temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,sunrise,sunset,uv_index_max&timezone=Europe%2FSarajevo&forecast_days=3';
var a='https://air-quality-api.open-meteo.com/v1/air-quality?latitude=44.8169&longitude=15.8708&current=european_aqi&timezone=Europe%2FSarajevo';
Promise.all([fetch(u).then(function(r){return r.json()}),fetch(a).then(function(r){return r.json()}).catch(function(){return null})]).then(function(x){var w=x[0],q=x[1],c=w.current,d=w.daily;
var aq=q&&q.current&&q.current.european_aqi!=null?q.current.european_aqi:null,ai=aq==null?-1:aq<=20?0:aq<=40?1:aq<=60?2:aq<=80?3:aq<=100?4:5;
var h=k(T.sada,(W[c.weather_code]||'')+' '+Math.round(c.temperature_2m)+' °C <small>'+T.osjet+' '+Math.round(c.apparent_temperature)+' °C</small>')+k(T.vjetar,Math.round(c.wind_speed_10m)+' km/h')+k(T.vlaga,c.relative_humidity_2m+' %')+k(T.uv,Math.round(d.uv_index_max[0]))+(ai>=0?k(T.zrak,T.aqi[ai]+' <small>EAQI '+Math.round(aq)+'</small>'):'')+k(T.izlazak,hm(d.sunrise[0]))+k(T.zalazak,hm(d.sunset[0]))+k(T.tecaj,'1 € = 1,95583 KM');
var dd='';for(var i=0;i<3;i++){var dt=new Date(d.time[i]+'T12:00:00');dd+=k(i===0?T.danas:i===1?T.sutra:T.dani[dt.getDay()],(W[d.weather_code[i]]||'')+' '+Math.round(d.temperature_2m_min[i])+'° / '+Math.round(d.temperature_2m_max[i])+'° <small>'+T.kisa+' '+(d.precipitation_probability_max[i]||0)+' %</small>')}
g.innerHTML=h;var e=document.createElement('div');e.className='abu-uz-d';e.innerHTML=dd;g.after(e)}).catch(function(){g.innerHTML=k('',T.greska)})})();</script>
JS;
    return $h . $js;
});

/** [abu_kamere lang="bs" grupa=""]: kamere iz seo/kamere.json. Slika uživo samo ako je "ugradi": true (izvor to dopušta). */
add_shortcode('abu_kamere', function ($a) {
    $a = shortcode_atts(array('lang' => 'bs', 'grupa' => ''), $a);
    $f = __DIR__ . '/seo/kamere.json';
    $k = is_readable($f) ? (array) json_decode((string) file_get_contents($f), true) : array();
    $l = in_array($a['lang'], array('bs', 'en', 'de'), true) ? $a['lang'] : 'bs';
    $otvori = array('bs' => 'Otvori kameru', 'en' => 'Open camera', 'de' => 'Kamera öffnen');
    $izv = array('bs' => 'Izvor', 'en' => 'Source', 'de' => 'Quelle');
    $h = '<style>.abu-kam{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px;margin:16px 0}.abu-kam-k{border:1px solid #e5e9e2;border-radius:14px;overflow:hidden;background:#fff}.abu-kam-k img{width:100%;aspect-ratio:16/9;object-fit:cover;display:block;background:#e9eee9}.abu-kam-k div{padding:10px 12px}.abu-kam-k strong{display:block}.abu-kam-k small{color:#555}</style><div class="abu-kam">';
    foreach ($k as $c) {
        if ($a['grupa'] && ($c['grupa'] ?? '') !== $a['grupa']) { continue; }
        $naziv = $c['naziv_' . $l] ?? ($c['naziv'] ?? '');
        $h .= '<div class="abu-kam-k">';
        if (!empty($c['ugradi']) && !empty($c['slika'])) {
            $h .= '<a href="' . esc_url($c['stranica']) . '" target="_blank" rel="noopener"><img src="' . esc_url($c['slika']) . '" alt="' . esc_attr($naziv) . '" loading="lazy" data-osvjezi="60"></a>';
        }
        $h .= '<div><strong>' . esc_html($naziv) . '</strong><small>' . esc_html($izv[$l] . ': ' . ($c['izvor'] ?? '')) . '</small><br><a href="' . esc_url($c['stranica']) . '" target="_blank" rel="noopener">' . esc_html($otvori[$l]) . ' →</a></div></div>';
    }
    $h .= '</div><script>setInterval(function(){document.querySelectorAll(".abu-kam img[data-osvjezi]").forEach(function(i){var u=i.src.split("#")[0].replace(/([?&])_t=\d+/,"");i.src=u+(u.indexOf("?")<0?"?":"&")+"_t="+Date.now()})},60000);</script>';
    return $h;
});
