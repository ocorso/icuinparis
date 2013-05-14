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