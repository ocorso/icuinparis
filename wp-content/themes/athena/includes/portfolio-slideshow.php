<?php 
/**
 * Homepage Portfolio Panel
 */
 
/**
 * The Variables
 *
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */
	
$settings = array(
				'portfolio_area_entries' => 3,
				'portfolio_area_title' => '',
				'portfolio_area_message' => '',
				'portfolio_area_link_text' => __( 'Go to Portfolio', 'woothemes' ),
				'portfolio_area_link_URL' => '',
				'portfolio_linkto' => 'post',
				'portfolio_area_gallery_term' => '0'
				);
				
$settings = woo_get_dynamic_values( $settings );
$orderby = 'date';
	
$query_args = array(	
    		'post_type' => 'portfolio', 
    		'posts_per_page' =>  intval( $settings['portfolio_area_entries'] ), 
    		'suppress_filters' => 0,
    		'order' => 'DESC', 
    		'orderby' => $orderby
    	);
    	
if ( 0 < intval( $settings['portfolio_area_gallery_term'] ) ) { 
	$query_args['tax_query'] = 	array(
										array(
													'taxonomy' => 'portfolio-gallery',
													'field' => 'id',
													'terms' => $settings['portfolio_area_gallery_term']
												)
									);
}

// The Query
$the_query = new WP_Query( $query_args );
$post_count = $the_query->post_count;
$hide_pagination = '';
if ( 3 >= $post_count ) { $hide_pagination = ' hide'; }
if ( $the_query->have_posts() ) {
?>					
<div id="portfolio-slideshow">

		<div class="slideshow col-full">

			<aside>
				<?php if ( $settings['portfolio_area_title'] != '' ) { ?>
				<header>
					<h1><?php echo $settings['portfolio_area_title']; ?></h1>
				</header><!--/header-->
				<?php } ?>
				<?php if ( $settings['portfolio_area_message'] != '' || $settings['portfolio_area_link_text'] != '' ) { ?>
				<div class="entry">
					<p><?php echo $settings['portfolio_area_message']; ?></p>
					<?php if ( $settings['portfolio_area_link_URL'] != '' ) { ?><a href="<?php echo esc_url( $settings['portfolio_area_link_URL'] ); ?>" class="portfolio-btn"><?php } ?><?php echo $settings['portfolio_area_link_text']; ?><?php if ( $settings['portfolio_area_link_URL'] != '' ) { ?></a><?php } ?>
				</div><!--/.entry-->
				<?php } ?>
			</aside><!--/aside-->

			<div class="flexslider">
				<ul class="slides">
					<?php while ( $the_query->have_posts() ) { $the_query->the_post(); global $post; ?>
					<li>
						<article class="portfolio-item">
							<?php 
							$rel = '';
						
    						$custom_url = get_post_meta( $post->ID, '_portfolio_url', true ); 
    						if ( $custom_url != '' )
    							$permalink = $custom_url;
    						else
    							$permalink = get_permalink();
							
							if ( $settings['portfolio_linkto'] == 'lightbox' ) {
								if ( $custom_url == '' )
									$permalink = woo_image( 'return=true&link=url' );
								$rel = ' rel="lightbox[\'home\']"';
							}
							
							$lightbox_url = get_post_meta( $post->ID, 'lightbox-url', true );
							
							if ( isset($lightbox_url) && $lightbox_url != '' ) {
								if ( $custom_url == '' )
									$permalink = $lightbox_url;
							}
						
							$image = woo_image( 'width=282&link=img&return=true&noheight=true' ); 
						
							if ( ! $image ) {
								$image = '<img src="' . get_template_directory_uri() . '/images/temp-portfolio.png" alt="" />';
								$rel = '';
							}
							?>
							<a href="<?php echo esc_url( $permalink ); ?>" class="item"<?php echo $rel; ?>>
								<?php echo $image; ?>
							</a>
							<div class="mask">
								<a href="<?php echo esc_url( $permalink ); ?>" title="<?php the_title_attribute(); ?>">
									<span>
										<span class="title"><?php the_title(); ?></span>
										<span class="content"><?php echo get_the_excerpt(); ?></span>
									</span>
								</a>
							</div><!--/.mask-->
						</article><!--/article-->
					</li><!--/li-->
					<?php } // End While Loop ?>
				</ul><!--/.slides-->

			</div>
			<div class="flexslider-controls-container<?php echo esc_attr( $hide_pagination ); ?>"></div><!--/.flexslider-controls-container-->
		</div>

<script type="text/javascript" charset="utf-8"> 
jQuery( window ).load( function() {
	var portfolioArgs = {
		animation: 'slide', 
		animationLoop: true, 
		itemWidth: 238, 
		itemMargin: 40, 
		maxItems: 3, 
		move: 1, 
		controlsContainer: '.flexslider-controls-container'
	};

	if ( 768 > jQuery( window ).width() ) {
		portfolioArgs.maxItems = 1;
	}

	jQuery( '#portfolio-slideshow .flexslider' ).flexslider( portfolioArgs );

	if ( jQuery( '#portfolio-slideshow .flexslider .portfolio-item' ).length <= 3 ) {
		jQuery( '#portfolio-slideshow .flexslider-controls-container' ).hide();
	}
}); 
</script>

</div><!--/#portfolio-slideshow-->
<?php } // End IF Statement ?>