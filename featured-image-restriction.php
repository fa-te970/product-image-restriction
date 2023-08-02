<?php
/*
Plugin Name: Featured Image Restriction
Description: Adds a checkbox to WooCommerce categories to hide product images from guests. If the user is not loged in, show thumbnail instead of product image
Version: 1.0
Author: Fatemeh
*/

//Product Cat Create page
add_action('product_cat_add_form_fields', 'wh_taxonomy_add_new_meta_field', 10);
add_action('product_cat_edit_form_fields', 'wh_taxonomy_edit_meta_field', 10, 1);

function wh_taxonomy_add_new_meta_field()
{
?>
    <div class="form-field">
        <label for="wh_hide_from_guests"><?php _e('Hide Product Images From Guests', 'wh'); ?></label>
        <input type="checkbox" name="wh_hide_from_guests" id="wh_hide_from_guests" value="Yes">
    </div>
<?php
}

//Product Cat Edit page
function wh_taxonomy_edit_meta_field($term)
{
    //Getting term ID
    $term_id = $term->term_id;

    // Retrieve the existing value(s) for this meta field.
    $wh_hide_from_guests = get_term_meta($term_id, 'wh_hide_from_guests', true);

?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="wh_hide_from_guests"><?php _e('hide from guests', 'wh'); ?></label></th>
        <td>
            <input type="checkbox" name="wh_hide_from_guests" id="wh_hide_from_guests" <?php checked($wh_hide_from_guests, 'Yes'); ?> value="Yes" ; ?>
    </tr>
<?php
}

// Save extra taxonomy fields callback function.
add_action('edited_product_cat', 'wh_save_taxonomy_custom_meta', 10, 1);
add_action('create_product_cat', 'wh_save_taxonomy_custom_meta', 10, 1);

function wh_save_taxonomy_custom_meta($term_id)
{
    $wh_hide_from_guests = filter_input(INPUT_POST, 'wh_hide_from_guests');
    update_term_meta($term_id, 'wh_hide_from_guests', !empty($wh_hide_from_guests) ? 'Yes' : 'No');
}

// Replace image with default thumbnail for guests
add_action('woocommerce_before_shop_loop_item_title', 'replace_image_with_default_thumbnail', 5);

function replace_image_with_default_thumbnail()
{
    if (!is_user_logged_in()) {
        global $product;

        $product_id = $product->get_id();
        $terms = get_the_terms($product_id, 'product_cat');

        // Check if there are categories assigned to the product
        if ($terms && !is_wp_error($terms)) {
            $hide_product_image = 'Yes';
            // Loop through each product category
            foreach ($terms as $term) {
                // Get the value of the 'wh_hide_from_guests' field for each category
                $wh_hide_from_guests = get_term_meta($term->term_id, 'wh_hide_from_guests', true);

                // Check if the 'wh_hide_from_guests' field is set to 'Yes' for any category
                if (!is_wp_error($wh_hide_from_guests) && $wh_hide_from_guests == $hide_product_image) {
                    $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);

                    // Hide the product thumbnail for the current product
                    echo '<style>.wc-block-components-product-image img[data-testid="product-image"] { display: none; }</style>';

                    // Output the video embed HTML after the thumbnail for the current product
                    echo "<img src='" . wp_get_attachment_url($thumb_id) . "'>";
                    break;
                }
            }
        } else {
            // Handle the case when there are no categories or an error occurred
            echo '<!-- No categories or error occurred -->';
        }
    }
}
