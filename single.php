<?php
/**
 * Single Post Template — Checkmate Daily
 */
get_header();
?>
<div id="cd-reading-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
  <div id="cd-reading-progress-fill"></div>
</div>
<?php
?>
<main id="cd-main" role="main">
<div class="container">
<div class="cd-content-wrap">

  <!-- ARTICLE COLUMN -->
  <div class="cd-main-col">
  <?php while (have_posts()) : the_post();
      $cats    = get_the_category();
      $cat     = $cats ? $cats[0] : null;
      $feat_img = cd_get_post_image_url(get_the_ID(),'cd-hero');
  ?>

    <!-- Breadcrumb -->
    <div class="cd-breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
      <?php if ($cat) : ?>
        <span>›</span>
        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"><?php echo esc_html($cat->name); ?></a>
      <?php endif; ?>
      <span>›</span>
      <span class="cd-breadcrumb-current"><?php echo wp_trim_words(get_the_title(), 6); ?></span>
    </div>

    <article class="cd-single" <?php post_class(); ?>>

      <!-- Header -->
      <header class="cd-single-header">
        <?php if ($cat) : ?>
          <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
             class="cd-cat-badge <?php echo esc_attr(cd_get_cat_class($cat->slug)); ?>">
            <?php echo esc_html($cat->name); ?>
          </a>
        <?php endif; ?>
        <h1 class="cd-single-title"><?php the_title(); ?></h1>
        <div class="cd-single-meta">
          <span>By <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a></span>
          <span><?php echo get_the_date(); ?></span>
          <span><?php echo get_the_modified_date() !== get_the_date() ? 'Updated: ' . get_the_modified_date() : ''; ?></span>
          <span><?php comments_number('0 comments','1 comment','% comments'); ?></span>
        </div>
      </header>

      <!-- Featured Image -->
      <?php if ($feat_img) : ?>
        <?php cd_render_post_image(get_the_ID(), 'cd-hero', array('class'=>'cd-single-feat-img', 'loading'=>'eager', 'width'=>1100, 'height'=>420)); ?>
      <?php endif; ?>

      <!-- Content -->
      <div class="cd-single-content">
        <?php the_content(); ?>
        <?php
        wp_link_pages(array(
            'before' => '<div class="cd-pagination" style="justify-content:flex-start;margin-top:20px;">',
            'after'  => '</div>',
        ));
        ?>
      </div>

      <!-- Tags -->
      <?php $tags = get_the_tags(); if ($tags) : ?>
      <div class="cd-single-tags">
        <strong style="font-size:12px;margin-right:6px;">Tags:</strong>
        <?php foreach ($tags as $tag) : ?>
          <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag"><?php echo esc_html($tag->name); ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Author Box -->
      <div class="cd-author-box">
        <div class="cd-author-avatar">
          <?php echo get_avatar(get_the_author_meta('email'), 60); ?>
        </div>
        <div>
          <div class="cd-author-name">
            <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a>
          </div>
          <div class="cd-author-bio"><?php echo esc_html(get_the_author_meta('description') ?: 'Chess journalist at Checkmate Daily.'); ?></div>
        </div>
      </div>

    </article>

    <!-- Related Posts -->
    <?php if ($cat) :
        $related = get_posts(array(
            'category__in'   => array($cat->term_id),
            'post__not_in'   => array(get_the_ID()),
            'posts_per_page' => 4,
        ));
        if ($related) :
    ?>
    <div class="cd-section-head" style="margin-top:24px;">
      <h2>Related Posts</h2>
      <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="view-all">More in <?php echo esc_html($cat->name); ?> →</a>
    </div>
    <div class="cd-news-grid">
      <?php foreach ($related as $rp) :
          $r_cats = get_the_category($rp->ID);
          $r_cat  = $r_cats ? $r_cats[0] : null;
          $r_img  = cd_get_post_image_url($rp->ID,'cd-card');
      ?>
      <div class="cd-news-card">
        <div class="cd-news-card-img">
          <?php if ($r_img) : ?>
            <?php cd_render_post_image($rp->ID, 'cd-card', array('width'=>400, 'height'=>230)); ?>
          <?php endif; ?>
        </div>
        <div class="cd-news-card-body">
          <?php if ($r_cat) : ?>
            <a href="<?php echo esc_url(get_category_link($r_cat->term_id)); ?>"
               class="cd-cat-badge <?php echo esc_attr(cd_get_cat_class($r_cat->slug)); ?>">
               <?php echo esc_html($r_cat->name); ?></a>
          <?php endif; ?>
          <div class="cd-news-card-title">
            <a href="<?php echo esc_url(get_permalink($rp->ID)); ?>"><?php echo esc_html(get_the_title($rp->ID)); ?></a>
          </div>
          <div class="cd-news-card-meta"><?php echo get_the_date('M j, Y', $rp->ID); ?></div>
          <a href="<?php echo esc_url(get_permalink($rp->ID)); ?>" class="cd-readmore">Read More »</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; endif; ?>

    <!-- Comments -->
    <?php if (comments_open() || get_comments_number()) : ?>
      <div style="background:var(--cd-white);border-radius:var(--cd-radius);padding:24px;margin-top:16px;">
        <?php comments_template(); ?>
      </div>
    <?php endif; ?>

  <?php endwhile; ?>
  </div><!-- .cd-main-col -->

  <?php get_sidebar(); ?>
</div>
</div>
</main>
<?php get_footer(); ?>
