<?php
// File Security Check
if ( ! function_exists( 'wp' ) && ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}
?><?php
/**
 * Index Template
 *
 * Here we setup all logic and XHTML that is required for the index template, used as both the homepage
 * and as a fallback template, if a more appropriate template file doesn't exist for a specific context.
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;

	$settings = array(
				'homepage_enable_features' => 'true',
				'homepage_enable_posts_timeline' => 'true',
				'homepage_enable_testimonials' => 'true',
				'homepage_enable_hero_product' => 'true',
				'homepage_enable_featured_products' => 'true',
				'homepage_enable_content' => 'true',
				'homepage_enable_contact' => 'true',
				'homepage_content_type' => 'posts',
				'homepage_number_of_features' => 4,
				'homepage_number_of_testimonials' => 4,
				'homepage_features_area_heading' => sprintf( __( 'Features', 'woothemes' ), get_bloginfo( 'name' ) ),
				'homepage_testimonials_area_heading' => sprintf( __( 'Some of One Pagers unique features', 'woothemes' ), get_bloginfo( 'name' ) ),
				'homepage_features_area_title' => sprintf( __( 'Testimonials', 'woothemes' ), get_bloginfo( 'name' ) ),
				'homepage_testimonials_area_title' => sprintf( __( 'What people think of %s', 'woothemes' ), get_bloginfo( 'name' ) ),
				'homepage_posts_timline_tag' => ''
				);

	$settings = woo_get_dynamic_values( $settings );

?>
	<div id="content" class="home-widgets">
		<?php woo_featured_slider_loader(); ?>
		<?php
			if ( is_home() && ! dynamic_sidebar( 'homepage' ) ) {

		    	if ( 'true' == $settings['homepage_enable_features'] ) {
					$args = array( 'title' => $settings['homepage_features_area_title'], 'size' => 235, 'per_row' => 4, 'limit' => $settings['homepage_number_of_features'] );
					$args['before'] = '<div id="features" class="widget_woo_component widget_woothemes_features"><div class="col-full">';
					$args['after'] = '</div></div>';
					$args['before_title'] = '<span class="heading">' . $settings['homepage_features_area_heading'] . '</span><h2 class="widget-title">';
					$args['after_title'] = '</h2>';

					do_action( 'woothemes_features', $args );
				}

		    	if ( 'true' == $settings['homepage_enable_posts_timeline'] ) {
		    		get_template_part( 'includes/posts-timeline' );
		    	}

		    	if ( is_woocommerce_activated() && 'true' == $settings['homepage_enable_hero_product'] ) {
		    		get_template_part( 'includes/hero-product' );
		    	}

		    	if ( is_woocommerce_activated() && 'true' == $settings['homepage_enable_featured_products'] ) {
		    		get_template_part( 'includes/featured-products' );
		    	}

		    	if ( 'true' == $settings['homepage_enable_testimonials'] ) {
					$args = array( 'title' => esc_html( stripslashes( $settings['homepage_testimonials_area_title'] ) ), 'size' => 80, 'per_row' => 4, 'limit' => $settings['homepage_number_of_testimonials'] );
					$args['before'] = '<div id="testimonials" class="widget_woo_component widget_woothemes_testimonials"><div class="col-full">';
					$args['after'] = '</div></div>';
					$args['before_title'] = '<span class="heading">' . esc_html( stripslashes( $settings['homepage_testimonials_area_heading'] ) ) . '</span><h2 class="widget-title">';
					$args['after_title'] = '</h2>';

					do_action( 'woothemes_testimonials', $args );
				}

		    	if ( 'true' == $settings['homepage_enable_content'] ) {
		    		switch ( $settings['homepage_content_type'] ) {
		    			case 'page':
		    			get_template_part( 'includes/specific-page-content' );
		    			break;

		    			case 'posts':
		    			default:
		    			get_template_part( 'includes/blog-posts' );
		    			break;
		    		}
		    	}

		    	if ( 'true' == $settings['homepage_enable_contact'] ) {
		    		get_template_part( 'includes/contact-area' );
		    	}
		    }
		?>

	</div><!-- /#home-widgets -->

<?php get_footer(); ?>