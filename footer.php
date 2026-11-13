</main>
<?php $tel = ab_tel(); $tel_h = 'tel:' . preg_replace('/[^0-9+]/', '', $tel); $mail = ab_mod('ab_email', 'apartmaniubihacu@gmail.com'); ?>
<footer class="site-foot">
  <svg class="river-edge" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c160-34 320 22 480 0s320-34 480-6 320 30 480 0V60H0z" fill="currentColor"/></svg>
  <div class="wrap foot-grid">
    <div class="foot-about">
      <p class="foot-brand">Apartmani <i>&amp;</i> Vikendice</p>
      <p>Apartmani i kuće za odmor u Bihaću i uz rijeku Unu. Cijeli apartman od 25 € (50 KM) po noći, plaćanje gotovinom pri dolasku.</p>
      <p class="foot-contact">
        <a href="<?php echo esc_attr($tel_h); ?>"><?php echo ab_ikona('tel'); // phpcs:ignore ?> <?php echo esc_html($tel); ?></a>
        <a href="<?php echo esc_url(ab_wa()); ?>" target="_blank" rel="noopener"><?php echo ab_ikona('wa'); // phpcs:ignore ?> WhatsApp / Viber</a>
        <a href="mailto:<?php echo esc_attr($mail); ?>"><?php echo ab_ikona('mail'); // phpcs:ignore ?> <?php echo esc_html($mail); ?></a>
      </p>
      <?php echo ab_povjerenje('trust--foot'); // phpcs:ignore ?>
    </div>
    <nav aria-label="Mjesta">
      <p class="foot-h">Smještaj</p>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Sav smještaj</a></li>
        <?php foreach (array_keys(ab_mjesta()) as $mj) : ?><li><a href="<?php echo esc_url(add_query_arg('mjesto', $mj, home_url('/smjestaj/'))); ?>">Smještaj <?php echo esc_html($mj); ?></a></li><?php endforeach; ?>
        <li><a href="<?php echo esc_url(home_url('/jeftin-smjestaj-bihac/')); ?>">Jeftin smještaj Bihać</a></li>
        <li><a href="<?php echo esc_url(home_url('/vikendice-na-uni/')); ?>">Vikendice na Uni</a></li>
      </ul>
    </nav>
    <nav aria-label="Vodič">
      <p class="foot-h">Vodič za Bihać</p>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/restorani-bihac/')); ?>">Restorani u Bihaću</a></li>
        <li><a href="<?php echo esc_url(home_url('/nocni-zivot-bihac/')); ?>">Noćni život</a></li>
        <li><a href="<?php echo esc_url(home_url('/izleti-bihac/')); ?>">Izleti iz Bihaća</a></li>
        <li><a href="<?php echo esc_url(home_url('/rafting-na-uni/')); ?>">Rafting na Uni</a></li>
        <li><a href="<?php echo esc_url(home_url('/team-building-bihac/')); ?>">Team building Bihać</a></li>
        <li><a href="<?php echo esc_url(home_url('/vodic/')); ?>">Blog i novosti</a></li>
        <li><a href="<?php echo esc_url(home_url('/cesta-pitanja/')); ?>">Česta pitanja</a></li>
        <li><a href="<?php echo esc_url(home_url('/promet-granice/')); ?>">Saobraćaj, granice i kamere</a></li>
        <li><a href="<?php echo esc_url(home_url('/usporedba-smjestaja/')); ?>">Poređenje smještaja</a></li>
        <li><a href="<?php echo esc_url(home_url('/en/guide/')); ?>" hreflang="en">Travel guides (English)</a></li>
        <li><a href="<?php echo esc_url(home_url('/de/reisefuehrer/')); ?>" hreflang="de">Reiseführer (Deutsch)</a></li>
      </ul>
    </nav>
    <div>
      <p class="foot-h">Pratite nas</p>
      <ul>
        <li><a href="<?php echo esc_url(ab_fb()); ?>" rel="noopener" target="_blank">Facebook · 139.000+</a></li>
        <li><a href="<?php echo esc_url(ab_ig()); ?>" rel="noopener" target="_blank">Instagram</a></li>
        <li><a href="<?php echo esc_url(home_url('/o-nama/')); ?>">O nama</a></li>
        <li><a href="<?php echo esc_url(home_url('/prijavite-objekt/')); ?>">Iznajmljujete? Prijavite objekt</a></li>
        <li><a href="<?php echo esc_url(home_url('/uvjeti-i-privatnost/')); ?>">Uslovi i privatnost</a></li>
      </ul>
    </div>
  </div>
  <div class="wrap foot-legal">© <?php echo esc_html(gmdate('Y')); ?> apartmanibihac.com <span class="foot-lang"><a href="<?php echo esc_url(home_url('/')); ?>" hreflang="bs">Bosanski</a> · <a href="<?php echo esc_url(home_url('/en/')); ?>" hreflang="en">English</a> · <a href="<?php echo esc_url(home_url('/de/')); ?>" hreflang="de">Deutsch</a></span></div>
</footer>
<nav class="actbar" aria-label="Kontakt">
  <a href="<?php echo esc_attr($tel_h); ?>"><?php echo ab_ikona('tel'); // phpcs:ignore ?><span><?php echo esc_html(ab_t('nazovi')); ?></span></a>
  <a href="<?php echo esc_url(ab_wa()); ?>" target="_blank" rel="noopener" class="actbar__wa"><?php echo ab_ikona('wa'); // phpcs:ignore ?><span>WhatsApp</span></a>
  <a href="viber://chat?number=%2B<?php echo esc_attr(preg_replace('/\D/', '', ab_mod('ab_wa', '38761591320'))); ?>" class="actbar__vb"><?php echo ab_ikona('vb'); // phpcs:ignore ?><span>Viber</span></a>
  <a href="<?php echo esc_url(home_url('/smjestaj/')); ?>" class="actbar__go"><span><?php echo esc_html(ab_t('upit')); ?></span></a>
</nav>
<?php wp_footer(); ?>
</body>
</html>
