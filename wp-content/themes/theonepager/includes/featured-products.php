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
					'homepage_number_of_products' => 4,
					'homepage_products_area_heading' => '', 
					'homepage_products_area_title' => '', 
					'homepage_featured_products_columns' => 4
					);
					
	$settings = woo_get_dynamic_values( $settings );
	
?>
<section id="home-shop" class="widget_woo_component woocommerce-columns-<?php echo esc_attr( intval( $settings['homepage_featured_products_columns'] ) ); ?> fix">

	<div class="col-full">
	
		<?php if ( '' != $settings['homepage_products_area_heading'] ) { ?><span class="heading"><?php echo $settings['homepage_products_area_heading']; ?></span><?php } ?>

		<?php if ( '' != $settings['homepage_products_area_title'] ) { ?><h2 class="widget-title"><?php echo esc_html( $settings['homepage_products_area_title'] ); ?></h2><?php } ?>
		
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
		
		<ul class="products">
	<?php
			$number_of_products = $settings['homepage_number_of_products'];
			$args = array( 
				'post_type' => 'product', 
				'posts_per_page' => intval( $number_of_products ), 
				'meta_query' => array(
									'relation' => 'AND', 
									array(
										'key' => '_visibility',
										'value' => array( 'catalog', 'visible' ),
										'compare' => 'IN'
									), 
									array(
										'key' => '_featured',
										'value' => array( 'yes' )
									)
								) 
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
			
					<?php woocommerce_get_template_part( 'content', 'product' ); ?>
	
			<?php endwhile; ?>
		</ul><!--/ul.recent-->	
		
		<?php do_action('woocommerce_after_shop_loop'); ?>
	
	</div><!-- /.col-full -->
		    			    		
</section>

<?php wp_reset_postdata(); ?>