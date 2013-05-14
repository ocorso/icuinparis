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
					'thumb_w' => 730,
					'thumb_h' => 210,
					'thumb_align' => 'alignleft',
					'shop_area_entries' => 8,
					'shop_area_title' => ''
					);

	$settings = woo_get_dynamic_values( $settings );

?>
			<section id="home-shop" class="minor flexslider fix">

				<div class="col-full">

					<header>
						<?php if ( '' != $settings['shop_area_title'] ) { ?>
							<h1><?php echo $settings['shop_area_title']; ?></h1>
						<?php } ?>
						<?php
							if ( function_exists( 'athena_print_minicart' ) ) {
								athena_print_minicart();
							}
						?>
					</header>


	    			<div class="flex-direction-nav"></div><!--/.flex-direction-nav-->
	    			<div class="fix"></div>

	    			<ul class="slides products">

						<?php
						$number_of_products = $settings['shop_area_entries'];
						$args = array(
							'post_type' => 'product',
							'posts_per_page' => intval( $number_of_products ),
							'meta_query' => array( array(
								'key' => '_visibility',
								'value' => array('catalog', 'visible'),
								'compare' => 'IN'
								))
						);

						$first_or_last = 'first';
						$loop = new WP_Query( $args );
						$query_count = $loop->post_count;
						$count = 0;
						?>

						<li class="slide">

						<?php

						while ( $loop->have_posts() ) : $loop->the_post(); $count++;
						if ( function_exists( 'get_product' ) ) {
							$_product = get_product( $loop->post->ID );
						} else {
							$_product = new WC_Product( $loop->post->ID );
						}

						?>

								<div class="product">

									<?php woocommerce_show_product_sale_flash( $post, $_product ); ?>

									<?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" />'; ?>

									<div class="product-hover">

										<a href="<?php echo esc_url( get_permalink( $loop->post->ID ) ); ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>" class="product-shot">

											<span class="title"><?php echo get_the_title(); ?></span>
											<span class="description"><?php echo woo_text_trim(get_the_excerpt(), 10); ?></span>

											<span class="price"><?php echo $_product->get_price_html(); ?></span>
											<?php woocommerce_template_loop_add_to_cart( $loop->post, $_product ); ?>

										</a>

									</div><!--/.product-hover-->

								</div><!--/.product-->

								<?php if ($count % 4 == 0 && $count != $query_count ): ?></li><li class="slide"><?php endif; ?>

						<?php endwhile; ?>

						</li>

					</ul><!--/ul.recent-->

				</div><!--/.col-full-->

    		</section>

    		<script type="text/javascript">
    			if ( jQuery( window ).width() > 768 ) {
					jQuery(window).load(function() {
						jQuery('#home-shop .col-full').flexslider({
							controlsContainer: "#home-shop .flex-direction-nav",
							animation: "slide",
						    animationLoop: false,
						    itemWidth: 960,
						    controlNav: false,
						    maxItems: 1,
						    move: 1
						});
					});
				} else {
					jQuery('#home-shop').addClass("mobile");
				}
			</script>
    		<?php wp_reset_postdata(); ?>