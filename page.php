<?php /* page.php — Static Pages */ get_header(); ?>
<main id="cd-main"><div class="container"><div class="cd-content-wrap">
<div class="cd-main-col">
<?php while(have_posts()):the_post(); ?>
<article class="cd-single"><?php if(has_post_thumbnail()):?><img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(),'cd-hero'));?>" class="cd-single-feat-img" alt="<?php the_title_attribute();?>"><?php endif;?>
<div class="cd-single-header"><h1 class="cd-single-title"><?php the_title();?></h1><div class="cd-single-meta"><span><?php echo get_the_date();?></span></div></div>
<div class="cd-single-content"><?php the_content();?></div></article>
<?php endwhile;?>
</div><?php get_sidebar();?></div></div></main>
<?php get_footer();
