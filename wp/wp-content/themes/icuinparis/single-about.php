<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Single Post Template
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a post ('post' post_type).
 * @link http://codex.wordpress.org/Post_Types#Post
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;
/**
 * The Variables
 *
 * Template Name: About
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */
	
	$settings = array(
					'thumb_single' => 'false', 
					'single_w' => 200, 
					'single_h' => 150, 
					'thumb_single_align' => 'alignright'
					);
					
	$settings = woo_get_dynamic_values( $settings );
?>
</div>
	<header>
    	<img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/icu-logo-black.png"/>
	</header>
    <div id="content" class="container">
        <?php
        	if ( have_posts() ) { $count = 0;
        		while ( have_posts() ) { the_post(); $count++;
        ?>                                                           
            <article <?php post_class(); ?>>
				
                <section class="entry">
                	<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) ); ?>
               	</section><!-- /.entry -->

				<?php edit_post_link( __( '{ Edit }', 'woothemes' ), '<span class="small">', '</span>' ); ?>
                
            </article><!-- /.post -->
            
            <?php
            	// Determine wether or not to display comments here, based on "Theme Options".
            	if ( isset( $woo_options['woo_comments'] ) && in_array( $woo_options['woo_comments'], array( 'page', 'both' ) ) ) {
            		comments_template();
            	}

				} // End WHILE Loop
			} else {
		?>
			<article <?php post_class(); ?>>
            	<p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->
        <?php } // End IF Statement ?>  

<section id="footer_widgets" class="container">
		<div class="row">
			<?php 
			$args = array( 	'post_type' => 'footer-widget', 
							'posts_per_page' => 4,
							'order'           => 'ASC' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post();
			
				echo '<div class="widget span3">';
					the_content();
				echo '</div>';
			endwhile;
			?>
		</div> <!-- test row -->

	</section><!-- /#footer-widgets  -->

<?php get_footer(); ?>