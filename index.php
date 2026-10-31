<?php get_header(); ?>
<section class="page-head"><div class="wrap"><h1 class="h1"><?php echo esc_html(is_home() ? 'Vodič za Bihać i Unu' : wp_strip_all_tags(get_the_archive_title())); ?></h1></div></section>
<div class="wrap">
  <?php if (have_posts()) : ?>
    <div class="grid-posts">
      <?php while (have_posts()) : the_post(); ?>
        <article class="post-card">
          <?php if (has_post_thumbnail()) : ?><a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail('ab-card', array('loading' => 'lazy')); ?></a><?php endif; ?>
          <h2 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo esc_html(get_the_excerpt()); ?></p>
        </article>
      <?php endwhile; ?>
    </div>
    <nav class="pager"><?php the_posts_pagination(array('prev_text' => 'Novije', 'next_text' => 'Starije')); ?></nav>
  <?php else : ?>
    <p>Ovdje još nema članaka.</p>
  <?php endif; ?>
</div>
<?php get_footer();
