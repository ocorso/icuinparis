<?php
/*
Plugin Name: WooCommerce Widget Product Slideshow LITE
Plugin URI: http://a3rev.com/shop/woocommerce-widget-product-slideshow/
Description: Add a scrolling Gallery Slideshow of products to any widgetized area on your site. Options to show a slideshow of products from any Category or any Tag or set to show a dynamic gallery of all products that are currently marked down in price. All setting are on each widget you set up.
Version: 1.0.3
Author: A3 Revolution
Author URI: http://www.a3rev.com/
Requires at least: 3.3
Tested up to: 3.5.1
License: GPLv2 or later

	WooCommerce Widget Product Slideshow LITE plugin.
	Copyright © 2011 A3 Revolution Software Development team

	A3 Revolution Software Development team
	admin@a3rev.com
	PO Box 1170
	Gympie 4570
	QLD Australia
*/
?>
<?php
define( 'WC_GALLERY_WIDGET_FILE_PATH', dirname( __FILE__ ) );
define( 'WC_GALLERY_WIDGET_DIR_NAME', basename( WC_GALLERY_WIDGET_FILE_PATH ) );
define( 'WC_GALLERY_WIDGET_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define(	'WC_GALLERY_WIDGET_NAME', plugin_basename(__FILE__) );
define( 'WC_GALLERY_WIDGET_URL', WP_CONTENT_URL . '/plugins/' . WC_GALLERY_WIDGET_FOLDER );
define( 'WC_GALLERY_WIDGET_DIR', WP_CONTENT_DIR . '/plugins/' . WC_GALLERY_WIDGET_FOLDER );
define( 'WC_GALLERY_WIDGET_IMAGES_URL',  WC_GALLERY_WIDGET_URL . '/assets/images' );
define( 'WC_GALLERY_WIDGET_JS_URL',  WC_GALLERY_WIDGET_URL . '/assets/js' );
define( 'WC_GALLERY_WIDGET_CSS_URL',  WC_GALLERY_WIDGET_URL . '/assets/css' );
if(!defined("WC_GALLERY_WIDGET_AUTHOR_URI"))
    define("WC_GALLERY_WIDGET_AUTHOR_URI", "http://a3rev.com/shop/woocommerce-widget-product-slideshow/");

include 'classes/class-gallery-widget-functions.php';
include 'classes/class-gallery-widget-hook.php';

include 'widget/class-cycle-widget.php';

include 'admin/plugin-init.php';

/**
 * Call when the plugin is activated
 */
register_activation_hook(__FILE__,'woo_gallery_widget_install');

?>