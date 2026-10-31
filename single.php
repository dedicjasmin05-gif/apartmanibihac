<?php get_header(); the_post(); ?>
<article>
  <section class="page-head"><div class="wrap wrap--narrow">
    <nav class="crumbs" aria-label="Putanja"><a href="<?php echo esc_url(home_url('/')); ?>">Početna</a> / <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/vodic/')); ?>">Vodič</a></nav>
    <h1 class="h1"><?php the_title(); ?></h1>
    <p class="meta"><time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>">Ažurirano <?php echo esc_html(get_the_modified_date('j. n. Y.')); ?></time></p>
  </div></section>
  <?php if (has_post_thumbnail()) : ?><div class="wrap wrap--narrow post-hero"><?php the_post_thumbnail('ab-wide'); ?></div><?php endif; ?>
  <div class="wrap wrap--narrow prose"><?php the_content(); ?></div>
  <div class="wrap wrap--narrow post-cta">
    <h2 class="h3">Tražite smještaj za ovaj izlet?</h2>
    <p><a class="btn btn--sun" href="<?php echo esc_url(home_url('/smjestaj/')); ?>">Pogledajte slobodne objekte</a></p>
  </div>
</article>
<?php get_footer();
