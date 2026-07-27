<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Skip to content — visible only on keyboard focus, always first in DOM -->
<a href="#cd-main" class="cd-skip-link">Skip to content</a>

<header id="cd-header" role="banner">
  <div class="container">
    <div class="cd-header-row">

      <a href="<?php echo esc_url(home_url('/')); ?>" class="cd-logo" rel="home">
        <?php if (has_custom_logo()) : the_custom_logo();
        else : ?>
          <div class="cd-logo-icon">&#9822;</div>
          <div class="cd-logo-text">Checkmate <span>Daily</span></div>
        <?php endif; ?>
      </a>

      <nav id="cd-nav" role="navigation" aria-label="Primary Navigation">
        <div id="cd-nav-menu">
          <?php wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => false,
            'fallback_cb'    => 'cd_fallback_nav',
          )); ?>
        </div>
      </nav>

      <div class="cd-header-socials">
        <a href="https://facebook.com/checkmatedaily" target="_blank" aria-label="Facebook">
          <svg viewBox="0 0 24 24"><path fill="currentColor" d="M22,12c0-5.52-4.48-10-10-10S2,6.48,2,12c0,4.84,3.44,8.87,8,9.8V15H8v-3h2V9.5C10,7.57,11.57,6,13.5,6H16v3h-2c-0.55,0-1,0.45-1,1v2h3v3h-3v6.95C18.05,21.45,22,17.19,22,12z"/></svg>
        </a>
        <a href="https://www.youtube.com/@CheckmateDailyChess" target="_blank" aria-label="YouTube">
          <svg viewBox="0 0 24 24"><path fill="currentColor" d="M21.58,7.19c-0.23-0.86-0.91-1.54-1.77-1.77C18.25,5,12,5,12,5s-6.25,0-7.81,0.42c-0.86,0.23-1.54,0.91-1.77,1.77C2,8.75,2,12,2,12s0,3.25,0.42,4.81c0.23,0.86,0.91,1.54,1.77,1.77C5.75,19,12,19,12,19s6.25,0,7.81-0.42c0.86-0.23,1.54-0.91,1.77-1.77C22,15.25,22,12,22,12S22,8.75,21.58,7.19z M10,15.5v-7l6,3.5L10,15.5z"/></svg>
        </a>
        <a href="https://www.instagram.com/checkmate.daily/" target="_blank" aria-label="Instagram">
          <svg viewBox="0 0 24 24"><path fill="currentColor" d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z"/></svg>
        </a>
        <a href="https://twitter.com/checkmatedaily" target="_blank" aria-label="Twitter">
          <svg viewBox="0 0 24 24"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
      </div>

      <!-- Desktop search — hidden on mobile via CSS -->
      <div class="cd-header-search">
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" role="search">
          <input type="text" name="s" placeholder="Search chess news..."
                 value="<?php echo esc_attr(get_search_query()); ?>" aria-label="Search">
          <button type="submit">Search</button>
        </form>
      </div>

      <!-- Mobile search icon — shown only on mobile via CSS -->
      <button id="cd-mob-search-btn"
              aria-label="Open search"
              aria-expanded="false"
              aria-controls="cd-mob-search-bar">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
          <path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
        </svg>
      </button>

      <button id="cd-mob-toggle" aria-label="Menu" aria-expanded="false">&#9776;</button>

    </div>
  </div>

  <!-- Mobile search bar — expands inside the sticky header on small screens -->
  <div id="cd-mob-search-bar" aria-hidden="true" role="search" aria-label="Mobile site search">
    <div class="container">
      <form method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="text"
               id="cd-mob-search-input"
               name="s"
               placeholder="Search chess news…"
               value="<?php echo esc_attr(get_search_query()); ?>"
               aria-label="Search"
               autocomplete="off">
        <button type="submit" aria-label="Submit search">
          <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
            <path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
          </svg>
        </button>
      </form>
      <button id="cd-mob-search-close" aria-label="Close search">&#10005;</button>
    </div>
  </div>

</header>

<div id="cd-ticker">
  <div class="container">
    <div class="cd-ticker-row">
      <span class="cd-ticker-label">Breaking</span>
      <div class="cd-ticker-track">
        <?php
        /*
         * Ticker query — cached 5 min via transient.
         * Cache is auto-cleared by save_post hook in functions.php.
         */
        $ticker_html = get_transient( 'cd_ticker_html' );

        if ( false === $ticker_html ) {
            $tp = get_posts( array(
                'posts_per_page' => 30,
                'post_status'    => 'publish',
                'date_query'     => array( array( 'after' => '1 week ago' ) ),
            ) );
            if ( empty( $tp ) ) {
                $tp = get_posts( array( 'posts_per_page' => 5, 'post_status' => 'publish' ) );
            }
            $ti = array();
            foreach ( $tp as $p ) {
                $ti[] = '<a href="' . esc_url( get_permalink( $p->ID ) ) . '">'
                      . esc_html( get_the_title( $p->ID ) ) . '</a>';
            }
            $ticker_html = implode( ' <span class="ticker-div">|</span> ', $ti );
            set_transient( 'cd_ticker_html', $ticker_html, 5 * MINUTE_IN_SECONDS );
        }
        ?>
        <div class="cd-ticker-scroll">
          <?php echo $ticker_html; ?> <span class="ticker-div">|</span>
        </div>
        <div class="cd-ticker-scroll" aria-hidden="true">
          <?php echo $ticker_html; ?> <span class="ticker-div">|</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
function cd_fallback_nav() {
  $links = array(
    '/'                          => 'Home',
    '/category/blitz-rating/'    => 'Blitz',
    '/category/rapid-rating/'    => 'Rapid',
    '/category/classical-rating/'=> 'Classical',
    '/category/chess960/'        => 'Chess960',
    '/chess-news-india/'         => 'Chess News India',
  );
  echo '<ul>';
  foreach ($links as $path => $label)
    echo '<li><a href="'.esc_url(home_url($path)).'">'.esc_html($label).'</a></li>';
  echo '</ul>';
}
?>