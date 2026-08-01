<?php
/**
 * Template Name: World Chess News Hub
 */
get_header();
?>
<main id="cd-main" role="main">
<div class="container">
<div class="cd-content-wrap">
<div class="cd-main-col">

  <nav class="cd-breadcrumb" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
    <span>&rsaquo;</span><span>World Chess News</span>
  </nav>

  <div class="cd-india-hero" style="background: var(--cd-blue-deeper);">
    <h1>World Chess News</h1>
    <p>Global chess coverage, international tournament results, and updates from top chess nations around the world. Select a country to see local chess news.</p>
  </div>

  <div class="cd-section-head" style="margin-top:24px;"><h2>Browse by Country</h2></div>
  <div class="cd-state-grid">
    <?php
    $countries = get_terms(array('taxonomy' => 'chess_country', 'hide_empty' => true));
    if (!empty($countries) && !is_wp_error($countries)) {
        foreach ($countries as $country) {
            $url = get_term_link($country);
            echo '<a href="' . esc_url($url) . '" class="cd-state-card">';
            echo '<div class="cd-state-card-name">' . esc_html($country->name) . '</div>';
            echo '<div class="cd-state-card-count">' . $country->count . ' articles</div>';
            echo '</a>';
        }
    } else {
        echo '<p style="font-size: 13px; color: var(--cd-gray-muted); grid-column: 1 / -1;">Countries will appear here automatically as you publish news about them!</p>';
    }
    ?>
  </div>

  <div class="cd-section-head" style="margin-top:24px;"><h2>Latest Global News</h2></div>

  <div id="cd-india-results">
    <?php
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
    $world_q = new WP_Query(array(
        'posts_per_page' => 8,
        'post_status'    => 'publish',
        'paged'          => $paged,
        'tax_query'      => array(array('taxonomy' => 'chess_country','operator' => 'EXISTS')),
    ));
    
    if ($world_q->have_posts()) :
      echo '<div class="cd-news-grid">';
      while ($world_q->have_posts()) : $world_q->the_post();
        $i_img   = cd_get_post_image_url(get_the_ID(),'cd-card');
        $i_terms = wp_get_object_terms(get_the_ID(), 'chess_country');
        $i_term  = $i_terms && !is_wp_error($i_terms) ? $i_terms[0] : null;
        ?>
        <div class="cd-news-card">
          <div class="cd-news-card-img"><?php if ($i_img) : ?><?php cd_render_post_image(get_the_ID(), 'cd-card', array('width'=>400, 'height'=>230)); ?><?php endif; ?></div>
          <div class="cd-news-card-body">
            <?php if ($i_term) : ?>
              <a href="<?php echo esc_url(get_term_link($i_term)); ?>" class="cd-cat-badge dark"><?php echo esc_html($i_term->name); ?></a>
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
    <?php cd_pagination( $world_q->max_num_pages ); ?>
      <?php
    else :
      echo '<p style="font-size: 13px; color: var(--cd-gray-muted);">Publish a post and tag it with a Country to see it appear here.</p>';
    endif;
    wp_reset_postdata();
    ?>
  </div>

</div><?php get_sidebar(); ?>
</div>
</div>
</main>
<?php get_footer(); ?>
