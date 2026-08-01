<?php
/**
 * Homepage / Main Index — Checkmate Daily
 * Fixes: Pagination swapped to Load More/All Caught Up logic
 *        TV section refactored from inline styles to CSS classes
 */
get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
?>

<main id="cd-main" role="main">
<div class="container">
<div class="cd-content-wrap">

  <div class="cd-main-col">

    <?php
    /* ==========================================================
       HERO & THUMBS (ONLY SHOW ON PAGE 1)
       ========================================================== */
    if ( $paged == 1 ) :
    ?>

      <?php
      /* ── HERO: Latest Post ── */
      $hero_args = array( 'posts_per_page' => 1, 'post_status' => 'publish', 'ignore_sticky_posts' => true );
      $hero_q    = new WP_Query( $hero_args );
      if ( $hero_q->have_posts() ) : $hero_q->the_post();
          $cats    = get_the_category();
          $cat     = $cats ? $cats[0] : null;
          $hero_bg = cd_get_post_image_url( get_the_ID(), 'cd-hero' );
      ?>
      <div class="cd-hero" style="<?php echo $hero_bg ? 'background-image:none;' : ''; ?>">
        <?php if ( $hero_bg ) : ?>
          <?php cd_render_post_image( get_the_ID(), 'cd-hero', array( 'class'=>'hero-thumbnail', 'loading'=>'eager', 'width'=>1100, 'height'=>420 ) ); ?>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <?php if ( $cat ) : ?>
            <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="hero-cat"><?php echo esc_html( $cat->name ); ?></a>
          <?php endif; ?>
          <h2 class="hero-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <div class="hero-meta">
            <span>By <?php the_author(); ?></span>
            <span><?php echo get_the_date(); ?></span>
            <?php if ( $cat ) echo '<span>' . esc_html( $cat->name ) . '</span>'; ?>
          </div>
          <div class="hero-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></div>
        </div>
      </div>
      <?php wp_reset_postdata(); endif; ?>

      <?php
      /* ── HERO THUMBS: Posts 2–4 ── */
      $thumb_args = array( 'posts_per_page' => 3, 'offset' => 1, 'post_status' => 'publish' );
      $thumb_q    = new WP_Query( $thumb_args );
      if ( $thumb_q->have_posts() ) :
      ?>
      <div class="cd-hero-thumbs">
        <?php while ( $thumb_q->have_posts() ) : $thumb_q->the_post();
            $t_cats = get_the_category();
            $t_cat  = $t_cats ? $t_cats[0] : null;
            $t_img  = cd_get_post_image_url( get_the_ID(), 'cd-thumb' );
        ?>
        <div class="cd-thumb-card">
          <div class="cd-thumb-img">
            <?php if ( $t_img ) : ?>
              <?php cd_render_post_image( get_the_ID(), 'cd-thumb', array( 'width'=>400, 'height'=>260 ) ); ?>
            <?php endif; ?>
          </div>
          <div class="cd-thumb-body">
            <?php if ( $t_cat ) : ?>
              <div class="cd-thumb-cat"><?php echo esc_html( $t_cat->name ); ?></div>
            <?php endif; ?>
            <div class="cd-thumb-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
            <div class="cd-thumb-date"><?php echo get_the_date(); ?></div>
          </div>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
      <?php endif; ?>

    <?php endif; // End Page 1 Hero & Thumbs ?>


    <?php
    /* ==========================================================
       LATEST NEWS GRID (PAGINATED ACROSS ALL PAGES)
       ========================================================== */

    $posts_per_page = get_option( 'posts_per_page' );
    $offset         = 4 + ( ( $paged - 1 ) * $posts_per_page );

    $grid_args = array(
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'offset'         => $offset,
    );
    $grid_q = new WP_Query( $grid_args );

    $total_posts             = wp_count_posts( 'post' )->publish;
    $grid_q->max_num_pages   = ceil( ( $total_posts - 4 ) / $posts_per_page );

    if ( $grid_q->have_posts() ) :
    ?>
    <div class="cd-section-head">
      <h2><?php echo ( $paged == 1 ) ? 'Latest News' : 'More News — Page ' . $paged; ?></h2>
      <?php if ( $paged == 1 ) : ?>
        <a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="view-all">View All →</a>
      <?php endif; ?>
    </div>

    <div class="cd-news-grid">
      <?php while ( $grid_q->have_posts() ) : $grid_q->the_post();
          $g_cats = get_the_category();
          $g_cat  = $g_cats ? $g_cats[0] : null;
          $g_img  = cd_get_post_image_url( get_the_ID(), 'cd-card' );
          $g_cls  = $g_cat ? cd_get_cat_class( $g_cat->slug ) : '';
      ?>
      <div class="cd-news-card">
        <div class="cd-news-card-img">
          <?php if ( $g_img ) : ?>
            <?php cd_render_post_image( get_the_ID(), 'cd-card', array( 'width'=>400, 'height'=>230 ) ); ?>
          <?php endif; ?>
        </div>
        <div class="cd-news-card-body">
          <?php if ( $g_cat ) : ?>
            <a href="<?php echo esc_url( get_category_link( $g_cat->term_id ) ); ?>"
               class="cd-cat-badge <?php echo esc_attr( $g_cls ); ?>"><?php echo esc_html( $g_cat->name ); ?></a>
          <?php endif; ?>
          <div class="cd-news-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
          <div class="cd-news-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 14 ); ?></div>
          <div class="cd-news-card-meta"><?php the_author(); ?> &middot; <?php echo get_the_date(); ?></div>
          <a href="<?php the_permalink(); ?>" class="cd-readmore">Read More »</a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; // End news grid have_posts ?>


    <?php
    /* ==========================================================
       SECTION REPORTS (ONLY SHOW ON PAGE 1)
       ========================================================== */
    if ( $paged == 1 ) :
      $format_slugs = array( 'classical-rating', 'rapid-rating', 'blitz-rating', 'chess960' );
      $format_terms = array();

      foreach ( $format_slugs as $format_slug ) {
        $format_term = get_category_by_slug( $format_slug );
        if ( $format_term && ! is_wp_error( $format_term ) && (int) $format_term->count > 0 ) {
          $format_terms[] = $format_term;
        }
      }

      $country_terms = get_terms( array(
        'taxonomy'   => 'chess_country',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 4,
      ) );

      if ( is_wp_error( $country_terms ) ) {
        $country_terms = array();
      }

      if ( $format_terms || $country_terms ) :
    ?>
    <div class="cd-report-section">
      <div class="cd-section-head">
        <h2>Section Reports</h2>
        <a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="view-all">View All</a>
      </div>

      <div class="cd-report-columns">
        <?php if ( $format_terms ) : ?>
        <div class="cd-report-group">
          <div class="cd-report-group-head">
            <h3>By Format</h3>
          </div>
          <div class="cd-report-card-grid">
            <?php foreach ( $format_terms as $format_term ) : ?>
              <a href="<?php echo esc_url( get_category_link( $format_term->term_id ) ); ?>" class="cd-report-card">
                <span class="cd-report-card-name"><?php echo esc_html( $format_term->name ); ?></span>
                <span class="cd-report-card-count"><?php echo number_format_i18n( $format_term->count ); ?> articles</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $country_terms ) : ?>
        <div class="cd-report-group">
          <div class="cd-report-group-head">
            <h3>By Country</h3>
            <a href="<?php echo esc_url( home_url( '/world/' ) ); ?>">More</a>
          </div>
          <div class="cd-report-card-grid">
            <?php foreach ( $country_terms as $country_term ) : ?>
              <a href="<?php echo esc_url( get_term_link( $country_term ) ); ?>" class="cd-report-card">
                <span class="cd-report-card-name"><?php echo esc_html( $country_term->name ); ?></span>
                <span class="cd-report-card-count"><?php echo number_format_i18n( $country_term->count ); ?> articles</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
      endif;
    endif; // End Page 1 reports
    ?>


    <?php
    /* ==========================================================
       CHECKMATE DAILY TV (DISABLED)
       ========================================================== */
    if ( false && $paged == 1 ) :
    ?>
    <div class="cd-tv-section">

      <div class="cd-section-head" style="border-bottom-color: #FF0000;">
        <h2 style="color: #FF0000;">Checkmate Daily TV</h2>
        <a href="https://www.youtube.com/@CheckmateDailyChess?sub_confirmation=1"
           target="_blank" class="view-all">Subscribe on YouTube →</a>
      </div>

      <!-- Latest Coverage -->
      <div class="cd-tv-section-head">
        <h3>Latest Coverage</h3>
        <a href="<?php echo esc_url( home_url( '/videos/' ) ); ?>" class="view-lib">View Library »</a>
      </div>

      <div class="cd-video-grid">
        <?php
        $video_args = array(
          'post_type'      => 'cd_video',
          'post_status'    => 'publish',
          'posts_per_page' => 4,
          'tax_query'      => array(
            'relation' => 'OR',
            array( 'taxonomy' => 'video_playlist', 'field' => 'slug', 'terms' => 'shorts', 'operator' => 'NOT IN' ),
            array( 'taxonomy' => 'video_playlist', 'operator' => 'NOT EXISTS' ),
          ),
        );
        $video_q = new WP_Query( $video_args );

        if ( $video_q->have_posts() ) :
          while ( $video_q->have_posts() ) : $video_q->the_post();
            $v_id = cd_get_video_youtube_id( get_the_ID() );
        ?>
        <div class="cd-video-card">
          <a href="<?php the_permalink(); ?>">
            <?php if ( $v_id ) : ?>
              <?php cd_render_youtube_thumbnail( $v_id, array( 'quality'=>'hq720', 'fallback_quality'=>'hqdefault', 'alt'=>get_the_title(), 'width'=>480, 'height'=>360 ) ); ?>
              <div class="cd-video-gradient"></div>
              <div class="cd-play-btn">&#9654;</div>
              <div class="cd-video-title"><?php echo wp_trim_words( get_the_title(), 8 ); ?></div>
            <?php else : ?>
              <div class="cd-video-placeholder"></div>
            <?php endif; ?>
          </a>
        </div>
        <?php
          endwhile;
          wp_reset_postdata();
        else : ?>
          <div class="cd-video-empty">Upload your first video to see it here!</div>
        <?php endif; ?>
      </div><!-- .cd-video-grid -->

      <!-- Trending Shorts -->
      <div class="cd-tv-section-head">
        <h3>Trending Shorts</h3>
      </div>

      <div class="cd-shorts-grid">
        <?php
        $shorts_args = array(
          'post_type'      => 'cd_video',
          'post_status'    => 'publish',
          'posts_per_page' => 4,
          'tax_query'      => array(
            array( 'taxonomy' => 'video_playlist', 'field' => 'slug', 'terms' => 'shorts' ),
          ),
        );
        $shorts_q = new WP_Query( $shorts_args );

        if ( $shorts_q->have_posts() ) :
          while ( $shorts_q->have_posts() ) : $shorts_q->the_post();
            $v_id = cd_get_video_youtube_id( get_the_ID() );
        ?>
        <div class="cd-short-card">
          <a href="<?php the_permalink(); ?>">
            <?php if ( $v_id ) : ?>
              <?php cd_render_youtube_thumbnail( $v_id, array( 'alt'=>get_the_title(), 'width'=>480, 'height'=>360 ) ); ?>
              <div class="cd-video-gradient"></div>
              <div class="cd-play-btn">&#9654;</div>
              <div class="cd-video-title"><?php echo wp_trim_words( get_the_title(), 7 ); ?></div>
            <?php else : ?>
              <div class="cd-video-placeholder"></div>
            <?php endif; ?>
          </a>
        </div>
        <?php
          endwhile;
          wp_reset_postdata();
        else : ?>
          <div class="cd-video-empty">Tag a video as "shorts" in your dashboard to move it here!</div>
        <?php endif; ?>
      </div><!-- .cd-shorts-grid -->

    </div><!-- .cd-tv-section -->
    <?php endif; // End Page 1 Only (TV section) ?>

<?php cd_pagination( $grid_q->max_num_pages ); ?>

    <?php wp_reset_postdata(); ?>

  </div><?php get_sidebar(); ?>

</div></div></main>

<?php get_footer(); ?>
