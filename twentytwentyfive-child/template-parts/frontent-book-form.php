<?php
/**
 * Template Name: Frontend Book Form
 */

if (!current_user_can('editor') && !current_user_can('administrator')) {
    echo '<p>You do not have permission to add books.</p>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book_nonce']) && wp_verify_nonce($_POST['add_book_nonce'], 'add_book_action')) {
    
    $title = sanitize_text_field($_POST['title']);
    $genre = isset($_POST['genre']) ? (int) $_POST['genre'] : 0;
    $publisher = isset($_POST['publisher']) ? (int) $_POST['publisher'] : 0;
    $author = sanitize_text_field($_POST['author']);
    $price = sanitize_text_field($_POST['price']);
    $release_date = sanitize_text_field($_POST['release_date']);

    $post_data = array(
        'post_title'   => $title,
        'post_type'    => 'book', 
        'post_status'  => 'publish',
        'meta_input'   => array(
            'author'      => $author,
            'price'       => $price,
            'release_date' => $release_date,
        ),
    );

    $post_id = wp_insert_post($post_data);

    if ($post_id) {
        if ($genre) {
            wp_set_post_terms($post_id, array($genre), 'genre');
        }

        if ($publisher) {
            wp_set_post_terms($post_id, array($publisher), 'publisher');
        }

        echo '<p>Book submitted successfully!</p>';
    } else {
        echo '<p>Error submitting the book.</p>';
    }
}
?>

<form method="POST" action="">
    <?php wp_nonce_field('add_book_action', 'add_book_nonce'); ?>

    <label for="title">Title:</label>
    <input type="text" name="title" id="title" required>

    <label for="genre">Genre:</label>
    <select name="genre" id="genre">
        <option value="">Select Genre</option>
        <?php
        
        $genres = get_terms(array(
            'taxonomy' => 'genre',
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false,
        ));
        foreach ($genres as $genre) {
            echo '<option value="' . $genre->term_id . '">' . $genre->name . '</option>';
        }
        ?>
    </select>

    <label for="publisher">Publisher:</label>
    <select name="publisher" id="publisher">
        <option value="">Select Publisher</option>
        <?php
        
        $publishers = get_terms(array(
            'taxonomy' => 'publisher',
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false,
        ));
        foreach ($publishers as $publisher) {
            echo '<option value="' . $publisher->term_id . '">' . $publisher->name . '</option>';
        }
        ?>
    </select>

    <label for="author">Author:</label>
    <input type="text" name="author" id="author" required>

    <label for="price">Price:</label>
    <input type="number" name="price" id="price" required>

    <label for="release_date">Release Date:</label>
    <input type="date" name="release_date" id="release_date" required>

    <button type="submit">Add Book</button>
</form>
