<?php
/**
 * Plugin Name: My Plugin
 */

function mp_test($message_count, $messages) {
    ?>
        <div>Det finns <?php echo $message_count ?> meddelanden</div>
    <?php
}

add_action('before_form_on_page_template', 'mp_test', 20, 2);

add_action('before_form_on_page_template', function() {
    ?>
        <div>Hej och välkommen</div>
    <?php
}, 10);

function mt_init() {
    //remove_action('form_on_page_template', 'mt_output_form', 10);
}
add_action('init', 'mt_init');

//add_action('form_on_page_template', 'mp_output_form', 10);

function mp_output_form() {
    ?>
       <div>Vi kan inte ta emot meddealnden just nu.</div>
    <?php
}

function mp_reverse($messages, $post_id) {
    var_dump($messages);
    var_dump($post_id);

    $messages[] = 'Medelanden för '.$post_id;

    return array_reverse($messages);
}

add_filter('secret_messages', 'mp_reverse', 10, 2);

function mp_add_thank_you($messages, $post_id, $original_messages) {

    $messages[] = "Thank you for reading";

    return $messages;
}

add_filter('secret_messages', 'mp_add_thank_you', 20, 3);

function mp_reset($messages, $post_id, $original_messages) {
    return $original_messages;
}

add_filter('secret_messages', 'mp_reset', 30, 3);

add_filter('the_content', function($content) {
    if(is_user_logged_in()) {
        return $content;
    }

    return null;
});

function mt_register_collections_post_type() {

    $args = array(
        'label'               => 'Collections',
        'public'              => true,
        'supports'            => array( 'title', 'editor', 'thumbnail' ),
        'has_archive'         => true,
        'show_in_rest' => true
    );

    register_post_type( 'collection', $args );

}
add_action( 'init', 'mt_register_collections_post_type' );

function mp_create_occasion_taxonomy() {
    register_taxonomy(
        'occasion',  // Taxonomy name
        'collection',  // Post type to attach to
        array(
            'label' => 'Occasions',
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite' => array( 'slug' => 'occasion' ),
            'hierarchical' => true,  // True for category-like behavior, false for tag-like
            'show_in_rest' => true
        )
    );
}

add_action( 'init', 'mp_create_occasion_taxonomy' );

function mp_get_latest_collections() {

    $args = array(
        'post_type' => 'collection',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => 3
    );

    $args = apply_filters('mp_get_latest_collections_query_args', $args);

    $query = new WP_Query($args);
    return $query;
}

add_filter( 'mp_get_latest_collections', 'mp_get_latest_collections' );

function mp_latest_collections($atts) {

        $return_html = '<h2>Senaste kollektioner</h2>';

        if(isset($atts['numberofposts'])) {
            global $number_of_latest_posts;
            $number_of_latest_posts = $atts['numberofposts'];

            add_filter( 'mp_get_latest_collections_query_args', function($args) {
                global $number_of_latest_posts;
                $args['posts_per_page'] = $number_of_latest_posts;
    
                return $args;
             } );
        }
         
        $query = apply_filters("mp_get_latest_collections", null);

        if($query) {

            ob_start();

            while($query->have_posts()) {
                $query->the_post();

                ?><a href="<?php echo(get_permalink(get_the_ID())); ?>">
                <?php
                the_title();
                ?>
                </a>
                <?php
            }

            $return_html .= ob_get_clean();

            wp_reset_postdata();
        }

        return $return_html;
}

add_shortcode( 'latestCollections', 'mp_latest_collections' );

add_filter( 'wp_is_application_passwords_available', '__return_true' );

?>