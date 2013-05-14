<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
/**
 * WooCommerce Gallery Widget Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 *
 * plugin_extra_links()
 * plugin_init()
 */
class WC_Gallery_Widget_Hook_Filter {
	function plugin_extra_links($links, $plugin_name) {
		if ( $plugin_name != WC_GALLERY_WIDGET_NAME) {
			return $links;
		}
		$links[] = '<a href="http://docs.a3rev.com/user-guides/woocommerce/woo-widget-product-slideshow/" target="_blank">'.__('Documentation', 'woo_gallery_widget').'</a>';
		$links[] = '<a href="http://a3rev.com/shop/woocommerce-widget-product-slideshow/#help_tab" target="_blank">'.__('Support', 'woo_gallery_widget').'</a>';
		return $links;
	}
	
	function plugin_init() {
		
		// Add language
		load_plugin_textdomain( 'woo_gallery_widget', false, WC_GALLERY_WIDGET_FOLDER.'/languages' );
		
	}
}
?>