<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
/**
 * WooCommerce Widget Product Slideshow Functions
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 *
 * plugins_loaded()
 * create_page()
 */
class WC_Gallery_Widget_Functions {
	
	/**
	 * get_template_image_file_info( $file )
	 *
	 * @access public
	 * @since 3.8
	 * @param $file string filename
	 * @return PATH to the file
	 */
	function get_template_image_file_info( $file = '' ) {
		// If we're not looking for a file, do not proceed
		if ( empty( $file ) )
			return;
	
		// Look for file in stylesheet
		$image_info = array();
		if ( file_exists( get_stylesheet_directory() . '/images/' . $file ) ) {
			$file_url = get_stylesheet_directory_uri() . '/images/' . $file;
			list($current_width, $current_height) = getimagesize(get_stylesheet_directory() . '/images/' . $file);
			$image_info['url'] = $file_url;
			$image_info['width'] = $current_width;
			$image_info['height'] = $current_height;
		// Look for file in template
		} elseif ( file_exists( get_template_directory() . '/images/' . $file ) ) {
			$file_url = get_template_directory_uri() . '/images/' . $file;
			list($current_width, $current_height) = getimagesize(get_template_directory() . '/images/' . $file);
			$image_info['url'] = $file_url;
			$image_info['width'] = $current_width;
			$image_info['height'] = $current_height;
		// Backwards compatibility
		} else {
			$file_url = woocommerce_placeholder_img_src();
			list($current_width, $current_height) = getimagesize($file_url);
			$image_info['url'] = $file_url;
			$image_info['width'] = $current_width;
			$image_info['height'] = $current_height;
		}
	
		if ( is_ssl() ) {
			$file_url = str_replace('http://', 'https://', $file_url);
			$image_info['url'] = $file_url;
		}
	
		return $image_info;
	}
	
	function get_products_cat($catid = 0, $orderby='title menu_order', $number = -1, $offset = 0) {
		$args = array(
			'numberposts'	=> $number,
			'offset'		=> $offset,
			'orderby'		=> $orderby,
			'order'			=> 'ASC',
			'post_type'		=> 'product',
			'post_status'	=> 'publish'
		);
		if ($catid > 0) {
			$args['tax_query'] = array(
						array(
							'taxonomy'			=> 'product_cat',
							'field'				=> 'id',
							'terms'				=> $catid,
							'include_children'	=> false
						)
			);
		}
		$results = get_posts($args);
		if ( $results && is_array($results) && count($results) > 0) {
			return $results;
		} else {
			return array();
		}
	}
	
	function get_products_tag($tagid = 0, $orderby='title menu_order', $number = -1, $offset = 0) {
		$args = array(
			'numberposts'	=> $number,
			'offset'		=> $offset,
			'orderby'		=> $orderby,
			'order'			=> 'ASC',
			'post_type'		=> 'product',
			'post_status'	=> 'publish'
		);
		if ($tagid > 0) {
			$args['tax_query'] = array(
						array(
							'taxonomy'			=> 'product_tag',
							'field'				=> 'id',
							'terms'				=> $tagid,
							'include_children'	=> false
						)
			);
		}
		$results = get_posts($args);
		if ( $results && is_array($results) && count($results) > 0) {
			return $results;
		} else {
			return array();
		}
	}
	
	function get_products_onsale($orderby='title menu_order', $number = -1, $offset = 0) {
		global $wp_query, $woocommerce;
		$current_db_version = get_option( 'woocommerce_db_version', null );
		
		if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
		
			$meta_query = array();
	
			$meta_query[] = array(
				'key' => '_sale_price',
				'value' 	=> 0,
				'compare' 	=> '>',
				'type'		=> 'NUMERIC'
			);
				
			$on_sale = get_posts( array(
				'numberposts'	=> -1,
				'post_type'		=> array('product', 'product_variation'),
				'post_status'	=> 'publish',
				'meta_query'	=> $meta_query,
				'fields'		=> 'id=>parent'
			) );
			
			$product_ids 	= array_keys( $on_sale );
			$parent_ids		= array_values( $on_sale );
			
			// Check for scheduled sales which have not started
			foreach ( $product_ids as $key => $id )
				if ( get_post_meta( $id, '_sale_price_dates_from', true ) > current_time('timestamp') )
					unset( $product_ids[ $key ] );
	
			$product_ids_on_sale = array_unique( array_merge( $product_ids, $parent_ids ) );
			
			$product_ids_on_sale[] = 0;
			
		} else {
			// Get products on sale
			$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
		}
		
		$meta_query = array();
		$meta_query[] = $woocommerce->query->visibility_meta_query();
	    $meta_query[] = $woocommerce->query->stock_status_meta_query();

    	$query_args = array(
    		'numberposts' 	=> $number,
    		'no_found_rows' => 1,
    		'post_status' 	=> 'publish',
    		'post_type' 	=> 'product',
			'offset'		=> $offset,
    		'orderby'		=> $orderby,
			'order'			=> 'ASC',
    		'meta_query' 	=> $meta_query,
    		'post__in'		=> $product_ids_on_sale
    	);
		
		$results = get_posts($query_args);
		if ( $results && is_array($results) && count($results) > 0) {
			return $results;
		} else {
			return array();
		}
	}
	
	function get_image_info($id, $size = 'full') {
		$thumbid = 0;
		if ( has_post_thumbnail($id) ) {	
			$thumbid = get_post_thumbnail_id($id);
		} else {
			$args = array( 'post_parent' => $id ,'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null); 
			$attachments = get_posts($args);
			if ($attachments) {
				foreach ( $attachments as $attachment ) {
					$thumbid = $attachment->ID;
					break;
				}
			}
		}
		$image_info = array();
		if ($thumbid > 0 ) {
			$image_attribute = wp_get_attachment_image_src( $thumbid, $size);
			$image_info['url'] = $image_attribute[0];
			$image_info['width'] = $image_attribute[1];
			$image_info['height'] = $image_attribute[2];	
		} else {
			$image_info = WC_Gallery_Widget_Functions::get_template_image_file_info('no-image.gif');
		}
		
		return $image_info;
	}
	
	function limit_words($str='',$len=100,$more=true) {
		if (trim($len) == '' || $len < 0) $len = 100;
	   if ( $str=="" || $str==NULL ) return $str;
	   if ( is_array($str) ) return $str;
	   $str = trim($str);
	   $str = strip_tags($str);
	   if ( strlen($str) <= $len ) return $str;
	   $str = substr($str,0,$len);
	   if ( $str != "" ) {
			if ( !substr_count($str," ") ) {
					  if ( $more ) $str .= " ...";
					return $str;
			}
			while( strlen($str) && ($str[strlen($str)-1] != " ") ) {
					$str = substr($str,0,-1);
			}
			$str = substr($str,0,-1);
			if ( $more ) $str .= " ...";
			}
			return $str;
	}
}
?>