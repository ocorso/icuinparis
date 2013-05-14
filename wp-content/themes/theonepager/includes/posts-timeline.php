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
				'homepage_posts_timeline_tag' => 0,
				'homepage_posts_timeline_heading' => '',
				'homepage_posts_timeline_title' => '',
				);
					
	$settings = woo_get_dynamic_values( $settings );

	$args = array( 'post_type' => 'post', 'posts_per_page' => 3 );
	if ( 0 < intval( $settings['homepage_posts_timeline_tag'] ) ) {
		$args['tax_query'] = array(
									array( 'taxonomy' => 'post_tag', 'field' => 'id', 'terms' => $settings['homepage_posts_timeline_tag'] )
								);
	}
	$query = new WP_Query( $args );
?>

<div id="posts-timeline" class="widget_woo_component">
	
	<div class="col-full">
	
		<span class="heading"><?php echo esc_html( $settings['homepage_posts_timeline_heading'] ); ?></span>

		<h2 class="widget-title"><?php echo esc_html( $settings['homepage_posts_timeline_title'] ); ?></h2>
		<?php
		    if ( $query->have_posts() ) : $count = 0;
				while ( $query->have_posts() ) : $query->the_post(); $count++; ?>
		
		    	<a class="timeline-post" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
	
					<span class="date"><?php the_time( get_option( 'date_format' ) ); ?></span>
					<span class="title"><?php the_title(); ?></span>
	
				</a>
		
		    <?php endwhile; ?>
		
		<?php else : ?>
		
		    <article <?php post_class(); ?>>
		        <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
		    </article><!-- /.post -->
		
		<?php endif; wp_reset_postdata(); ?>
	
	</div>
	
</div>