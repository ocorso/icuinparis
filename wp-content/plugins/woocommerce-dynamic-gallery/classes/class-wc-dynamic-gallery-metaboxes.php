<?php
/**
 * WooCommerce Dynamic Gallery Meta_Boxes Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * woocommerce_meta_boxes_image()
 * woocommerce_product_image_box()
 */
class WC_Dynamic_Gallery_Meta_Boxes{

	function woocommerce_meta_boxes_image() {
		global $post;
		$global_wc_dgallery_activate = get_option('wc_dgallery_activate');
		$actived_d_gallery = get_post_meta($post->ID, '_actived_d_gallery',true);
		
		if ($actived_d_gallery == '' && $global_wc_dgallery_activate != 'no') {
			$actived_d_gallery = 1;
		}

		$check = '';
		if($actived_d_gallery == 1){$check = 'checked="checked"';}
		
		// Products
		$current_db_version = get_option( 'woocommerce_db_version', null );
		if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
			$woocommerce_product_image_box = 'woocommerce-product-image';
		} else {
			$woocommerce_product_image_box = 'woocommerce-product-images';
		}
		add_meta_box( $woocommerce_product_image_box, '<label style="margin-right: 50px;">'.__('A3 Dynamic Image Gallery activated', 'woo_dgallery').' : <input type="checkbox" '.$check.' value="1" name="_actived_d_gallery" /></label> <label>'.__('Product Variation Images activated', 'woo_dgallery').' : <span><input disabled="disabled" type="checkbox" value="1" name="_show_variation" /></label>', array('WC_Dynamic_Gallery_Meta_Boxes','woocommerce_product_image_box'), 'product', 'normal', 'high' );
	}
	function woocommerce_product_image_box() {
		
		global $post, $thepostid;
		
		$thepostid = $post->ID;
		
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		ob_start();
		
		?>
        <script src="<?php echo WOO_DYNAMIC_GALLERY_JS_URL;?>/tipTip/jquery.tipTip<?php echo $suffix;?>.js" type="text/javascript"></script>
		
		<style>
        .wc_dgallery_a_container {
			width:70px;
			height:70px;
			border:1px solid #CCC;
			display:inline-block;
			overflow:hidden;
			text-align:center;
			vertical-align:middle;
			margin-right:15px;
			margin-bottom:15px;	
			position:relative;
		}
		.wc_dgallery_a_container .image_item {
			width:100%;
			line-height:70px;
		}
		.wc_dgallery_delete_feature_image {
			background:#EEE url(<?php echo WOO_DYNAMIC_GALLERY_IMAGES_URL; ?>/delete.png) no-repeat center center;
			border: 1px solid #DDDDDD;
			border-radius: 2px 2px 2px 2px;
			padding:2px;
			position:absolute;
			right:2px;
			top:2px;
			width:10px;
			height:10px;
			cursor:pointer;
		}
        </style>
		<div class="woocommerce_options_panel">
        <?php
		$featured_img_id = (int)get_post_meta($post->ID, '_thumbnail_id', true);
		$attached_images = (array)get_posts( array(
			'post_type'   => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID ,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			'exclude'	  => array($featured_img_id),
		) );
		?>
        	<a href="#" onclick="tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&TB_iframe=true');return false;" class="wc_dgallery_a_container upload_image_button1" rel="<?php echo $post->ID; ?>"><img class="image_item" src="<?php echo WOO_DYNAMIC_GALLERY_IMAGES_URL; ?>/no-image-thumb.jpg" /><input type="hidden" name="upload_image_id[1]" class="upload_image_id" value="0" /></a>
        <?php
			if ($featured_img_id > 0) {
				$image_attribute = wp_get_attachment_image_src( $featured_img_id, array(70, 70));
				$feature_image_data = get_post( $featured_img_id );
				
				if ( $feature_image_data && $feature_image_data->post_parent == $post->ID ) {
					if ( get_post_meta( $featured_img_id, '_woocommerce_exclude_image', true ) != 1 ) {
		?>
        		<a href="#" class="wc_dgallery_feature_image wc_dgallery_a_container upload_image_button" rel="<?php echo $post->ID; ?>"><img class="image_item" src="<?php echo $image_attribute[0]; ?>" /><input type="hidden" name="upload_image_id[2]" class="upload_image_id" value="<?php echo $item_thumb->ID; ?>" /></a>
        <?php	
					}
				} else {
		?>
        		<span class="wc_dg_help_tip wc_dgallery_feature_image wc_dgallery_a_container" rel="<?php echo $post->ID; ?>" data-tip="<?php _e("'This image is a 'Featured Image' from the WordPress Media Library. Dynamic Gallery is showing it but you cannot edit or manage it in the Gallery Manager like images uploaded to the Gallery. Please click the red X to remove the 'feature' and upload the image to this Products Gallery and feature it. NOTE: If you feature another image from this Products Gallery this image will not show if you have not uploaded it to the Gallery.'", 'woo_dgallery'); ?>"><img class="image_item" src="<?php echo $image_attribute[0]; ?>" /><input type="hidden" name="upload_image_id[2]" class="upload_image_id" value="<?php echo $item_thumb->ID; ?>" /><span class="wc_dgallery_delete_feature_image">&nbsp;</span></span>
        <?php
				}
			}

		if(is_array($attached_images) && count($attached_images)>0){
			$i = 2 ;
			foreach($attached_images as $item_thumb){
				$i++;
				if ( get_post_meta( $item_thumb->ID, '_woocommerce_exclude_image', true ) == 1 ) continue;
				$image_attribute = wp_get_attachment_image_src( $item_thumb->ID, array(70, 70));
		?>
			<a href="#" class="wc_dgallery_a_container upload_image_button" rel="<?php echo $post->ID; ?>"><img class="image_item" src="<?php echo $image_attribute[0]; ?>" /><input type="hidden" name="upload_image_id[<?php echo $i; ?>]" class="upload_image_id" value="<?php echo $item_thumb->ID; ?>" /></a>
		<?php
        	}
		}
		?>
		</div>
        <script type="text/javascript">
		jQuery(".wc_dg_help_tip").tipTip({
			"attribute" : "data-tip",
			"maxWidth" : "500px",
			"fadeIn" : 50,
			"fadeOut" : 50
		});
		jQuery('#woocommerce-product-image h3').live('click', function(){
			jQuery('#woocommerce-product-image').removeClass("closed");
		});
		jQuery('.upload_image_button').live('click', function(){
			var post_id = <?php echo $post->ID; ?>;
			//window.send_to_editor = window.send_to_termmeta;
			tb_show('', 'media-upload.php?post_id=' + post_id + '&type=image&tab=gallery&TB_iframe=true');
			return false;
		});
		jQuery('.wc_dgallery_delete_feature_image').live('click', function(){
			jQuery('#remove-post-thumbnail').click();
			jQuery('.wc_dgallery_feature_image').remove();
			jQuery('#tiptip_holder').remove();
		});
		
		</script>
        
        <?php
		$output = ob_get_clean();
		echo $output;	
	}
	
	function save_actived_d_gallery( $post_id ) {
		global $post;
		if ( empty($post_id) || empty($post) || empty($_POST) ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( empty($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' )) return;
		if ( !current_user_can( 'edit_post', $post_id )) return;
		if ( $post->post_type != 'product' && $post->post_type != 'shop_order' && $post->post_type != 'shop_coupon' ) return;
		
		if(isset($_REQUEST['_actived_d_gallery'])){
			update_post_meta($post_id, '_actived_d_gallery', 1); 
		}else{
			update_post_meta($post_id, '_actived_d_gallery', 0); 
		}
	}
}
add_action( 'add_meta_boxes', array('WC_Dynamic_Gallery_Meta_Boxes','woocommerce_meta_boxes_image'), 11 );
add_action( 'save_post', array('WC_Dynamic_Gallery_Meta_Boxes','save_actived_d_gallery') );
?>