<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
/**
 * Install Database, settings option
 */
function woo_gallery_widget_install(){
	update_option('woo_gallery_widget_pro_version', '1.0.3');
	delete_transient("woo_gallery_widget_update_info");
}

update_option('woo_gallery_widget_plugin', 'woo_gallery_widget');

add_action( 'init', array('WC_Gallery_Widget_Hook_Filter', 'plugin_init') );

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WC_Gallery_Widget_Hook_Filter', 'plugin_extra_links'), 10, 2 );

add_action( 'admin_notices', 'woo_gallery_widget_confirm_pin' );

// AJAX get authorization popup
add_action( 'wp_ajax_woo_gallery_widget_authorization', 'woo_gallery_widget_authorization_popup' );
add_action( 'wp_ajax_nopriv_woo_gallery_widget_authorization', 'woo_gallery_widget_authorization_popup' );

if ( woo_gallery_widget_check_pin() ) {

// Registry Widgets
add_action( 'widgets_init', create_function('', 'return register_widget("WC_Gallery_Cycle_Widget");') );

update_option('woo_gallery_widget_pro_version', '1.0.3');

}

/**
 * Call this function when plugin is deactivated
 */
function woo_gallery_widget_uninstall(){
	
	delete_transient("woo_gallery_widget_update_info");
	$respone_api = __('Connection Error! Could not reach the a3API on Amazon Cloud, the network may be busy. Please try again in a few minutes.', 'woo_gallery_widget');
	$options = array(
		'method' 	=> 'POST', 
		'timeout' 	=> 45, 
		'body' 		=> array(
			'act'			=> 'deactivate',
			'ssl'			=> get_option('a3rev_auth_woo_gallery_widget'),
			'plugin' 		=> get_option('woo_gallery_widget_plugin'),
			'domain_name'	=> $_SERVER['SERVER_NAME'],
			'address_ip'	=> $_SERVER['SERVER_ADDR'],
		) 
	);
	$server_a3 = base64_decode('aHR0cDovL2EzYXBpLmNvbS9hdXRoYXBpL2luZGV4LnBocA==');
	$raw_response = wp_remote_request($server_a3 , $options);
	if ( !is_wp_error( $raw_response ) && 200 == $raw_response['response']['code']) {
		$respone_api = $raw_response['body'];
	}
	
	delete_option ( 'a3rev_pin_woo_gallery_widget' );
	delete_option ( 'a3rev_auth_woo_gallery_widget' );
}

function woo_gallery_widget_confirm_pin() {

	/**
	* Check pin for confirm plugin
	*/
	if(isset($_POST['woo_gallery_widget_pin_submit'])){
		$respone_api = __('Connection Error! Could not reach the a3API on Amazon Cloud, the network may be busy. Please try again in a few minutes.', 'woo_gallery_widget');
		$ji = md5(trim($_POST['P_pin']));
		$options = array(
			'method' 	=> 'POST', 
			'timeout' 	=> 45, 
			'body' 		=> array(
				'act'			=> 'activate',
				'ssl'			=> $ji,
				'plugin' 		=> get_option('woo_gallery_widget_plugin'),
				'domain_name'	=> $_SERVER['SERVER_NAME'],
				'address_ip'	=> $_SERVER['SERVER_ADDR'],
			) 
		);
		$server_a3 = base64_decode('aHR0cDovL2EzYXBpLmNvbS9hdXRoYXBpL2luZGV4LnBocA==');
		$raw_response = wp_remote_request($server_a3 , $options);
		if ( !is_wp_error( $raw_response ) && 200 == $raw_response['response']['code']) {
			$respone_api = $raw_response['body'];
		} elseif ( is_wp_error( $raw_response ) ) {
			$respone_api = __('Error Code: ', 'woo_gallery_widget').$raw_response['response']['code'].' | '.$raw_response->get_error_message();
		}
		
		if($respone_api == md5('valid')) {
			update_option( 'a3rev_pin_woo_gallery_widget', sha1(md5('a3rev.com_'.get_option('siteurl').'_woo_gallery_widget')));
			update_option( 'a3rev_auth_woo_gallery_widget', $ji );
			update_option( 'a3rev_woo_gallery_widget_message', __('Thank you. This Authorization Key is valid.', 'woo_gallery_widget') );
		}else{
			delete_option('a3rev_pin_woo_gallery_widget' );
			delete_option('a3rev_auth_woo_gallery_widget' );
			update_option('a3rev_woo_gallery_widget_message', $respone_api );
		}

		delete_transient("woo_gallery_widget_update_info");
		if ( woo_gallery_widget_check_pin() ) {
			echo '<div class="updated"><p>'.get_option('a3rev_woo_gallery_widget_message').'</p></div>';
			update_option('a3rev_woo_gallery_widget_message', '');
		} else {
			echo '<div class="error"><p>'.get_option('a3rev_woo_gallery_widget_message').'</p></div>';
		}
	}
}

function woo_gallery_widget_check_pin() {
	$domain_name = get_option('siteurl');
	if (function_exists('is_multisite')){
		if (is_multisite()) {
			global $wpdb;
			$domain_name = $wpdb->get_var("SELECT option_value FROM ".$wpdb->options." WHERE option_name = 'siteurl'");
			if ( substr($domain_name, -1) == '/') {
				$domain_name = substr( $domain_name, 0 , -1 );
			}
		}
	}
	if (get_option('a3rev_auth_woo_gallery_widget') != '' && get_option("a3rev_pin_woo_gallery_widget") == sha1(md5('a3rev.com_'.$domain_name.'_woo_gallery_widget'))) return true;
	else return false;
}

function woo_gallery_widget_authorization_popup() {
	check_ajax_referer( 'woo_gallery_widget_authorization', 'security' );
	?>
    <div id="TB_iframeContent" style="position:relative;width:100%;">
		<div style="padding:10px 25px;">
    <?php
	if(!file_exists(WC_GALLERY_WIDGET_FILE_PATH."/encryp.inc")){
		echo '<font size="+2" color="#FF0000"> '. __("No find the encryp.inc file. Please copy encryp.inc file to folder", "woo_gallery_widget") .' '.WC_GALLERY_WIDGET_FILE_PATH.' </font>';
	}else{
		$getfile = file_get_contents(WC_GALLERY_WIDGET_FILE_PATH ."/encryp.inc");
		$str = "THlvTkNsQnNkV2RwYmlCT1lXMWxPaUJYVUMxQ2JHOW5VM1J2Y21VZ1ptOXlJRmR2Y21Sd2NtVnpjdzBLVUd4MVoybHVJRlZTU1RvZ2FIUjBjRG92TDNkM2R5NWlkV2xzWkdGaWJHOW5jM1J2Y21VdVkyOXRMdzBLUkdWelkzSnBjSFJwYjI0NklFRjFkRzl0WVhScFkyRnNiSGtnWjJWdVpYSmhkR1VnWlVKaGVTQmhabVpwYkdsaGRHVWdZbXh2WjNNZ2QybDBhQ0IxYm1seGRXVWdkR2wwYkdWekxDQjBaWGgwTENCbFFtRjVJR0YxWTNScGIyNXpMZzBLVm1WeWMybHZiam9nTXk0d0RRcEVZWFJsT2lCTllYSmphQ0F4TENBeU1EQTVEUXBCZFhSb2IzSTZJRUoxYVd4a1FVSnNiMmRUZEc5eVpRMEtRWFYwYUc5eUlGVlNTVG9nYUhSMGNEb3ZMM2QzZHk1aWRXbHNaR0ZpYkc5bmMzUnZjbVV1WTI5dEx3MEtLaThnRFFvTkNnMEtJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJdzBLSXlBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSXcwS0l5QWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJRmRRTFVKc2IyZFRkRzl5WlNCWGIzSmtjSEpsYzNNZ1VHeDFaMmx1SUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0l3MEtJeUFnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJdzBLSXlBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSXcwS0l5QWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0l3MEtJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJdzBLRFFvTkNpTWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU09";
		if(strpos($getfile, $str) === FALSE){
			echo '<font size="+2" color="#FF0000"> '.__("encryp.inc was modified. Please keep it by default", "woo_gallery_widget").'. </font>';	
		}else{
		?>
		<div class="wrap">
        <?php
			// Determine the current tab in effect.
			if(isset($_REQUEST['woo_gallery_widget_pin_submit'])){
				echo '<div id="" class="error"><p>'.get_option("a3rev_woo_gallery_widget_message").'</p></div>';
			}
		?>
			<div class="main_title"><div id="icon-ms-admin" class="icon32"><br></div><h2><?php _e("Enter Your Plugin Authorization Key", "woo_gallery_widget") ; ?></h2></div>
			<div style="clear:both;height:30px;"></div>
			<div>
            <form method="post" action="">
				<p>
					<?php _e("Authorization Key", "woo_gallery_widget"); ?>: <input name="P_pin" type="text" id="P_pin" style="padding:10px; width:250px;" />
                </p>
				<p class="submit">
					<input class="button-primary" type="submit" name="woo_gallery_widget_pin_submit" value="<?php _e("Validate", "woo_gallery_widget"); ?>" />
				</p>
            </form>
			</div>
		</div>
		<?php
		}
	}
	?>
    	</div>
    </div>
    <?php
	die();
}
?>