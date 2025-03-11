<?php
function twentytwentyfive_enqueue_child_styles() {
	wp_enqueue_style( 'twentytwentyfive-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_child_styles' );


function enqueue_ajax_scripts() {
    wp_enqueue_script('books-ajax', get_stylesheet_directory_uri() . '/assets/js/books-ajax.js', array('jquery'), null, true);
    
    wp_localize_script('books-ajax', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('books_filter_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_scripts');

require_once get_stylesheet_directory() . '/includes/rest-books-api.php';

function create_books_post_type() {
    $labels = array(
        'name'               => 'Books',
        'singular_name'      => 'Book',
        'menu_name'          => 'Books',
        'name_admin_bar'     => 'Book',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Book',
        'new_item'           => 'New Book',
        'edit_item'          => 'Edit Book',
        'view_item'          => 'View Book',
        'all_items'          => 'All Books',
        'search_items'       => 'Search Books',
        'parent_item_colon'  => 'Parent Books:',
        'not_found'          => 'No books found.',
        'not_found_in_trash' => 'No books found in Trash.',
        'rewrite'            => array( 'slug' => 'book' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'rewrite'            => array( 'slug' => 'book' ),
        'taxonomies'         => array( 'genre', 'publisher' ),
    );

    register_post_type( 'book', $args );
}
add_action( 'init', 'create_books_post_type' );

function create_genre_taxonomy() {
    $labels = array(
        'name'              => 'Genres',
        'singular_name'     => 'Genre',
        'search_items'      => 'Search Genres',
        'all_items'         => 'All Genres',
        'parent_item'       => 'Parent Genre',
        'parent_item_colon' => 'Parent Genre:',
        'edit_item'         => 'Edit Genre',
        'update_item'       => 'Update Genre',
        'add_new_item'      => 'Add New Genre',
        'new_item_name'     => 'New Genre Name',
        'menu_name'         => 'Genre',
        'rewrite'           => array( 'slug' => 'genre' ),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_in_rest'      => true,
    );

    register_taxonomy( 'genre', 'book', $args );
}
add_action( 'init', 'create_genre_taxonomy' );

function create_publisher_taxonomy() {
    $labels = array(
        'name'              => 'Publishers',
        'singular_name'     => 'Publisher',
        'search_items'      => 'Search Publishers',
        'all_items'         => 'All Publishers',
        'parent_item'       => 'Parent Publisher',
        'parent_item_colon' => 'Parent Publisher:',
        'edit_item'         => 'Edit Publisher',
        'update_item'       => 'Update Publisher',
        'add_new_item'      => 'Add New Publisher',
        'new_item_name'     => 'New Publisher Name',
        'menu_name'         => 'Publisher',
        'rewrite'           => array( 'slug' => 'publisher' ),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => false,
        'show_in_rest'      => true,
    );

    register_taxonomy( 'publisher', 'book', $args );
}
add_action( 'init', 'create_publisher_taxonomy');


function display_books_query($genre = '', $publisher = '') {

    $args = array(
        'post_type'      => 'book', 
        'posts_per_page' => '-1',  
        'orderby'        => 'release_date',  
        'order'          => 'ASC',
    );

    if ($genre) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',  
            'field'    => 'slug',
            'terms'    => $genre,
            'operator' => 'IN',
        );
    }

    if ($publisher) {
        $args['tax_query'][] = array(
            'taxonomy' => 'publisher',  
            'field'    => 'slug',
            'terms'    => $publisher,
            'operator' => 'IN',
        );
    }

    $books_query = new WP_Query($args);

    return $books_query;
}

function register_books_rest_api() {
    register_rest_route('books/v1', '/list/', array(
        'methods'  => 'GET',
        'callback' => 'get_books_list',
        'permission_callback' => '__return_true',
        'args' => array(
            'page' => array(
                'required' => false,
                'default'  => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'required' => false,
                'default'  => 10,
                'sanitize_callback' => 'absint',
            ),
            'genre' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'publisher' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
}
add_action('rest_api_init', 'register_books_rest_api');

function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );


function cf_search_where( $where ) {
    global $pagenow, $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );


function search_by_post_type($query) {
    if ($query->is_search && !is_admin()) {
        $query->set('post_type', 'book');
    }
    return $query;
}
add_filter('pre_get_posts', 'search_by_post_type');

