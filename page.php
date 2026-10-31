<?php get_header(); the_post(); ?>
<section class="page-head"><div class="wrap wrap--narrow"><h1 class="h1"><?php the_title(); ?></h1><?php if (has_excerpt()) : ?><p class="lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?></div></section>
<div class="wrap wrap--narrow prose"><?php the_content(); ?></div>
<?php get_footer();
