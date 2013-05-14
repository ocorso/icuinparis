<?php
/**
 * Homepage Shop Panel
 */
 	
	/**
 	* The Variables
 	*
 	* Setup default variables, overriding them if the "Theme Options" have been saved.
 	*/
	
	global $woocommerce, $post;
	
	$settings = array(
					'homepage_hero_product_id' => 0,
					'homepage_hero_product_heading' => ''
					);
					
	$settings = woo_get_dynamic_values( $settings );
	if ( 0 < intval( $settings['homepage_hero_product_id'] ) ) {
?>
<section id="home-hero" class="widget_woo_component">

	<div class="col-full">
	
		<?php
			$args = array( 
				'post_type' => 'product', 
				'post__in' => array( intval( $settings['homepage_hero_product_id'] ) ) 
			);
	
			$first_or_last = 'first';
			$loop = new WP_Query( $args );
			$query_count = $loop->post_count;
			$count = 0;

			while ( $loop->have_posts() ) : $loop->the_post(); $count++;
	
			if ( function_exists( 'get_product' ) ) {
				$_product = get_product( $loop->post->ID );
			} else { 
				$_product = new WC_Product( $loop->post->ID );
			}
		?>

		<?php if ( '' != $settings['homepage_hero_product_heading'] ) { ?><span class="heading"><?php echo $settings['homepage_hero_product_heading']; ?></span><?php } ?>
		<h2 class="widget-title"><a href="<?php echo esc_url( get_permalink( $loop->post->ID ) ); ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>"><?php echo get_the_title(); ?></a></h2>
	
		<div class="hero-product">
					
			<div class="hero-image">
			
			    <?php woocommerce_show_product_sale_flash( $post, $_product ); ?>
			
			    <?php if (has_post_thumbnail( $loop->post->ID )) { ?>
			    	<a href="<?php echo esc_url( get_permalink( $loop->post->ID ) ); ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">
			    		<?php echo get_the_post_thumbnail( $loop->post->ID, 'shop_single' ); ?>
			    	</a>
			    <?php } 
			    	else {
			    		echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" />';
			    	}
			    ?>
			    
			    <span class="price-wrap"><a href="<?php echo esc_url( get_permalink( $loop->post->ID ) ); ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>"><span class="price"><strong><?php echo $_product->get_price_html(); ?></strong></span></a></span>
			    
			</div>
			
			<div class="hero-excerpt">

				<?php woocommerce_template_single_excerpt(); ?>
				
				<a class="button details" href="<?php echo esc_url( get_permalink( $loop->post->ID ) ); ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>"><?php _e('View Details' ,'woothemes'); ?></a>
				
				<?php woocommerce_template_loop_add_to_cart( $loop->post, $_product ); ?>
			
			</div>
	
			<?php endwhile; ?>
		</div><!--/ul.recent-->		  
	
	</div><!-- /.col-full -->
	  			    		
</section>
<?php wp_reset_postdata(); ?>
<?php } ?>