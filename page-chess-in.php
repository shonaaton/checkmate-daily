<?php
/**
 * Template Name: Indian Chess News Hub
 */
get_header();
?>
<main id="cd-main" role="main">
<div class="container">
<div class="cd-content-wrap">
<div class="cd-main-col">

  <nav class="cd-breadcrumb" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
    <span>&rsaquo;</span><span>Indian Chess News Hub</span>
  </nav>

  <div class="cd-india-hero">
    <h1>Indian Chess News Hub</h1>
    <p>The most comprehensive source for Indian chess news — covering tournaments, ratings, and player achievements from all states. From grassroots to grandmaster level, updated daily.</p>
  </div>

  <div class="cd-section-head" style="margin-top:14px;">
    <h2>Latest Indian Chess Hub Articles</h2>
  </div>

  <div id="cd-india-results">
    <?php
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
    $india_q = new WP_Query(array('posts_per_page'=>8,'post_status'=>'publish', 'paged'=>$paged));
    if ($india_q->have_posts()) :
      echo '<div class="cd-news-grid">';
      while ($india_q->have_posts()) : $india_q->the_post();
        $i_cats   = get_the_category();
        $i_cat    = $i_cats ? $i_cats[0] : null;
        $i_img    = cd_get_post_image_url(get_the_ID(),'cd-card');
        $i_states = wp_get_object_terms(get_the_ID(),'chess_state');
        $i_state  = $i_states && !is_wp_error($i_states) ? $i_states[0] : null;
        ?>
        <div class="cd-news-card">
          <div class="cd-news-card-img"><?php if ($i_img) : ?><?php cd_render_post_image(get_the_ID(), 'cd-card', array('width'=>400, 'height'=>230)); ?><?php endif; ?></div>
          <div class="cd-news-card-body">
            <?php if ($i_state) : ?>
              <a href="<?php echo esc_url(get_term_link($i_state)); ?>" class="cd-cat-badge"><?php echo esc_html($i_state->name); ?></a>
            <?php elseif ($i_cat) : ?>
              <a href="<?php echo esc_url(get_category_link($i_cat->term_id)); ?>" class="cd-cat-badge <?php echo esc_attr(cd_get_cat_class($i_cat->slug)); ?>"><?php echo esc_html($i_cat->name); ?></a>
            <?php endif; ?>
            <div class="cd-news-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
            <div class="cd-news-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(),14); ?></div>
            <div class="cd-news-card-meta"><?php the_author(); ?> &middot; <?php echo get_the_date(); ?></div>
            <a href="<?php the_permalink(); ?>" class="cd-readmore">Read More</a>
          </div>
        </div>
        <?php
      endwhile;
      echo '</div>';
      ?>
     <?php cd_pagination( $india_q->max_num_pages ); ?>
      <?php
    else :
      cd_render_empty_state();
    endif;
    wp_reset_postdata();
    ?>
  </div>

  <div class="cd-section-head" style="margin-top:24px;"><h2>Browse by State</h2></div>
  <div class="cd-state-grid">
    <?php
    $states = cd_get_india_states();
    foreach ($states as $i => $st) :
      $term  = get_term_by('slug', $st['slug'], 'chess_state');
      $count = $term ? $term->count : 0;
      $url   = $term ? get_term_link($term) : home_url('/chess-in/' . $st['slug'] . '/');
    ?>
    <a href="<?php echo esc_url($url); ?>" class="cd-state-card<?php echo ($i===0)?' featured':''; ?>">
      <div class="cd-state-card-name"><?php echo esc_html($st['name']); ?></div>
      <div class="cd-state-card-count"><?php echo $count ? $count . ' articles' : 'Coming soon'; ?></div>
    </a>
    <?php endforeach; ?>
  </div>

</div><?php get_sidebar(); ?>
</div>
</div>
</main>
<?php get_footer(); ?>
