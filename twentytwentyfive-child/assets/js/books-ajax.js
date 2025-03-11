//todo complete functionality
jQuery(document).ready(function($) {
    $('#genre, #publisher').on('change', function() {
        var genre = $('#genre').val();
        var publisher = $('#publisher').val();
        
        $.ajax({
            url: ajax_object.ajax_url, 
            data: {
                action: 'filter_books',  
                genre: genre,            
                publisher: publisher,   
                nonce: ajax_object.nonce 
            },
            success: function(response) {
                $('#books-list').html(response); 
            }
        });
    });
});
