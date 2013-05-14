<?php
	add_filter( 'lib_update', 'lib_update' );
	
	function lib_update( $options )
	{

		$options['lib_options']['logo'] 	   = lib_file_upload( 'logo' );
		$options['lib_options']['body_bg'] = lib_file_upload( 'body_bg' );
		
		if ( $_POST['remove_logo'] ) {
			$options['lib_options']['logo'] = '';
		}
		elseif ( $_POST['remove_bg'] ) {
			$options['lib_options']['body_bg'] = '';
		}
	return $options;
	}
	
	function lib_file_upload( $type )
	{
		
		$field_name = $type;
		
		$file = array( 
					'tmp_name'  => $_FILES['lib_options']['tmp_name'][$field_name],
					'name' 		  => $_FILES['lib_options']['name'][$field_name],
					'size' 		  => $_FILES['lib_options']['size'][$field_name],
					'error' 		  => $_FILES['lib_options']['error'][$field_name]
				 );
		
		/*$dir = WP_CONTENT_DIR . '/uploads/maintenance/';*/
		    $upload_dir = wp_upload_dir();
  		    $dir = $upload_dir[ 'basedir' ] . '/maintenance/';
            $url = $upload_dir[ 'baseurl' ] . '/maintenance/';

		if ( !file_exists( dirname($dir) ) ) {
			mkdir( dirname($dir) );
		}
		if ( !file_exists( $dir ) ) {
			mkdir( $dir );
		}
		
		if ( $file['name'] ) {
			$filename = strtolower($field_name.'.'.end( explode( '.', $file['name'] ) ));
			move_uploaded_file( $file['tmp_name'], $dir . $filename );
			/*return WP_CONTENT_URL . '/uploads/maintenance/'.$filename;*/
			 return $url . $filename;
		}
		else {
			$options = mt_get_option();
			return $options['lib_options'][$field_name];
		}
	}

?>