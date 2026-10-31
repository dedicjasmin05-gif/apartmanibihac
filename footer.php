</main>
<footer class="site-foot">
  <svg class="river-edge" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c160-34 320 22 480 0s320-34 480-6 320 30 480 0V60H0z" fill="currentColor"/></svg>
  <div class="wrap foot-grid">
    <div class="foot-about">
      <p class="foot-brand">apartmani<b>bihać</b></p>
      <p>Apartmani i kuće za odmor u Bihaću i uz rijeku Unu. Upit šaljete direktno, bez provizije posrednika.</p>
      <p class="foot-contact">
        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', ab_tel())); ?>"><?php echo esc_html(ab_tel()); ?></a><br>
        <a href="<?php echo esc_url(ab_wa()); ?>" target="_blank" rel="noopener">WhatsApp / Viber</a><br>
        <a href="mailto:<?php echo esc_attr(ab_mod('ab_email', 'apartmaniubihacu@gmail.com')); ?>"><?php echo esc_html(ab_mod('ab_email', 'apartmaniubihacu@gmail.com')); ?></a>
      </p>
    </div>
    <nav aria-label="Mjesta">
      <p class="foot-h">Smještaj po mjestu</p>
      <ul><?php foreach (array_keys(ab_mjesta()) as $mj) : ?><li><a href="<?php echo esc_url(add_query_arg('mjesto', $mj, home_url('/smjestaj/'))); ?>">Smještaj <?php echo esc_html($mj); ?></a></li><?php endforeach; ?></ul>
    </nav>
    <nav aria-label="Korisno">
      <p class="foot-h">Korisno</p>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/rafting-na-uni/')); ?>">Rafting na Uni</a></li>
        <li><a href="<?php echo esc_url(home_url('/restorani-bihac/')); ?>">Restorani u Bihaću</a></li>
        <li><a href="<?php echo esc_url(home_url('/nocni-zivot-bihac/')); ?>">Noćni život</a></li>
        <li><a href="<?php echo esc_url(home_url('/vodic/')); ?>">Vodič za Bihać i Unu</a></li>
        <li><a href="<?php echo esc_url(home_url('/prijavite-objekt/')); ?>">Iznajmljujete? Prijavite objekt</a></li>
      </ul>
    </nav>
    <div>
      <p class="foot-h">Pratite nas</p>
      <ul>
        <?php if (ab_mod('ab_facebook')) : ?><li><a href="<?php echo esc_url(ab_mod('ab_facebook')); ?>" rel="noopener" target="_blank">Facebook</a></li><?php endif; ?>
        <?php if (ab_mod('ab_instagram')) : ?><li><a href="<?php echo esc_url(ab_mod('ab_instagram')); ?>" rel="noopener" target="_blank">Instagram</a></li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="wrap foot-legal">© <?php echo esc_html(gmdate('Y')); ?> apartmanibihac.com</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
