<?php
/**
 * Plugin Name: My Plugin
 */

function mt_init() {
    
}
add_action('init', 'mt_init');

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

    register_taxonomy(
        'attribute',  // Taxonomy name
        'collection',  // Post type to attach to
        array(
            'label' => 'Attribut',
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite' => array( 'slug' => 'attribut' ),
            'hierarchical' => false,  // True for category-like behavior, false for tag-like
            'show_in_rest' => true
        )
    );

    register_taxonomy_for_object_type( 'product_cat', 'collection' );
    register_taxonomy_for_object_type( 'occasion', 'product' );
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

// Create a meta box in the Gutenberg editor
function wp_add_custom_meta_box() {
    add_meta_box(
        'wp_product_selector',           // ID of the meta box
        'Select WooCommerce Products',   // Title of the meta box
        'wp_product_selector_callback',  // Callback function that renders the meta box
        'page',                          // Post type where the meta box will appear
        'side',                          // Context: 'normal', 'side', or 'advanced'
        'high'                           // Priority: 'low', 'default', 'high'
    );
}
add_action( 'add_meta_boxes', 'wp_add_custom_meta_box' );

// Callback function to render the product selector dropdown
function wp_product_selector_callback( $post ) {
    // Fetch the saved product IDs from the post meta
    $selected_products = get_post_meta( $post->ID, '_selected_products', true );
    $selected_products = is_array( $selected_products ) ? $selected_products : array();

    // Fetch WooCommerce products (you can change the arguments as needed)
    $products = wc_get_products( array(
        'limit' => -1, // Fetch all products
        'orderby' => 'name',
        'order' => 'ASC',
    ) );

    // Create a dropdown with WooCommerce products
    echo '<select name="selected_products[]" multiple style="width: 100%;">';
    foreach ( $products as $product ) {
        $selected = in_array( $product->get_id(), $selected_products ) ? 'selected' : '';
        echo '<option value="' . esc_attr( $product->get_id() ) . '" ' . $selected . '>' . esc_html( $product->get_name() ) . '</option>';
    }
    echo '</select>';
}

// Save the selected products when the post is saved
function wp_save_selected_products( $post_id ) {
    // Check if the current user has permission to edit the post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check if the product selection exists in the $_POST data
    if ( isset( $_POST['selected_products'] ) && is_array( $_POST['selected_products'] ) ) {
        // Sanitize and save the selected products as post meta
        $selected_products = array_map( 'intval', $_POST['selected_products'] );
        update_post_meta( $post_id, '_selected_products', $selected_products );
    } else {
        // If no products were selected, delete the meta
        delete_post_meta( $post_id, '_selected_products' );
    }
}
add_action( 'save_post', 'wp_save_selected_products' );

/*
function my_mutable_function(&$array) {
    $array[2] = "test";
}

function my_immutable_function($array) {
    $array[2] = "test";

    return $array;
}

$test_array = array('A', 'B', 'C'); 
my_immutable_function($test_array);
var_dump($test_array);
*/

?>