<?php
/*
	Plugin Name: Maintenance
	Plugin URI: http://wordpress.org/extend/plugins/maintenance/
	Description: Take your site down from public view with a click of a button hiding the site when you need to change a few things or run an upgrade, making it only accessible by login and password. There is also an area to add a custom message which will be shown to the users while your site is down. Users stay on the same page when they input wrong initials.
	Version: 1.2.1
	Author: fruitfulcode
	Author URI: http://fruitfulcode.com
	License: GPL2
*/
/*  Copyright 2013  Fruitful Code  (email : support@fruitfulcode.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
	
	
	define( 'LIB_DIR', 'lib' ); 
	define( 'PLUGIN_URL', WP_PLUGIN_URL . '/' . basename (dirname ( __FILE__ ) ) . '/' );
	
	include_once 'functions.php';
	
	$mt_options = mt_get_option();
		
	if ($mt_options['state'] == "maintenance") {
		if (($mt_options['lib_options']['admin_bar_enabled'] == "1")) { 
			add_filter('show_admin_bar', '__return_true');  																	 
		} else {
			add_filter('show_admin_bar', '__return_false');  																	 
		}
	}
	
	
	/*maintenance mode is active*/
	
	add_action( 'template_redirect', 'mt_template_redirect' );
	add_action( 'admin_menu', 'dashboard_menu' );
	add_action( 'wp_logout','user_logout');
	register_deactivation_hook( __FILE__, 'deactivate_plugin' );
	
	include_once 'page-options.php';

	function dashboard_menu()
	{
		$page = add_menu_page('Maintenance', 'Maintenance', 'manage_options', 'maintenance', 'mt_manage_options', PLUGIN_URL . '/images/icon-small.png');
		add_action( "admin_print_styles-$page", 'admin_head' );
	}
	
	
	function mn_plugin_init() {
		load_plugin_textdomain( 'maintenance', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action('plugins_loaded', 'mn_plugin_init');
	
	function admin_head()
	{	
	
		echo css_activate ();
		wp_enqueue_script( 'maintenance', PLUGIN_URL .'js/init.js' );
	    wp_enqueue_style  ('maintenance', PLUGIN_URL .'css/admin.css' );	
	}
	
	function deactivate_plugin()
	{
		return delete_option( basename(dirname(__FILE__)) );
	}

		
?>
