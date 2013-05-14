<?php
/**
 * WooCommerce Dynamic Gallery Meta_Boxes Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * media_fields()
 * save_media_fields()
 */
class WC_Dynamic_Gallery_Variations{
	
	function media_fields( $form_fields, $attachment ) {
	
		global $woocommerce;
		
		$product_id = $_GET['post_id'];
		
		if( isset($_GET['tab']) && $_GET['tab'] == 'gallery' && get_post_type($product_id) == 'product' && wp_attachment_is_image($attachment->ID) ) {
			
			if (!isset($form_fields['woocommerce_exclude_image'])) {
				$checked_exclude_image = '';
				$exclude_image = (int)get_post_meta($attachment->ID, '_woocommerce_exclude_image', true);
				if ($exclude_image == 1) $checked_exclude_image = 'checked="checked"';
				$form_fields['woocommerce_exclude_image'] = array(
						'label' => __('Exclude image', 'woo_dgallery'),
						'input' => 'html',
						'html' =>  '<label><input type="hidden" name="woo_dynamic_gallery_exclude_image" value="1" /><input type="checkbox" '.$checked_exclude_image.' name="attachments['.$attachment->ID.'][woocommerce_exclude_image]" id="attachments['.$attachment->ID.'][woocommerce_exclude_image]" /> '.__('Enabling this option will hide it from the product page image gallery.', 'woo_dgallery').' '.__('If assigned to variations below the image will show when option is selected.', 'woo_dgallery').'</label>',
						'value' => '',
						'helps' => '',
				);
				
			} else {
				$form_fields['woocommerce_exclude_image']['helps'] = __('Enabling this option will hide it from the product page image gallery.', 'woo_dgallery').' '.__('If assigned to variations below the image will show when option is selected.', 'woo_dgallery');
			}
					
			$attributes = (array) maybe_unserialize(get_post_meta($product_id, '_product_attributes', true) );
						
			$have_variation = false;
			
			if (is_array( $attributes ) && count($attributes) > 0 ) {
				foreach($attributes as $attribute => $data) {
					if(isset($data['is_variation']) && $data['is_variation']) {
						$have_variation = true;
						break;
					}
				}
			}
			if ($have_variation) {
				$form_fields['start_variation'] = array(
						'label' => __('Variations', 'woo_dgallery'),
						'input' => 'html',
						'html' => '<style>.start_variation {border-width:2px 2px 0} .end_variation {border-width:0 2px 2px} .start_variation, .end_variation {border-style:solid ;border-color:#E6DB55;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px;position:relative;}</style>',
						'value' => '',
						'helps'	=> __('Upgrade to the PRO version to use this feature.', 'woo_dgallery'),
					);
				foreach($attributes as $attribute => $data) {
					
					if(isset($data['is_variation']) && $data['is_variation']) {
						$html = "<style>.in_variations_".$attribute." {border-width:0 2px;border-style:solid ;border-color:#E6DB55;}</style>";
						
						$html .= "<input disabled='disabled' type='checkbox' class='assign_image_all_variations' id='".$attachment->ID."_".$attribute."' name='".$attachment->ID."_".$attribute."' value=''> <label for='".$attachment->ID."_".$attribute."'><strong>".__('Apply to All', 'woo_dgallery')."</strong></label><br />";
						
						if (strpos($data['name'], 'pa_') !== false) {
							
							$terms = wp_get_post_terms( $product_id, $data['name'] );
							
							$values = array();
							foreach($terms as $term) {
								$values[] = $term->name;
							}
							
							$data['name'] = str_replace('pa_','',$data['name']);
							$data['name'] = ucwords($data['name']);
							
							$i = 0; foreach($values as $slug => $value) {
								$html .= "&nbsp;- &nbsp; <input disabled='disabled' class='".$attachment->ID."_".$attribute."' type='checkbox' id='".$attachment->ID."_".$attribute."_".$i."' name='attachments[".$attachment->ID."][in_variations][".$attribute."][".$i."]' value='".$slug."' > <label for='".$attachment->ID."_".$attribute."_".$i."'>".$value."</label><br />";
							$i++; }
							
						} else {
							
							$values = explode('|', $data['value']);
							
							$i = 0; foreach($values as $value) {
								$slug = esc_attr($value);
								$html .= "&nbsp;- &nbsp; <input disabled='disabled' class='".$attachment->ID."_".$attribute."' type='checkbox' id='".$attachment->ID."_".$attribute."_".$i."' name='attachments[".$attachment->ID."][in_variations][".$attribute."][".$i."]' value='".$slug."' > <label for='".$attachment->ID."_".$attribute."_".$i."'>".$value."</label><br />";
							$i++; }
				
							
						} // End check to see if attribute is defined through woocomm, or manually
						
							$form_fields['in_variations_'.$attribute] = array(
								'label' => $data['name'],
								'input' => 'html',
								'html' => $html,
								'value' => ''
							);
					
					}
				}	
				$form_fields['end_variation'] = array(
						'label' => '',
						'input' => 'html',
						'html' => '&nbsp;',
						'value' => ''
					);
			}
			
		} // if('product')
	
		return $form_fields;
	}
	
	function save_media_fields( $post, $attachment ) {
		if (substr($post['post_mime_type'], 0, 5) == 'image') {
			if (isset($_REQUEST['woo_dynamic_gallery_exclude_image'])) {
				if (isset($_REQUEST['attachments'][$post['ID']]['woocommerce_exclude_image'])) :
					delete_post_meta( (int) $post['ID'], '_woocommerce_exclude_image' );
					update_post_meta( (int) $post['ID'], '_woocommerce_exclude_image', 1);
				else :
					delete_post_meta( (int) $post['ID'], '_woocommerce_exclude_image' );
					update_post_meta( (int) $post['ID'], '_woocommerce_exclude_image', 0);
				endif;
			}
		}
		return $post;
	}
}
?>