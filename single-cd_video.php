<?php
/**
 * Single Video Template (Cinematic Player)
 * Fix: Pure inline block styles to bypass all flexbox bugs
 */
get_header(); ?>

<main id="cd-main" style="background: #0a0a0a; color: #fff; min-height: 100vh; display: block; width: 100%;">
    <?php while ( have_posts() ) : the_post(); 
        
        // Extract the exact 11-character video ID
        $v_id = cd_get_video_youtube_id(get_the_ID());

        // Check if tagged as "shorts"
        $is_short = false;
        $playlists = get_the_terms(get_the_ID(), 'video_playlist');
        if($playlists && !is_wp_error($playlists)) {
            foreach($playlists as $pl) {
                if($pl->slug === 'shorts') {
                    $is_short = true;
                    break;
                }
            }
        }
        
        // Set exact dimensions
        $max_w = $is_short ? '400px' : '1000px';
        $pad_b = $is_short ? '177.77%' : '56.25%';
    ?>

    <div style="background: #000; padding: 40px 20px; border-bottom: 3px solid #FF0000; display: block; width: 100%; box-sizing: border-box;">
        
        <div style="width: 100%; max-width: <?php echo esc_attr($max_w); ?>; margin: 0 auto; display: block;">
            
            <div style="position: relative; display: block; width: 100%; height: 0; padding-bottom: <?php echo esc_attr($pad_b); ?>; background: #111; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.9);">
                
                <?php if ($v_id) : ?>
                    <iframe src="https://www.youtube.com/embed/<?php echo esc_attr($v_id); ?>?autoplay=1&rel=0&modestbranding=1" 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; margin: 0; padding: 0; display: block;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #888;">
                        <h3>Video Link Missing</h3>
                    </div>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>

    <div style="max-width: 1000px; margin: 40px auto; padding: 0 20px; display: block; box-sizing: border-box;">
        <div style="margin-bottom: 25px;">
            <?php 
                if($playlists && !is_wp_error($playlists)) {
                    echo '<span style="background: #FF0000; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; display: inline-block;">' . esc_html($playlists[0]->name) . '</span>';
                }
            ?>
            <h1 style="font-size: 36px; margin: 0 0 10px 0; color: #fff; font-family: var(--cd-font-heading); line-height: 1.2;"><?php the_title(); ?></h1>
            <div style="color: #888; font-size: 14px; display: flex; gap: 15px; align-items: center;">
                <span>🗓️ <?php echo get_the_date(); ?></span>
                <a href="https://www.youtube.com/@CheckmateDailyChess?sub_confirmation=1" target="_blank" style="color: #FF0000; text-decoration: none; font-weight: bold;">▶ Subscribe</a>
            </div>
        </div>

        <div style="color: #cccccc; line-height: 1.7; font-size: 17px;">
            <?php the_content(); ?>
        </div>
        
        <hr style="border-color: #222; margin: 50px 0;">
        <a href="<?php echo esc_url(home_url('/videos/')); ?>" style="display: inline-block; padding: 12px 24px; background: #222; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.2s;">&larr; Back to Video Library</a>
    </div>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
