<?php
/**
 * Template Name: News Hub
 */
get_header();
?>
<main id="cd-main" role="main">
<div class="container">
<div class="cd-content-wrap">
<div class="cd-main-col">

  <nav class="cd-breadcrumb" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
    <span>&rsaquo;</span><span>News</span>
  </nav>

  <div class="cd-archive-header">
    <h1>All Chess News</h1>
    <p>Latest chess news from India and around the world — Blitz, Rapid, Classical, Chess960 and more. All categories in one place.</p>
  </div>

  <div class="cd-filter-panel">
    <div class="cd-filter-row">
      <label class="cd-filter-label">Filter by:</label>
      <div class="cd-filter-bar" id="cd-news-cat-filters">
        <a href="<?php echo esc_url(get_permalink()); ?>" class="cd-filter-btn active" data-filter="category" data-value="all">All</a>
        <?php
        $all_cats = cd_get_all_chess_categories();
        foreach ($all_cats as $cat) :
          if ($cat->count === 0) continue;
        ?>
        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="cd-filter-btn" data-filter="category" data-value="<?php echo esc_attr($cat->slug); ?>">
          <?php echo esc_html($cat->name); ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="cd-section-head"><h2>Latest News</h2></div>

  <div id="cd-india-results">
    <?php
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
    $all_q = new WP_Query(array('posts_per_page' => 8, 'post_status' => 'publish', 'paged' => $paged));
    if ($all_q->have_posts()) :
      echo '<div class="cd-news-grid">';
      while ($all_q->have_posts()) : $all_q->the_post();
        $nc = get_the_category(); $nc = $nc ? $nc[0] : null;
        $ni = cd_get_post_image_url(get_the_ID(), 'cd-card');
        ?>
        <div class="cd-news-card">
          <div class="cd-news-card-img"><?php if ($ni) cd_render_post_image(get_the_ID(), 'cd-card', array('width'=>400, 'height'=>230)); ?></div>
          <div class="cd-news-card-body">
            <?php if ($nc) : ?><a href="<?php echo esc_url(get_category_link($nc->term_id)); ?>" class="cd-cat-badge <?php echo esc_attr(cd_get_cat_class($nc->slug)); ?>"><?php echo esc_html($nc->name); ?></a><?php endif; ?>
            <div class="cd-news-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
            <div class="cd-news-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 14); ?></div>
            <div class="cd-news-card-meta"><?php the_author(); ?> &middot; <?php echo get_the_date(); ?></div>
            <a href="<?php the_permalink(); ?>" class="cd-readmore">Read More</a>
          </div>
        </div>
        <?php
      endwhile;
      echo '</div>';
      ?>
      <?php cd_pagination( $all_q->max_num_pages ); ?>
      <?php
    else :
      cd_render_empty_state();
    endif;
    wp_reset_postdata();
    ?>
  </div>
  <?php cd_render_category_suggestions(); ?>
</div>
<?php get_sidebar(); ?>
</div>
</div>
</main>
<?php get_footer(); ?>
