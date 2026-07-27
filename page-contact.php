<?php
/**
 * Template Name: Contact Page
 *
 * Dedicated contact page for Checkmate Daily. WordPress will use this
 * automatically for a page with the slug "contact".
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$has_page = have_posts();
if ( $has_page ) {
    the_post();
}

$page_title   = $has_page ? get_the_title() : 'Contact Us';
$status       = isset( $_GET['contact-status'] ) ? sanitize_key( wp_unslash( $_GET['contact-status'] ) ) : '';
$public_email = cd_get_public_contact_email();

get_header();
?>

<main id="cd-main" class="cd-contact-main">
  <div class="container">
    <div class="cd-contact-shell">

      <section class="cd-contact-hero" aria-labelledby="cd-contact-title">
        <div class="cd-contact-eyebrow">Checkmate Daily</div>
        <h1 id="cd-contact-title"><?php echo esc_html( $page_title ); ?></h1>
        <p>Send news tips, tournament updates, correction requests, partnership enquiries, and advertising questions to the Checkmate Daily team.</p>
      </section>

      <div class="cd-contact-grid">
        <section class="cd-contact-card cd-contact-form-card" aria-labelledby="cd-contact-form-title">
          <h2 id="cd-contact-form-title">Send a Message</h2>

          <?php if ( 'sent' === $status ) : ?>
            <div class="cd-contact-notice success" role="status">Thank you. Your message has been sent.</div>
          <?php elseif ( 'invalid' === $status ) : ?>
            <div class="cd-contact-notice error" role="alert">Please complete the required fields with a valid email address.</div>
          <?php elseif ( 'mail-error' === $status ) : ?>
            <div class="cd-contact-notice error" role="alert">The message could not be sent. Please email us directly instead.</div>
          <?php endif; ?>

          <?php if ( $has_page ) : ?>
            <?php
            ob_start();
            the_content();
            $page_content = trim( ob_get_clean() );
            ?>
            <?php if ( $page_content ) : ?>
              <div class="cd-contact-intro">
                <?php echo $page_content; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <form class="cd-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="cd_contact_form">
            <?php wp_nonce_field( 'cd_contact_form', 'cd_contact_nonce' ); ?>

            <div class="cd-contact-field">
              <label for="cd-contact-name">Name <span aria-hidden="true">*</span></label>
              <input id="cd-contact-name" name="contact_name" type="text" autocomplete="name" required>
            </div>

            <div class="cd-contact-field">
              <label for="cd-contact-email">Email <span aria-hidden="true">*</span></label>
              <input id="cd-contact-email" name="contact_email" type="email" autocomplete="email" required>
            </div>

            <div class="cd-contact-field">
              <label for="cd-contact-subject">Subject</label>
              <input id="cd-contact-subject" name="contact_subject" type="text" autocomplete="off">
            </div>

            <div class="cd-contact-field">
              <label for="cd-contact-message">Message <span aria-hidden="true">*</span></label>
              <textarea id="cd-contact-message" name="contact_message" rows="7" required></textarea>
            </div>

            <div class="cd-contact-hp" aria-hidden="true">
              <label for="cd-contact-company">Company</label>
              <input id="cd-contact-company" name="contact_company" type="text" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="cd-contact-submit">Send Message</button>
          </form>
        </section>

        <aside class="cd-contact-card cd-contact-details" aria-labelledby="cd-contact-details-title">
          <h2 id="cd-contact-details-title">Contact Details</h2>

          <div class="cd-contact-method">
            <span class="cd-contact-label">Email</span>
            <a href="mailto:<?php echo esc_attr( $public_email ); ?>"><?php echo esc_html( $public_email ); ?></a>
          </div>

          <div class="cd-contact-method">
            <span class="cd-contact-label">News Tips</span>
            <a href="mailto:<?php echo esc_attr( $public_email ); ?>?subject=News%20Tip%20for%20Checkmate%20Daily">Share a tournament update</a>
          </div>

          <div class="cd-contact-method">
            <span class="cd-contact-label">Advertising</span>
            <a href="mailto:<?php echo esc_attr( $public_email ); ?>?subject=Advertising%20Enquiry%20for%20Checkmate%20Daily">Request advertising details</a>
          </div>

          <div class="cd-contact-socials" aria-label="Social links">
            <a href="https://facebook.com/checkmatedaily" target="_blank" rel="noopener noreferrer">Facebook</a>
            <a href="https://www.youtube.com/@CheckmateDailyChess" target="_blank" rel="noopener noreferrer">YouTube</a>
            <a href="https://www.instagram.com/checkmate.daily/" target="_blank" rel="noopener noreferrer">Instagram</a>
            <a href="https://twitter.com/checkmatedaily" target="_blank" rel="noopener noreferrer">X</a>
          </div>
        </aside>
      </div>

    </div>
  </div>
</main>

<?php
if ( $has_page ) {
    wp_reset_postdata();
}
get_footer();
