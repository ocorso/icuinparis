<?php
function wc_dynamic_gallery_show() {
	WC_Gallery_Display_Class::wc_dynamic_gallery_display();
}

function wc_dynamic_gallery_install(){
	update_option('a3rev_woo_dgallery_version', '1.1.8');
	WC_Dynamic_Gallery::wc_dynamic_gallery_set_setting(true, true);
	
	update_option('a3rev_woo_dgallery_just_installed', true);
}

/**
 * Load languages file
 */
function wc_dynamic_gallery_init() {
	if ( get_option('a3rev_woo_dgallery_just_installed') ) {
		delete_option('a3rev_woo_dgallery_just_installed');
		wp_redirect( ( ( is_ssl() || force_ssl_admin() || force_ssl_login() ) ? str_replace( 'http:', 'https:', admin_url( 'admin.php?page=woocommerce&tab=dynamic_gallery' ) ) : str_replace( 'https:', 'http:', admin_url( 'admin.php?page=woocommerce&tab=dynamic_gallery' ) ) ) );
		exit;
	}
	load_plugin_textdomain( 'woo_dgallery', false, WOO_DYNAMIC_GALLERY_FOLDER.'/languages' );
	$thumb_width = get_option('thumb_width');
	$thumb_height = get_option('thumb_height');
	add_image_size( 'wc-dynamic-gallery-thumb', $thumb_width, $thumb_height, false  );
}
// Add language
add_action('init', 'wc_dynamic_gallery_init');

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WC_Dynamic_Gallery', 'plugin_extra_links'), 10, 2 );

add_filter( 'attachment_fields_to_edit', array('WC_Dynamic_Gallery_Variations', 'media_fields'), 10, 2 );
add_filter( 'attachment_fields_to_save', array('WC_Dynamic_Gallery_Variations', 'save_media_fields'), 10, 2 );

add_action( 'wp', 'setup_dynamic_gallery', 20);
function setup_dynamic_gallery() {
	global $woocommerce, $post;
	$current_db_version = get_option( 'woocommerce_db_version', null );
	$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	if (is_product()) {
		$global_wc_dgallery_activate = get_option('wc_dgallery_activate');
		$actived_d_gallery = get_post_meta($post->ID, '_actived_d_gallery',true);
		
		if ($actived_d_gallery == '' && $global_wc_dgallery_activate != 'no') {
			$actived_d_gallery = 1;
		}
		
		if($actived_d_gallery == 1){
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			
			add_action( 'woocommerce_before_single_product_summary', 'wc_dynamic_gallery_show', 30);
			
			wp_enqueue_style( 'ad-gallery-style', WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/jquery.ad-gallery.css' );
			wp_enqueue_script( 'ad-gallery-script', WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/jquery.ad-gallery.js', array(), false, true );
			
			$popup_gallery = get_option('popup_gallery');
			//wp_enqueue_script('jquery');
			if ($popup_gallery == 'fb') {
				wp_enqueue_style( 'woocommerce_fancybox_styles', WOO_DYNAMIC_GALLERY_JS_URL . '/fancybox/fancybox.css' );
				wp_enqueue_script( 'fancybox', WOO_DYNAMIC_GALLERY_JS_URL . '/fancybox/fancybox'.$suffix.'.js', array(), false, true );
			} elseif ($popup_gallery == 'colorbox') {
				wp_enqueue_style( 'a3_colorbox_style', WOO_DYNAMIC_GALLERY_JS_URL . '/colorbox/colorbox.css' );
				wp_enqueue_script( 'colorbox_script', WOO_DYNAMIC_GALLERY_JS_URL . '/colorbox/jquery.colorbox'.$suffix.'.js', array(), false, true );
			} elseif ($popup_gallery != 'deactivate') {
				if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
					wp_enqueue_style( 'woocommerce_prettyPhoto_css', WOO_DYNAMIC_GALLERY_JS_URL . '/prettyPhoto/prettyPhoto.css');
					wp_enqueue_script( 'prettyPhoto', WOO_DYNAMIC_GALLERY_JS_URL . '/prettyPhoto/jquery.prettyPhoto'.$suffix.'.js', array(), false, true);
				} else {
					wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
					wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array(), false, true );
				}
			}
	
			if ( in_array( 'woocommerce-professor-cloud/woocommerce-professor-cloud.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && get_option('woocommerce_cloud_enableCloud') == 'true' ) :
				remove_action( 'woocommerce_before_single_product_summary', 'wc_dynamic_gallery_show', 30);
			endif;
		}
	}
}

// Upgrade to 1.0.4
if(version_compare(get_option('a3rev_woo_dgallery_version'), '1.0.4') === -1){
	update_option('woo_dg_width_type','px');
	WC_Dynamic_Gallery::wc_dynamic_gallery_set_setting(true, true);
	update_option('a3rev_woo_dgallery_version', '1.0.4');
}

// Upgrade to 1.1.0
if(version_compare(get_option('a3rev_woo_dgallery_version'), '1.1.0') === -1){
	if(get_option('wc_dgallery_activate') === false){
		update_option('wc_dgallery_activate','yes');
	}
	update_option('a3rev_woo_dgallery_version', '1.1.0');
}

update_option('a3rev_woo_dgallery_version', '1.1.8');

global $wc_dg;
$wc_dg = new WC_Dynamic_Gallery();
?>