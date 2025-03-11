<?php
/**
 * Template Name: Books List
 */
get_header();

$genre_filter = isset($_GET['genre']) ? sanitize_text_field($_GET['genre']) : '';
$publisher_filter = isset($_GET['publisher']) ? sanitize_text_field($_GET['publisher']) : '';

$books_query = display_books_query($genre_filter, $publisher_filter);

?>

<form method="get" action="">
    <label for="genre">Genre:</label>
    <select name="genre" id="genre">
        <option value="">All Genres</option>
        <?php
        $genres = get_terms(array(
            'taxonomy' => 'genre',
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false,
        ));
        foreach ($genres as $genre) {
            echo '<option value="' . $genre->slug . '" ' . selected($genre->slug, $genre_filter, false) . '>' . $genre->name . '</option>';
        }
        ?>
    </select>

    <label for="publisher">Publisher:</label>
    <select name="publisher" id="publisher">
        <option value="">All Publishers</option>
        <?php
        $publishers = get_terms(array(
            'taxonomy' => 'publisher',
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false,
        ));
        foreach ($publishers as $publisher) {
            echo '<option value="' . $publisher->slug . '" ' . selected($publisher->slug, $publisher_filter, false) . '>' . $publisher->name . '</option>';
        }
        ?>
    </select>

    <button type="submit">Filter</button>
</form>

<div id="books-list">
<?php
if ($books_query->have_posts()) :
    echo '<ul class="books-list">';
    while ($books_query->have_posts()) :
        $books_query->the_post();
        ?>
        <li>
            <h2><?php the_title(); ?></h2>
            <p>Author: <?php the_field('author'); ?></p> 
            <p>Release Date: <?php the_field('release_date'); ?></p> 
            <p>Price: <?php the_field('price'); ?></p> 
        </li>
        <?php
    endwhile;
    echo '</ul>';
    wp_reset_postdata(); 
else :
    echo '<p>No books found.</p>';
endif;
?>
</div>
<?php get_footer(); ?>
