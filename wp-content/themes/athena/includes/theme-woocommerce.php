<?php
// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page' );
}
?>
<?php

global $woo_options;

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

// If WooCommerce is active, do all the things
if ( is_woocommerce_activated() )

// Load WooCommerce stylsheet
if ( ! is_admin() ) { add_action( 'get_header', 'woo_load_woocommerce_css', 20 ); }

if ( ! function_exists( 'woo_load_woocommerce_css' ) ) {
	function woo_load_woocommerce_css () {
		wp_register_style( 'woocommerce', get_template_directory_uri() . '/css/woocommerce.css' );
		wp_enqueue_style( 'woocommerce' );
	}
}

/*-----------------------------------------------------------------------------------*/
/* Products */
/*-----------------------------------------------------------------------------------*/

// Number of products per page
add_filter('loop_shop_per_page', 'wooframework_products_per_page');
if (!function_exists('wooframework_products_per_page')) {
	function wooframework_products_per_page() {
		global $woo_options;
		if ( isset( $woo_options['woocommerce_products_per_page'] ) ) {
			return $woo_options['woocommerce_products_per_page'];
		}
	}
}

// Display product tabs?
add_action('wp_head','wooframework_tab_check');
if ( ! function_exists( 'wooframework_tab_check' ) ) {
	function wooframework_tab_check() {
		global $woo_options;
		if ( isset( $woo_options[ 'woocommerce_product_tabs' ] ) && $woo_options[ 'woocommerce_product_tabs' ] == "false" ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
		}
	}
}

// Display related products
add_action('wp_head','wooframework_related_products');
if ( ! function_exists( 'wooframework_related_products' ) ) {
	function wooframework_related_products() {
		global $woo_options;
		if ( isset( $woo_options[ 'woocommerce_related_products' ] ) && $woo_options[ 'woocommerce_related_products' ] == "false" ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		}
	}
}

/*-----------------------------------------------------------------------------------*/
/* Layout */
/*-----------------------------------------------------------------------------------*/

// Shop archives full width?

// Only display sidebar on product archives if instructed to do so via woocommerce_archives_fullwidth
if (!function_exists('woocommerce_get_sidebar')) {
	function woocommerce_get_sidebar() {
		global $woo_options;

		if (!is_woocommerce()) {
			get_sidebar();
		} elseif ( isset( $woo_options[ 'woocommerce_archives_fullwidth' ] ) && $woo_options[ 'woocommerce_archives_fullwidth' ] == "true" && (is_woocommerce()) || (is_product()) ) {
			get_sidebar();
		} elseif ( isset( $woo_options[ 'woocommerce_archives_fullwidth' ] ) && $woo_options[ 'woocommerce_archives_fullwidth' ] == "false" && (is_archive(array('product'))) ) {
			// no sidebar
		}
	}
}

// Add a class to the body if full width shop archives are specified
add_filter( 'body_class','wooframework_layout_body_class', 10 );		// Add layout to body_class output
if ( ! function_exists( 'wooframework_layout_body_class' ) ) {
	function wooframework_layout_body_class( $wc_classes ) {

		global $woo_options;

		$layout = '';

		// Add woocommerce-fullwidth class if full width option is enabled
		if ( isset( $woo_options[ 'woocommerce_archives_fullwidth' ] ) && $woo_options[ 'woocommerce_archives_fullwidth' ] == "false" && (is_shop() || is_product_category())) {
			$layout = 'layout-full';
		}

		// Add classes to body_class() output
		$wc_classes[] = $layout;
		return $wc_classes;

	} // End woocommerce_layout_body_class()
}

/*-----------------------------------------------------------------------------------*/
/* Product Archives */
/*-----------------------------------------------------------------------------------*/
// 3 products per row
add_filter('loop_shop_columns', 'loop_columns');
if (!function_exists('loop_columns')) {
	function loop_columns() {
		return 3;
	}
}

/*-----------------------------------------------------------------------------------*/
/* Hook in on activation */
/*-----------------------------------------------------------------------------------*/

global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) add_action('init', 'woo_install_theme', 1);

/*-----------------------------------------------------------------------------------*/
/* Install */
/*-----------------------------------------------------------------------------------*/

function woo_install_theme() {

update_option( 'woocommerce_thumbnail_image_width', '400' );
update_option( 'woocommerce_thumbnail_image_height', '400' );
update_option( 'woocommerce_single_image_width', '720' );
update_option( 'woocommerce_single_image_height', '720' );
update_option( 'woocommerce_catalog_image_width', '350' );
update_option( 'woocommerce_catalog_image_height', '350' );

}

/*-----------------------------------------------------------------------------------*/
/* Disable WooCommerce Styles */
/*-----------------------------------------------------------------------------------*/
define('WOOCOMMERCE_USE_CSS', false);

/*-----------------------------------------------------------------------------------*/
/* Layout Tweaks */
/*-----------------------------------------------------------------------------------*/

// Remove the WooCommerce sidebar
remove_action('woocommerce_sidebar','woocommerce_get_sidebar');

// Hook the sidebar into the Woo Layout
add_action('pixelpress_after_wc_content','woocommerce_get_sidebar');

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 10 );

// Remove breadcrumb (we're using the WooFramework default breadcrumb)
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);

// Remove pagination (we're using the WooFramework default pagination)
// < 2.0
remove_action( 'woocommerce_pagination', 'woocommerce_pagination', 10 );
add_action( 'woocommerce_pagination', 'woocommerceframework_pagination', 10 );
// 2.0 +
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
add_action( 'woocommerce_after_shop_loop', 'woocommerceframework_pagination', 10 );

if (!function_exists('woocommerceframework_pagination')) {
	function woocommerceframework_pagination() {
		if ( is_search() && is_post_type_archive() ) {
			add_filter( 'woo_pagination_args', 'woocommerceframework_add_search_fragment', 10 );
		}
		woo_pagenav();
	}
}

if (!function_exists('woocommerceframework_add_search_fragment')) {
	function woocommerceframework_add_search_fragment ( $settings ) {
		$settings['add_fragment'] = '&post_type=product';
		return $settings;
	}
}

/*-----------------------------------------------------------------------------------*/
/* Print the mini cart  */
/*-----------------------------------------------------------------------------------*/
add_action( 'woo_nav_before', 'athena_print_minicart' );
if (!function_exists('athena_print_minicart')) {
	function athena_print_minicart ( ) {

		global $woocommerce;

		?>
		<ul class="mini-cart">
			<li>
				<a href="#" class="cart-parent">
					<span>
					<?php
					echo '<mark>' . $woocommerce->cart->get_cart_total() . '</mark>';
					_e('Cart', 'woothemes');
					?>
					</span>
				</a>
				<?php

		        echo '<ul class="cart_list">';
		        echo '<li class="cart-title"><h3>'.__('Your Cart Contents', 'woothemes').'</h3></li>';
		           if (sizeof($woocommerce->cart->cart_contents)>0) : foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item) :
			           $_product = $cart_item['data'];
			           if ($_product->exists() && $cart_item['quantity']>0) :
			               echo '<li class="cart_list_product"><a href="'.get_permalink($cart_item['product_id']).'">';

			               echo $_product->get_image();

			               echo apply_filters('woocommerce_cart_widget_product_title', $_product->get_title(), $_product).'</a>';

			               if($_product instanceof woocommerce_product_variation && is_array($cart_item['variation'])) :
			                   echo woocommerce_get_formatted_variation( $cart_item['variation'] );
			                 endif;

			               echo '<span class="quantity">' .$cart_item['quantity'].' &times; '.woocommerce_price($_product->get_price()).'</span></li>';
			           endif;
			       endforeach;

		        	else: echo '<li class="empty">'.__('No products in the cart.','woothemes').'</li>'; endif;
		        	if (sizeof($woocommerce->cart->cart_contents)>0) :
		            echo '<li class="total"><strong>';

		            if (get_option('js_prices_include_tax')=='yes') :
		                _e('Total', 'woothemes');
		            else :
		                _e('Subtotal', 'woothemes');
		            endif;



		            echo ':</strong>'.$woocommerce->cart->get_cart_total().'</li>';

		            echo '<li class="buttons"><a href="'.$woocommerce->cart->get_cart_url().'" class="button">'.__('View Cart &rarr;','woothemes').'</a> <a href="'.$woocommerce->cart->get_checkout_url().'" class="button checkout">'.__('Checkout &rarr;','woothemes').'</a></li>';
		        endif;

		        echo '</ul>';

		    ?>
			</li>
		</ul>
		<?php

	}
}

/*-----------------------------------------------------------------------------------*/
/* The mini cart fragment */
/*-----------------------------------------------------------------------------------*/
add_action( 'add_to_cart_fragments', 'woocommerceframework_header_add_to_cart_fragment' );
if (!function_exists('woocommerceframework_header_add_to_cart_fragment')) {
	function woocommerceframework_header_add_to_cart_fragment( $fragments ) {

		global $woocommerce;

		ob_start();

		?>
		<ul class="mini-cart">
			<li>
				<a href="#" class="cart-parent">
					<span>
					<?php
					echo '<mark>' . $woocommerce->cart->get_cart_total() . '</mark>';
					_e('Cart', 'woothemes');
					?>
					</span>
				</a>
				<?php

		        echo '<ul class="cart_list">';
		        echo '<li class="cart-title"><h3>'.__('Your Cart Contents', 'woothemes').'</h3></li>';
		           if (sizeof($woocommerce->cart->cart_contents)>0) : foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item) :
			           $_product = $cart_item['data'];
			           if ($_product->exists() && $cart_item['quantity']>0) :
			               echo '<li class="cart_list_product"><a href="'.get_permalink($cart_item['product_id']).'">';

			               echo $_product->get_image();

			               echo apply_filters('woocommerce_cart_widget_product_title', $_product->get_title(), $_product).'</a>';

			               if($_product instanceof woocommerce_product_variation && is_array($cart_item['variation'])) :
			                   echo woocommerce_get_formatted_variation( $cart_item['variation'] );
			                 endif;

			               echo '<span class="quantity">' .$cart_item['quantity'].' &times; '.woocommerce_price($_product->get_price()).'</span></li>';
			           endif;
			       endforeach;

		        	else: echo '<li class="empty">'.__('No products in the cart.','woothemes').'</li>'; endif;
		        	if (sizeof($woocommerce->cart->cart_contents)>0) :
		            echo '<li class="total"><strong>';

		            if (get_option('js_prices_include_tax')=='yes') :
		                _e('Total', 'woothemes');
		            else :
		                _e('Subtotal', 'woothemes');
		            endif;



		            echo ':</strong>'.$woocommerce->cart->get_cart_total().'</li>';

		            echo '<li class="buttons"><a href="'.$woocommerce->cart->get_cart_url().'" class="button">'.__('View Cart &rarr;','woothemes').'</a> <a href="'.$woocommerce->cart->get_checkout_url().'" class="button checkout">'.__('Checkout &rarr;','woothemes').'</a></li>';
		        endif;

		        echo '</ul>';

		    ?>
			</li>
		</ul>
		<?php

		$fragments['ul.mini-cart'] = ob_get_clean();

		return $fragments;

	}
}

/*-----------------------------------------------------------------------------------*/
/* Single Product */
/*-----------------------------------------------------------------------------------*/
// Change thumbs on the single page to 4 per column
add_filter( 'woocommerce_product_thumbnails_columns', 'woocommerce_custom_product_thumbnails_columns' );

if (!function_exists('woocommerce_custom_product_thumbnails_columns')) {
	function woocommerce_custom_product_thumbnails_columns() {
		return 4;
	}
}

// Move the sale marker
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_sale_flash', 60 );

// Change the number of related products displayed
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 20);

if (!function_exists('woocommerce_output_related_products')) {
	function woocommerce_output_related_products() {
	    woocommerce_related_products(3,3); // 3 products, 3 columns
	}
}

// Change the number of upsells displayed
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product', 'woocommerce_output_upsells', 20);

if (!function_exists('woocommerce_output_upsells')) {
	function woocommerce_output_upsells() {
	    woocommerce_upsell_display(3,3); // Display 3 products in rows of 3
	}
}

// Wrapper around description
add_filter('woocommerce_short_description', 'athena_short_description_wrapper');
if (!function_exists('athena_short_description_wrapper')) {
	function athena_short_description_wrapper($excerpt) {
		return '<div class="description">'.$excerpt.'</div>';
	}
}

// Edit Product Meta
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
add_action( 'woocommerce_single_product_summary', 'athena_custom_single_product_meta', 40 );
if (!function_exists('athena_custom_single_product_meta')) {
	function athena_custom_single_product_meta() {

		global $post, $product;

?>

		<div class="product_meta">

			<?php if ( $product->is_type( array( 'simple', 'variable' ) ) && get_option('woocommerce_enable_sku') == 'yes' && $product->get_sku() ) : ?>
				<span itemprop="productID" class="sku"><?php _e('SKU:', 'woocommerce'); ?> <?php echo $product->get_sku(); ?>.</span>
			<?php endif; ?>

			<?php echo $product->get_categories( ', ', ' <span class="posted_in"><span>'.__('Category:', 'woocommerce').'</span> ', '</span>'); ?>

			<?php echo $product->get_tags( ', ', ' <span class="tagged_as"><span>'.__('Tags:', 'woocommerce').'</span> ', '</span>'); ?>

		</div>

<?php
	}

}

?>