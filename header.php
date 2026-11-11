<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0B3B39">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#sadrzaj">Preskoči na sadržaj</a>
<?php $tel = ab_tel(); $tel_h = 'tel:' . preg_replace('/[^0-9+]/', '', $tel); $l = ab_jezik(); ?>
<div class="topbar">
  <div class="wrap topbar__in">
    <a class="topbar__trust" href="<?php echo esc_url(ab_fb()); ?>" target="_blank" rel="noopener">
      <span class="topbar__fb"><?php echo ab_ikona('fb'); // phpcs:ignore ?></span>
      <span class="stars" aria-hidden="true">★★★★★</span>
      <span><b>9.362</b> <?php echo esc_html(ab_t('recenzija') . ' ' . ab_t('na_fb')); ?><span class="topbar__more"> · <b>139.000</b> <?php echo esc_html(ab_t('pratitelja')); ?> · 100 % <?php echo esc_html(ab_t('preporuka')); ?></span></span>
    </a>
    <div class="topbar__contact">
      <a class="topbar__tel" href="<?php echo esc_attr($tel_h); ?>"><?php echo ab_ikona('tel'); // phpcs:ignore ?><span><?php echo esc_html($tel); ?></span></a>
      <a class="topbar__ic" href="<?php echo esc_url(ab_wa()); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo ab_ikona('wa'); // phpcs:ignore ?></a>
      <a class="topbar__ic" href="viber://chat?number=%2B<?php echo esc_attr(preg_replace('/\D/', '', ab_mod('ab_wa', '38761591320'))); ?>" aria-label="Viber"><?php echo ab_ikona('vb'); // phpcs:ignore ?></a>
      <a class="topbar__ic" href="mailto:<?php echo esc_attr(ab_mod('ab_email', 'apartmaniubihacu@gmail.com')); ?>" aria-label="E-mail"><?php echo ab_ikona('mail'); // phpcs:ignore ?></a>
      <a class="topbar__ic" href="<?php echo esc_url(ab_ig()); ?>" target="_blank" rel="noopener" aria-label="Instagram"><?php echo ab_ikona('ig'); // phpcs:ignore ?></a>
      <span class="topbar__lang"><?php foreach (array('bs' => '/', 'en' => '/en/', 'de' => '/de/') as $k => $u) : ?><a href="<?php echo esc_url(home_url($u)); ?>" hreflang="<?php echo esc_attr($k); ?>"<?php echo $k === $l ? ' aria-current="true"' : ''; ?>><?php echo esc_html(strtoupper($k)); ?></a><?php endforeach; ?></span>
    </div>
  </div>
</div>
<header class="site-head<?php echo is_front_page() ? ' site-head--over' : ''; ?>">
  <div class="wrap site-head__in">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>, početna">
      <?php if (has_custom_logo()) : echo wp_get_attachment_image(get_theme_mod('custom_logo'), 'medium', false, array('class' => 'brand__logo', 'alt' => get_bloginfo('name'))); else : ?>
      <svg class="brand__mark" viewBox="0 0 40 40" aria-hidden="true"><path d="M3 26c6-6 10 4 17-2s10-4 17 2" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/><path d="M8 18 20 8l12 10" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span class="brand__name"><span>Apartmani <i>&amp;</i> Vikendice</span><small>Bihać · rijeka Una</small></span>
      <?php endif; ?>
    </a>
    <nav class="nav" aria-label="Glavni izbornik">
      <ul class="nav__list">
        <?php foreach (ab_izbornik() as $s) : ?><li><a href="<?php echo esc_url($s[1]); ?>"><?php echo esc_html($s[0]); ?></a></li><?php endforeach; ?>
      </ul>
    </nav>
    <a class="btn btn--sun head-cta" href="<?php echo esc_url(home_url('/smjestaj/')); ?>"><?php echo esc_html(ab_t('rezerviraj')); ?></a>
    <button class="nav-toggle" aria-expanded="false" aria-controls="mob-nav"><span class="sr">Izbornik</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
  </div>
  <div class="mob-nav" id="mob-nav" hidden>
    <ul>
      <?php foreach (ab_izbornik() as $s) : ?><li><a href="<?php echo esc_url($s[1]); ?>"><?php echo esc_html($s[0]); ?></a></li><?php endforeach; ?>
      <li><a href="<?php echo esc_attr($tel_h); ?>"><?php echo esc_html($tel); ?></a></li>
    </ul>
  </div>
</header>
<?php if (!is_front_page() && function_exists('abu_vijesti_traka')) { echo abu_vijesti_traka(); } // phpcs:ignore ?>
<main id="sadrzaj">
