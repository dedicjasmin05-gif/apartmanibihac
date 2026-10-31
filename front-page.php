<?php
get_header();
$slike = ab_hero_slike();
$mjesta = ab_mjesta();
$istaknuti = ab_upit_objekti(array('broj' => 6));
?>
<section class="hero" aria-label="Pretraga smještaja">
  <div class="hero__slides" data-slides>
    <?php foreach ($slike as $i => $s) : ?>
      <img class="hero__img<?php echo $i === 0 ? ' is-on' : ''; ?>" src="<?php echo esc_url($s['src']); ?>" alt="<?php echo esc_attr($s['alt']); ?>" <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
    <?php endforeach; ?>
  </div>
  <div class="wrap hero__in">
    <h1 class="hero__title"><?php echo esc_html(ab_mod('ab_naslov', 'Smještaj u Bihaću, uz rijeku Unu')); ?></h1>
    <p class="hero__sub"><?php echo esc_html(ab_mod('ab_podnaslov', 'Apartmani u gradu i kuće za odmor na obali. Pošaljite upit, vlasnik potvrđuje u roku od 12 sati.')); ?></p>
  </div>
  <form class="wrap search" action="<?php echo esc_url(home_url('/smjestaj/')); ?>" method="get">
    <label class="search__f"><span>Gdje</span>
      <select name="mjesto"><option value="">Bihać i okolica</option><?php foreach (array_keys($mjesta) as $mj) : ?><option><?php echo esc_html($mj); ?></option><?php endforeach; ?></select>
    </label>
    <label class="search__f"><span>Dolazak</span><input type="date" name="dolazak" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
    <label class="search__f"><span>Odlazak</span><input type="date" name="odlazak" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
    <label class="search__f search__f--s"><span>Osoba</span><input type="number" name="osobe" min="1" max="30" value="2"></label>
    <button class="btn btn--sun search__go" type="submit">Pronađi slobodno</button>
  </form>
</section>

<section class="river" aria-labelledby="river-h">
  <div class="wrap">
    <h2 id="river-h" class="h2">Odaberite svoj dio Une</h2>
    <p class="lead">Una teče kroz Bihać i oko njega. Ista rijeka, četiri vrlo različita odmora.</p>
  </div>
  <div class="wrap river__map">
    <svg class="river__line" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0 70 C 120 20, 200 110, 330 66 S 520 18, 620 62 S 820 116, 930 60 S 1110 20, 1200 54" fill="none" stroke="currentColor" stroke-width="16" stroke-linecap="round"/>
      <path class="river__flow" d="M0 70 C 120 20, 200 110, 330 66 S 520 18, 620 62 S 820 116, 930 60 S 1110 20, 1200 54" fill="none" stroke="#fff" stroke-width="2" stroke-dasharray="3 22" stroke-linecap="round"/>
    </svg>
    <ol class="river__stops">
      <?php $n = 0; foreach ($mjesta as $mj => $opis) : $n++; ?>
        <li class="river__stop river__stop--<?php echo (int) $n; ?>">
          <a href="<?php echo esc_url(add_query_arg('mjesto', $mj, home_url('/smjestaj/'))); ?>">
            <span class="river__dot" aria-hidden="true"></span>
            <span class="river__name"><?php echo esc_html($mj); ?></span>
            <span class="river__desc"><?php echo esc_html($opis); ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ol>
    <p class="river__dir" aria-hidden="true">smjer toka Une</p>
  </div>
</section>

<?php if ($istaknuti) : ?>
<section class="section" aria-labelledby="izd-h">
  <div class="wrap">
    <div class="section__head">
      <h2 id="izd-h" class="h2">Smještaj koji gosti najčešće biraju</h2>
      <a class="link" href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Sav smještaj</a>
    </div>
    <div class="grid-cards">
      <?php foreach ($istaknuti as $o) { echo ab_kartica($o['id']); } // phpcs:ignore ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="how" aria-labelledby="how-h">
  <div class="wrap how__in">
    <h2 id="how-h" class="h2">Kako ide rezervacija</h2>
    <ol class="how__steps">
      <li><strong>Pošaljete upit</strong>Odaberete objekt i datume. Termin je 12 sati držan samo za vas.</li>
      <li><strong>Vlasnik potvrđuje</strong>Dobijete e-mail i SMS s ukupnom cijenom, najkasnije za 12 sati.</li>
      <li><strong>Uplatite kaparu od 30 %</strong>Karticom ili na račun. Ostatak plaćate pri dolasku.</li>
      <li><strong>Stižu adresa i kontakt</strong>Uz potvrdu dobijete adresu i broj vlasnika, a dan prije dolaska podsjetnik.</li>
    </ol>
    <p class="how__note">Radije telefonom? <a href="<?php echo esc_url(ab_wa('Pozdrav, tražim smještaj u Bihaću.')); ?>" target="_blank" rel="noopener">Pišite nam na WhatsApp</a> ili nazovite <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', ab_tel())); ?>"><?php echo esc_html(ab_tel()); ?></a>.</p>
  </div>
</section>

<section class="section split" aria-labelledby="raft-h">
  <div class="wrap split__in">
    <div class="split__text">
      <h2 id="raft-h" class="h2">Dan na Uni: rafting, ručak uz rijeku</h2>
      <p>Una Olympic Rafting vodi spust kroz kanjon uz licencirane skipere i svu opremu, a nakon raftinga čeka vas restoran na obali. Smještaj i rafting dogovaramo u istom upitu.</p>
      <p><a class="btn btn--deep" href="<?php echo esc_url(home_url('/rafting-na-uni/')); ?>">Rafting i paketi</a></p>
    </div>
    <div class="split__media">
      <?php $rs = ab_slike_svih(1); if ($rs) { echo wp_get_attachment_image(reset($rs), 'ab-wide', false, array('loading' => 'lazy')); } // phpcs:ignore ?>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="prep-h">
  <div class="wrap">
    <h2 id="prep-h" class="h2">Što raditi u Bihaću</h2>
    <ul class="tiles">
      <li><a href="<?php echo esc_url(home_url('/restorani-bihac/')); ?>"><b>Gdje jesti</b><span>Riba iz Une, travnički ćevapi, domaća kuhinja</span></a></li>
      <li><a href="<?php echo esc_url(home_url('/nocni-zivot-bihac/')); ?>"><b>Gdje izaći</b><span>Barovi na obali i klubovi u gradu</span></a></li>
      <li><a href="<?php echo esc_url(home_url('/vodic/')); ?>"><b>Izleti</b><span>Štrbački buk, Plitvice, Kostelski buk</span></a></li>
    </ul>
  </div>
</section>

<?php $blog = get_posts(array('posts_per_page' => 3)); if ($blog) : ?>
<section class="section" aria-labelledby="blog-h">
  <div class="wrap">
    <div class="section__head">
      <h2 id="blog-h" class="h2">Iz vodiča</h2>
      <a class="link" href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/vodic/')); ?>">Svi članci</a>
    </div>
    <div class="grid-posts">
      <?php foreach ($blog as $p) : ?>
        <article class="post-card">
          <h3><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html(get_the_title($p)); ?></a></h3>
          <p><?php echo esc_html(get_the_excerpt($p)); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section faq" aria-labelledby="faq-h">
  <div class="wrap wrap--narrow">
    <h2 id="faq-h" class="h2">Česta pitanja</h2>
    <?php foreach (ab_faq() as $f) : ?>
      <details><summary><?php echo esc_html($f[0]); ?></summary><p><?php echo esc_html($f[1]); ?></p></details>
    <?php endforeach; ?>
  </div>
</section>
<?php get_footer();
