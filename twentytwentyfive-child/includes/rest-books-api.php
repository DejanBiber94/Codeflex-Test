<?php
function get_books_list( $request ) {
    $params = $request->get_params();
    
    $page = isset($params['page']) ? absint($params['page']) : 1;
    $per_page = isset($params['per_page']) ? absint($params['per_page']) : 10;
    
    $args = array(
        'post_type'      => 'book',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    );

    $tax_query = array();
    if (!empty($params['genre'])) {
        $tax_query[] = array(
            'taxonomy' => 'genre',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($params['genre']),
        );
    }
    if (!empty($params['publisher'])) {
        $tax_query[] = array(
            'taxonomy' => 'publisher',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($params['publisher']),
        );
    }
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($args);

    $books = array();
    while ($query->have_posts()) {
        $query->the_post();
        $books[] = array(
            'id'          => get_the_ID(),
            'title'       => get_the_title(),
            'author'      => get_field('author'),
            'price'       => get_field('price'),
            'release_date'=> get_field('release_date'),
            'permalink'   => get_permalink(),
        );
    }

    wp_reset_postdata();

    return new WP_REST_Response(
        array(
            'books'      => $books,
            'total_pages'=> $query->max_num_pages,
            'current_page'=> $page
        ),
        200
    );
}

?>