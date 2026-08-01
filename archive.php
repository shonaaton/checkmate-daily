<?php
/**
 * Archive / Category / Taxonomy Template — v2.5
 * Updates: Infinite Scroll Trigger & "All Caught Up" Logic
 */
get_header();

$is_state   = is_tax('chess_state');
$is_country = is_tax('chess_country');
$is_cat     = is_category();
$obj        = get_queried_object();
$cat_slug   = $is_cat ? $obj->slug : '';
$fmt        = cd_get_format_data($cat_slug);
$is_format  = !is_null($fmt);
?>
<main id="cd-main" role="main">
<div class="container">
<div class="cd-content-wrap">
<div class="cd-main-col">

<?php /* ── BREADCRUMB ── */ ?>
<nav class="cd-breadcrumb" aria-label="Breadcrumb">
  <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
  <?php if ($is_state) : ?>
    <span>&rsaquo;</span><a href="<?php echo esc_url(home_url('/chess-in/')); ?>">Indian State Chess News</a>
    <span>&rsaquo;</span><span class="cd-breadcrumb-current"><?php echo esc_html($obj->name); ?></span>
  <?php elseif ($is_country) : ?>
    <span>&rsaquo;</span><a href="<?php echo esc_url(home_url('/world/')); ?>">World Chess News</a>
    <span>&rsaquo;</span><span class="cd-breadcrumb-current"><?php echo esc_html($obj->name); ?></span>
  <?php elseif ($is_cat) : ?>
    <span>&rsaquo;</span><span class="cd-breadcrumb-current"><?php single_cat_title(); ?></span>
  <?php else : ?>
    <span>&rsaquo;</span><span class="cd-breadcrumb-current"><?php the_archive_title(); ?></span>
  <?php endif; ?>
</nav>

<?php if (($is_state || $is_country) && $obj) :
  /* ── REGIONAL PAGE HERO ── */
  $loc_desc = $obj->description ?: 'Latest chess news, tournament results, player ratings and FIDE updates from ' . $obj->name . '. Updated daily.';
?>
  <div class="cd-state-hero">
    <h1>Chess News <?php echo esc_html($obj->name); ?></h1>
    <p><?php echo esc_html($loc_desc); ?></p>
  </div>
  <div class="cd-seo-box">
    <h3>Related Searches</h3>
    <div class="cd-seo-keywords">
      <?php
      $kws = array(
        'chess news ' . strtolower($obj->name),
        strtolower($obj->name) . ' chess tournament 2026',
        strtolower($obj->name) . ' chess players fide',
      );
      if ($is_state) {
        $kws[] = 'aicf rated chess ' . strtolower($obj->name);
        $kws[] = strtolower($obj->name) . ' chess association';
      } else {
        $kws[] = strtolower($obj->name) . ' global chess news';
        $kws[] = strtolower($obj->name) . ' chess federation';
      }
      foreach ($kws as $kw) echo '<a href="' . esc_url(home_url('/?s=' . urlencode($kw))) . '" class="cd-seo-kw">' . esc_html($kw) . '</a>';
      ?>
    </div>
  </div>

<?php elseif ($is_format) :
  /* ── FORMAT PAGE HERO ── */
?>
  <div class="cd-format-hero <?php echo esc_attr($cat_slug); ?>">
    <div class="cd-format-hero-inner">
      <div class="fmt-tag">Chess Format</div>
      <h1><?php echo esc_html($fmt['title']); ?></h1>
      <p><?php echo esc_html($fmt['desc']); ?></p>
    </div>
  </div>
  <div class="cd-seo-box">
    <h3>Related Searches</h3>
    <div class="cd-seo-keywords">
      <?php 
      foreach ($fmt['seo_kw'] as $kw) echo '<a href="' . esc_url(home_url('/?s=' . urlencode($kw))) . '" class="cd-seo-kw">' . esc_html($kw) . '</a>'; 
      ?>
    </div>
  </div>

<?php else :
  /* ── GENERIC CATEGORY HEADER ── */
?>
  <div class="cd-archive-header">
    <h1><?php $is_cat ? single_cat_title() : the_archive_title(); ?></h1>
    <?php $desc = $is_cat ? category_description() : get_the_archive_description(); if ($desc) echo '<p>' . wp_kses_post($desc) . '</p>'; ?>
  </div>
<?php endif; ?>

<?php /* ── SECTION HEAD ── */ ?>
<div class="cd-section-head">
  <h2>
    <?php
    if ($is_state || $is_country) echo esc_html($obj->name) . ' Chess News';
    elseif ($is_cat) single_cat_title('');
    else             the_archive_title();
    ?>
  </h2>
  <?php global $wp_query; if ($wp_query->found_posts > 0) : ?>
  <span class="cd-post-count"><?php echo number_format_i18n($wp_query->found_posts); ?> articles</span>
  <?php endif; ?>
</div>

<?php /* ── POSTS GRID ── */ ?>
<?php if (have_posts()) : ?>
  <div class="cd-news-grid">
    <?php while (have_posts()) : the_post();
      $a_cats = get_the_category();
      $a_cat  = $a_cats ? $a_cats[0] : null;
      $a_img  = cd_get_post_image_url(get_the_ID(), 'cd-card');
    ?>
    <div class="cd-news-card">
      <div class="cd-news-card-img">
        <?php if ($a_img) : ?><?php cd_render_post_image(get_the_ID(), 'cd-card', array('width'=>400, 'height'=>230)); ?><?php endif; ?>
      </div>
      <div class="cd-news-card-body">
        <?php if ($a_cat) : ?>
          <a href="<?php echo esc_url(get_category_link($a_cat->term_id)); ?>"
             class="cd-cat-badge <?php echo esc_attr(cd_get_cat_class($a_cat->slug)); ?>"><?php echo esc_html($a_cat->name); ?></a>
        <?php endif; ?>
        <div class="cd-news-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
        <div class="cd-news-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 14); ?></div>
        <div class="cd-news-card-meta"><?php the_author(); ?> &middot; <?php echo get_the_date(); ?></div>
        <a href="<?php the_permalink(); ?>" class="cd-readmore">Read More</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

<?php cd_pagination(); ?>

<?php else :
  cd_render_empty_state(($is_state || $is_country) ? 'location' : 'category');
endif; ?>

<?php 
/* ── NEW: EXPLORE MORE COUNTRIES ── */
if ($is_country) : 
  $countries = get_terms(array('taxonomy' => 'chess_country', 'hide_empty' => true));
  if (!empty($countries) && !is_wp_error($countries) && count($countries) > 1) :
?>
  <div class="cd-section-head" style="margin-top:24px;"><h2>Explore More Countries</h2></div>
  <div class="cd-state-grid">
    <?php
    foreach ($countries as $c) {
        if ($c->term_id === $obj->term_id) continue; // Don't show the country we are currently on
        echo '<a href="' . esc_url(get_term_link($c)) . '" class="cd-state-card">';
        echo '<div class="cd-state-card-name">' . esc_html($c->name) . '</div>';
        echo '<div class="cd-state-card-count">' . $c->count . ' articles</div>';
        echo '</a>';
    }
    ?>
  </div>
<?php 
  endif; 
endif; 
?>

<?php cd_render_category_suggestions($cat_slug); ?>

<?php if ($is_format) : ?>
  <div class="cd-section-head" style="margin-top:8px;"><h2>About <?php echo esc_html($obj->name); ?></h2></div>
  <div class="cd-info-box">
    <?php
    $about = array(
      'blitz-rating'  => 'Blitz chess uses time controls between 3 and 5 minutes per player. It is officially recognised by FIDE, which runs a separate World Blitz Championship. India hosts hundreds of AICF-rated blitz tournaments annually.',
      'rapid-rating'  => 'Rapid chess uses time controls between 10 and 60 minutes per player. One of three official FIDE rating categories. India hosts a vibrant rapid circuit producing many of the world\'s top players.',
      'classical'     => 'Classical chess is the traditional format with 90+ minutes per player — the format of the World Chess Championship. India is now the undisputed global power in classical chess with World Champion D Gukesh.',
      'chess960'      => 'Chess960 (Fischer Random) randomises the starting positions of pieces on the back rank, creating 960 unique starting positions. FIDE officially recognises Chess960 and holds a separate World Championship.',
    );
    echo '<p>' . esc_html($about[$cat_slug] ?? '') . '</p>';
    ?>
  </div>
<?php endif; ?>

</div><?php get_sidebar(); ?>
</div>
</div>
</main>
<?php get_footer(); ?>
