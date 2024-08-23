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
    remove_action('form_on_page_template', 'mt_output_form', 10);
}
add_action('init', 'mt_init');

add_action('form_on_page_template', 'mp_output_form', 10);

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

?>