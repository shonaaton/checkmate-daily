<?php get_header(); ?>
<main id="cd-main"><div class="container">
<div class="cd-404">
  <div class="cd-404-num">404</div>
  <h2>Page Not Found</h2>
  <p>The chess piece has moved off the board. The page you're looking for doesn't exist.</p>
  <a href="<?php echo esc_url(home_url('/'));?>">← Back to Home</a>
</div>
</div></main>
<?php get_footer();
