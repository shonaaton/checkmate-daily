<?php
/**
 * Video Library Archive
 */
get_header();
?>

<main id="cd-main" class="cd-video-library-page">
    <section class="cd-video-library-hero">
        <div class="container">
            <div class="cd-video-kicker">Checkmate Daily TV</div>
            <h1>Chess Video Coverage</h1>
            <p>Interviews, tournament moments, analysis clips, and short chess stories from Checkmate Daily.</p>
            <a href="https://www.youtube.com/@CheckmateDailyChess?sub_confirmation=1" target="_blank" rel="noopener" class="cd-video-subscribe">Subscribe on YouTube</a>
        </div>
    </section>

    <div class="container cd-video-library-wrap">
        <div class="cd-section-head cd-video-library-head">
            <h2>Latest Coverage</h2>
        </div>

        <div class="cd-video-library-grid">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
                $yt_url    = get_post_meta( get_the_ID(), '_cd_youtube_url', true );
                $v_id      = cd_extract_youtube_id( $yt_url );
                $playlists = get_the_terms( get_the_ID(), 'video_playlist' );
                $label     = ( $playlists && ! is_wp_error( $playlists ) ) ? $playlists[0]->name : 'Coverage';
            ?>
            <article class="cd-library-video-card">
                <a href="<?php the_permalink(); ?>" class="cd-library-video-thumb" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                    <?php if ( $v_id ) : ?>
                        <?php cd_render_youtube_thumbnail( $v_id, array(
                            'quality'          => 'hq720',
                            'fallback_quality' => 'hqdefault',
                            'alt'              => get_the_title(),
                            'class'            => 'cd-library-thumb-img',
                            'width'            => 640,
                            'height'           => 360,
                        ) ); ?>
                    <?php else : ?>
                        <span class="cd-library-thumb-placeholder"></span>
                    <?php endif; ?>
                    <span class="cd-library-play" aria-hidden="true">&#9654;</span>
                </a>

                <div class="cd-library-video-body">
                    <span class="cd-library-label"><?php echo esc_html( $label ); ?></span>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="cd-library-meta"><?php echo esc_html( get_the_date() ); ?></div>
                </div>
            </article>
            <?php endwhile; else : ?>
                <div class="cd-video-empty">More coverage coming soon.</div>
            <?php endif; ?>
        </div>

        <?php cd_pagination(); ?>

        <div class="cd-section-head cd-video-library-head cd-video-shorts-head">
            <h2>Shorts</h2>
        </div>

        <div class="cd-library-shorts-grid">
            <?php
            $shorts_q = new WP_Query( array(
                'post_type'      => 'cd_video',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                'tax_query'      => array(
                    array( 'taxonomy' => 'video_playlist', 'field' => 'slug', 'terms' => 'shorts' ),
                ),
            ) );

            if ( $shorts_q->have_posts() ) : while ( $shorts_q->have_posts() ) : $shorts_q->the_post();
                $yt_url = get_post_meta( get_the_ID(), '_cd_youtube_url', true );
                $v_id   = cd_extract_youtube_id( $yt_url );
            ?>
            <article class="cd-library-short-card">
                <a href="<?php the_permalink(); ?>" class="cd-library-short-thumb" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                    <?php if ( $v_id ) : ?>
                        <?php cd_render_youtube_thumbnail( $v_id, array(
                            'quality'          => 'hqdefault',
                            'fallback_quality' => 'mqdefault',
                            'alt'              => get_the_title(),
                            'class'            => 'cd-library-thumb-img',
                            'width'            => 480,
                            'height'           => 270,
                        ) ); ?>
                    <?php else : ?>
                        <span class="cd-library-thumb-placeholder"></span>
                    <?php endif; ?>
                    <span class="cd-library-play" aria-hidden="true">&#9654;</span>
                </a>

                <div class="cd-library-short-body">
                    <h3><a href="<?php the_permalink(); ?>"><?php echo esc_html( wp_trim_words( get_the_title(), 9 ) ); ?></a></h3>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); else : ?>
                <div class="cd-video-empty">No shorts found.</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
