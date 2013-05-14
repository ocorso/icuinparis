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
		$show_type = $instance['show_type'];
		$category_id = $instance['category_id'];
		$tag_id = $instance['tag_id'];
		$image_height = intval($instance['image_height']);
		$auto_scroll = esc_attr($instance['auto_scroll']);
		$effect = esc_attr($instance['effect']);
		$timeout = intval($instance['timeout'])*1000;
		$effect_speed = intval($instance['effect_speed'])*1000;
		$number_products = $instance['number_products'];
		if ($number_products != '-1' && $number_products < 1) $number_products = 6;
		$item_text = esc_attr($instance['item_text']);
		$category_text = esc_attr($instance['category_text']);
		$tag_text = esc_attr($instance['tag_text']);
		
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo $this->items_cycle($widget_id, $show_type, $category_id, $tag_id, $image_height, $auto_scroll, $effect, $timeout, $effect_speed, $number_products, $item_text, $category_text, $tag_text);
		echo $after_widget;
	}
	
	function items_cycle($widget_id, $show_type, $category_id, $tag_id, $image_height = 140, $auto_scroll = 'no', $effect = 'scrollLeft', $timeout = 4000, $effect_speed = 1000, $number_products = '6', $item_text = '' , $category_text= '', $tag_text= '') {
		
		$id = rand(100, 100000);
		
		$all_html = '';
		if ($show_type == 'tag') {
			if ( $tag_id < 1) return;
			$all_html = '<div style="clear:both"></div><div class="product_cycle_category_container product_cycle_tag_container"><a class="category_widget_link tag_widget_link" href="'.get_term_link( (int) $tag_id, 'product_tag').'">'.$tag_text.'</a></div><div style="clear:both"></div>';
			$product_results = WC_Gallery_Widget_Functions::get_products_tag($tag_id, 'title menu_order', $number_products, 0);
		} elseif ($show_type == 'onsale') {
			$product_results = WC_Gallery_Widget_Functions::get_products_onsale('title menu_order', $number_products, 0);
		} else {
			if ( $category_id < 1) return;
			$all_html = '<div style="clear:both"></div><div class="product_cycle_category_container"><a class="category_widget_link" href="'.get_term_link( (int) $category_id, 'product_cat').'">'.$category_text.'</a></div><div style="clear:both"></div>';
			$product_results = WC_Gallery_Widget_Functions::get_products_cat($category_id, 'title menu_order', $number_products, 0);
		}
		
		if ( count($product_results) < 1) return;
		
		add_action( 'wp_footer', array('WC_Gallery_Cycle_Widget', 'product_cycle_script') );
		
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
                <?php 
				if ($show_type == 'onsale') { 
					$current_db_version = get_option( 'woocommerce_db_version', null );
					if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
						$product_onsale = new WC_Product($product->ID); 
					} else {
						$product_onsale = get_product($product->ID);
					}
					echo '<div class="product_cycle_price">'.$product_onsale->get_price_html().'</div><div style="clear:both"></div>'; 
				}
				?>
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
		<?php echo $all_html; ?>
        <?php
		$html = ob_get_clean();
		return $html;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['show_type'] = $new_instance['show_type'];
		$instance['category_id'] = $new_instance['category_id'];
		$instance['tag_id'] = $new_instance['tag_id'];
		$instance['image_height'] = intval($new_instance['image_height']);
		$instance['auto_scroll'] = esc_attr($new_instance['auto_scroll']);
		$instance['effect'] = esc_attr($new_instance['effect']);
		$instance['timeout'] = intval($new_instance['timeout']);
		$instance['effect_speed'] = intval($new_instance['effect_speed']);
		$instance['number_products'] = $new_instance['number_products'];
		$instance['item_text'] = esc_attr($new_instance['item_text']);
		$instance['category_text'] = esc_attr($new_instance['category_text']);
		$instance['tag_text'] = esc_attr($new_instance['tag_text']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'show_type' => 'category', 'image_height' => 140, 'auto_scroll' => 'no', 'effect' => 'scrollLeft', 'timeout' => 4, 'effect_speed' => 1, 'number_products' => 6, 'item_text' => __('View this Product', 'woo_gallery_widget'), 'category_text' => __('View all Products in this Category', 'woo_gallery_widget'), 'tag_text' => __('View all Products in this Tag', 'woo_gallery_widget') ) );
		
		$widget_id = str_replace('widget_product_cycle-','',$this->id);
		
		$title = esc_attr($instance['title']);
		$show_type = $instance['show_type'];
		$category_id = $instance['category_id'];
		$tag_id = $instance['tag_id'];
		$image_height = intval($instance['image_height']);
		$auto_scroll = esc_attr($instance['auto_scroll']);
		$effect = esc_attr($instance['effect']);
		$timeout = intval($instance['timeout']);
		$effect_speed = intval($instance['effect_speed']);
		$number_products = intval($instance['number_products']);
		$item_text = esc_attr($instance['item_text']);
		$category_text = esc_attr($instance['category_text']);
		$tag_text = esc_attr($instance['tag_text']);
?>
			<style>
			.cycle_category_dropdown, .cycle_tag_dropdown, .cycle_category_text, .cycle_tag_text { display:none; }
			</style>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
            <p><label for=""><strong><?php _e('Show Type:', 'woo_gallery_widget'); ?></strong></label><br />
            <input type="radio" class="show_type_<?php echo $widget_id; ?>" id="<?php echo $this->get_field_id('show_type'); ?>1" name="<?php echo $this->get_field_name('show_type'); ?>" value="category" checked="checked" onclick="javascript:document.getElementById('cycle_category_dropdown_<?php echo $widget_id; ?>').style.display='block';document.getElementById('cycle_tag_dropdown_<?php echo $widget_id; ?>').style.display='none';document.getElementById('cycle_category_text_<?php echo $widget_id; ?>').style.display='block';document.getElementById('cycle_tag_text_<?php echo $widget_id; ?>').style.display='none';" /> <label for="<?php echo $this->get_field_id('show_type'); ?>1"><?php _e('Category', 'woo_gallery_widget'); ?></label> &nbsp;&nbsp;&nbsp;
            <input type="radio" class="show_type_<?php echo $widget_id; ?>" id="<?php echo $this->get_field_id('show_type'); ?>2" name="<?php echo $this->get_field_name('show_type'); ?>" value="tag" <?php if($show_type == 'tag'){ echo 'checked="checked"'; } ?> onclick="javascript:document.getElementById('cycle_category_dropdown_<?php echo $widget_id; ?>').style.display='none';document.getElementById('cycle_tag_dropdown_<?php echo $widget_id; ?>').style.display='block';document.getElementById('cycle_category_text_<?php echo $widget_id; ?>').style.display='none';document.getElementById('cycle_tag_text_<?php echo $widget_id; ?>').style.display='block';" /> <label for="<?php echo $this->get_field_id('show_type'); ?>2"><?php _e('Tag', 'woo_gallery_widget'); ?></label> &nbsp;&nbsp;&nbsp;
            <input type="radio" class="show_type_<?php echo $widget_id; ?>" id="<?php echo $this->get_field_id('show_type'); ?>3" name="<?php echo $this->get_field_name('show_type'); ?>" value="onsale" <?php if($show_type == 'onsale'){ echo 'checked="checked"'; } ?> onclick="javascript:document.getElementById('cycle_category_dropdown_<?php echo $widget_id; ?>').style.display='none';document.getElementById('cycle_tag_dropdown_<?php echo $widget_id; ?>').style.display='none';document.getElementById('cycle_category_text_<?php echo $widget_id; ?>').style.display='none';document.getElementById('cycle_tag_text_<?php echo $widget_id; ?>').style.display='none';" /> <label for="<?php echo $this->get_field_id('show_type'); ?>3"><?php _e('On Sale', 'woo_gallery_widget'); ?></label>
            </p>
            <p id="cycle_category_dropdown_<?php echo $widget_id; ?>" class="cycle_category_dropdown" <?php if (!in_array($show_type, array('tag', 'onsale') ) ) { echo 'style="display:block"'; } ?> ><label for="<?php echo $this->get_field_id('category_id'); ?>"><?php _e('Category:', 'woo_gallery_widget'); ?></label> 
            <?php wp_dropdown_categories( array('orderby' => 'name', 'selected' => $category_id, 'name' => $this->get_field_name('category_id'), 'id' => $this->get_field_id('category_id'), 'class' => 'widefat', 'depth' => true, 'taxonomy' => 'product_cat') ); ?>
            </p>
            <p id="cycle_tag_dropdown_<?php echo $widget_id; ?>" class="cycle_tag_dropdown" <?php if ( $show_type == 'tag' ) { echo 'style="display:block"'; } ?> ><label for="<?php echo $this->get_field_id('tag_id'); ?>"><?php _e('Tag:', 'woo_gallery_widget'); ?></label> 
            <?php wp_dropdown_categories( array('orderby' => 'name', 'selected' => $tag_id, 'name' => $this->get_field_name('tag_id'), 'id' => $this->get_field_id('tag_id'), 'class' => 'widefat', 'depth' => true, 'taxonomy' => 'product_tag') ); ?>
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
            
            <p><label for="<?php echo $this->get_field_id('timeout'); ?>"><?php _e('Time between transitions:', 'woo_gallery_widget'); ?></label> <input class="" id="<?php echo $this->get_field_id('timeout'); ?>" name="<?php echo $this->get_field_name('timeout'); ?>" type="text" value="<?php echo $timeout; ?>" size="1" /> <?php _e('seconds', 'woo_gallery_widget'); ?></p>
            <p><label for="<?php echo $this->get_field_id('effect_speed'); ?>"><?php _e('Transition effect Speed:', 'woo_gallery_widget'); ?></label> <input class="" id="<?php echo $this->get_field_id('effect_speed'); ?>" name="<?php echo $this->get_field_name('effect_speed'); ?>" type="text" value="<?php echo $effect_speed; ?>" size="1" /> <?php _e('seconds', 'woo_gallery_widget'); ?></p>
            <p><label for="<?php echo $this->get_field_id('number_products'); ?>"><?php _e('Number of products to show:', 'woo_gallery_widget'); ?></label> <input class="" id="<?php echo $this->get_field_id('number_products'); ?>" name="<?php echo $this->get_field_name('number_products'); ?>" type="text" value="<?php echo $number_products; ?>" size="2" /><br /><span class="description"><?php _e('Enter -1 to show all products of the Category or Tag or On Sale', 'woo_gallery_widget'); ?></span></p>
            <p><label for="<?php echo $this->get_field_id('item_text'); ?>"><?php _e('View this Product Link Text:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('item_text'); ?>" name="<?php echo $this->get_field_name('item_text'); ?>" type="text" value="<?php echo $item_text; ?>" /></p>
            <p id="cycle_category_text_<?php echo $widget_id; ?>" class="cycle_category_text" <?php if (!in_array($show_type, array('tag', 'onsale') ) ) { echo 'style="display:block"'; } ?> ><label for="<?php echo $this->get_field_id('category_text'); ?>"><?php _e('Product Category Text Link:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('category_text'); ?>" name="<?php echo $this->get_field_name('category_text'); ?>" type="text" value="<?php echo $category_text; ?>" /></p>
            <p id="cycle_tag_text_<?php echo $widget_id; ?>" class="cycle_tag_text" <?php if ( $show_type == 'tag' ) { echo 'style="display:block"'; } ?> ><label for="<?php echo $this->get_field_id('tag_text'); ?>"><?php _e('Product Tag Text Link:', 'woo_gallery_widget'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('tag_text'); ?>" name="<?php echo $this->get_field_name('tag_text'); ?>" type="text" value="<?php echo $tag_text; ?>" /></p>
<?php
	}
	
	function product_cycle_script() {
		wp_enqueue_style( 'woo-product-cycle-style', WC_GALLERY_WIDGET_CSS_URL . '/product_cycle_widget.css' );
		wp_enqueue_script( 'a3-cycle-script', WC_GALLERY_WIDGET_JS_URL . '/jquery.cycle.all.js', array(), false, true );
	}
}
?>