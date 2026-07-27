<?php
/**
 * Master Video Hub (archive-cd_video.php)
 * Dark mode library showing all videos and shorts.
 */
get_header(); ?>

<main id="cd-main" style="background: #0a0a0a; min-height: 100vh; padding: 40px 0;">
    <div class="container">
        
        <div class="cd-section-head" style="border-bottom: 2px solid #FF0000; padding-bottom: 10px; margin-bottom: 30px;">
            <h1 style="color: #fff; margin:0; font-family: var(--cd-font-heading); text-transform: uppercase;">Checkmate Daily TV Library</h1>
        </div>

        <div class="cd-news-grid" style="margin-bottom: 50px;">
            <?php 
            // The main query already excludes Shorts thanks to our functions.php code!
            if ( have_posts() ) : while ( have_posts() ) : the_post(); 
                $yt_url = get_post_meta(get_the_ID(), '_cd_youtube_url', true);
                $v_id = cd_extract_youtube_id($yt_url);
            ?>
            <div class="cd-news-card" style="background: #111; border: 1px solid #222;">
                <div class="cd-news-card-img" style="position: relative;">
                    <a href="<?php the_permalink(); ?>">
                        <?php if ($v_id) : ?>
                            <?php cd_render_youtube_thumbnail($v_id, array('quality'=>'hq720','fallback_quality'=>'hqdefault','alt'=>get_the_title(),'style'=>'width: 100%; display: block; opacity: 0.85;')); ?>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255, 0, 0, 0.9); color: #fff; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; padding-left: 3px;">▶</div>
                        <?php else: ?>
                            <div style="background: #222; padding-bottom: 56.25%;"></div>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="cd-news-card-body" style="padding: 15px;">
                    <div class="cd-news-card-title">
                        <a href="<?php the_permalink(); ?>" style="color: #fff; font-size: 16px; font-weight: bold;"><?php the_title(); ?></a>
                    </div>
                    <div style="color: #888; font-size: 12px; margin-top: 8px;"><?php echo get_the_date(); ?></div>
                </div>
            </div>
            <?php endwhile; else : ?>
                <p style="color: #fff; grid-column: 1/-1;">More coverage coming soon.</p>
            <?php endif; ?>
        </div>
        
        <div class="cd-pagination" style="margin-bottom: 60px;">
            <?php echo paginate_links(array('prev_text' => '← Newer', 'next_text' => 'Older →')); ?>
        </div>

        <div class="cd-section-head" style="border-bottom: 2px solid #FF0000; padding-bottom: 10px; margin-bottom: 30px;">
            <h2 style="color: #fff; margin:0; font-family: var(--cd-font-heading); text-transform: uppercase;">All Shorts</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-bottom: 50px;">
        <?php 
            // Custom query to fetch ONLY the Shorts for the bottom grid
            $shorts_args = array(
                'post_type'      => 'cd_video',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                'tax_query'      => array(
                    array('taxonomy' => 'video_playlist', 'field' => 'slug', 'terms' => 'shorts')
                ),
            );
            $shorts_q = new WP_Query($shorts_args);
            if ($shorts_q->have_posts()) : while ($shorts_q->have_posts()) : $shorts_q->the_post();
                $yt_url = get_post_meta(get_the_ID(), '_cd_youtube_url', true);
                $v_id = cd_extract_youtube_id($yt_url);
        ?>
            <div style="background: #111; border: 1px solid #222; border-radius: 8px; overflow: hidden; transition: transform 0.2s;">
                <div style="position: relative; padding-bottom: 177%; height: 0; overflow: hidden;">
                    <a href="<?php the_permalink(); ?>">
                        <?php if ($v_id) : ?>
                            <?php cd_render_youtube_thumbnail($v_id, array('alt'=>get_the_title(),'style'=>'position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.9;')); ?>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255, 0, 0, 0.9); color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; padding-left: 2px;">▶</div>
                        <?php endif; ?>
                    </a>
                </div>
                <div style="padding: 12px;">
                    <a href="<?php the_permalink(); ?>" style="color: #fff; font-size: 13px; font-weight: bold; text-decoration: none; line-height: 1.4; display: block;"><?php echo wp_trim_words(get_the_title(), 7); ?></a>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); else: ?>
            <p style="color: #666; grid-column: 1/-1;">No shorts found.</p>
        <?php endif; ?>
        </div>
        
    </div>
</main>

<?php get_footer(); ?>
