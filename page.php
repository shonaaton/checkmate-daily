<?php /* page.php — Static Pages */ get_header(); ?>
<main id="cd-main"><div class="container"><div class="cd-content-wrap">
<div class="cd-main-col">
<?php while(have_posts()):the_post(); ?>
<article class="cd-single"><?php if(cd_get_post_image_url(get_the_ID(),'cd-hero')):?><?php cd_render_post_image(get_the_ID(), 'cd-hero', array('class'=>'cd-single-feat-img', 'loading'=>'eager', 'width'=>1100, 'height'=>420)); ?><?php endif;?>
<div class="cd-single-header"><h1 class="cd-single-title"><?php the_title();?></h1><div class="cd-single-meta"><span><?php echo get_the_date();?></span></div></div>
<div class="cd-single-content"><?php the_content();?></div></article>
<?php endwhile;?>
</div><?php get_sidebar();?></div></div></main>
<?php get_footer();
