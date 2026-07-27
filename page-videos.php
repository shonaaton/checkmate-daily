<?php
/**
 * Template Name: Video Media Hub
 */
get_header(); 
?>

<main id="cd-video-hub" class="video-hub-main">

    <section class="cd-format-hero" style="background-color: var(--cd-black) !important; padding: 60px 0; text-align: center; border-bottom: 3px solid var(--cd-blue);">
        <div class="container">
            <div class="fmt-tag" style="background-color: var(--cd-blue) !important; color: #fff !important;">Checkmate Daily TV</div>
            <h1 style="color: #ffffff !important; font-size: 36px; margin-top: 15px;">Chess Video Coverage</h1>
            <p style="color: rgba(255,255,255,0.8) !important; max-width: 700px; margin: 0 auto 25px;">Exclusive player interviews, crunch-time time scrambles, and on-the-ground tournament highlights from the Indian chess scene.</p>
            <a href="https://www.youtube.com/@CheckmateDailyChess?sub_confirmation=1" target="_blank" style="display: inline-block; background: #FF0000; color: #fff; padding: 10px 20px; font-weight: bold; border-radius: 4px; text-decoration: none;">Subscribe on YouTube</a>
        </div>
    </section>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        
        <div class="cd-section-head">
            <h2>Latest Coverage</h2>
        </div>

        <div class="cd-news-grid">
            <?php 
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
            $video_args = array(
                'post_type'      => 'cd_video',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                'paged'          => $paged
            );
            $video_q = new WP_Query($video_args);

            if ($video_q->have_posts()) : 
                while ($video_q->have_posts()) : $video_q->the_post(); 
                
                $v_id = cd_get_video_youtube_id(get_the_ID());
            ?>
                <div class="cd-news-card">
                    <div class="cd-news-card-img" style="position: relative;">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ($v_id) : ?>
                                <?php cd_render_youtube_thumbnail($v_id, array('quality'=>'hq720','fallback_quality'=>'hqdefault','alt'=>get_the_title(),'style'=>'width: 100%; display: block;')); ?>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(22, 140, 207, 0.9); color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">▶</div>
                            <?php else : ?>
                                <div style="background: #1a1a1a; padding-bottom: 56.25%;"></div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="cd-news-card-body">
                        <?php 
                            $playlists = get_the_terms(get_the_ID(), 'video_playlist');
                            if($playlists && !is_wp_error($playlists)) {
                                echo '<span class="cd-cat-badge" style="background: var(--cd-black);">' . esc_html($playlists[0]->name) . '</span>';
                            }
                        ?>
                        <div class="cd-news-card-title" style="margin-top: 10px;">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </div>
                        <div class="cd-news-card-meta" style="margin-top: 8px;">
                            <?php echo get_the_date(); ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
       <?php cd_pagination( $video_q->max_num_pages ); ?>

        <?php else : ?>
            <p>Coverage coming soon. Subscribe to our YouTube channel to stay updated!</p>
            </div>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>

    </div>
</main>

<?php get_footer(); ?>
