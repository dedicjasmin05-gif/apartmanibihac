<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0E4F4C">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#sadrzaj">Preskoči na sadržaj</a>
<header class="site-head<?php echo is_front_page() ? ' site-head--over' : ''; ?>">
  <div class="wrap site-head__in">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>, početna">
      <svg class="brand__mark" viewBox="0 0 40 40" aria-hidden="true"><path d="M3 26c6-6 10 4 17-2s10-4 17 2" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/><path d="M8 18 20 8l12 10" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span class="brand__name">apartmani<b>bihać</b></span>
    </a>
    <nav class="nav" aria-label="Glavni izbornik">
      <?php if (has_nav_menu('glavni')) {
          wp_nav_menu(array('theme_location' => 'glavni', 'container' => false, 'menu_class' => 'nav__list', 'depth' => 1));
      } else { ?>
        <ul class="nav__list">
          <li><a href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Smještaj</a></li>
          <li><a href="<?php echo esc_url(home_url('/smjestaj/?mjesto=Bihać')); ?>">U gradu</a></li>
          <li><a href="<?php echo esc_url(home_url('/smjestaj/?oprema=izlaz-na-unu')); ?>">Na Uni</a></li>
          <li><a href="<?php echo esc_url(home_url('/rafting-na-uni/')); ?>">Rafting</a></li>
          <li><a href="<?php echo esc_url(home_url('/vodic/')); ?>">Vodič</a></li>
        </ul>
      <?php } ?>
    </nav>
    <a class="btn btn--sun head-cta" href="<?php echo esc_url(ab_wa('Pozdrav, tražim smještaj u Bihaću.')); ?>" target="_blank" rel="noopener">WhatsApp</a>
    <button class="nav-toggle" aria-expanded="false" aria-controls="mob-nav"><span class="sr">Izbornik</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
  </div>
  <div class="mob-nav" id="mob-nav" hidden>
    <ul>
      <li><a href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Sav smještaj</a></li>
      <?php foreach (array_keys(ab_mjesta()) as $mj) : ?><li><a href="<?php echo esc_url(add_query_arg('mjesto', $mj, home_url('/smjestaj/'))); ?>"><?php echo esc_html($mj); ?></a></li><?php endforeach; ?>
      <li><a href="<?php echo esc_url(home_url('/rafting-na-uni/')); ?>">Rafting</a></li>
      <li><a href="<?php echo esc_url(home_url('/vodic/')); ?>">Vodič za Bihać</a></li>
      <li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', ab_tel())); ?>"><?php echo esc_html(ab_tel()); ?></a></li>
    </ul>
  </div>
</header>
<main id="sadrzaj">
