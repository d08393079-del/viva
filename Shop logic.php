<?php
/**
 * Remove default WooCommerce "price" and "add to cart" button on shop/archive pages
 */
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

/**
 * Custom product footer
 */
// Enqueue the JavaScript file for handling zip code logic.
add_action('wp_enqueue_scripts', 'enqueue_zip_code_logic_script');
function enqueue_zip_code_logic_script() {
        wp_enqueue_script('zip-code-logic', get_stylesheet_directory_uri() . '/js/zip-code-logic.js', ['jquery'], null, true);
        wp_localize_script('zip-code-logic', 'zipCodeLogic', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('zip_code_nonce'),
            'is_logged_in' => is_user_logged_in(),
            'current_url' => home_url(add_query_arg([], $GLOBALS['wp']->request))
        ]);
}

// Main function to display product information or a placeholder.
add_action('woocommerce_after_shop_loop_item', 'custom_product_display_logic', 20);
function custom_product_display_logic() {
    global $product;

    $product_id = $product->get_id();
    $zip_code_found = false;
    $zip_code = '';
    
    if (!$zip_code_found && isset($_COOKIE['guest_zip_code'])) {
        $zip_code_found = true;
        $zip_code = $_COOKIE['guest_zip_code'];
    }

    $price_html = $product->get_price_html($zip_code);
    if(!empty($zip_code) && function_exists('calculate_shipping_zip_shop_loop')){
         $nearest_store_zip = calculate_shipping_zip_shop_loop($zip_code,'custom');
         //echo 'nerzip'.$nearest_store_zip;
         $price = get_price_by_zip($product_id,$nearest_store_zip);
         //echo 'rocket-----'.$zip_code.'----'.$nearest_store_zip.'----'.$product_id;
         $price_html = wc_price($price);
        // echo 'rocket'.rand(0,10).'--test---';
        // print_r($nearest_store_zip);
    }
   

    
    
   
    $link = get_permalink($product->get_id());
    $from_date = date_i18n('M j', strtotime('+2 weekdays'));
    $to_date = date_i18n('M j', strtotime('+5 weekdays'));

    echo '<div class="custom-product-footer" data-product-id="' . esc_attr($product->get_id()) . '">';

    if ($zip_code_found) {
        echo '<div class="custom-product-price '.$zip_code.'">' . $price_html . '</div>';
        echo '<div class="custom-product-delivery">Delivery between: ' . $from_date . ' - ' . $to_date . '</div>';
        echo '<a href="' . esc_url($link) . '" class="button view-product-button">Buy online</a>';
    } else {
        // Otherwise, display a placeholder that JavaScript will target.
        echo '<div class="zip-code-placeholder" data-product-id="' . esc_attr($product->get_id()) . '">';
		echo '<div><a href="" class="custom-product-price get-quote-link">Enter zip code to see price</a></div>';
		echo '<div class="custom-product-delivery">Delivery between: ' . $from_date . ' - ' . $to_date . '</div>';
        echo '<button class="button get-quote-button">Get quote</button>';
        echo '</div>';
    }
    echo '</div>';
}

// AJAX handler to receive the zip code and update user data.
add_action('wp_ajax_update_zip_code', 'handle_update_zip_code');
add_action('wp_ajax_nopriv_update_zip_code', 'handle_update_zip_code');
function handle_update_zip_code() {
    check_ajax_referer('zip_code_nonce', 'nonce');

	$url = esc_url_raw($_POST['url']);
    $zip_code = sanitize_text_field($_POST['zip_code']);
    
    if (empty($zip_code)) {
        wp_send_json_error(['success' => false, 'message' => 'Zip code is required.']);
        wp_die();
    }
    
	setcookie('guest_zip_code', $zip_code, time() + (86400 * 30), '/'); // 30-day cookie

    wp_send_json_success([
        'message' => 'Zip code updated successfully.',
        'purged'  => true
    ]);
    wp_die();
}