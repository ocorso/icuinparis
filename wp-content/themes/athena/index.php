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
	
	$settings = array(
					'custom_intro_message' => 'true',
					'portfolio_area' => 'true', 
					'shop_area' => 'true'
					);
					
	$settings = woo_get_dynamic_values( $settings );
	if ( get_query_var( 'page' ) > 1 ) { $paged = get_query_var( 'page' ); } elseif ( get_query_var( 'paged' ) > 1 ) { $paged = get_query_var( 'paged' ); } else { $paged = 1; } 
?>

    <div id="content">
    
    	<?php woo_main_before(); ?>

			<?php
				if ( ! dynamic_sidebar( 'homepage' ) ) {
					if ( 1 == $paged ) {
						// Output the Intro Message	
						if ( 'true' == $settings['custom_intro_message'] ) { get_template_part( 'includes/intro-message' ); }
				
						// Output the Portfolio Area	
						if ( 'true' == $settings['portfolio_area'] ) { get_template_part( 'includes/portfolio-slideshow' ); }

						// Output the Products Slider	
						if ( 'true' == $settings['shop_area'] && is_woocommerce_activated() ) { get_template_part( 'includes/home-shop' ); }
					}
				}
			?>
		
		<?php woo_main_after(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>