<?php get_header(); ?>
<main id="cd-main"><div class="container"><div class="cd-content-wrap">
<div class="cd-main-col">
<div class="cd-search-header">
  <h1>Search Results for: <em><?php echo esc_html(get_search_query());?></em></h1>
  <p><?php global $wp_query; echo $wp_query->found_posts; ?> posts found</p>
</div>
<?php if(have_posts()):?>
<div class="cd-news-grid">
<?php while(have_posts()):the_post();
  $s_cats=get_the_category();$s_cat=$s_cats?$s_cats[0]:null;
  $s_img=get_the_post_thumbnail_url(get_the_ID(),'cd-card');?>
<div class="cd-news-card">
  <div class="cd-news-card-img"><?php if($s_img):?><img src="<?php echo esc_url($s_img);?>" alt="<?php the_title_attribute();?>" loading="lazy"><?php endif;?></div>
  <div class="cd-news-card-body">
    <?php if($s_cat):?><a href="<?php echo esc_url(get_category_link($s_cat->term_id));?>" class="cd-cat-badge"><?php echo esc_html($s_cat->name);?></a><?php endif;?>
    <div class="cd-news-card-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></div>
    <div class="cd-news-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(),15);?></div>
    <div class="cd-news-card-meta"><?php the_author();?> &middot; <?php echo get_the_date();?></div>
    <a href="<?php the_permalink();?>" class="cd-readmore">Read More »</a>
  </div>
</div>
<?php endwhile;?>
</div>
<div class="cd-pagination"><?php the_posts_pagination(array('prev_text'=>'← Prev','next_text'=>'Next →'));?></div>
<?php else:?>
<div style="background:#fff;border-radius:4px;padding:40px;text-align:center;">
  <p style="font-size:16px;color:#666;">No results found for "<?php echo esc_html(get_search_query());?>".</p>
  <?php get_search_form();?>
</div>
<?php endif;?>
</div><?php get_sidebar();?></div></div></main>
<?php get_footer();
