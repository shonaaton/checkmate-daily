<?php
/**
 * Checkmate Daily Theme Functions — v2.1
 * Updates: SEO Taxonomy Slug changed to 'chess-in'
 */
if (!defined('ABSPATH')) exit;
define('CD_VERSION', '2.1.18');
define('CD_DIR', get_template_directory());
define('CD_URI', get_template_directory_uri());

/* ── THEME SETUP ── */
function cd_theme_setup() {
    load_theme_textdomain('checkmate-daily', CD_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_post_type_support('post', 'thumbnail');
    add_theme_support('custom-logo', array('height'=>48,'width'=>200,'flex-height'=>true,'flex-width'=>true));
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption'));
    add_theme_support('automatic-feed-links');
    add_image_size('cd-hero',    1100, 420, true);
    add_image_size('cd-thumb',   400,  260, true);
    add_image_size('cd-card',    400,  230, true);
    add_image_size('cd-sidebar', 140,  110, true);
    register_nav_menus(array('primary'=>'Primary Navigation','footer'=>'Footer Navigation'));
}
add_action('after_setup_theme', 'cd_theme_setup');

/* ── ENQUEUE ASSETS ── */
function cd_enqueue_assets() {
    wp_enqueue_style('cd-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Source+Sans+3:wght@400;600;700&display=swap',
        array(), null);
    wp_enqueue_style('cd-style', get_stylesheet_uri(), array(), CD_VERSION);
    wp_enqueue_script('cd-main', CD_URI . '/assets/js/main.js', array('jquery'), CD_VERSION, true);
    if (is_singular() && comments_open()) wp_enqueue_script('comment-reply');
    wp_localize_script('cd-main', 'cdAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('cd_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'cd_enqueue_assets');

/* ── WIDGET AREAS ── */
function cd_register_sidebars() {
    $def = array(
        'before_widget' => '<div class="cd-sidebar-box" id="%1$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="cd-sidebar-head">',
        'after_title'   => '</div>',
    );
    register_sidebar(array_merge($def, array('name'=>'Main Sidebar','id'=>'cd-sidebar-main')));
    register_sidebar(array_merge($def, array('name'=>'Footer Col 1','id'=>'cd-footer-1')));
    register_sidebar(array_merge($def, array('name'=>'Footer Col 2','id'=>'cd-footer-2')));
}
add_action('widgets_init', 'cd_register_sidebars');

/* ── CUSTOM POST TYPE: EVENTS ── */
function cd_register_cpts() {
    register_post_type('cd_event', array(
        'labels'       => array('name'=>'Upcoming Events','singular_name'=>'Event','add_new_item'=>'Add New Event'),
        'public'       => true, 'has_archive' => true,
        'supports'     => array('title','editor','thumbnail','custom-fields'),
        'rewrite'      => array('slug'=>'events'),
        'menu_icon'    => 'dashicons-calendar-alt',
        'show_in_rest' => true,
    ));
}
add_action('init', 'cd_register_cpts');

/* ── TAXONOMIES: STATES & COUNTRIES ── */
function cd_get_location_taxonomy_caps() {
    return array(
        'manage_terms' => 'edit_posts',
        'edit_terms'   => 'edit_posts',
        'delete_terms' => 'manage_categories',
        'assign_terms' => 'edit_posts',
    );
}

function cd_get_location_taxonomy_labels( $singular, $plural ) {
    return array(
        'name'                       => $plural,
        'singular_name'              => $singular,
        'menu_name'                  => $plural,
        'all_items'                  => 'All ' . $plural,
        'edit_item'                  => 'Edit ' . $singular,
        'view_item'                  => 'View ' . $singular,
        'update_item'                => 'Update ' . $singular,
        'add_new_item'               => 'Add New ' . $singular,
        'new_item_name'              => 'New ' . $singular . ' Name',
        'parent_item'                => 'Parent ' . $singular,
        'parent_item_colon'          => 'Parent ' . $singular . ':',
        'search_items'               => 'Search ' . $plural,
        'popular_items'              => 'Popular ' . $plural,
        'separate_items_with_commas' => 'Separate ' . strtolower( $plural ) . ' with commas',
        'add_or_remove_items'        => 'Add or remove ' . strtolower( $plural ),
        'choose_from_most_used'      => 'Choose from the most used ' . strtolower( $plural ),
        'not_found'                  => 'No ' . strtolower( $plural ) . ' found',
        'back_to_items'              => 'Back to ' . $plural,
        'item_link'                  => $singular . ' Link',
        'item_link_description'      => 'A link to a ' . strtolower( $singular ) . '.',
    );
}

function cd_get_location_taxonomy_args( $singular, $plural, $rewrite_slug, $rest_base ) {
    return array(
        'labels'              => cd_get_location_taxonomy_labels( $singular, $plural ),
        'hierarchical'        => true,
        'public'              => true,
        'show_ui'             => true,
        'show_admin_column'   => true,
        'show_in_quick_edit'  => true,
        'show_in_nav_menus'   => true,
        'show_in_rest'        => true,
        'rest_base'           => $rest_base,
        'rest_controller_class' => 'WP_REST_Terms_Controller',
        'rewrite'             => array( 'slug' => $rewrite_slug ),
        'capabilities'        => cd_get_location_taxonomy_caps(),
    );
}

function cd_normalize_location_term_rest_data( $data ) {
    if ( ! is_array( $data ) ) return $data;

    foreach ( array( 'name', 'slug', 'description', 'taxonomy', 'link' ) as $field ) {
        if ( ! array_key_exists( $field, $data ) || null === $data[ $field ] || ! is_scalar( $data[ $field ] ) ) {
            $data[ $field ] = '';
        } else {
            $data[ $field ] = (string) $data[ $field ];
        }
    }

    return $data;
}

function cd_normalize_location_term_rest_response( $response ) {
    if ( ! $response instanceof WP_REST_Response ) return $response;

    $response->set_data( cd_normalize_location_term_rest_data( $response->get_data() ) );
    return $response;
}
add_filter( 'rest_prepare_chess_state', 'cd_normalize_location_term_rest_response', PHP_INT_MAX );
add_filter( 'rest_prepare_chess_country', 'cd_normalize_location_term_rest_response', PHP_INT_MAX );

function cd_is_location_taxonomy_rest_request( $request ) {
    if ( ! $request instanceof WP_REST_Request ) return false;

    $route = $request->get_route();
    return 0 === strpos( $route, '/wp/v2/chess_country' ) || 0 === strpos( $route, '/wp/v2/chess_state' );
}

/* Run after every plugin has modified the REST response Gutenberg receives. */
function cd_finalize_location_taxonomy_rest_response( $response, $server, $request ) {
    if ( ! $response instanceof WP_REST_Response || ! cd_is_location_taxonomy_rest_request( $request ) ) {
        return $response;
    }

    if ( $response->get_status() >= 400 ) return $response;

    $data = $response->get_data();
    if ( isset( $data[0] ) && is_array( $data[0] ) ) {
        foreach ( $data as $index => $term_data ) {
            $data[ $index ] = cd_normalize_location_term_rest_data( $term_data );
        }
    } else {
        $data = cd_normalize_location_term_rest_data( $data );
    }

    $response->set_data( $data );
    return $response;
}
add_filter( 'rest_post_dispatch', 'cd_finalize_location_taxonomy_rest_response', PHP_INT_MAX, 3 );

function cd_record_location_taxonomy_create_attempt( $response, $handler, $request ) {
    if ( ! cd_is_location_taxonomy_rest_request( $request ) || 'POST' !== $request->get_method() || ! is_user_logged_in() ) {
        return $response;
    }

    $data = $response instanceof WP_REST_Response ? $response->get_data() : array();
    $record = array(
        'time'           => current_time( 'mysql' ),
        'route'          => $request->get_route(),
        'requested_name' => sanitize_text_field( (string) $request->get_param( 'name' ) ),
        'parent'         => (int) $request->get_param( 'parent' ),
        'status'         => $response instanceof WP_REST_Response ? $response->get_status() : 0,
        'response_code'  => is_array( $data ) && isset( $data['code'] ) ? (string) $data['code'] : '',
        'response_name'  => is_array( $data ) && isset( $data['name'] ) ? (string) $data['name'] : '',
        'response_id'    => is_array( $data ) && isset( $data['id'] ) ? (int) $data['id'] : 0,
    );

    update_user_meta( get_current_user_id(), '_cd_last_location_term_create', wp_json_encode( $record ) );
    return $response;
}
add_filter( 'rest_request_after_callbacks', 'cd_record_location_taxonomy_create_attempt', 100, 3 );

function cd_get_location_term_editor_data( $term ) {
    $link = get_term_link( $term );

    return array(
        'id'          => (int) $term->term_id,
        'count'       => (int) $term->count,
        'description' => (string) $term->description,
        'link'        => is_wp_error( $link ) ? '' : (string) $link,
        'name'        => (string) $term->name,
        'slug'        => (string) $term->slug,
        'taxonomy'    => (string) $term->taxonomy,
        'parent'      => (int) $term->parent,
        'meta'        => array(),
    );
}

/* Gutenberg fallback when another plugin breaks the native term-create request. */
function cd_ajax_create_location_term() {
    check_ajax_referer( 'cd_location_term_guard', 'nonce' );

    $taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
    $name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $parent   = isset( $_POST['parent'] ) ? (int) wp_unslash( $_POST['parent'] ) : 0;

    if ( ! in_array( $taxonomy, array( 'chess_country', 'chess_state' ), true ) || ! taxonomy_exists( $taxonomy ) ) {
        wp_send_json_error( array( 'code' => 'invalid_taxonomy', 'message' => 'Invalid location taxonomy.' ), 400 );
    }

    $tax = get_taxonomy( $taxonomy );
    if ( ! $tax || ! current_user_can( $tax->cap->manage_terms ) ) {
        wp_send_json_error( array( 'code' => 'forbidden', 'message' => 'You are not allowed to add locations.' ), 403 );
    }

    if ( '' === trim( $name ) ) {
        wp_send_json_error( array( 'code' => 'empty_term_name', 'message' => 'Enter a country or state name.' ), 400 );
    }

    if ( $parent && ! term_exists( $parent, $taxonomy ) ) {
        wp_send_json_error( array( 'code' => 'invalid_parent', 'message' => 'The selected parent does not exist.' ), 400 );
    }

    $created = wp_insert_term( $name, $taxonomy, array( 'parent' => $parent ) );
    if ( is_wp_error( $created ) ) {
        if ( 'term_exists' !== $created->get_error_code() ) {
            wp_send_json_error( array(
                'code'    => $created->get_error_code(),
                'message' => $created->get_error_message(),
            ), 400 );
        }

        $term_id = (int) $created->get_error_data();
    } else {
        $term_id = (int) $created['term_id'];
    }

    $term = get_term( $term_id, $taxonomy );
    if ( ! $term || is_wp_error( $term ) ) {
        wp_send_json_error( array( 'code' => 'term_lookup_failed', 'message' => 'The location could not be loaded after saving.' ), 500 );
    }

    update_user_meta( get_current_user_id(), '_cd_last_location_term_create', wp_json_encode( array(
        'time'           => current_time( 'mysql' ),
        'source'         => 'editor_ajax_fallback',
        'route'          => 'admin-ajax.php',
        'requested_name' => $name,
        'parent'         => $parent,
        'status'         => 201,
        'response_code'  => '',
        'response_name'  => $term->name,
        'response_id'    => (int) $term->term_id,
    ) ) );

    wp_send_json_success( array( 'term' => cd_get_location_term_editor_data( $term ) ), 201 );
}
add_action( 'wp_ajax_cd_create_location_term', 'cd_ajax_create_location_term' );

function cd_register_taxonomies() {
    register_taxonomy(
        'chess_state',
        array( 'post' ),
        cd_get_location_taxonomy_args( 'State', 'Chess States', 'chess-in', 'chess_state' )
    );

    register_taxonomy(
        'chess_country',
        array( 'post' ),
        cd_get_location_taxonomy_args( 'Country', 'Countries', 'chess-news', 'chess_country' )
    );
}
add_action('init', 'cd_register_taxonomies');

function cd_is_editor_debug_request() {
    return is_admin()
        && current_user_can( 'edit_posts' )
        && isset( $_GET['cd_debug'] )
        && '0' !== (string) $_GET['cd_debug'];
}

function cd_debug_yes_no( $value ) {
    return $value ? 'yes' : 'no';
}

function cd_get_debug_post_id() {
    $post_id = 0;

    if ( isset( $_GET['post'] ) ) {
        $post_id = (int) $_GET['post'];
    } elseif ( isset( $_GET['post_ID'] ) ) {
        $post_id = (int) $_GET['post_ID'];
    } elseif ( isset( $_POST['post_ID'] ) ) {
        $post_id = (int) $_POST['post_ID'];
    }

    return $post_id > 0 ? $post_id : 0;
}

function cd_get_taxonomy_debug_data( $taxonomy ) {
    $tax = get_taxonomy( $taxonomy );
    if ( ! $tax ) {
        return array(
            'taxonomy' => $taxonomy,
            'exists'   => false,
        );
    }

    $caps = (array) $tax->cap;
    $cap_checks = array();

    foreach ( array( 'manage_terms', 'edit_terms', 'delete_terms', 'assign_terms' ) as $cap_key ) {
        $cap = isset( $caps[ $cap_key ] ) ? (string) $caps[ $cap_key ] : '';
        $cap_checks[ $cap_key ] = array(
            'capability' => $cap,
            'allowed'    => $cap ? cd_debug_yes_no( current_user_can( $cap ) ) : 'no capability set',
        );
    }

    $terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'number'     => 12,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );

    $term_samples = array();
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $term_samples[] = array(
                'id'     => (int) $term->term_id,
                'name'   => (string) $term->name,
                'slug'   => (string) $term->slug,
                'parent' => (int) $term->parent,
                'count'  => (int) $term->count,
            );
        }
    }

    return array(
        'taxonomy'             => $taxonomy,
        'exists'               => true,
        'object_type'          => array_values( (array) $tax->object_type ),
        'hierarchical'         => cd_debug_yes_no( $tax->hierarchical ),
        'show_ui'              => cd_debug_yes_no( $tax->show_ui ),
        'show_in_rest'         => cd_debug_yes_no( $tax->show_in_rest ),
        'rest_base'            => (string) $tax->rest_base,
        'rest_controller'      => (string) $tax->rest_controller_class,
        'capability_checks'    => $cap_checks,
        'rest_terms_url'       => rest_url( 'wp/v2/' . $tax->rest_base ),
        'first_terms_or_error' => is_wp_error( $terms ) ? $terms->get_error_message() : $term_samples,
    );
}

function cd_get_featured_image_debug_data( $post_id ) {
    if ( ! $post_id ) {
        return array( 'post_id' => 0, 'message' => 'No post ID found in the current editor URL.' );
    }

    $thumb_id = (int) get_post_thumbnail_id( $post_id );
    $attachment = $thumb_id ? get_post( $thumb_id ) : null;
    $post_type = get_post_type( $post_id );

    $data = array(
        'post_id'                      => (int) $post_id,
        'post_type'                    => (string) $post_type,
        'post_type_supports_thumbnail' => $post_type ? cd_debug_yes_no( post_type_supports( $post_type, 'thumbnail' ) ) : 'unknown post type',
        'raw_thumbnail_meta'           => (string) get_post_meta( $post_id, '_thumbnail_id', true ),
        'featured_image_guard_id'      => (string) get_post_meta( $post_id, '_cd_featured_image_guard_id', true ),
        'last_featured_save_attempt'   => (string) get_post_meta( $post_id, '_cd_featured_image_last_save', true ),
        'get_post_thumbnail_id'        => $thumb_id,
        'attachment_exists'            => cd_debug_yes_no( (bool) $attachment ),
        'featured_cd_hero_url'         => cd_get_featured_image_url( $post_id, 'cd-hero' ),
        'featured_cd_card_url'         => cd_get_featured_image_url( $post_id, 'cd-card' ),
        'featured_full_url'            => cd_get_featured_image_url( $post_id, 'full' ),
        'theme_home_hero_url'          => cd_get_post_image_url( $post_id, 'cd-hero' ),
        'theme_card_url'               => cd_get_post_image_url( $post_id, 'cd-card' ),
        'theme_onerror_fallback_url'   => cd_get_post_image_fallback_url( $post_id, cd_get_post_image_url( $post_id, 'cd-card' ) ),
        'first_attached_image_url'     => cd_get_attached_image_url( $post_id, 'full' ),
        'first_content_image_url'      => cd_get_first_content_image_url( $post_id ),
    );

    if ( $attachment ) {
        $attachment_meta = wp_get_attachment_metadata( $attachment->ID );
        $data['attachment'] = array(
            'id'                         => (int) $attachment->ID,
            'title'                      => (string) get_the_title( $attachment ),
            'status'                     => (string) $attachment->post_status,
            'mime_type'                  => (string) $attachment->post_mime_type,
            'parent'                     => (int) $attachment->post_parent,
            'guid'                       => (string) $attachment->guid,
            'attached_file_meta'         => (string) get_post_meta( $attachment->ID, '_wp_attached_file', true ),
            'metadata_file'              => is_array( $attachment_meta ) ? (string) ( $attachment_meta['file'] ?? '' ) : '',
            'metadata_sizes'             => is_array( $attachment_meta ) && ! empty( $attachment_meta['sizes'] ) ? array_keys( $attachment_meta['sizes'] ) : array(),
            'wordpress_attachment_url'   => wp_get_attachment_url( $attachment->ID ),
            'theme_recovered_image_url'  => cd_get_attachment_original_url( $attachment->ID ),
            'edit_link'                  => get_edit_post_link( $attachment->ID, 'raw' ),
        );
    }

    return $data;
}

function cd_render_editor_debug_panel() {
    if ( ! cd_is_editor_debug_request() ) return;

    cd_render_debug_textarea_panel( 'Checkmate Daily Debug', cd_get_admin_debug_data(), 'Copy this full block and send it back if Countries, States, or Featured Image still fail.' );
}
add_action( 'admin_notices', 'cd_render_editor_debug_panel' );

function cd_get_admin_debug_data( $post_id = null ) {
    $user = wp_get_current_user();
    $post_id = is_null( $post_id ) ? cd_get_debug_post_id() : (int) $post_id;
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    return array(
        'theme_version' => CD_VERSION,
        'screen'        => $screen ? array(
            'id'        => (string) $screen->id,
            'base'      => (string) $screen->base,
            'post_type' => (string) $screen->post_type,
        ) : 'screen not available',
        'current_user'  => array(
            'id'    => (int) $user->ID,
            'roles' => array_values( (array) $user->roles ),
            'can'   => array(
                'edit_posts'        => cd_debug_yes_no( current_user_can( 'edit_posts' ) ),
                'publish_posts'     => cd_debug_yes_no( current_user_can( 'publish_posts' ) ),
                'manage_categories' => cd_debug_yes_no( current_user_can( 'manage_categories' ) ),
                'upload_files'      => cd_debug_yes_no( current_user_can( 'upload_files' ) ),
            ),
        ),
        'taxonomies'     => array(
            'chess_country' => cd_get_taxonomy_debug_data( 'chess_country' ),
            'chess_state'   => cd_get_taxonomy_debug_data( 'chess_state' ),
        ),
        'last_location_term_create_attempt' => (string) get_user_meta( $user->ID, '_cd_last_location_term_create', true ),
        'featured_image' => cd_get_featured_image_debug_data( $post_id ),
    );
}

function cd_render_debug_textarea_panel( $title, $debug, $description = '' ) {
    echo '<div class="notice notice-info" style="padding:12px 14px;">';
    echo '<h2 style="margin:0 0 8px;">' . esc_html( $title ) . '</h2>';
    if ( $description ) {
        echo '<p style="margin:0 0 8px;">' . esc_html( $description ) . '</p>';
    }
    echo '<textarea readonly style="width:100%;min-height:360px;font-family:monospace;font-size:12px;">';
    echo esc_textarea( wp_json_encode( $debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
    echo '</textarea>';
    echo '</div>';
}

function cd_register_debug_tools_page() {
    add_management_page(
        'Checkmate Debug',
        'Checkmate Debug',
        'edit_posts',
        'checkmate-daily-debug',
        'cd_render_debug_tools_page'
    );
}
add_action( 'admin_menu', 'cd_register_debug_tools_page' );

function cd_render_debug_tools_page() {
    if ( ! current_user_can( 'edit_posts' ) ) return;

    $post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : cd_get_debug_post_id();
    $debug = cd_get_admin_debug_data( $post_id );

    echo '<div class="wrap">';
    echo '<h1>Checkmate Daily Debug</h1>';
    echo '<form method="get" style="margin:12px 0 18px;">';
    echo '<input type="hidden" name="page" value="checkmate-daily-debug">';
    echo '<label for="cd-debug-post-id"><strong>Post ID</strong></label> ';
    echo '<input id="cd-debug-post-id" type="number" name="post_id" value="' . esc_attr( $post_id ) . '" class="small-text"> ';
    submit_button( 'Load Debug', 'secondary', '', false );
    echo '</form>';
    echo '<p>For your current article, use Post ID <code>5731</code> if that is the article shown in your screenshot.</p>';
    cd_render_debug_textarea_panel( 'Debug Output', $debug, 'Copy this full block and send it back.' );
    echo '</div>';
}

function cd_render_editor_js_error_debugger() {
    if ( ! cd_is_editor_debug_request() ) return;
    ?>
    <script>
    (function () {
      function showCdDebugError(message) {
        var box = document.createElement('div');
        box.className = 'notice notice-error';
        box.style.padding = '12px 14px';
        box.innerHTML = '<p><strong>Checkmate Daily JS error:</strong> ' + String(message).replace(/[<>&]/g, function (c) {
          return {'<':'&lt;','>':'&gt;','&':'&amp;'}[c];
        }) + '</p>';
        var target = document.querySelector('.wrap') || document.body;
        target.insertBefore(box, target.firstChild);
      }
      window.addEventListener('error', function (event) {
        showCdDebugError(event.message || 'Unknown browser error');
      });
      window.addEventListener('unhandledrejection', function (event) {
        var reason = event.reason && (event.reason.message || event.reason);
        showCdDebugError(reason || 'Unhandled promise rejection');
      });
    }());
    </script>
    <?php
}
add_action( 'admin_footer', 'cd_render_editor_js_error_debugger' );

function cd_is_valid_image_attachment( $attachment_id ) {
    $attachment_id = (int) $attachment_id;
    if ( $attachment_id <= 0 ) return false;

    $attachment = get_post( $attachment_id );
    return $attachment
        && 'attachment' === $attachment->post_type
        && 0 === strpos( (string) $attachment->post_mime_type, 'image/' );
}

/* Recover image records imported without WordPress attachment metadata. */
function cd_get_attachment_original_url( $attachment_id ) {
    $attachment_id = (int) $attachment_id;
    if ( $attachment_id <= 0 ) return '';

    $url = wp_get_attachment_url( $attachment_id );
    if ( $url ) return cd_normalize_upload_url( $url );

    $relative_file = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );
    if ( $relative_file ) return cd_uploads_url( $relative_file );

    $metadata = wp_get_attachment_metadata( $attachment_id );
    if ( is_array( $metadata ) && ! empty( $metadata['file'] ) ) {
        return cd_uploads_url( $metadata['file'] );
    }

    $attachment = get_post( $attachment_id );
    if ( $attachment && filter_var( $attachment->guid, FILTER_VALIDATE_URL ) ) {
        return cd_normalize_upload_url( $attachment->guid );
    }

    return '';
}

function cd_repair_attachment_file_reference( $attachment_id ) {
    $attachment_id = (int) $attachment_id;
    if ( $attachment_id <= 0 ) return array();

    $before = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );
    $result = array( 'attached_file_before' => $before, 'attached_file_after' => $before );

    if ( ! $before ) {
        $attachment = get_post( $attachment_id );
        $path       = $attachment ? wp_parse_url( $attachment->guid, PHP_URL_PATH ) : '';
        $marker     = '/wp-content/uploads/';
        $position   = $path ? strpos( $path, $marker ) : false;

        if ( false !== $position ) {
            $relative = ltrim( substr( $path, $position + strlen( $marker ) ), '/' );
            if ( $relative ) {
                update_post_meta( $attachment_id, '_wp_attached_file', $relative );
                $result['attached_file_after'] = $relative;
            }
        }
    }

    return $result;
}

function cd_apply_featured_image_value( $post_id, $attachment_id, $source = 'unknown' ) {
    $post_id       = (int) $post_id;
    $attachment_id = (int) $attachment_id;

    if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
        return false;
    }

    if ( $attachment_id <= 0 ) {
        delete_post_thumbnail( $post_id );
        delete_post_meta( $post_id, '_cd_featured_image_guard_id' );
        update_post_meta( $post_id, '_cd_featured_image_last_save', wp_json_encode( array(
            'source'        => $source,
            'action'        => 'deleted',
            'attachment_id' => $attachment_id,
            'time'          => current_time( 'mysql' ),
        ) ) );
        return true;
    }

    if ( ! cd_is_valid_image_attachment( $attachment_id ) ) {
        update_post_meta( $post_id, '_cd_featured_image_last_save', wp_json_encode( array(
            'source'        => $source,
            'action'        => 'rejected_invalid_attachment',
            'attachment_id' => $attachment_id,
            'time'          => current_time( 'mysql' ),
        ) ) );
        return false;
    }

    $attachment_repair          = cd_repair_attachment_file_reference( $attachment_id );
    $set_post_thumbnail_result = set_post_thumbnail( $post_id, $attachment_id );
    $direct_meta_result        = update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
    update_post_meta( $post_id, '_cd_featured_image_guard_id', $attachment_id );
    clean_post_cache( $post_id );

    update_post_meta( $post_id, '_cd_featured_image_last_save', wp_json_encode( array(
        'source'                    => $source,
        'action'                    => 'set',
        'attachment_id'             => $attachment_id,
        'set_post_thumbnail_result' => $set_post_thumbnail_result,
        'direct_meta_result'        => $direct_meta_result,
        'saved_meta'                => (int) get_post_meta( $post_id, '_thumbnail_id', true ),
        'attachment_is_image'       => cd_debug_yes_no( wp_attachment_is_image( $attachment_id ) ),
        'attachment_repair'         => $attachment_repair,
        'time'                      => current_time( 'mysql' ),
    ) ) );

    return true;
}

function cd_force_rest_featured_media_save( $post, $request, $creating ) {
    if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) return;
    if ( ! $request instanceof WP_REST_Request ) return;
    if ( ! $request->offsetExists( 'featured_media' ) ) return;

    $attachment_id = (int) $request->get_param( 'featured_media' );

    if ( $attachment_id > 0 ) {
        cd_apply_featured_image_value( $post->ID, $attachment_id, 'rest_after_insert_post' );
        return;
    }

    $guard_id = (int) get_post_meta( $post->ID, '_cd_featured_image_guard_id', true );
    if ( $guard_id > 0 && cd_is_valid_image_attachment( $guard_id ) ) {
        cd_apply_featured_image_value( $post->ID, $guard_id, 'rest_after_insert_post_guard_restore' );
    }
}
add_action( 'rest_after_insert_post', 'cd_force_rest_featured_media_save', 100, 3 );

function cd_force_classic_featured_image_save( $post_id, $post, $update ) {
    if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) return;
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
    if ( ! isset( $_POST['_thumbnail_id'] ) ) return;

    cd_apply_featured_image_value( $post_id, (int) wp_unslash( $_POST['_thumbnail_id'] ), 'save_post_post' );
}
add_action( 'save_post_post', 'cd_force_classic_featured_image_save', 100, 3 );

function cd_ajax_set_featured_image() {
    check_ajax_referer( 'cd_featured_image_guard', 'nonce' );

    $post_id       = isset( $_POST['post_id'] ) ? (int) wp_unslash( $_POST['post_id'] ) : 0;
    $attachment_id = isset( $_POST['attachment_id'] ) ? (int) wp_unslash( $_POST['attachment_id'] ) : 0;
    $source        = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : 'ajax_featured_image_guard';

    if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array( 'message' => 'Not allowed to edit this post.' ), 403 );
    }

    $saved = cd_apply_featured_image_value( $post_id, $attachment_id, $source );

    wp_send_json_success( array(
        'saved'        => cd_debug_yes_no( $saved ),
        'post_id'      => $post_id,
        'requested_id' => $attachment_id,
        'stored_id'    => (int) get_post_meta( $post_id, '_thumbnail_id', true ),
        'log'          => (string) get_post_meta( $post_id, '_cd_featured_image_last_save', true ),
    ) );
}
add_action( 'wp_ajax_cd_set_featured_image', 'cd_ajax_set_featured_image' );

function cd_enqueue_featured_image_editor_guard() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'post' !== $screen->post_type || 'post' !== $screen->base ) return;
    if ( ! wp_script_is( 'wp-edit-post', 'registered' ) ) return;

    $post_id  = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
    $thumb_id = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;
    if ( $thumb_id > 0 && ! get_post_meta( $post_id, '_cd_featured_image_guard_id', true ) ) {
        update_post_meta( $post_id, '_cd_featured_image_guard_id', $thumb_id );
    }

    $config = array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce( 'cd_featured_image_guard' ),
        'locationNonce' => wp_create_nonce( 'cd_location_term_guard' ),
    );

    $script = 'window.cdFeaturedImageGuard=' . wp_json_encode( $config ) . ';
(function(wp, config){
    if (!wp || !wp.data || !window.fetch || !config) return;

    var lastSeen = null;
    var lastSent = null;
    var wasSaving = false;
    var hasInitialized = false;
    var suppressZeroUntil = 0;
    var timer = null;

    function getEditor() {
        return wp.data.select("core/editor");
    }

    function getPostId() {
        var editor = getEditor();
        return editor && editor.getCurrentPostId ? parseInt(editor.getCurrentPostId(), 10) || 0 : 0;
    }

    function getFeaturedMedia() {
        var editor = getEditor();
        if (!editor || !editor.getEditedPostAttribute) return null;
        var value = editor.getEditedPostAttribute("featured_media");
        if (value === undefined || value === null) return null;
        value = parseInt(value, 10);
        return isNaN(value) ? null : value;
    }

    function sendFeaturedMedia(source) {
        var postId = getPostId();
        var mediaId = getFeaturedMedia();
        if (!postId || mediaId === null) return;
        if (mediaId <= 0 && source === "after_save") return;
        if (mediaId === lastSent && source !== "after_save") return;

        lastSent = mediaId;
        var body = new window.URLSearchParams();
        body.set("action", "cd_set_featured_image");
        body.set("nonce", config.nonce);
        body.set("post_id", String(postId));
        body.set("attachment_id", String(mediaId));
        body.set("source", source);

        window.fetch(config.ajaxurl, {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: body.toString()
        }).catch(function(){});
    }

    function scheduleSend(source) {
        window.clearTimeout(timer);
        timer = window.setTimeout(function(){ sendFeaturedMedia(source); }, 350);
    }

    wp.data.subscribe(function(){
        var editor = getEditor();
        var isSaving = !!(editor && editor.isSavingPost && editor.isSavingPost());
        var justFinishedSaving = wasSaving && !isSaving;
        if (justFinishedSaving) suppressZeroUntil = Date.now() + 1500;

        var mediaId = getFeaturedMedia();
        if (mediaId !== null && mediaId !== lastSeen) {
            lastSeen = mediaId;
            if ((hasInitialized || mediaId > 0) && !(mediaId <= 0 && (isSaving || Date.now() < suppressZeroUntil))) {
                scheduleSend("editor_change");
            }
        }
        hasInitialized = true;

        if (justFinishedSaving) {
            scheduleSend("after_save");
        }
        wasSaving = isSaving;
    });
}(window.wp, window.cdFeaturedImageGuard));
(function(wp, config){
    if (!wp || !wp.apiFetch || !wp.apiFetch.use || !window.fetch || !config || !config.locationNonce) return;

    function getLocationTaxonomy(options) {
        if (!options || String(options.method || "GET").toUpperCase() !== "POST") return "";
        var route = String(options.path || options.url || "");
        var match = route.match(/\/wp\/v2\/(chess_country|chess_state)(?:\?|$)/);
        return match ? match[1] : "";
    }

    function createLocationFallback(taxonomy, data) {
        var body = new window.URLSearchParams();
        body.set("action", "cd_create_location_term");
        body.set("nonce", config.locationNonce);
        body.set("taxonomy", taxonomy);
        body.set("name", String((data && data.name) || ""));
        body.set("parent", String((data && data.parent) || 0));

        return window.fetch(config.ajaxurl, {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: body.toString()
        }).then(function(response){ return response.json(); }).then(function(payload){
            if (!payload || !payload.success || !payload.data || !payload.data.term) {
                throw new Error((payload && payload.data && payload.data.message) || "Could not save the location.");
            }
            return payload.data.term;
        });
    }

    wp.apiFetch.use(function(options, next){
        var taxonomy = getLocationTaxonomy(options);
        if (!taxonomy) return next(options);

        return Promise.resolve(next(options)).catch(function(error){
            return createLocationFallback(taxonomy, options.data || {}).catch(function(){ throw error; });
        });
    });
}(window.wp, window.cdFeaturedImageGuard));';

    wp_add_inline_script( 'wp-edit-post', $script, 'after' );
}
add_action( 'enqueue_block_editor_assets', 'cd_enqueue_featured_image_editor_guard' );

function cd_is_frontend_debug_request() {
    return ! is_admin()
        && current_user_can( 'edit_posts' )
        && isset( $_GET['cd_debug'] )
        && '0' !== (string) $_GET['cd_debug'];
}

function cd_render_frontend_image_debug_panel() {
    if ( ! cd_is_frontend_debug_request() ) return;

    $posts = get_posts( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
    ) );

    $debug = array(
        'theme_version' => CD_VERSION,
        'page'          => array(
            'is_front_page' => cd_debug_yes_no( is_front_page() ),
            'is_home'       => cd_debug_yes_no( is_home() ),
            'request_uri'   => isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
        ),
        'posts'         => array(),
    );

    foreach ( $posts as $post ) {
        $debug['posts'][] = array(
            'title'          => get_the_title( $post ),
            'permalink'      => get_permalink( $post ),
            'image_decision' => cd_get_featured_image_debug_data( $post->ID ),
        );
    }

    echo '<div style="position:fixed;z-index:999999;left:16px;right:16px;bottom:16px;max-height:55vh;overflow:auto;background:#111;color:#fff;border:2px solid #168ccf;border-radius:4px;padding:14px;box-shadow:0 8px 30px rgba(0,0,0,.35);">';
    echo '<strong style="display:block;margin-bottom:8px;">Checkmate Daily Image Debug</strong>';
    echo '<textarea readonly style="width:100%;min-height:260px;font-family:monospace;font-size:12px;color:#111;">';
    echo esc_textarea( wp_json_encode( $debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
    echo '</textarea>';
    echo '</div>';
}
add_action( 'wp_footer', 'cd_render_frontend_image_debug_panel', 100 );

/* ── GET ALL CATEGORIES DYNAMICALLY ── */
function cd_get_all_chess_categories() {
    return get_categories(array(
        'orderby'    => 'count',
        'order'      => 'DESC',
        'hide_empty' => false,
        'exclude'    => array(get_cat_ID('Uncategorized')),
    ));
}

/* ── CATEGORY COLOR CLASS ── */
function cd_get_cat_class($slug) {
    return in_array($slug, array('rapid-rating','classical','chess960','announcements')) ? 'dark' : '';
}

/* --- Media URL resilience after host migrations --- */
function cd_uploads_url( $relative_path = '' ) {
    $uploads = wp_upload_dir();
    if ( empty( $uploads['baseurl'] ) ) return '';

    return trailingslashit( $uploads['baseurl'] ) . ltrim( (string) $relative_path, '/' );
}

function cd_normalize_upload_url( $url ) {
    if ( ! $url || ! is_string( $url ) ) return $url;

    $path = wp_parse_url( $url, PHP_URL_PATH );
    if ( ! $path ) return $url;

    $marker = '/wp-content/uploads/';
    $pos    = strpos( $path, $marker );
    if ( false === $pos ) return $url;

    $relative = substr( $path, $pos + strlen( $marker ) );
    if ( '' === $relative ) return $url;

    return cd_uploads_url( $relative );
}
add_filter( 'wp_get_attachment_url', 'cd_normalize_upload_url' );

function cd_fallback_missing_intermediate_image( $downsize, $attachment_id, $size ) {
    if ( $downsize || 'full' === $size || is_array( $size ) ) return $downsize;

    $meta = wp_get_attachment_metadata( $attachment_id );
    if ( empty( $meta['file'] ) || empty( $meta['sizes'][ $size ]['file'] ) ) return false;

    $uploads = wp_upload_dir();
    if ( empty( $uploads['basedir'] ) ) return false;

    $subdir = dirname( $meta['file'] );
    $subdir = ( '.' === $subdir ) ? '' : trailingslashit( $subdir );
    $sized_file = trailingslashit( $uploads['basedir'] ) . $subdir . $meta['sizes'][ $size ]['file'];

    if ( file_exists( $sized_file ) ) return false;

    $full = wp_get_attachment_image_src( $attachment_id, 'full' );
    if ( ! $full ) return false;

    return array( $full[0], $full[1], $full[2], false );
}
add_filter( 'image_downsize', 'cd_fallback_missing_intermediate_image', 10, 3 );

function cd_get_featured_image_url( $post_id, $size = 'full' ) {
    $thumb_id = get_post_thumbnail_id( $post_id );
    if ( ! $thumb_id ) return '';

    $url = wp_get_attachment_image_url( $thumb_id, $size );
    if ( ! $url && 'full' !== $size ) {
        $url = wp_get_attachment_image_url( $thumb_id, 'full' );
    }

    if ( ! $url ) {
        $url = cd_get_attachment_original_url( $thumb_id );
    }

    return $url ? cd_normalize_upload_url( $url ) : '';
}

function cd_has_featured_image( $post_id ) {
    return (bool) get_post_thumbnail_id( $post_id );
}

function cd_get_attached_image_url( $post_id, $size = 'full' ) {
    $attachments = get_children( array(
        'post_parent'    => $post_id,
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'numberposts'    => 1,
        'orderby'        => 'menu_order ID',
        'order'          => 'ASC',
    ) );

    if ( ! $attachments ) return '';

    $attachment = reset( $attachments );
    $url        = wp_get_attachment_image_url( $attachment->ID, $size );

    if ( ! $url && 'full' !== $size ) {
        $url = wp_get_attachment_image_url( $attachment->ID, 'full' );
    }

    if ( ! $url ) {
        $url = cd_get_attachment_original_url( $attachment->ID );
    }

    return $url ? cd_normalize_upload_url( $url ) : '';
}

function cd_make_image_url_absolute( $url ) {
    $url = trim( html_entity_decode( (string) $url ) );
    if ( '' === $url || 0 === strpos( $url, 'data:' ) ) return '';

    if ( 0 === strpos( $url, '//' ) ) {
        $scheme = is_ssl() ? 'https:' : 'http:';
        return $scheme . $url;
    }

    if ( preg_match( '#^https?://#i', $url ) ) {
        return cd_normalize_upload_url( $url );
    }

    if ( 0 === strpos( $url, '/' ) ) {
        return home_url( $url );
    }

    return '';
}

function cd_get_first_content_image_url( $post_id ) {
    $content = get_post_field( 'post_content', $post_id );
    if ( ! $content ) return '';

    if ( preg_match_all( '/<img[^>]+(?:data-lazy-src|data-src|src)=["\']([^"\']+)["\']/i', $content, $matches ) ) {
        foreach ( $matches[1] as $raw_url ) {
            $url = cd_make_image_url_absolute( $raw_url );
            if ( $url ) return $url;
        }
    }

    return '';
}

function cd_get_post_image_url( $post_id, $size = 'full' ) {
    if ( cd_has_featured_image( $post_id ) ) {
        $candidates = array(
            cd_get_featured_image_url( $post_id, $size ),
        );

        if ( 'full' !== $size ) {
            $candidates[] = cd_get_featured_image_url( $post_id, 'full' );
        }

        foreach ( $candidates as $url ) {
            if ( $url ) return cd_normalize_upload_url( $url );
        }

        return '';
    }

    $candidates = array(
        cd_get_attached_image_url( $post_id, $size ),
    );

    if ( 'full' !== $size ) {
        $candidates[] = cd_get_attached_image_url( $post_id, 'full' );
    }

    $candidates[] = cd_get_first_content_image_url( $post_id );

    foreach ( $candidates as $url ) {
        if ( $url ) return cd_normalize_upload_url( $url );
    }

    return '';
}

function cd_get_post_image_fallback_url( $post_id, $primary = '' ) {
    if ( cd_has_featured_image( $post_id ) ) {
        $url = cd_get_featured_image_url( $post_id, 'full' );
        $url = $url ? cd_normalize_upload_url( $url ) : '';

        return ( $url && $url !== $primary ) ? $url : '';
    }

    $candidates = array(
        cd_get_attached_image_url( $post_id, 'full' ),
        cd_get_first_content_image_url( $post_id ),
    );

    foreach ( $candidates as $url ) {
        $url = $url ? cd_normalize_upload_url( $url ) : '';
        if ( $url && $url !== $primary ) return $url;
    }

    return '';
}

function cd_render_post_image( $post_id, $size = 'full', $args = array() ) {
    $primary  = cd_get_post_image_url( $post_id, $size );
    $fallback = cd_get_post_image_fallback_url( $post_id, $primary );

    if ( ! $primary ) $primary = $fallback;
    if ( ! $primary ) return false;

    $attrs = array(
        'src'      => esc_url( $primary ),
        'alt'      => esc_attr( $args['alt'] ?? get_the_title( $post_id ) ),
        'loading'  => esc_attr( $args['loading'] ?? 'lazy' ),
        'decoding' => esc_attr( $args['decoding'] ?? 'async' ),
    );

    foreach ( array( 'class', 'width', 'height' ) as $name ) {
        if ( ! empty( $args[ $name ] ) ) {
            $attrs[ $name ] = esc_attr( $args[ $name ] );
        }
    }

    if ( $fallback && $fallback !== $primary ) {
        $attrs['onerror'] = esc_attr( 'this.onerror=null;this.src=' . wp_json_encode( $fallback ) . ';' );
    }

    echo '<img';
    foreach ( $attrs as $name => $value ) {
        echo ' ' . esc_attr( $name ) . '="' . $value . '"';
    }
    echo '>';

    return true;
}

function cd_extract_youtube_id( $url ) {
    $url = trim( (string) $url );
    if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $url ) ) return $url;

    preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:shorts/|[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([A-Za-z0-9_-]{11})%i', $url, $match );
    return $match[1] ?? '';
}

function cd_find_youtube_id_in_value( $value ) {
    if ( is_array( $value ) || is_object( $value ) ) {
        foreach ( (array) $value as $item ) {
            $video_id = cd_find_youtube_id_in_value( $item );
            if ( $video_id ) return $video_id;
        }
        return '';
    }

    $value = html_entity_decode( (string) $value );
    if ( '' === trim( $value ) ) return '';

    return cd_extract_youtube_id( $value );
}

function cd_get_video_youtube_id( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id ) return '';

    $sources = array(
        get_post_meta( $post_id, '_cd_youtube_id', true ),
        get_post_meta( $post_id, '_cd_youtube_url', true ),
        get_post_field( 'post_content', $post_id ),
        get_post_field( 'post_excerpt', $post_id ),
    );

    foreach ( $sources as $source ) {
        $video_id = cd_find_youtube_id_in_value( $source );
        if ( $video_id ) {
            cd_store_video_youtube_id( $post_id, $video_id );
            return $video_id;
        }
    }

    $all_meta = get_post_meta( $post_id );
    foreach ( $all_meta as $key => $values ) {
        if ( false === stripos( $key, 'youtube' ) && false === stripos( $key, 'video' ) && false === stripos( $key, 'embed' ) && false === stripos( $key, 'oembed' ) ) {
            continue;
        }
        $video_id = cd_find_youtube_id_in_value( $values );
        if ( $video_id ) {
            cd_store_video_youtube_id( $post_id, $video_id );
            return $video_id;
        }
    }

    return '';
}

function cd_store_video_youtube_id( $post_id, $video_id ) {
    $video_id = cd_extract_youtube_id( $video_id );
    if ( ! $video_id || 'cd_video' !== get_post_type( $post_id ) ) return;

    if ( get_post_meta( $post_id, '_cd_youtube_id', true ) !== $video_id ) {
        update_post_meta( $post_id, '_cd_youtube_id', $video_id );
    }
    if ( ! get_post_meta( $post_id, '_cd_youtube_url', true ) ) {
        update_post_meta( $post_id, '_cd_youtube_url', 'https://www.youtube.com/watch?v=' . $video_id );
    }
}

function cd_youtube_thumbnail_url( $video_id, $quality = 'hqdefault' ) {
    $video_id = cd_extract_youtube_id( $video_id );
    if ( ! $video_id ) return '';

    return 'https://i.ytimg.com/vi/' . rawurlencode( $video_id ) . '/' . sanitize_key( $quality ) . '.jpg';
}

function cd_render_youtube_thumbnail( $video_id, $args = array() ) {
    $quality  = $args['quality'] ?? 'hqdefault';
    $fallback = $args['fallback_quality'] ?? 'hqdefault';
    $primary  = cd_youtube_thumbnail_url( $video_id, $quality );
    $backup   = cd_youtube_thumbnail_url( $video_id, $fallback );

    if ( ! $primary ) return false;

    $attrs = array(
        'src'      => esc_url( $primary ),
        'alt'      => esc_attr( $args['alt'] ?? '' ),
        'loading'  => esc_attr( $args['loading'] ?? 'lazy' ),
        'decoding' => esc_attr( $args['decoding'] ?? 'async' ),
    );

    foreach ( array( 'class', 'width', 'height', 'style' ) as $name ) {
        if ( ! empty( $args[ $name ] ) ) {
            $attrs[ $name ] = esc_attr( $args[ $name ] );
        }
    }

    if ( $backup && $backup !== $primary ) {
        $attrs['onerror'] = esc_attr( 'this.onerror=null;this.src=' . wp_json_encode( $backup ) . ';' );
    }

    echo '<img';
    foreach ( $attrs as $name => $value ) {
        echo ' ' . esc_attr( $name ) . '="' . $value . '"';
    }
    echo '>';

    return true;
}

/* ── FORMAT DATA ── */
function cd_get_format_data($slug) {
    $map = array(
        'blitz-rating' => array(
            'title'       => 'Blitz Chess — Latest News and Ratings',
            'desc'        => 'All Blitz chess news, tournament results, and FIDE rating updates. Blitz uses 3+2 or 5+0 time control.',
            'seo_kw'      => array('blitz chess india','blitz chess results','fide blitz rating india','blitz chess tournament 2026'),
            'schema_desc' => 'Blitz chess news and results from India.',
        ),
        'rapid-rating' => array(
            'title'       => 'Rapid Chess — Latest News and Ratings',
            'desc'        => 'Rapid chess news, tournament results, and FIDE rating updates from and much more.',
            'seo_kw'      => array('rapid chess india','rapid chess tournament','fide rapid rating india','rapid chess news 2026'),
            'schema_desc' => 'Rapid chess news and results from India.',
        ),
        'classical-rating' => array(
            'title'       => 'Classical Chess — Latest News and Ratings',
            'desc'        => 'Classical chess news from India and the world. The original format with 90+ minutes per player.',
            'seo_kw'      => array('classical chess india','classical chess news','india chess rating','chess championship india 2026'),
            'schema_desc' => 'Classical chess news and results from India.',
        ),
        'chess960' => array(
            'title'       => 'Chess960 — Fischer Random Chess News',
            'desc'        => 'Chess960 (Fischer Random) randomizes the back-rank starting position, creating 960 unique starting setups.',
            'seo_kw'      => array('chess960 india','fischer random chess india','chess960 tournament 2026'),
            'schema_desc' => 'Chess960 Fischer Random chess news from India.',
        ),
    );
    return isset($map[$slug]) ? $map[$slug] : null;
}

/* ── INDIA STATE DATA ── */
function cd_get_india_states() {
    return array(
        array('name'=>'Tamil Nadu',     'slug'=>'tamil-nadu',     'gms'=>24),
        array('name'=>'Maharashtra',    'slug'=>'maharashtra',    'gms'=>12),
        array('name'=>'Kerala',         'slug'=>'kerala',         'gms'=>8),
        array('name'=>'West Bengal',    'slug'=>'west-bengal',    'gms'=>6),
        array('name'=>'Karnataka',      'slug'=>'karnataka',      'gms'=>9),
        array('name'=>'Gujarat',        'slug'=>'gujarat',        'gms'=>5),
        array('name'=>'Telangana',      'slug'=>'telangana',      'gms'=>7),
        array('name'=>'Andhra Pradesh', 'slug'=>'andhra-pradesh', 'gms'=>4),
        array('name'=>'Rajasthan',      'slug'=>'rajasthan',      'gms'=>3),
        array('name'=>'Delhi',          'slug'=>'delhi',          'gms'=>8),
        array('name'=>'Punjab',         'slug'=>'punjab',         'gms'=>3),
        array('name'=>'Uttar Pradesh',  'slug'=>'uttar-pradesh',  'gms'=>4),
        array('name'=>'Madhya Pradesh', 'slug'=>'madhya-pradesh', 'gms'=>2),
        array('name'=>'Odisha',         'slug'=>'odisha',         'gms'=>2),
        array('name'=>'Goa',            'slug'=>'goa',            'gms'=>1),
        array('name'=>'Assam',          'slug'=>'assam',          'gms'=>1),
        array('name'=>'Bihar',          'slug'=>'bihar',          'gms'=>1),
        array('name'=>'Haryana',        'slug'=>'haryana',        'gms'=>1),
    );
}

/* ── EMPTY STATE RENDER ── */
function cd_render_empty_state($context = 'category') {
    $label = ($context === 'state') ? 'this state' : 'this category';
    echo '<div class="cd-empty-state"><div class="cd-empty-icon">&#9820;</div>';
    echo '<h3>No articles available in ' . esc_html($label) . ' yet.</h3>';
    echo '<p>Check back soon. We publish chess news from across India daily.</p></div>';
    $trending = get_posts(array('posts_per_page'=>4,'orderby'=>'comment_count','order'=>'DESC'));
    if (!$trending) return;
    echo '<div class="cd-section-head" style="margin-top:24px;"><h2>Latest Chess News</h2></div>';
    echo '<div class="cd-news-grid">';
    foreach ($trending as $tp) {
        $tp_cats = get_the_category($tp->ID);
        $tp_cat  = $tp_cats ? $tp_cats[0] : null;
        $tp_img  = cd_get_post_image_url($tp->ID, 'cd-card');
        echo '<div class="cd-news-card">';
        echo '<div class="cd-news-card-img">';
        if ($tp_img) cd_render_post_image($tp->ID, 'cd-card', array('width'=>400, 'height'=>230));
        echo '</div>';
        echo '<div class="cd-news-card-body">';
        if ($tp_cat) echo '<a href="' . esc_url(get_category_link($tp_cat->term_id)) . '" class="cd-cat-badge ' . esc_attr(cd_get_cat_class($tp_cat->slug)) . '">' . esc_html($tp_cat->name) . '</a>';
        echo '<div class="cd-news-card-title"><a href="' . esc_url(get_permalink($tp->ID)) . '">' . esc_html(get_the_title($tp->ID)) . '</a></div>';
        echo '<div class="cd-news-card-meta">' . get_the_date('M j, Y', $tp->ID) . '</div>';
        echo '<a href="' . esc_url(get_permalink($tp->ID)) . '" class="cd-readmore">Read More</a>';
        echo '</div></div>';
    }
    echo '</div>';
}

/* ── CATEGORY SUGGESTIONS ── */
function cd_render_category_suggestions($exclude_slug = '') {
    $all  = cd_get_all_chess_categories();
    $list = array_filter($all, function($c) use ($exclude_slug) { return $c->slug !== $exclude_slug && $c->count > 0; });
    $list = array_slice(array_values($list), 0, 5);
    if (!$list) return;
    echo '<div class="cd-section-head" style="margin-top:24px;"><h2>Explore More Categories</h2></div>';
    echo '<div class="cd-cat-suggest-grid">';
    foreach ($list as $sc) {
        echo '<a href="' . esc_url(get_category_link($sc->term_id)) . '" class="cd-cat-suggest-card">';
        echo '<div class="cd-cat-suggest-name">' . esc_html($sc->name) . '</div>';
        echo '<div class="cd-cat-suggest-count">' . (int)$sc->count . ' articles</div>';
        echo '</a>';
    }
    echo '</div>';
}

/* ── SEO META GENERATION ── */
function cd_get_seo_meta() {
    $site = get_bloginfo('name');
    $sep  = ' | ';
    if (is_home() || is_front_page()) return array(
        'title'       => $site . ' — Chess News India',
        'description' => 'India\'s leading chess news website. Latest results, ratings, and tournament updates from all states. Blitz, Rapid, Classical, Chess960.',
    );
    if (is_category()) {
        $cat = get_queried_object();
        $fmt = cd_get_format_data($cat->slug);
        return array(
            'title'       => $cat->name . ' Chess News' . $sep . $site,
            'description' => $fmt ? $fmt['schema_desc'] : 'Latest ' . $cat->name . ' chess news, tournament results and ratings from India. Updated daily.',
        );
    }
    if (is_tax('chess_state') || is_tax('chess_country')) {
        $term = get_queried_object();
        return array(
            'title'       => 'Chess News ' . $term->name . $sep . $site,
            'description' => 'Latest chess news from ' . $term->name . ' — tournament results, player ratings, FIDE updates and chess events. Updated daily.',
        );
    }
    if (is_singular('post')) return array(
        'title'       => get_the_title() . $sep . $site,
        'description' => wp_trim_words(get_the_excerpt(), 25),
    );
    if (cd_is_contact_page_request()) return array(
        'title'       => 'Contact Us' . $sep . $site,
        'description' => 'Contact Checkmate Daily for chess news tips, tournament updates, corrections, partnerships, and advertising enquiries.',
    );
    if (is_page()) return array(
        'title'       => get_the_title() . $sep . $site,
        'description' => wp_trim_words(get_the_excerpt() ?: strip_tags(get_the_content()), 25),
    );
    if (is_search()) return array(
        'title'       => 'Search: ' . get_search_query() . $sep . $site,
        'description' => 'Search results for "' . get_search_query() . '" on ' . $site . '.',
    );
    return array('title' => $site . ' — Chess News India', 'description' => 'Latest chess news from India.');
}

/* ── SEO FILTERS (single declaration — no duplicates) ── */
add_filter('wpseo_metadesc', function($d) {
    if (!is_tax('chess_state') && !is_tax('chess_country')) return $d;
    $t = get_queried_object();
    return $t ? 'Latest chess news from ' . $t->name . ' — tournament results, ratings, FIDE updates. Updated daily.' : $d;
});

/* ── OG IMAGE HELPER ── */
function cd_get_og_image() {
    // For single posts: use the registered cd-hero crop (1100×420)
    if ( is_singular() && has_post_thumbnail() ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'cd-hero' );
        if ( $img ) {
            return array( 'url' => $img[0], 'width' => $img[1], 'height' => $img[2] );
        }
    }
    // Fallback: use the site's custom logo
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $img = wp_get_attachment_image_src( $logo_id, 'full' );
        if ( $img ) {
            return array( 'url' => $img[0], 'width' => $img[1], 'height' => $img[2] );
        }
    }
    // Hard fallback: the footer logo already used in the theme
    return array(
        'url'    => cd_uploads_url( '2026/02/cropped-Checkmate-daily.jpg' ),
        'width'  => 1200,
        'height' => 630,
    );
}

/* ── BREADCRUMB SCHEMA HELPER ── */
function cd_get_breadcrumb_schema() {
    $items = array();
    $pos   = 1;

    // Home is always item 1
    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => 'Home',
        'item'     => home_url( '/' ),
    );

    if ( is_singular( 'post' ) ) {
        // Home > Category > Post
        $cats = get_the_category();
        if ( $cats ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $cats[0]->name,
                'item'     => get_category_link( $cats[0]->term_id ),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );

    } elseif ( is_singular( 'page' ) ) {
        // Home > Page Title
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );

    } elseif ( is_category() ) {
        // Home > Category
        $cat     = get_queried_object();
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $cat->name,
            'item'     => get_category_link( $cat->term_id ),
        );

    } elseif ( is_tax( 'chess_state' ) || is_tax( 'chess_country' ) ) {
        // Home > Chess News India > State/Country
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Chess News India',
            'item'     => home_url( '/chess-news-india/' ),
        );
        $term    = get_queried_object();
        $t_link  = get_term_link( $term );
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $term->name,
            'item'     => is_wp_error( $t_link ) ? home_url( '/' ) : $t_link,
        );

    } elseif ( is_search() ) {
        // Home > Search results for "…"
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Search results for "' . get_search_query() . '"',
            'item'     => get_search_link(),
        );

    } else {
        // Any other archive — no useful breadcrumb beyond Home
        return null;
    }

    // Only emit schema if there are at least 2 items
    if ( count( $items ) < 2 ) return null;

    return array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );
}

/* ── CANONICAL URL HELPER ── */
function cd_get_canonical_url() {
    $paged = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) ?: 1 );

    if ( cd_is_contact_page_request() ) {
        if ( is_page() ) {
            return get_permalink();
        }

        return home_url( '/contact/' );
    }
    if ( is_singular() ) {
        return get_permalink();
    }
    if ( is_front_page() || is_home() ) {
        return $paged > 1 ? get_pagenum_link( $paged ) : home_url( '/' );
    }
    if ( is_category() ) {
        $url = get_category_link( get_queried_object_id() );
        return $paged > 1 ? get_pagenum_link( $paged ) : $url;
    }
    if ( is_tax() ) {
        $url = get_term_link( get_queried_object() );
        return is_wp_error( $url ) ? home_url( '/' ) : ( $paged > 1 ? get_pagenum_link( $paged ) : $url );
    }
    if ( is_search() ) {
        return get_search_link();
    }
    return get_pagenum_link( $paged );
}

/* ─────────────────────────────────────────────────────────────────────
   MAIN SEO HEAD INJECTION
   Outputs: meta description, canonical, rel prev/next,
            Open Graph, Twitter Card, NewsArticle JSON-LD,
            BreadcrumbList JSON-LD, WebSite JSON-LD (homepage).
   Bails silently if Yoast SEO is active (WPSEO_VERSION defined).
───────────────────────────────────────────────────────────────────── */
function cd_inject_seo() {
    if ( defined( 'WPSEO_VERSION' ) ) return;

    global $wp_query, $post;

    $seo       = cd_get_seo_meta();
    $site_name = get_bloginfo( 'name' );
    $og_image  = cd_get_og_image();
    $canonical = cd_get_canonical_url();
    $paged     = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) ?: 1 );
    $max_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 0;

    /* ── 1. Meta description ── */
    echo '<meta name="description" content="' . esc_attr( $seo['description'] ) . '">' . "\n";

    /* ── 2. Canonical ── */
    echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";

    /* ── 3. Pagination hints (rel prev / next) — non-singular archives only ── */
    if ( ! is_singular() ) {
        if ( $paged > 1 ) {
            echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '">' . "\n";
        }
        if ( $max_pages && $paged < $max_pages ) {
            echo '<link rel="next" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '">' . "\n";
        }
    }

    /* ── 4. Open Graph — core ── */
    $og_type = is_singular( 'post' ) ? 'article' : 'website';
    echo '<meta property="og:type"        content="' . esc_attr( $og_type ) . '">'                    . "\n";
    echo '<meta property="og:title"       content="' . esc_attr( $seo['title'] ) . '">'               . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $seo['description'] ) . '">'         . "\n";
    echo '<meta property="og:url"         content="' . esc_url( $canonical ) . '">'                   . "\n";
    echo '<meta property="og:site_name"   content="' . esc_attr( $site_name ) . '">'                  . "\n";
    echo '<meta property="og:locale"      content="en_IN">'                                            . "\n";

    if ( $og_image ) {
        echo '<meta property="og:image"        content="' . esc_url( $og_image['url'] ) . '">'       . "\n";
        echo '<meta property="og:image:width"  content="' . (int) $og_image['width'] . '">'          . "\n";
        echo '<meta property="og:image:height" content="' . (int) $og_image['height'] . '">'         . "\n";
        echo '<meta property="og:image:alt"    content="' . esc_attr( is_singular() ? get_the_title() : $site_name ) . '">' . "\n";
    }

    /* ── 5. Open Graph — article-specific (og:article:*) ── */
    if ( is_singular( 'post' ) ) {
        echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">'          . "\n";
        echo '<meta property="article:modified_time"  content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
        echo '<meta property="article:author"         content="' . esc_attr( get_the_author() ) . '">'             . "\n";
        $a_cats = get_the_category();
        if ( $a_cats ) {
            echo '<meta property="article:section" content="' . esc_attr( $a_cats[0]->name ) . '">' . "\n";
        }
        $a_tags = get_the_tags();
        if ( $a_tags ) {
            foreach ( array_slice( $a_tags, 0, 5 ) as $tag ) {
                echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '">' . "\n";
            }
        }
    }

    /* ── 6. Twitter / X Card ── */
    echo '<meta name="twitter:card"        content="summary_large_image">'                             . "\n";
    echo '<meta name="twitter:site"        content="@checkmatedaily">'                                 . "\n";
    echo '<meta name="twitter:title"       content="' . esc_attr( $seo['title'] ) . '">'              . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $seo['description'] ) . '">'        . "\n";
    if ( $og_image ) {
        echo '<meta name="twitter:image"     content="' . esc_url( $og_image['url'] ) . '">'          . "\n";
        echo '<meta name="twitter:image:alt" content="' . esc_attr( is_singular() ? get_the_title() : $site_name ) . '">' . "\n";
    }

    /* ── 7. JSON-LD: NewsArticle (single posts only) ── */
    if ( is_singular( 'post' ) && $post ) {
        $n_cats       = get_the_category();
        $n_tags       = get_the_tags();
        $word_count   = (int) str_word_count( strip_tags( $post->post_content ) );
        $author_id    = (int) $post->post_author;
        $author_name  = get_the_author_meta( 'display_name', $author_id );
        $author_url   = get_author_posts_url( $author_id );

        $article = array(
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => get_permalink(),
            ),
            'headline'         => get_the_title(),
            'description'      => $seo['description'],
            'url'              => get_permalink(),
            'datePublished'    => get_the_date( 'c' ),
            'dateModified'     => get_the_modified_date( 'c' ),
            'wordCount'        => $word_count,
            'inLanguage'       => 'en-IN',
            'author'           => array(
                '@type' => 'Person',
                'name'  => $author_name,
                'url'   => $author_url,
            ),
            'publisher'        => array(
                '@type' => 'Organization',
                'name'  => $site_name,
                'url'   => home_url( '/' ),
                'logo'  => array(
                    '@type'  => 'ImageObject',
                    'url'    => cd_uploads_url( '2026/02/cropped-Checkmate-daily.jpg' ),
                    'width'  => 220,
                    'height' => 60,
                ),
            ),
        );

        if ( $n_cats ) {
            $article['articleSection'] = $n_cats[0]->name;
        }
        if ( $n_tags ) {
            $article['keywords'] = implode( ', ', wp_list_pluck( $n_tags, 'name' ) );
        }
        if ( has_post_thumbnail() ) {
            $img_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'cd-hero' );
            if ( $img_src ) {
                $article['image'] = array(
                    '@type'  => 'ImageObject',
                    'url'    => $img_src[0],
                    'width'  => (int) $img_src[1],
                    'height' => (int) $img_src[2],
                );
            }
        }

        echo '<script type="application/ld+json">'
             . wp_json_encode( $article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
             . '</script>' . "\n";
    }

    /* ── 8. JSON-LD: BreadcrumbList ── */
    $breadcrumb = cd_get_breadcrumb_schema();
    if ( $breadcrumb ) {
        echo '<script type="application/ld+json">'
             . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
             . '</script>' . "\n";
    }

    /* ── 9. JSON-LD: WebSite + Sitelinks SearchBox (homepage only) ── */
    if ( is_front_page() || is_home() ) {
        $website = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $site_name,
            'url'             => home_url( '/' ),
            'description'     => $seo['description'],
            'inLanguage'      => 'en-IN',
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => array(
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => home_url( '/?s={search_term_string}' ),
                ),
                'query-input' => 'required name=search_term_string',
            ),
        );
        echo '<script type="application/ld+json">'
             . wp_json_encode( $website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
             . '</script>' . "\n";
    }
}
add_action( 'wp_head', 'cd_inject_seo', 1 );

/* ── PERFORMANCE: GOOGLE FONTS PRECONNECT ──────────────────────────────
 * Fires at priority 1 — before wp_enqueue_style outputs the <link> for
 * the font stylesheet, so the connection is already warm when the browser
 * encounters the font URL.
 * ─────────────────────────────────────────────────────────────────────── */
function cd_preconnect_fonts() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'cd_preconnect_fonts', 1 );

/* ── PERFORMANCE: HERO IMAGE PRELOAD ───────────────────────────────────
 * Fires at priority 2 — outputs <link rel="preload" as="image"> for the
 * above-the-fold hero image so the browser fetches it immediately instead
 * of waiting for the HTML parser to discover it.
 *
 * Homepage: hero URL is cached in a 10-minute transient to avoid a DB
 * query on every page load. Cleared automatically when a new post is
 * published (see the save_post hook below).
 *
 * Single posts: uses the global $post which WordPress has already set
 * by the time header.php is included — no extra query needed.
 * ─────────────────────────────────────────────────────────────────────── */
function cd_preload_hero_image() {
    $hero_url = '';

    if ( is_singular() ) {
        // On single posts/pages the global $post is already populated
        global $post;
        if ( $post && has_post_thumbnail( $post->ID ) ) {
            $hero_url = get_the_post_thumbnail_url( $post->ID, 'cd-hero' );
        }
    } elseif ( is_front_page() || is_home() ) {
        // Cache the latest post thumbnail URL so we don't hit the DB every load
        $hero_url = get_transient( 'cd_hero_preload_url' );
        if ( false === $hero_url ) {
            $posts    = get_posts( array(
                'posts_per_page'      => 1,
                'post_status'         => 'publish',
                'ignore_sticky_posts' => true,
            ) );
            $hero_url = ! empty( $posts )
                ? (string) get_the_post_thumbnail_url( $posts[0]->ID, 'cd-hero' )
                : '';
            set_transient( 'cd_hero_preload_url', $hero_url, 10 * MINUTE_IN_SECONDS );
        }
    }

    if ( $hero_url ) {
        echo '<link rel="preload" as="image" href="' . esc_url( $hero_url ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'cd_preload_hero_image', 2 );

/* ── PERFORMANCE: FLUSH CACHES ON NEW PUBLISH ─────────────────────────
 * When a post is published or updated, delete the ticker HTML transient
 * and the hero preload URL transient so they reflect the latest content
 * on the next page load (within their respective TTLs).
 * ─────────────────────────────────────────────────────────────────────── */
function cd_flush_performance_caches( $post_id, $post, $update ) {
    if ( $post->post_type !== 'post' || $post->post_status !== 'publish' ) return;
    delete_transient( 'cd_ticker_html' );
    delete_transient( 'cd_hero_preload_url' );
}
add_action( 'save_post', 'cd_flush_performance_caches', 10, 3 );

/* --- Contact page route and form handling --- */
function cd_is_contact_page_request() {
    if ( is_page( array( 'contact', 'contact-us' ) ) ) return true;
    if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) return false;

    $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
    $home_path    = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

    if ( $home_path && 0 === strpos( $request_path, $home_path . '/' ) ) {
        $request_path = trim( substr( $request_path, strlen( $home_path ) + 1 ), '/' );
    } elseif ( $home_path === $request_path ) {
        $request_path = '';
    }

    return in_array( untrailingslashit( $request_path ), array( 'contact', 'contact-us' ), true );
}

function cd_load_contact_template( $template ) {
    if ( ! cd_is_contact_page_request() ) return $template;

    $contact_template = locate_template( 'page-contact.php' );
    if ( ! $contact_template ) return $template;

    if ( is_404() ) {
        status_header( 200 );
        nocache_headers();
    }

    return $contact_template;
}
add_filter( 'template_include', 'cd_load_contact_template' );

function cd_get_public_contact_email() {
    $email = sanitize_email( apply_filters( 'cd_public_contact_email', 'contact@checkmatedaily.com' ) );
    return is_email( $email ) ? $email : 'contact@checkmatedaily.com';
}

function cd_get_contact_recipient_email() {
    $recipient = get_option( 'admin_email' );
    if ( ! is_email( $recipient ) ) {
        $recipient = cd_get_public_contact_email();
    }

    $recipient = sanitize_email( apply_filters( 'cd_contact_recipient_email', $recipient ) );
    return is_email( $recipient ) ? $recipient : cd_get_public_contact_email();
}

function cd_contact_redirect( $status ) {
    $fallback = home_url( '/contact/' );
    $redirect = wp_get_referer();

    if ( ! $redirect ) {
        $redirect = $fallback;
    }

    $redirect = remove_query_arg( 'contact-status', $redirect );
    wp_safe_redirect( add_query_arg( 'contact-status', sanitize_key( $status ), $redirect ) );
    exit;
}

function cd_handle_contact_form() {
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        cd_contact_redirect( 'invalid' );
    }

    $nonce = isset( $_POST['cd_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cd_contact_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'cd_contact_form' ) ) {
        cd_contact_redirect( 'invalid' );
    }

    $honeypot = isset( $_POST['contact_company'] ) ? trim( (string) wp_unslash( $_POST['contact_company'] ) ) : '';
    if ( '' !== $honeypot ) {
        cd_contact_redirect( 'sent' );
    }

    $name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
    $email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
    $subject = isset( $_POST['contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_subject'] ) ) : '';
    $message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

    if ( '' === $name || ! is_email( $email ) || '' === $message ) {
        cd_contact_redirect( 'invalid' );
    }

    if ( '' === $subject ) {
        $subject = 'Contact request';
    }

    $mail_subject = sprintf( '[Checkmate Daily] %s', $subject );
    $mail_body    = sprintf(
        "Name: %s\nEmail: %s\nSubject: %s\n\nMessage:\n%s",
        $name,
        $email,
        $subject,
        $message
    );
    $headers = array(
        sprintf( 'Reply-To: %s <%s>', $name, $email ),
    );

    $sent = wp_mail( cd_get_contact_recipient_email(), $mail_subject, $mail_body, $headers );
    cd_contact_redirect( $sent ? 'sent' : 'mail-error' );
}
add_action( 'admin_post_nopriv_cd_contact_form', 'cd_handle_contact_form' );
add_action( 'admin_post_cd_contact_form', 'cd_handle_contact_form' );


add_filter('document_title_parts', function($p) {
    if (defined('WPSEO_VERSION')) return $p;
    $seo = cd_get_seo_meta();
    $p['title'] = $seo['title'];
    unset($p['tagline'], $p['site']);
    return $p;
});

/* ── AJAX: DYNAMIC FILTER ── */
function cd_ajax_filter() {
    check_ajax_referer('cd_nonce', 'nonce');
    $cat   = sanitize_text_field($_POST['category'] ?? '');
    $state = sanitize_text_field($_POST['state'] ?? '');
    $pg    = max(1, (int)($_POST['paged'] ?? 1));
    $args  = array('posts_per_page'=>8,'paged'=>$pg,'post_status'=>'publish');
    if ($cat && $cat !== 'all') { $co = get_category_by_slug($cat); if ($co) $args['cat'] = $co->term_id; }
    if ($state && $state !== 'all') $args['tax_query'] = array(array('taxonomy'=>'chess_state','field'=>'slug','terms'=>$state));
    $q = new WP_Query($args);
    ob_start();
    if ($q->have_posts()) {
        echo '<div class="cd-news-grid">';
        while ($q->have_posts()) { $q->the_post();
            $ac = get_the_category(); $ac = $ac ? $ac[0] : null;
            $ai = cd_get_post_image_url(get_the_ID(),'cd-card');
            echo '<div class="cd-news-card"><div class="cd-news-card-img">';
            if ($ai) cd_render_post_image(get_the_ID(), 'cd-card', array('width'=>400,'height'=>230));
            echo '</div>';
            echo '<div class="cd-news-card-body">';
            if ($ac) echo '<a href="' . esc_url(get_category_link($ac->term_id)) . '" class="cd-cat-badge ' . esc_attr(cd_get_cat_class($ac->slug)) . '">' . esc_html($ac->name) . '</a>';
            echo '<div class="cd-news-card-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
            echo '<div class="cd-news-card-meta">' . get_the_author() . ' &middot; ' . get_the_date() . '</div>';
            echo '<a href="' . get_permalink() . '" class="cd-readmore">Read More</a>';
            echo '</div></div>';
        }
        echo '</div>';
    } else { cd_render_empty_state(); }
    wp_reset_postdata();
    wp_send_json_success(array('html'=>ob_get_clean(),'found'=>$q->found_posts));
}
add_action('wp_ajax_nopriv_cd_filter_posts','cd_ajax_filter');
add_action('wp_ajax_cd_filter_posts','cd_ajax_filter');

/* ── NEWSLETTER AJAX ── */
function cd_newsletter_signup() {
    check_ajax_referer('cd_nonce','nonce');
    $email = sanitize_email($_POST['email'] ?? '');
    if (is_email($email)) wp_send_json_success(array('msg'=>'Thank you for subscribing!'));
    else wp_send_json_error(array('msg'=>'Please enter a valid email address.'));
}
add_action('wp_ajax_nopriv_cd_newsletter','cd_newsletter_signup');
add_action('wp_ajax_cd_newsletter','cd_newsletter_signup');

/* ── EXCERPT ── */
add_filter('excerpt_length', function(){ return 18; }, 999);
add_filter('excerpt_more',   function(){ return '...'; });

/* ── BODY CLASSES ── */
add_filter('body_class', function($c) {
    if (is_singular())         $c[] = 'cd-single-page';
    if (is_front_page())       $c[] = 'cd-home-page';
    if (is_category())         $c[] = 'cd-category-page';
    if (is_tax('chess_state')) $c[] = 'cd-state-page';
    if (cd_is_contact_page_request()) {
        $c[] = 'cd-contact-page';
        $c   = array_diff( $c, array( 'error404' ) );
    }
    return $c;
});

/* ── CLEAN WORDPRESS BLOAT ── */
remove_action('wp_head','wp_generator');
remove_action('wp_head','wlwmanifest_link');
remove_action('wp_head','rsd_link');
add_filter('the_generator','__return_empty_string');

/* =====================================================
   NUMBERED PAGINATION HELPER
===================================================== */
/**
 * Renders branded numbered pagination.
 *
 * @param int $max_pages  Total number of pages. Pass 0 to use the main query.
 */
function cd_pagination( $max_pages = 0 ) {
    global $wp_query;

    if ( ! $max_pages ) {
        $max_pages = (int) $wp_query->max_num_pages;
    }
    if ( $max_pages <= 1 ) return;

    $paged = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) ?: 1 );

    echo '<nav class="cd-pagination" aria-label="Posts navigation">';
    echo paginate_links( array(
        'base'      => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
        'format'    => '',
        'current'   => $paged,
        'total'     => $max_pages,
        'prev_text' => '&laquo; Prev',
        'next_text' => 'Next &raquo;',
        'type'      => 'plain',
        'end_size'  => 1,
        'mid_size'  => 2,
    ) );
    echo '</nav>';
}

/* ── CONTENT FLOW (end of every page) ── */
function cd_render_content_flow($exclude='') {
    $posts = get_posts(array('posts_per_page'=>4,'orderby'=>'rand','post_status'=>'publish'));
    if ($posts) {
        echo '<div style="background:var(--cd-white);border-radius:var(--cd-radius);padding:16px;margin-top:16px">';
        echo '<div class="cd-section-head"><h2>You May Also Like</h2></div>';
        echo '<div style="display:flex;gap:10px;overflow-x:auto;padding-bottom:4px">';
        foreach ($posts as $p) {
            $img = cd_get_post_image_url($p->ID,'cd-card');
            echo '<a href="'.esc_url(get_permalink($p->ID)).'" style="background:var(--cd-gray-light);border-radius:var(--cd-radius);min-width:190px;max-width:200px;flex-shrink:0;overflow:hidden;text-decoration:none;display:block">';
            if ($img) echo '<img src="'.esc_url($img).'" alt="'.esc_attr(get_the_title($p->ID)).'" style="width:100%;height:90px;object-fit:cover" loading="lazy">';
            echo '<div style="padding:8px"><div style="font-size:12px;font-weight:700;color:var(--cd-black);line-height:1.35">'.esc_html(get_the_title($p->ID)).'</div>';
            echo '<div style="font-size:10px;color:var(--cd-gray-muted);margin-top:3px">'.get_the_date('M j, Y',$p->ID).'</div></div></a>';
        }
        echo '</div></div>';
    }
    $fmts = array(
        'blitz-rating'    => array('Blitz Chess',    '/category/blitz-rating/'),
        'rapid-rating'    => array('Rapid Chess',    '/category/rapid-rating/'),
        'classical-rating'=> array('Classical',      '/category/classical-rating/'),
        'chess960'        => array('Chess960',       '/category/chess960/'),
    );
    unset($fmts[$exclude]);
    echo '<div style="background:var(--cd-white);border-radius:var(--cd-radius);padding:16px;margin-top:12px">';
    echo '<div class="cd-section-head"><h2>Explore Other Formats</h2></div>';
    echo '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">';
    $i = 0;
    foreach ($fmts as $sl => $f) {
        $bg = $i % 2 === 0 ? 'var(--cd-blue)' : 'var(--cd-black)';
        echo '<a href="'.esc_url(home_url($f[1])).'" style="display:block;text-align:center;background:'.$bg.';color:#fff;padding:12px 8px;border-radius:var(--cd-radius);font-size:12px;font-weight:700;text-decoration:none">'.esc_html($f[0]).'</a>';
        $i++;
    }
    echo '</div></div>';
}

/* =====================================================
   CHECKMATE DAILY TV: MEDIA VIDEO ENGINE
===================================================== */
function cd_register_media_video_hub() {
    register_post_type('cd_video', array(
        'labels'             => array(
            'name'          => 'Videos', 'singular_name' => 'Video',
            'menu_name'     => 'Videos', 'add_new'       => 'Add New Video',
            'add_new_item'  => 'Add New Video', 'edit_item' => 'Edit Video',
            'view_item'     => 'View Video', 'search_items' => 'Search Videos',
            'not_found'     => 'No videos found',
        ),
        'public'             => true,
        'has_archive'        => 'videos',
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-video-alt3',
        'supports'           => array('title','editor','thumbnail','excerpt'),
        'show_in_rest'       => true,
        'rewrite'            => array('slug'=>'video'),
    ));
    register_taxonomy('video_playlist', array('cd_video'), array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'          => 'Coverage Types', 'singular_name' => 'Coverage Type',
            'all_items'     => 'All Types', 'edit_item' => 'Edit Type',
            'add_new_item'  => 'Add New Type',
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug'=>'videos/coverage'),
    ));
}
add_action('init', 'cd_register_media_video_hub');

/* ── YOUTUBE URL META BOX ── */
function cd_add_youtube_meta_box() {
    add_meta_box('cd_youtube_url_box','YouTube Video Link','cd_youtube_url_box_html','cd_video','side','high');
}
add_action('add_meta_boxes','cd_add_youtube_meta_box');

function cd_youtube_url_box_html($post) {
    $value = get_post_meta($post->ID,'_cd_youtube_url',true);
    wp_nonce_field('cd_save_youtube_url','cd_youtube_nonce');
    ?>
    <label for="cd_youtube_url"><strong>Paste YouTube URL or Short Link:</strong></label><br>
    <input type="url" id="cd_youtube_url" name="cd_youtube_url" value="<?php echo esc_attr($value); ?>" style="width:100%;margin-top:5px;" placeholder="https://www.youtube.com/watch?v=...">
    <p style="font-size:11px;color:#666;">Paste the full video link or the YouTube Shorts link here. The thumbnail will load automatically.</p>
    <?php
}

function cd_save_youtube_meta_box($post_id) {
    if (!isset($_POST['cd_youtube_nonce']) || !wp_verify_nonce($_POST['cd_youtube_nonce'],'cd_save_youtube_url')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post',$post_id)) return;
    if (isset($_POST['cd_youtube_url'])) {
        $url = sanitize_url($_POST['cd_youtube_url']);
        update_post_meta($post_id,'_cd_youtube_url',$url);

        $video_id = cd_extract_youtube_id($url);
        if ($video_id) update_post_meta($post_id,'_cd_youtube_id',$video_id);

        if (cd_youtube_item_is_short($url, get_the_title($post_id), '')) {
            cd_mark_video_as_short($post_id);
        }
    }
}
add_action('save_post','cd_save_youtube_meta_box');

/* ── AUTO-SYNC YOUTUBE VIDEOS ── */
function cd_find_video_post_by_youtube_id($video_id) {
    $video_id = cd_extract_youtube_id($video_id);
    if (!$video_id) return 0;

    $matches = get_posts(array(
        'post_type'      => 'cd_video',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'OR',
            array('key'=>'_cd_youtube_id','value'=>$video_id),
            array('key'=>'_cd_youtube_url','value'=>$video_id,'compare'=>'LIKE'),
        ),
    ));

    return $matches ? (int) $matches[0] : 0;
}

function cd_get_video_playlist_term_id($slug, $name) {
    $term = term_exists($slug, 'video_playlist');
    if (!$term) {
        $term = wp_insert_term($name, 'video_playlist', array('slug'=>$slug));
    }
    if (is_wp_error($term) || !$term) return 0;

    return is_array($term) ? (int) $term['term_id'] : (int) $term;
}

function cd_mark_video_as_short($post_id) {
    $term_id = cd_get_video_playlist_term_id('shorts', 'Shorts');
    if ($term_id) {
        wp_set_object_terms($post_id, array($term_id), 'video_playlist', true);
    }
}

function cd_youtube_item_is_short($url, $title = '', $description = '', $short_ids = array()) {
    $video_id = cd_extract_youtube_id($url);
    if ($video_id && in_array($video_id, $short_ids, true)) return true;

    $haystack = strtolower((string) $url . ' ' . (string) $title . ' ' . (string) $description);
    if (false !== strpos($haystack, '/shorts/')) return true;
    if (false !== strpos($haystack, '#shorts')) return true;

    return (bool) preg_match('/(^|[^a-z0-9])shorts?([^a-z0-9]|$)/', $haystack);
}

function cd_fetch_youtube_shorts_ids($limit = 12) {
    $cached = get_transient('cd_youtube_shorts_ids');
    if (is_array($cached)) return array_slice($cached, 0, $limit);

    $response = wp_remote_get('https://www.youtube.com/@CheckmateDailyChess/shorts', array(
        'timeout'     => 8,
        'redirection' => 3,
        'headers'     => array(
            'User-Agent' => 'Mozilla/5.0 (compatible; CheckmateDaily/2.1; +https://checkmatedaily.com)',
        ),
    ));
    if (is_wp_error($response)) return array();

    $body = wp_remote_retrieve_body($response);
    if (!$body) return array();

    preg_match_all('/"videoId":"([A-Za-z0-9_-]{11})"/', $body, $matches);
    $ids = array_values(array_unique($matches[1] ?? array()));
    $ids = array_slice($ids, 0, $limit);

    set_transient('cd_youtube_shorts_ids', $ids, 30 * MINUTE_IN_SECONDS);
    return $ids;
}

function cd_fetch_youtube_title($url) {
    $response = wp_remote_get('https://www.youtube.com/oembed?url=' . rawurlencode($url) . '&format=json', array('timeout'=>5));
    if (is_wp_error($response)) return '';

    $data = json_decode(wp_remote_retrieve_body($response), true);
    return !empty($data['title']) ? sanitize_text_field($data['title']) : '';
}

function cd_upsert_youtube_video($video_id, $title, $url, $is_short = false, $published = '') {
    $video_id = cd_extract_youtube_id($video_id);
    if (!$video_id) return 0;

    $post_id = cd_find_video_post_by_youtube_id($video_id);
    $title   = sanitize_text_field($title ?: 'Checkmate Daily Video');
    $url     = esc_url_raw($url);

    if (!$post_id) {
        $post_data = array(
            'post_title'  => $title,
            'post_type'   => 'cd_video',
            'post_status' => 'publish',
        );
        if ($published) {
            $timestamp = strtotime($published);
            if ($timestamp) {
                $post_data['post_date']     = get_date_from_gmt(gmdate('Y-m-d H:i:s', $timestamp));
                $post_data['post_date_gmt'] = gmdate('Y-m-d H:i:s', $timestamp);
            }
        }
        $post_id = wp_insert_post($post_data);
    }

    if (!$post_id || is_wp_error($post_id)) return 0;

    update_post_meta($post_id, '_cd_youtube_id', $video_id);
    if (!get_post_meta($post_id, '_cd_youtube_url', true)) {
        update_post_meta($post_id, '_cd_youtube_url', $url);
    }

    if ($is_short) {
        cd_mark_video_as_short($post_id);
    }

    return (int) $post_id;
}

function cd_sync_youtube_videos() {
    $channel_id = 'UCgKguiN-6jfPvVrneHHswsw';
    $rss_url    = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel_id;
    $short_ids  = cd_fetch_youtube_shorts_ids(12);

    if (!function_exists('fetch_feed')) {
        include_once ABSPATH . WPINC . '/feed.php';
    }

    $rss        = fetch_feed($rss_url);
    if (!is_wp_error($rss)) {
        $rss_items = $rss->get_items(0, $rss->get_item_quantity(15));
        foreach ($rss_items as $item) {
            $video_id = str_replace('yt:video:','',$item->get_id());
            $url      = 'https://www.youtube.com/watch?v=' . $video_id;
            $title    = $item->get_title();
            $desc     = wp_strip_all_tags($item->get_description());
            $is_short = cd_youtube_item_is_short($url, $title, $desc, $short_ids);
            $date     = $item->get_date('Y-m-d H:i:s');

            cd_upsert_youtube_video($video_id, $title, $url, $is_short, $date);
        }
    }

    foreach (array_slice($short_ids, 0, 6) as $short_id) {
        $short_url = 'https://www.youtube.com/shorts/' . $short_id;
        $post_id   = cd_find_video_post_by_youtube_id($short_id);

        if ($post_id) {
            update_post_meta($post_id, '_cd_youtube_id', $short_id);
            cd_mark_video_as_short($post_id);
            continue;
        }

        $title = cd_fetch_youtube_title($short_url);
        cd_upsert_youtube_video($short_id, $title ?: 'Checkmate Daily Short', $short_url, true);
    }

    set_transient('cd_youtube_last_sync', time(), 30 * MINUTE_IN_SECONDS);
}
if (!wp_next_scheduled('cd_sync_youtube_cron')) wp_schedule_event(time(),'hourly','cd_sync_youtube_cron');
add_action('cd_sync_youtube_cron','cd_sync_youtube_videos');
if (isset($_GET['sync_yt']) && current_user_can('manage_options')) add_action('init','cd_sync_youtube_videos');

function cd_maybe_sync_youtube_on_visit() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;
    if (!(is_front_page() || is_home() || is_page_template('page-videos.php') || is_post_type_archive('cd_video'))) return;
    if (get_transient('cd_youtube_sync_lock')) return;

    set_transient('cd_youtube_sync_lock', 1, 30 * MINUTE_IN_SECONDS);
    cd_sync_youtube_videos();
}
add_action('template_redirect', 'cd_maybe_sync_youtube_on_visit');

/* ── HIDE SHORTS FROM MAIN VIDEO ARCHIVE ── */
function cd_exclude_shorts_from_archive($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('cd_video')) {
        $tax_query   = (array)$query->get('tax_query');
        $tax_query[] = array(
            'relation' => 'OR',
            array('taxonomy'=>'video_playlist','field'=>'slug','terms'=>'shorts','operator'=>'NOT IN'),
            array('taxonomy'=>'video_playlist','operator'=>'NOT EXISTS'),
        );
        $query->set('tax_query',$tax_query);
    }
}
add_action('pre_get_posts','cd_exclude_shorts_from_archive');

/* =====================================================
   READING PROGRESS BAR
===================================================== */
add_action('wp_footer', function() {
    if (!is_single()) return;
    ?>
    <div id="cd-reading-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div id="cd-reading-progress-fill"></div>
    </div>
    <script>
    (function(){
        var fill = document.getElementById('cd-reading-progress-fill');
        var wrap = document.getElementById('cd-reading-progress');
        if (!fill) return;
        var article = document.querySelector('.cd-single-content') || document.body;
        var ticking = false;
        function updateProgress() {
            var articleTop    = article.getBoundingClientRect().top + window.pageYOffset;
            var articleBottom = articleTop + article.offsetHeight;
            var viewportH     = window.innerHeight;
            var scrolled      = window.pageYOffset + viewportH - articleTop;
            var total         = articleBottom - articleTop;
            var pct = Math.min(100, Math.max(0, Math.round((scrolled / total) * 100)));
            fill.style.width = pct + '%';
            wrap.setAttribute('aria-valuenow', pct);
        }
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function(){ updateProgress(); ticking = false; });
                ticking = true;
            }
        }, { passive: true });
        updateProgress();
    })();
    </script>
    <?php
});

/* =====================================================
   INLINE "READ ALSO" INJECTION
===================================================== */
add_filter('the_content', 'cd_inject_read_also');
function cd_inject_read_also($content) {
    if (!is_single() || is_admin()) return $content;
    $paragraphs = explode('</p>', $content);
    $count      = count($paragraphs);
    if ($count > 4) {
        $middle  = floor($count / 2);
        $related = get_posts(array('posts_per_page'=>1,'post__not_in'=>array(get_the_ID()),'orderby'=>'rand'));
        if ($related) {
            $rel_post = $related[0];
            $inject   = '<div class="cd-read-also"><strong>&#127919; Read Also:</strong> <a href="' . get_permalink($rel_post->ID) . '">' . esc_html($rel_post->post_title) . '</a></div>';
            $paragraphs[$middle] .= '</p>' . $inject;
        }
    }
    return implode('</p>', $paragraphs);
}

/* =====================================================
   LICHESS PUZZLE OF THE DAY — Widget
===================================================== */

/**
 * Parse FEN string and render an 8x8 board as HTML.
 * Server-side rendering — board visible immediately, no JS dependency.
 */
function cd_fen_to_board_html( $fen ) {
    $pieces = array(
        'K'=>'&#9812;','Q'=>'&#9813;','R'=>'&#9814;','B'=>'&#9815;','N'=>'&#9816;','P'=>'&#9817;',
        'k'=>'&#9818;','q'=>'&#9819;','r'=>'&#9820;','b'=>'&#9821;','n'=>'&#9822;','p'=>'&#9823;',
    );
    $fen_rows = explode( '/', explode( ' ', $fen )[0] );
    $html = '<div class="cd-puzzle-grid">';
    for ( $r = 0; $r < 8; $r++ ) {
        $squares = array();
        foreach ( str_split( $fen_rows[ $r ] ) as $ch ) {
            if ( is_numeric( $ch ) ) {
                for ( $i = 0; $i < intval( $ch ); $i++ ) $squares[] = '';
            } else {
                $squares[] = $ch;
            }
        }
        for ( $f = 0; $f < 8; $f++ ) {
            $color  = ( ( $r + $f ) % 2 === 0 ) ? 'cd-sq-light' : 'cd-sq-dark';
            $piece  = isset( $squares[ $f ] ) ? $squares[ $f ] : '';
            $symbol = isset( $pieces[ $piece ] ) ? $pieces[ $piece ] : '';
            $html  .= '<div class="cd-puzzle-sq ' . $color . '" data-r="' . $r . '" data-f="' . $f . '">' . $symbol . '</div>';
        }
    }
    $html .= '</div>';
    return $html;
}

class CD_Lichess_Puzzle_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'cd_lichess_puzzle',
            'CD: Lichess Puzzle of the Day',
            array( 'description' => 'Shows today\'s Lichess puzzle with board in the sidebar.' )
        );
    }

    public function widget( $args, $instance ) {
        $puzzle = cd_get_lichess_daily_puzzle();
        if ( ! $puzzle ) return;

        $title     = ! empty( $instance['title'] ) ? esc_html( $instance['title'] ) : 'Puzzle of the Day';
        $puzzle_id = esc_attr( $puzzle['id'] );
        $fen       = $puzzle['fen'];
        $turn      = $puzzle['turn'] === 'w' ? 'White to move' : 'Black to move';
        $rating    = intval( $puzzle['rating'] );
        $themes    = array_slice( $puzzle['themes'], 0, 3 );
        $sol_json  = esc_attr( wp_json_encode( array_map( 'esc_attr', $puzzle['solution'] ) ) );
        $solve_url = esc_url( 'https://lichess.org/training/' . $puzzle_id );

        echo $args['before_widget'];
        ?>
        <div class="cd-sidebar-box cd-puzzle-box">
            <div class="cd-sidebar-head">
                <?php echo $title; ?>
                <span class="cd-puzzle-lichess">lichess.org</span>
            </div>

            <div class="cd-puzzle-board">
                <div class="cd-puzzle-board-inner" data-solution="<?php echo $sol_json; ?>">
                    <?php echo cd_fen_to_board_html( $fen ); ?>
                </div>
            </div>

            <div class="cd-puzzle-meta">
                <span class="cd-puzzle-turn"><?php echo esc_html( $turn ); ?></span>
                <span class="cd-puzzle-rating">Rating: <?php echo $rating; ?></span>
            </div>

            <?php if ( $themes ) : ?>
            <div class="cd-puzzle-themes">
                <?php foreach ( $themes as $theme ) : ?>
                    <span class="cd-puzzle-theme"><?php echo esc_html( $theme ); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="cd-puzzle-btns">
                <button class="cd-puzzle-reveal">Show solution</button>
                <a href="<?php echo $solve_url; ?>" target="_blank" rel="noopener" class="cd-puzzle-open">Solve &#8599;</a>
            </div>

            <div class="cd-puzzle-solution"></div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Puzzle of the Day';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance          = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        return $instance;
    }
}

function cd_register_lichess_widget() {
    register_widget( 'CD_Lichess_Puzzle_Widget' );
}
add_action( 'widgets_init', 'cd_register_lichess_widget' );


/**
 * Fetch today's puzzle from Lichess API and cache for 1 hour.
 */
function cd_get_lichess_daily_puzzle() {
    $cached = get_transient( 'cd_lichess_daily_puzzle' );
    if ( $cached ) return $cached;

    $response = wp_remote_get( 'https://lichess.org/api/puzzle/daily', array(
        'timeout' => 8,
        'headers' => array( 'Accept' => 'application/json' ),
    ) );

    if ( is_wp_error( $response ) ) return false;

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $data['puzzle'] ) ) return false;

    $puzzle = array(
        'id'       => sanitize_text_field( $data['puzzle']['id'] ),
        'fen'      => sanitize_text_field( $data['puzzle']['initialFen'] ),
        'turn'     => explode( ' ', $data['puzzle']['initialFen'] )[1] ?? 'w',
        'rating'   => intval( $data['puzzle']['rating'] ),
        'themes'   => array_map( 'sanitize_text_field', $data['puzzle']['themes'] ?? array() ),
        'solution' => array_map( 'sanitize_text_field', $data['puzzle']['solution'] ?? array() ),
    );

    set_transient( 'cd_lichess_daily_puzzle', $puzzle, HOUR_IN_SECONDS );
    return $puzzle;
}

/**
 * Inline JS for puzzle reveal/highlight — no external file needed.
 */
function cd_puzzle_inline_js() {
    if ( ! is_active_widget( false, false, 'cd_lichess_puzzle', true ) ) return;
    ?>
    <script>
    (function(){
        var FILES = 'abcdefgh';
        function uciToIndices(uci) {
            return {
                fromFile: FILES.indexOf(uci[0]), fromRank: 8 - parseInt(uci[1]),
                toFile:   FILES.indexOf(uci[2]), toRank:   8 - parseInt(uci[3])
            };
        }
        function uciToLabel(uci) { return uci.slice(0,2) + '\u2192' + uci.slice(2,4); }
        document.addEventListener('DOMContentLoaded', function() {
            var boxes = document.querySelectorAll('.cd-puzzle-box');
            boxes.forEach(function(box) {
                var inner  = box.querySelector('.cd-puzzle-board-inner');
                var btn    = box.querySelector('.cd-puzzle-reveal');
                var solBox = box.querySelector('.cd-puzzle-solution');
                if (!inner || !btn || !solBox) return;
                var solution = [];
                try { solution = JSON.parse(inner.dataset.solution || '[]'); } catch(e) {}
                var shown = false;
                btn.addEventListener('click', function() {
                    shown = !shown;
                    var squares = inner.querySelectorAll('.cd-puzzle-sq');
                    squares.forEach(function(sq) { sq.classList.remove('cd-sq-hl'); });
                    if (shown) {
                        var first = solution[0];
                        if (first && first.length >= 4) {
                            var idx = uciToIndices(first);
                            squares.forEach(function(sq) {
                                var r = parseInt(sq.dataset.r), f = parseInt(sq.dataset.f);
                                if ((r === idx.fromRank && f === idx.fromFile) ||
                                    (r === idx.toRank   && f === idx.toFile)) {
                                    sq.classList.add('cd-sq-hl');
                                }
                            });
                        }
                        solBox.innerHTML = '<strong>Solution:</strong> <span class="cd-sol-moves">'
                            + solution.map(uciToLabel).join('&nbsp;&nbsp;') + '</span>';
                        solBox.style.display = 'block';
                        btn.textContent = 'Hide solution';
                    } else {
                        solBox.style.display = 'none';
                        solBox.innerHTML = '';
                        btn.textContent = 'Show solution';
                    }
                });
            });
        });
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'cd_puzzle_inline_js' );
