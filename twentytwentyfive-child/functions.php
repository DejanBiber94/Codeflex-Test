<?php
function twentytwentyfive_enqueue_child_styles() {
	wp_enqueue_style( 'twentytwentyfive-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_child_styles' );
