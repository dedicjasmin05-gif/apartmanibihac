<?php
get_header();
$slike = ab_hero_slike();
$mjesta = ab_mjesta();
$istaknuti = ab_upit_objekti(array('broj' => 8));
$tel = ab_tel();
$slika = function ($kljuc) { return function_exists('abu_vodic_naslovna') ? abu_vodic_naslovna($kljuc, 'ab-card') : ''; };
$forma = function ($klasa) use ($mjesta) {
    ob_start(); ?>
  <form class="search <?php echo esc_attr($klasa); ?>" action="<?php echo esc_url(home_url('/smjestaj/')); ?>" method="get">
    <label class="search__f"><span>Gdje</span>
      <select name="mjesto"><option value="">Bihać i okolica</option><?php foreach (array_keys($mjesta) as $mj) : ?><option><?php echo esc_html($mj); ?></option><?php endforeach; ?></select>
    </label>
    <label class="search__f"><span>Dolazak</span><input type="date" name="dolazak" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
    <label class="search__f"><span>Odlazak</span><input type="date" name="odlazak" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
    <label class="search__f search__f--s"><span>Osoba</span><input type="number" name="osobe" min="1" max="30" value="2"></label>
    <button class="btn btn--sun search__go" type="submit">Pronađi slobodno</button>
  </form>
    <?php return ob_get_clean();
};
?>
<section class="hero" aria-label="Smještaj u Bihaću">
  <div class="hero__slides" data-slides>
    <?php foreach ($slike as $i => $s) : ?>
      <img class="hero__img<?php echo $i === 0 ? ' is-on' : ''; ?>" src="<?php echo esc_url($s['src']); ?>"<?php echo ab_hero_srcset($s['src']); // phpcs:ignore ?> alt="<?php echo esc_attr($s['alt']); ?>" <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
    <?php endforeach; ?>
  </div>
  <div class="wrap hero__in">
    <p class="hero__kicker">Cijeli apartman od <b>25 € (50 KM)</b> po noći</p>
    <h1 class="hero__title"><?php echo esc_html(ab_mod('ab_naslov', 'Smještaj u Bihaću, uz rijeku Unu')); ?></h1>
    <p class="hero__sub">Apartmani u gradu i kuće uz Unu. Plaćate gotovinom pri dolasku, a posteljina i čišćenje su uključeni.</p>
    <?php echo ab_povjerenje('trust--hero'); // phpcs:ignore ?>
    <div class="hero__cta">
      <a class="btn btn--sun" href="#smjestaj">Pogledaj smještaj</a>
      <a class="btn btn--glass" href="<?php echo esc_url(ab_wa('Pozdrav, tražim smještaj u Bihaću.')); ?>" target="_blank" rel="noopener"><?php echo ab_ikona('wa'); // phpcs:ignore ?> WhatsApp</a>
    </div>
  </div>
  <div class="wrap hero__search"><?php echo $forma('search--hero'); // phpcs:ignore ?></div>
</section>

<?php if (function_exists('abu_vijesti_traka')) { echo abu_vijesti_traka(); } // phpcs:ignore ?>

<?php if ($istaknuti) : ?>
<section class="section section--first" id="smjestaj" aria-labelledby="izd-h">
  <div class="wrap">
    <div class="section__head">
      <div>
        <p class="eyebrow">Naš smještaj</p>
        <h2 id="izd-h" class="h2">Smještaj koji gosti najčešće biraju</h2>
      </div>
      <a class="link" href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Sav smještaj (<?php echo (int) wp_count_posts('objekt')->publish; ?>) →</a>
    </div>
  </div>
  <div class="wrap wrap--bleed">
    <div class="grid-cards grid-cards--slide">
      <?php foreach ($istaknuti as $o) { echo ab_kartica($o['id']); } // phpcs:ignore ?>
    </div>
  </div>
  <div class="wrap"><p class="more-mob"><a class="btn btn--ghost" href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Pogledaj sav smještaj</a></p></div>
</section>
<?php endif; ?>

<?php // Istaknute vijesti iz turizma (ako ih još nema, opće novosti); pretraga je ispod
if (function_exists('abu_vijesti_blok')) { $tv = abu_vijesti_blok('turizam', 4, 'Istaknute vijesti iz turizma'); echo $tv ? $tv : abu_vijesti_blok('', 4, 'Novosti iz Bihaća i s Une'); } // phpcs:ignore ?>

<section class="find" aria-labelledby="find-h">
  <div class="wrap">
    <p class="eyebrow eyebrow--light">Pretraga</p>
    <h2 id="find-h" class="h2">Slobodni termini za vaše datume</h2>
    <?php echo $forma('search--mid'); // phpcs:ignore ?>
    <ol class="places">
      <?php foreach ($mjesta as $mj => $opis) : ?>
        <li><a href="<?php echo esc_url(add_query_arg('mjesto', $mj, home_url('/smjestaj/'))); ?>"><b><?php echo esc_html($mj); ?></b><span><?php echo esc_html($opis); ?></span></a></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="how" aria-labelledby="how-h">
  <div class="wrap how__in">
    <div>
      <p class="eyebrow">Bez komplikacija</p>
      <h2 id="how-h" class="h2">Kako ide rezervacija</h2>
      <p class="how__note">Radije telefonom? Nazovite <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $tel)); ?>"><?php echo esc_html($tel); ?></a> ili pišite na <a href="<?php echo esc_url(ab_wa('Pozdrav, tražim smještaj u Bihaću.')); ?>" target="_blank" rel="noopener">WhatsApp</a> i Viber.</p>
    </div>
    <ol class="how__steps">
      <li><strong>Pošaljete upit</strong>Odaberete objekt i datume. Termin je 12 sati držan samo za vas.</li>
      <li><strong>Potvrdimo termin</strong>Dobijete potvrdu s ukupnom cijenom, adresom i kontaktom, najkasnije za 12 sati.</li>
      <li><strong>Plaćate pri dolasku</strong>Većinu smještaja plaćate gotovinom pri dolasku. Avansno plaćanje kod pojedinih objekata ovisi o pravilima tog objekta.</li>
      <li><strong>Ulazak od 12 sati</strong>Izlazak do 11, ili do 12 ako je objekt slobodan. Posteljina i čišćenje su uključeni.</li>
    </ol>
  </div>
</section>

<section class="promo" aria-labelledby="tb-h">
  <div class="wrap promo__in">
    <div class="promo__media"><?php echo $slika('teambuilding'); // phpcs:ignore ?></div>
    <div class="promo__text">
      <p class="eyebrow eyebrow--light">Za firme i grupe</p>
      <h2 id="tb-h" class="h2">Team building u Bihaću</h2>
      <p>Smještaj, rafting, kajaci, kvadovi, jahanje, ručak na rijeci i muzika uživo. Sve složite sami u nekoliko klikova, a mi spojimo hotele, rafting kuće i izvođače u jednu ponudu.</p>
      <p><a class="btn btn--sun" href="<?php echo esc_url(home_url('/team-building-bihac/')); ?>">Složi team building</a></p>
    </div>
  </div>
</section>

<section class="section split" aria-labelledby="raft-h">
  <div class="wrap split__in">
    <div class="split__text">
      <p class="eyebrow">Una Olympic Rafting</p>
      <h2 id="raft-h" class="h2">Dan na Uni: rafting i ručak uz rijeku</h2>
      <p>Spust kroz kanjon Une uz licencirane skipere i svu opremu, a nakon raftinga ručak na obali. Smještaj i rafting dogovaramo u istom upitu.</p>
      <p><a class="btn btn--deep" href="<?php echo esc_url(home_url('/rafting-na-uni/')); ?>">Rafting i paketi</a></p>
    </div>
    <div class="split__media">
      <?php $r = $slika('rafting'); if ($r) { echo $r; } else { $rs = ab_slike_svih(1); if ($rs) { echo wp_get_attachment_image(reset($rs), 'ab-wide', false, array('loading' => 'lazy')); } } // phpcs:ignore ?>
    </div>
  </div>
</section>

<section class="section section--tight" aria-labelledby="prep-h">
  <div class="wrap">
    <p class="eyebrow">Vodič</p>
    <h2 id="prep-h" class="h2">Što raditi u Bihaću</h2>
    <ul class="ptiles">
      <?php foreach (array(
          array('restorani', 'restorani-bihac', 'Gdje jesti', 'Riba iz Une, ćevapi, domaća kuhinja'),
          array('izlasci', 'nocni-zivot-bihac', 'Gdje izaći', 'Barovi na obali, terase i klubovi'),
          array('izleti', 'izleti-bihac', 'Izleti', 'Štrbački buk, Martin Brod, Plitvice'),
          array('rafting', 'rafting-na-uni', 'Rafting', 'Kanjon Une uz skipera'),
          array('teambuilding', 'team-building-bihac', 'Team building', 'Za firme i grupe'),
          array('', 'cesta-pitanja', 'Česta pitanja', '1000 odgovora o Bihaću i Uni'),
      ) as $t) : ?>
        <li><a href="<?php echo esc_url(home_url('/' . $t[1] . '/')); ?>"><span class="ptiles__img"><?php echo $t[0] ? $slika($t[0]) : ''; // phpcs:ignore ?></span><span class="ptiles__txt"><b><?php echo esc_html($t[2]); ?></b><span><?php echo esc_html($t[3]); ?></span></span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php $blog = get_posts(array('posts_per_page' => 3, 'category__not_in' => function_exists('abu_vijesti_kat_ids') ? abu_vijesti_kat_ids() : array())); if ($blog) : ?>
<section class="section section--tight" aria-labelledby="blog-h">
  <div class="wrap">
    <div class="section__head">
      <div><p class="eyebrow">Blog</p><h2 id="blog-h" class="h2">Iz vodiča</h2></div>
      <a class="link" href="<?php echo esc_url(home_url('/vodic/')); ?>">Svi članci →</a>
    </div>
    <div class="grid-posts">
      <?php foreach ($blog as $p) : ?>
        <article class="post-card">
          <?php if (has_post_thumbnail($p)) : ?><a href="<?php echo esc_url(get_permalink($p)); ?>" tabindex="-1" aria-hidden="true"><?php echo get_the_post_thumbnail($p, 'ab-card', array('loading' => 'lazy')); ?></a><?php endif; ?>
          <h3><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html(get_the_title($p)); ?></a></h3>
          <p><?php echo esc_html(get_the_excerpt($p)); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="social" aria-labelledby="soc-h">
  <div class="wrap social__in">
    <div>
      <p class="eyebrow eyebrow--light">Apartmani &amp; Vikendice</p>
      <h2 id="soc-h" class="h2">Najveća zajednica za smještaj u Bihaću</h2>
      <p>Nove fotografije, slobodne termine i akcije prvo objavljujemo na Facebooku i Instagramu.</p>
      <p class="social__btns">
        <a class="btn btn--fb" href="<?php echo esc_url(ab_fb()); ?>" target="_blank" rel="noopener"><?php echo ab_ikona('fb'); // phpcs:ignore ?> Facebook</a>
        <a class="btn btn--ig" href="<?php echo esc_url(ab_ig()); ?>" target="_blank" rel="noopener"><?php echo ab_ikona('ig'); // phpcs:ignore ?> Instagram</a>
      </p>
    </div>
    <ul class="social__nums">
      <li><b>139.000+</b><span>pratitelja na Facebooku</span></li>
      <li><b>9.362</b><span>recenzija na Facebooku</span></li>
      <li><b>100 %</b><span>preporučuje</span></li>
      <li><b>12 h</b><span>do potvrde upita</span></li>
    </ul>
  </div>
</section>

<section class="section faq" aria-labelledby="faq-h">
  <div class="wrap wrap--narrow">
    <h2 id="faq-h" class="h2">Česta pitanja</h2>
    <?php foreach (ab_faq() as $f) : ?>
      <details><summary><?php echo esc_html($f[0]); ?></summary><p><?php echo esc_html($f[1]); ?></p></details>
    <?php endforeach; ?>
    <p><a class="link" href="<?php echo esc_url(home_url('/cesta-pitanja/')); ?>">Svih 1000 pitanja i odgovora →</a></p>
  </div>
</section>
<?php get_footer();
