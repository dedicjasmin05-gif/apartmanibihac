<?php
get_header();
the_post();
$id = get_the_ID();
$slike = ab_slike($id);
$kap = (int) ab_m($id, 'kapacitet');
$mjesto = ab_m($id, 'mjesto');
$min = max(1, (int) ab_m($id, 'min_noci'));
$oprema = wp_get_post_terms($id, 'oprema', array('fields' => 'names'));
?>
<article class="objekt">
  <div class="wrap">
    <nav class="crumbs" aria-label="Putanja"><a href="<?php echo esc_url(home_url('/')); ?>">Početna</a> / <a href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Smještaj</a><?php if ($mjesto) : ?> / <a href="<?php echo esc_url(add_query_arg('mjesto', $mjesto, home_url('/smjestaj/'))); ?>"><?php echo esc_html($mjesto); ?></a><?php endif; ?></nav>
    <h1 class="h1 objekt__title"><?php the_title(); ?></h1>
    <ul class="objekt__facts">
      <?php if ($mjesto) : ?><li><?php echo esc_html($mjesto); ?></li><?php endif; ?>
      <?php if ($kap) : ?><li>do <?php echo esc_html(ab_osobe($kap)); ?></li><?php endif; ?>
      <li><?php echo esc_html(ab_cijena_txt($id)); ?></li>
      <?php if ($min > 1) : ?><li>najmanje <?php echo (int) $min; ?> noći</li><?php endif; ?>
    </ul>
  </div>

  <?php if ($slike) : ?>
  <div class="wrap mosaic mosaic--<?php echo (int) min(5, count($slike)); ?>" data-gallery>
    <?php foreach (array_slice($slike, 0, 5) as $i => $sid) :
        $full = wp_get_attachment_image_url($sid, 'full'); ?>
      <a class="mosaic__item mosaic__item--<?php echo (int) $i; ?>" href="<?php echo esc_url($full); ?>">
        <?php echo wp_get_attachment_image($sid, $i === 0 ? 'ab-wide' : 'ab-card', false, array('loading' => $i === 0 ? 'eager' : 'lazy', 'alt' => get_the_title() . ', fotografija ' . ($i + 1))); // phpcs:ignore ?>
      </a>
    <?php endforeach; ?>
    <?php if (count($slike) > 5) : ?>
      <button class="mosaic__all" type="button" data-gallery-open>Sve fotografije (<?php echo count($slike); ?>)</button>
      <div hidden><?php foreach (array_slice($slike, 5) as $sid) : ?><a href="<?php echo esc_url(wp_get_attachment_image_url($sid, 'full')); ?>"></a><?php endforeach; ?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="wrap objekt__layout">
    <div class="objekt__main prose">
      <?php the_content(); ?>
      <?php if ($oprema && !is_wp_error($oprema)) : ?>
        <h2>Oprema</h2>
        <ul class="amen"><?php foreach ($oprema as $o) : ?><li><?php echo esc_html($o); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if ($mjesto) : ?>
        <h2>Lokacija</h2>
        <p>Točnu adresu šaljemo nakon potvrde rezervacije. Karta prikazuje mjesto <?php echo esc_html($mjesto); ?>.</p>
        <div class="map"><iframe title="Karta: <?php echo esc_attr($mjesto); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?php echo esc_url('https://www.google.com/maps?q=' . rawurlencode($mjesto . ', Bihać, Bosna i Hercegovina') . '&z=13&output=embed'); ?>"></iframe></div>
      <?php endif; ?>
    </div>
    <aside class="book" id="upit" aria-label="Upit za ovaj objekt">
      <div class="book__card">
        <p class="book__price"><?php echo esc_html(ab_cijena_txt($id)); ?></p>
        <h2 class="h3">Slobodni termini</h2>
        <?php echo do_shortcode('[abu_kalendar id="' . (int) $id . '" mjeseci="2"]'); // phpcs:ignore ?>
        <h2 class="h3">Pošaljite upit</h2>
        <?php echo do_shortcode('[abu_upit id="' . (int) $id . '"]'); // phpcs:ignore ?>
        <p class="book__alt">Ili odmah na <a href="<?php echo esc_url(ab_wa('Pozdrav, zanima me ' . get_the_title() . '. Termin: ')); ?>" target="_blank" rel="noopener">WhatsApp</a>.</p>
      </div>
    </aside>
  </div>

  <?php $slicni = array_filter(ab_upit_objekti(array('mjesto' => $mjesto, 'broj' => 4)), function ($o) use ($id) { return $o['id'] !== $id; }); if ($slicni) : ?>
  <section class="section" aria-labelledby="sl-h">
    <div class="wrap">
      <h2 id="sl-h" class="h2">Još smještaja<?php echo $mjesto ? ' u mjestu ' . esc_html($mjesto) : ''; ?></h2>
      <div class="grid-cards"><?php foreach (array_slice($slicni, 0, 3) as $o) { echo ab_kartica($o['id']); } // phpcs:ignore ?></div>
    </div>
  </section>
  <?php endif; ?>
</article>
<div class="mob-book"><span><?php echo esc_html(ab_cijena_txt($id)); ?></span><a class="btn btn--sun" href="#upit">Pošalji upit</a></div>
<?php get_footer();
