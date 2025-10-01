<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

/**
 * Global "Get a Quote" pop-up
 */
add_action('wp_footer', function() {
    ?>
    <div id="getQuote" class="zip-modal">
      <div class="zip-modal-content">
        <span class="zip-close">&times;</span>
        <h3>Get a Quote</h3>

		<?php echo do_shortcode('[contact-form-7 id="9b14a99" title="Get a Quote"]'); ?>
      </div>
    </div>
    <?php
});

add_action('wp_enqueue_scripts', function() {
    wp_add_inline_script('jquery-core', "
        jQuery(document).ready(function($){
		const modal = $('#getQuote');

            // Open popup
            $('.inquirenow').on('click', function(e){
                e.preventDefault();
                $('#getQuote').fadeIn();
            });

            // Close popup
            $('#getQuote .zip-close').on('click', function(){
                $('#getQuote').fadeOut();
            });

            // Click outside modal content closes it
            $('#getQuote').on('click', function(e){
                if($(e.target).is('#getQuote')) {
                    $('#getQuote').fadeOut();
                }
            });
			
			// CF7 success event
			document.addEventListener('wpcf7mailsent', function (event) {
				const form = event.target;
				const responseOutput = $(form).find('.wpcf7-response-output');

				// Fade out the success message after 2 seconds
				responseOutput.delay(2000).fadeOut(600);

				// Close modal after 1.5 seconds
				setTimeout(function () {
					modal.fadeOut(600); // smooth fade
				}, 1500);
			}, false);
        });
    ");
});


/**
 * Global "zip-code" pop-up
 */
add_action('wp_footer', function() {
    ?>
    <div id="zipModal" class="zip-modal">
      <div class="zip-modal-content">
        <span class="zip-close">&times;</span>
        <h3>Enter Your Zip Code for Local Pricing and Fastest Delivery</h3>
        
		<input id="zipInput" type="text" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" placeholder="Enter Zip Code" name="zip_code" required>
        
        <div class="zip-actions">
          <a href="/shop/" class="zip-later">Do it later</a>
          <button id="zip-pop-up-submit" class="zip-submit" disabled>Get an offer</button>
        </div>
      </div>
    </div>
	<script>
      document.addEventListener("DOMContentLoaded", function () {
        const zipInput = document.getElementById("zipInput");
        const btn = document.getElementById("zip-pop-up-submit");
        if (zipInput) {
          zipInput.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 5);
            btn.disabled = !(zipInput.value.length === 5);
          });
        }
      });
    </script>
    <?php
});

/**
 * Load custom shop page logic
 */
require_once get_stylesheet_directory() . '/inc/shop-logic.php';

/**
 * Single product page
 * 
 * Add custom delivery block
 */
add_action( 'woocommerce_before_add_to_cart_button', 'custom_delivery_block' );
function custom_delivery_block() {
    ?>
    <!-- Extra section after delivery options -->
    <div class="extra-info">
        <div class="volume-discount">
            <span>Volume discount</span>
            <span class="extra-amount">from 2pcs.</span>
        </div>
	<!-- Delivery options -->
	<?php echo do_shortcode('[delivery_date]'); ?>
    </div>
    <?php
}

/**
 * Add text swatch condition display
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_add_inline_script( 'jquery-core', "
        jQuery(function($) {
  			$('.wd-swatch.wd-enabled.wd-text').addClass('radio-option');
            $('.wd-swatch.wd-disabled').removeAttr('href');
        });
    " );
});


/**
 * Warranty block
 */
add_shortcode( 'single_product_warranty_block', function() {
    if ( ! is_product() ) return '';

    global $product;
    if ( ! $product ) return '';

    $condition = $product->get_attribute( 'condition' );

    if ( strtolower( $condition ) === 'new' ) {
        return do_shortcode('[elementor-template id="3158"]');
    }

    if ( strtolower( $condition ) === 'used' ) {
        return do_shortcode('[elementor-template id="3162"]');
    }
    return '';
});

// // Save to order
// add_action( 'woocommerce_add_order_item_meta', function( $item_id, $values ) {
//     if ( isset( $values['delivery_option'] ) ) {
//         wc_add_order_item_meta( $item_id, 'Delivery Option', $values['delivery_option'] );
//     }
// }, 10, 2 );

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'zip-check-logic',
        get_stylesheet_directory_uri() . '/js/single-product-zip.js',
        [],
        null,
        true
    );

    // For logged-in users, send the postcode to JS
    $postcode = '';
    if (is_user_logged_in()) {
        $postcode = get_user_meta(get_current_user_id(), 'billing_postcode', true);
    }
    wp_localize_script('zip-check-logic', 'zipCheckData', [
        'userPostcode' => $postcode,
    ]);
});

/**
 * Checkout page:
 * readonly shipping zip filed
 * additional info hover
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_add_inline_script( 'jquery-core', "
        jQuery(function($) {
            function addInfoIcons() {
                $('#shipping_postcode').prop('readonly', true);
                var tooltip = '<span class=\"info-icon\" data-tooltip=\"If you wish to change your location, go back to the product page\">i</span>';

                var shippingLabel = $('label[for=\"shipping_postcode\"]');
                if (shippingLabel.find('.info-icon').length === 0) {
                    shippingLabel.find('.required').before(tooltip + ' ');
                }
            }

            addInfoIcons();
            $(document.body).on('updated_checkout', addInfoIcons);
        });
    " );
});

//add_action('wp_head','test_data');
function test_data(){
    $price = get_price_by_zip('2072', '02048');

    echo 'rocket----02048---'.$price;
}

add_shortcode('tidio_open', function ($atts, $content = '') {
    $a = shortcode_atts([
        'text'  => 'Open Chat',
        'class' => 'elementor-button elementor-button-link elementor-size-md'
    ], $atts);

    // Button markup (inherits Elementor styles via classes)
    return '<a href="#" class="js-open-tidio '.esc_attr($a['class']).'">'.
           esc_html($content ?: $a['text']).'</a>';
});
