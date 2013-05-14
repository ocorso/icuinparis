<?php
		function css_activate () {
			$str_css = "<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700|Arvo:400,400italic,700,700italic' rel='stylesheet' type='text/css'>";
	    return $str_css;
		}
 
 		function user_logout() { 
			wp_safe_redirect(get_bloginfo('url'));
		exit; 
		}
		

	function mt_template_redirect($template)
	{
		global $mt_options;
		
		$mt_options = mt_get_option();
		if ( $mt_options['logged_in_permission'] ) {
			  mt_handle_template($mt_options);
		}
		
	}

	function mt_get_option()
	{
		$option = get_option( basename(dirname(__FILE__)) );
		if ( !$option ) {
			$option = array( 'state' => 'live', 'logged_in_permission' => true );
			add_option(basename(dirname(__FILE__)), $option);
		}
		return $option;
	}
	

	function mt_update_option($data)
	{
		return update_option(basename(dirname(__FILE__)), $data);
	}
	

	 function mt_get_lib_var()
	 {
	 	global $mt_themes;
				  $mt_themes = array();
		
	 	$dir = dirname(__FILE__).'/'.LIB_DIR.'/';
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && !is_file($file) && file_exists($dir.$file.'/index.php') ) {
					$mt_themes[] = ucfirst($file);
				}
			}
			closedir($handle);
		}
		
		return true;
	 }
	

	function mt_handle_template($option)
	{
		$dir = get_template_directory().'/';
		
		if (!is_user_logged_in()) {
			switch ( $option['state'] ) {
			case 'maintenance':
				 {
					/* load selected theme */
					if ( $option['expiry_date'] ) {
						list( $date,$time ) = explode( '|', $option['expiry_date'] );
						list( $month, $day, $year ) = explode( '.', $date );
						list( $hour, $minute, $second ) = explode ( ':', $time );
						$timestamp = mktime( $hour, $minute, $second, $month, $day, $year );

						/* if page should be opened now, return true and break function */
						if ( time() > $timestamp ) {
							return true;
						}
					}

					$lib_dir = dirname( __FILE__ ) . '/' . LIB_DIR . '/';
					if ( file_exists( $lib_dir . 'index.php' ) ) {
						mt_prepare_template_vars();
						include_once $lib_dir . 'index.php';
						exit();
					}
				}
			break;
		}
		
		}
	}
	
	
	function mt_prepare_template_vars()
	{
		global $mt_lib,    $mt_options;
				  $mt_lib  = $mt_options['lib_options'];
		return true;
	
	}	

?>
