<?php
/**
 * Sidebar Template — v2.3
 * Fix #7: single sidebar, no duplication, no emojis
 * Update: Reordered to place Advertisements at the very bottom for Premium Sticky Effect
 */
?>
<aside class="cd-sidebar" role="complementary" aria-label="Sidebar">

  <div class="cd-newsletter-box">
    <h3>Stay Updated</h3>
    <p>Get the latest chess news delivered to your inbox daily.</p>
    <form id="cd-newsletter-form">
      <input type="email" name="email" placeholder="Your email address" required aria-label="Email">
      <button type="submit">Subscribe</button>
      <p id="cd-nl-msg" style="font-size:11px;margin-top:6px;display:none;"></p>
    </form>
  </div>

  <div class="cd-sidebar-box">
    <div class="cd-sidebar-head">Latest Chess News</div>
    <?php
    $recent = get_posts(array('posts_per_page'=>5,'post_status'=>'publish'));
    foreach ($recent as $rp) :
    ?>
    <div class="cd-sb-item">
      <div class="cd-sb-thumb"><?php cd_render_post_image($rp->ID, 'cd-sidebar', array('width'=>140,'height'=>110)); ?></div>
      <div>
        <div class="cd-sb-title"><a href="<?php echo esc_url(get_permalink($rp->ID)); ?>"><?php echo esc_html(get_the_title($rp->ID)); ?></a></div>
        <div class="cd-sb-date"><?php echo get_the_date('M j, Y', $rp->ID); ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php
  $events = get_posts(array('post_type'=>'cd_event','posts_per_page'=>4,'post_status'=>'publish','meta_key'=>'event_date','orderby'=>'meta_value','order'=>'ASC'));
  if ($events) :
  ?>
  <div class="cd-sidebar-box">
    <div class="cd-sidebar-head">Upcoming Events</div>
    <?php foreach ($events as $ev) :
      $ev_date = get_post_meta($ev->ID,'event_date',true);
      $ev_loc  = get_post_meta($ev->ID,'event_location',true);
      $day = $ev_date ? date('d', strtotime($ev_date)) : '?';
      $mon = $ev_date ? date('M', strtotime($ev_date)) : '';
    ?>
    <div class="cd-upcoming-item">
      <div class="cd-upcoming-date"><div class="day"><?php echo esc_html($day); ?></div><div class="mon"><?php echo esc_html($mon); ?></div></div>
      <div>
        <div class="cd-upcoming-title"><a href="<?php echo esc_url(get_permalink($ev->ID)); ?>"><?php echo esc_html(get_the_title($ev->ID)); ?></a></div>
        <?php if ($ev_loc) echo '<div class="cd-upcoming-loc">' . esc_html($ev_loc) . '</div>'; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="cd-sidebar-box">
    <div class="cd-sidebar-head">Categories</div>
    <div class="cd-cat-pills">
      <?php
      $cats = cd_get_all_chess_categories();
      $i = 0;
      foreach ($cats as $cat) :
        if ($cat->count === 0) continue;
        $cls = ($i % 2 === 0) ? '' : 'dark';
      ?>
      <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="cd-cat-pill <?php echo $cls; ?>"><?php echo esc_html($cat->name); ?></a>
      <?php $i++; endforeach; ?>
    </div>
  </div>

  <div class="cd-sidebar-box">
    <div class="cd-sidebar-head">Indian States</div>
    <?php
    $states = array_slice(cd_get_india_states(), 0, 7);
    foreach ($states as $st) :
      $term = get_term_by('slug', $st['slug'], 'chess_state');
      $url  = $term ? get_term_link($term) : home_url('/chess-in/' . $st['slug'] . '/');
    ?>
    <div style="padding:5px 0;border-bottom:1px solid var(--cd-gray-mid);font-size:12px;">
      <a href="<?php echo esc_url($url); ?>" style="color:var(--cd-black);"><?php echo esc_html($st['name']); ?></a>
    </div>
    <?php endforeach; ?>
    <div style="margin-top:8px;">
      <a href="<?php echo esc_url(home_url('/chess-in/')); ?>" style="font-size:12px;font-weight:700;color:var(--cd-blue);">View All States</a>
    </div>
  </div>

  <div class="cd-sidebar-box">
    <div class="cd-sidebar-head">Archives</div>
    <ul class="cd-archives-list">
      <?php wp_get_archives(array('type'=>'monthly','limit'=>6,'format'=>'custom','before'=>'<li>','after'=>'</li>')); ?>
    </ul>
  </div>

  <?php if (is_active_sidebar('cd-sidebar-main')) dynamic_sidebar('cd-sidebar-main'); ?>

  <div class="cd-sidebar-box cd-affiliate-box">
      <div class="cd-sidebar-head">Advertisements</div>
      <div class="cd-affiliate-list" style="display:flex;flex-direction:column;gap:12px;">

          <a href="https://chessmood.com/?r=checkmatedaily" target="_blank" rel="sponsored noopener noreferrer" class="cd-affiliate-link" style="display:block;padding:12px;border-radius:10px;overflow:hidden;border:1.5px solid #dfe5eb;background:linear-gradient(180deg,#f8fafc 0%,#eef3f7 100%);">
              <img src="<?php echo esc_url( cd_uploads_url( '2026/07/images-3.jpeg' ) ); ?>" alt="ChessMood" width="262" loading="lazy" style="display:block;width:100%;height:86px;object-fit:contain;object-position:center;background:#ffffff;border:1px solid #edf1f5;border-radius:8px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(255,255,255,0.75);">
          </a>
          
          <a href="https://www.play-asia.com/?affiliate_id=3262541" target="_blank" rel="sponsored noopener noreferrer" class="cd-affiliate-link" style="display:block;padding:12px;border-radius:10px;overflow:hidden;border:1.5px solid #dfe5eb;background:linear-gradient(180deg,#f8fafc 0%,#eef3f7 100%);">
              <img src="<?php echo esc_url( cd_uploads_url( '2026/04/images-1.jpeg' ) ); ?>" alt="Playasia" width="262" loading="lazy" style="display:block;width:100%;height:86px;object-fit:contain;object-position:center;background:#ffffff;border:1px solid #edf1f5;border-radius:8px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(255,255,255,0.75);">
          </a>

          <a href="https://chess-teacher.com/affiliates/idevaffiliate.php?id=3050" target="_blank" rel="sponsored noopener noreferrer" class="cd-affiliate-link" style="display:block;padding:12px;border-radius:10px;overflow:hidden;border:1.5px solid #dfe5eb;background:linear-gradient(180deg,#f8fafc 0%,#eef3f7 100%);">
              <img src="<?php echo esc_url( cd_uploads_url( '2026/04/logo1.png' ) ); ?>" alt="Remote Chess Academy" width="262" loading="lazy" style="display:block;width:100%;height:86px;object-fit:contain;object-position:center;background:#ffffff;border:1px solid #edf1f5;border-radius:8px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(255,255,255,0.75);">
          </a>

          <a href="https://www.chessnutech.com?sca_ref=11084331.3bDAZwMhddyY" target="_blank" rel="sponsored noopener noreferrer" class="cd-affiliate-link" style="display:block;padding:12px;border-radius:10px;overflow:hidden;border:1.5px solid #dfe5eb;background:linear-gradient(180deg,#f8fafc 0%,#eef3f7 100%);">
              <img src="<?php echo esc_url( cd_uploads_url( '2026/04/chnut-600x600-1.jpeg' ) ); ?>" alt="ChessNut" width="262" loading="lazy" style="display:block;width:100%;height:86px;object-fit:contain;object-position:center;background:#ffffff;border:1px solid #edf1f5;border-radius:8px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(255,255,255,0.75);">
          </a>

          <a href="https://jobowl.co?atp=CheckmateDaily" target="_blank" rel="sponsored noopener noreferrer" class="cd-affiliate-link" style="display:block;padding:12px;border-radius:10px;overflow:hidden;border:1.5px solid #dfe5eb;background:linear-gradient(180deg,#f8fafc 0%,#eef3f7 100%);">
              <img src="<?php echo esc_url( cd_uploads_url( '2026/05/jobowl-logo.jpg' ) ); ?>" alt="JobOwl" width="262" loading="lazy" style="display:block;width:100%;height:86px;object-fit:contain;object-position:center;background:#ffffff;border:1px solid #edf1f5;border-radius:8px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(255,255,255,0.75);">
          </a>

      </div>
  </div>

</aside>
