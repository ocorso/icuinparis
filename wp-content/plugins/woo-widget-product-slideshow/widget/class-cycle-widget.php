<?php
/**
 * WooCommerce Product Image Gallery Cycle Widget
 *
 * Table Of Contents
 *
 * __construct()
 * widget()
 * update()
 * form()
 */
class WC_Gallery_Cycle_Widget extends WP_Widget {
	function WC_Gallery_Cycle_Widget() {
		$widget_ops = array('classname' => 'widget_product_cycle', 'description' => __( 'Add a scrolling Gallery of products. Set to show a slideshow of products from any Category, Tag or products that are currently marked down in price.', 'woo_gallery_widget') );
		$this->WP_Widget('widget_product_cycle', __('Woo Products Slideshow', 'woo_gallery_widget'), $widget_ops);

	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$category_id = $instance['category_id'];
		$image_height = intval($instance['image_height']);
		$auto_scroll = esc_attr($instance['auto_scroll']);
		$effect = esc_attr($instance['effect']);
		$item_text = __('View this Product', 'woo_gallery_widget');
		$category_text = __('View All Products', 'woo_gallery_widget');
		
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo $this->items_cycle($widget_id, $category_id, $image_height, $auto_scroll, $effect, $item_text, $category_text);
		echo $after_widget;
	}
	
	function items_cycle($widget_id, $category_id, $image_height = 140, $auto_scroll = 'no', $effect = 'scrollLeft', $item_text = '' , $category_text= '') {
		
		$id = rand(100, 100000);
		
		if ( $category_id < 1) return;
		$product_results = WC_Gallery_Widget_Functions::get_products_cat($category_id, 'title menu_order', 6, 0);
		
		if ( count($product_results) < 1) return;
		
		$shop_page_id = 1;
		if (function_exists( 'woocommerce_get_page_id' ) ) $shop_page_id = woocommerce_get_page_id('shop');
				
		add_action( 'wp_footer', array('WC_Gallery_Cycle_Widget', 'product_cycle_script') );
		
		$timeout = 4000;
		$effect_speed = 1000;
		
		if ($auto_scroll == 'no') {
			$timeout = 0;
			if ($effect == 'scrollUp' || $effect == 'scrollDown') $effect = 'scrollVert';
			elseif ($effect == 'scrollLeft' || $effect == 'scrollRight') $effect = 'scrollHorz';
		}
		?>
		<script type="text/javascript">
		jQuery(function() {
			jQuery("#product_cycle_<?php echo $id;?>").cycle({ 
				fx:     '<?php echo $effect; ?>', 
				timeout: <?php echo $timeout; ?>, 
				delay:  -1000,
				speed:  <?php echo $effect_speed; ?>, 
				prev:   '#p_prev_<?php echo $id;?>', 
				next:   '#p_next_<?php echo $id;?>',
				paused: onAfter_<?php echo $id;?>,
				sync: true,
   				pause: 1
			});
			jQuery(".product_cycle_widget_container_<?php echo $id;?>").mouseleave(function (){ 
				jQuery('.w_next_prev_<?php echo $id;?>').fadeOut("slow");
			});
		});
		function onAfter_<?php echo $id;?>(curr,next,opts) {
			jQuery('.w_next_prev_<?php echo $id;?>').fadeIn("slow");
		}
        </script>
		<style>
		.product_cycle_item .content-slide-img_<?php echo $id; ?>{height: <?php echo $image_height; ?>px }
		.product_cycle_item .content-slide-img_<?php echo $id; ?> .content-slide-img-center{height: <?php echo $image_height; ?>px }
        </style>
        <?php ob_start(); ?>
		<div style="clear:both"></div>
		<div class="product_cycle_widget_container product_cycle_widget_container_<?php echo $id; ?>"><div class="product_cycle_widgets" id="product_cycle_<?php echo $id; ?>">
		<?php
		$image_size = 'full';
		
		$shop_catalog_height = get_option('woocommerce_catalog_image_height');
		$shop_single_height = get_option('woocommerce_single_image_width');
		
		if ($image_height <= $shop_catalog_height ) {
			$image_size = 'shop_catalog';
		} elseif ($image_height <= $shop_single_height ) {
			$image_size = 'shop_single';
		} 
		
		$no_product = 0;
		foreach ($product_results as $product) {
			$no_product++;
			$thumb_image_info = WC_Gallery_Widget_Functions::get_image_info($product->ID,  $image_size);
			$thumb_image_url = $thumb_image_info['url'];
			$max_width = '';
			if ( is_array($thumb_image_info) ) {
				$current_height = $thumb_image_info['height'];
				$current_width = $thumb_image_info['width'];
				if ($current_height > $image_height) {
					$factor = ($current_height / $image_height);
					$max_width = round($current_width / $factor);
				}
			}
		?>
			<div class="product_cycle_item" <?php if($no_product == 1) { echo 'style="display:block"'; } ?>>
				<div style="clear:both"></div><span class="product_cycle_item_title"><?php esc_attr_e($product->post_title); ?></span><div style="clear:both"></div>
				<div class="content-slide-img content-slide-img_<?php echo $id; ?>">
                	<div class="content-slide-img-center">
                    	<a class="product_cycle_image_a" href="<?php echo get_permalink($product->ID); ?>">
                        	<img src="<?php echo $thumb_image_url; ?>" class="product_cycle_image" alt="" style="max-height:<?php echo $image_height; ?>px !important; <?php if(trim($max_width) != '') echo 'max-width:'.$max_width.'px !important;'; ?>" />
						</a>
                	</div>
                </div>
				<div class="item_link"><a class="item_widget_link" href="<?php echo get_permalink($product->ID); ?>"><?php echo $item_text; ?></a></div>
			</div>
        <?php
		}
		?>
		</div>
		<div class="w_next_prev w_next_prev_<?php echo $id; ?>"><span class="p_prev_arrow"><a href="" id="p_prev_<?php echo $id; ?>" class="p_prev"><?php _e('Prev', 'woo_gallery_widget'); ?></a></span><span class="p_next_arrow"><a href="" id="p_next_<?php echo $id; ?>" class="p_next"><?php _e('Next', 'woo_gallery_widget'); ?></a></span></div>
				
		</div><div style="clear:both"></div>
        
        <div class="product_cycle_category_container"><a class="category_widget_link" href="<?php echo get_permalink($shop_page_id); ?>"><?php echo $category_text; ?></a></div><div style="clear:both"></div>
        <?php
		$html = ob_get_clean();
		return $html;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['category_id'] = $new_instance['category_id'];
		$instance['image_height'] = intval($new_instance['image_height']);
		$instance['auto_scroll'] = esc_attr($new_instance['auto_scroll']);
		$instance['effect'] = esc_attr($new_instance['effect']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'image_height' => 140, 'auto_scroll' => 'no', 'effect' => 'scrollLeft' ) );
		
		$widget_id = str_replace('widget_product_cycle-','',$this->id);
		
		$title = esc_attr($instance['title']);
		$category_id = $instance['category_id'];
		$image_height = intval($instance['image_height']);
		$auto_scroll = esc_attr($instance['auto_scroll']);
		$effect = esc_attr($instance['effect']);
		$item_text = __('View this Product', 'woo_gallery_widget');
		$category_text = __('View All Products', 'woo_gallery_widget');
?>
		<style>
			#woo_gallery_widget_upgrade_area { border:2px solid #E6DB55;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px; padding:5px; position:relative; margin-bottom:10px; padding-top:10px;}
			#woo_gallery_widget_upgrade_area legend {margin-left:4px; font-weight:bold;}
			.item_heading{ width:130px; display:inline-block;}
		</style>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
            
            <p><label for="<?php echo $this->get_field_id('category_id'); ?>"><?php _e('Category:', 'woo_gallery_widget'); ?></label> 
            <?php wp_dropdown_categories( array('orderby' => 'name', 'selected' => $category_id, 'name' => $this->get_field_name('category_id'), 'id' => $this->get_field_id('category_id'), 'class' => 'widefat', 'depth' => true, 'taxonomy' => 'product_cat') ); ?>
            </p>
            <p><label for="<?php echo $this->get_field_id('image_height'); ?>"><?php _e('Image Tall:', 'woo_gallery_widget'); ?></label> <input class="" id="<?php echo $this->get_field_id('image_height'); ?>" name="<?php echo $this->get_field_name('image_height'); ?>" type="text" value="<?php echo $image_height; ?>" size="3" /> px</p>
            
            <p><input type="radio" id="<?php echo $this->get_field_id('auto_scroll'); ?>1" name="<?php echo $this->get_field_name('auto_scroll'); ?>" value="no" checked="checked" /> <label for="<?php echo $this->get_field_id('auto_scroll'); ?>1"><?php _e('Manual Scroll', 'woo_gallery_widget'); ?></label></p>
            <p><input type="radio" id="<?php echo $this->get_field_id('auto_scroll'); ?>2" name="<?php echo $this->get_field_name('auto_scroll'); ?>" value="yes" <?php if($auto_scroll == 'yes'){ echo 'checked="checked"'; } ?> /> <label for="<?php echo $this->get_field_id('auto_scroll'); ?>2"><?php _e('Auto Scroll', 'woo_gallery_widget'); ?></label></p>
            
            <p><label for=""><strong><?php _e('Effect:', 'woo_gallery_widget'); ?></strong></label><br />
            <input type="radio" id="<?php echo $this->get_field_id('effect'); ?>1" name="<?php echo $this->get_field_name('effect'); ?>" value="scrollUp" <?php if($effect == 'scrollUp'){ echo 'checked="checked"'; } ?> /> <label for="<?php echo $this->get_field_id('effect'); ?>1"><?php _e('Scroll Up', 'woo_gallery_widget'); ?></label><br />
            <input type="radio" id="<?php echo $this->get_field_id('effect'); ?>2" name="<?php echo $this->get_field_name('effect'); ?>" value="scrollDown" <?php if($effect == 'scrollDown'){ echo 'checked="checked"'; } ?> /> <label for="<?php echo $this->get_field_id('effect'); ?>2"><?php _e('Scroll Down', 'woo_gallery_widget'); ?></label><br />
            <input type="radio" id="<?php echo $this->get_field_id('effect'); ?>3" name="<?php echo $this->get_field_name('effect'); ?>" value="scrollLeft" <?php if($effect == 'scrollLeft'){ echo 'checked="checked"'; } ?> /> <label for="<?php echo $this->get_field_id('effect'); ?>3"><?php _e('Scroll Left', 'woo_gallery_widget'); ?></label><br />
            <input type="radio" id="<?php echo $this->get_field_id('effect'); ?>4" name="<?php echo $this->get_field_name('effect'); ?>" value="scrollRight" <?php if($effect == 'scrollRight'){ echo 'checked="checked"'; } ?> /> <label for="<?php echo $this->get_field_id('effect'); ?>4"><?php _e('Scroll Right', 'woo_gallery_widget'); ?></label><br />
            <input type="radio" id="<?php echo $this->get_field_id('effect'); ?>5" name="<?php echo $this->get_field_name('effect'); ?>" value="fade" <?php if($effect == 'fade'){ echo 'checked="checked"'; } ?> /> <label for="<?php echo $this->get_field_id('effect'); ?>5"><?php _e('Fade', 'woo_gallery_widget'); ?></label><br />
            </p>
            
            <fieldset id="woo_gallery_widget_upgrade_area"><legend><?php _e('Upgrade to','woo_gallery_widget'); ?> <a href="<?php echo WC_GALLERY_WIDGET_AUTHOR_URI; ?>" target="_blank"><?php _e('Pro Version', 'woo_gallery_widget'); ?></a> <?php _e('to activate', 'woo_gallery_widget'); ?></legend>
            
            <p><label for=""><strong><?php _e('Show Type:', 'woo_gallery_widget'); ?></strong></label><br />
            <input type="radio" id="show_type<?php echo $widget_id; ?>1" name="show_type<?php echo $widget_id; ?>" value="" checked="checked" disabled="disabled" /> <label for="show_type<?php echo $widget_id; ?>1"><?php _e('Category', 'woo_gallery_widget'); ?></label> &nbsp;&nbsp;&nbsp;
            <input type="radio" id="show_type<?php echo $widget_id; ?>2" name="show_type<?php echo $widget_id; ?>" value="" disabled="disabled" /> <label for="show_type<?php echo $widget_id; ?>2"><?php _e('Tag', 'woo_gallery_widget'); ?></label> &nbsp;&nbsp;&nbsp;
            <input type="radio" id="show_type<?php echo $widget_id; ?>3" name="show_type<?php echo $widget_id; ?>" value="" disabled="disabled" /> <label for="show_type<?php echo $widget_id; ?>3"><?php _e('On Sale', 'woo_gallery_widget'); ?></label>
            </p>
            <p><label for="timeout<?php echo $widget_id; ?>"><?php _e('Time between transitions:', 'woo_gallery_widget'); ?></label> <input class="" id="timeout<?php echo $widget_id; ?>" name="timeout<?php echo $widget_id; ?>" type="text" value="4" size="1" disabled="disabled" /> <?php _e('seconds', 'woo_gallery_widget'); ?></p>
            <p><label for="effect_speed<?php echo $widget_id; ?>"><?php _e('Transition effect Speed:', 'woo_gallery_widget'); ?></label> <input class="" id="effect_speed<?php echo $widget_id; ?>" name="effect_speed<?php echo $widget_id; ?>" type="text" value="1" size="1" disabled="disabled" /> <?php _e('seconds', 'woo_gallery_widget'); ?></p>
            <p><label for="number_products<?php echo $widget_id; ?>"><?php _e('Number of products to show:', 'woo_gallery_widget'); ?></label> <input class="" id="number_products<?php echo $widget_id; ?>" name="number_products<?php echo $widget_id; ?>" type="text" value="6" size="2" disabled="disabled" /><br /><span class="description"><?php _e('Enter -1 to show all products of the Category or Tag or On Sale', 'woo_gallery_widget'); ?></span></p>
            
            <p><label for="item_text<?php echo $widget_id; ?>"><?php _e('View this Product Link Text:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="item_text<?php echo $widget_id; ?>" name="item_text<?php echo $widget_id; ?>" type="text" value="<?php echo $item_text; ?>" disabled="disabled" /></p>
            <p><label for="category_text<?php echo $widget_id; ?>"><?php _e('Product Category Text Link:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="category_text<?php echo $widget_id; ?>" name="category_text<?php echo $widget_id; ?>" type="text" value="<?php echo $category_text; ?>" disabled="disabled" /></p>
            
            </fieldset>
<?php
	}
	
	function product_cycle_script() {
		wp_enqueue_style( 'woo-product-cycle-style', WC_GALLERY_WIDGET_CSS_URL . '/product_cycle_widget.css' );
		wp_enqueue_script( 'a3-cycle-script', WC_GALLERY_WIDGET_JS_URL . '/jquery.cycle.all.js', array(), false, true );
	}
}
?>