<?php
/**
 * Footer Template — v2.1
 * Added: Back-to-top button (shown/hidden via JS scroll threshold)
 */
?>
<footer id="cd-footer" role="contentinfo">
  <div class="container">
    <div class="cd-footer-grid">

      <div class="cd-footer-col">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="cd-footer-logo" style="display:block; margin-bottom: 16px; max-width: 220px;">
          <img src="<?php echo esc_url( cd_uploads_url( '2026/02/cropped-Checkmate-daily.jpg' ) ); ?>" alt="Checkmate Daily" style="width: 100%; height: auto; display: block; border-radius: 4px;">
        </a>
        <p class="cd-footer-desc">India's premier chess news source — covering tournaments, ratings, and player achievements from every state. Updated daily.</p>
      </div>

      <div class="cd-footer-col">
        <h4>Navigate</h4>
        <ul>
          <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
          <li><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a></li>
          <li><a href="<?php echo esc_url(home_url('/chess-news-india/')); ?>">Chess News India</a></li>
          <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About Us</a></li>
          <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
        </ul>
      </div>

      <div class="cd-footer-col">
        <h4>Chess Formats</h4>
        <ul>
          <?php
          $cats = cd_get_all_chess_categories();
          foreach (array_slice($cats, 0, 6) as $cat) :
          ?>
          <li><a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"><?php echo esc_html($cat->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="cd-footer-col">
        <h4>Chess News India</h4>
        <ul>
          <?php
          $states = array_slice(cd_get_india_states(), 0, 8);
          foreach ($states as $st) :
            $term = get_term_by('slug', $st['slug'], 'chess_state');
            $url  = $term ? get_term_link($term) : home_url('/chess-news-india/' . $st['slug'] . '/');
          ?>
          <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($st['name']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

    <div class="cd-footer-bottom">
      <span>&copy; <?php echo date('Y'); ?> Checkmate Daily. All rights reserved.</span>
    </div>

  </div>
</footer>

<!-- Back to top button — visibility toggled by JS scroll listener (threshold: 400px) -->
<button id="cd-back-top" aria-label="Back to top" title="Back to top">
  <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
    <path fill="currentColor" d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6 1.41 1.41z"/>
  </svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
