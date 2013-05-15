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
 * Template Name: Designers
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */
	
	$settings = array(
					'thumb_single' => 'false', 
					'single_w' => 234, 
					'single_h' => 340, 
					'thumb_single_align' => 'alignright'
					);
					
	$settings = woo_get_dynamic_values( $settings );
?>
        <div id="content" class="page col-full">
    
    	<?php woo_main_before(); ?>
    	
		<section id="main" class="col-left"> 			

        <?php
        	if ( have_posts() ) { $count = 0;
        		while ( have_posts() ) { the_post(); $count++;
        ?>                                                           
            <article <?php post_class(); ?>>
            	
            	<header>
			    	<h1><?php the_title(); ?></h1>
			    	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam ut dui turpis, in varius mi. Fusce fermentum aliquam elit, id viverra diam pellentesque a. Nullam ac tortor non tellus fermentum porttitor. Nullam vel tempor nibh. Cras vitae urna sem, sed dictum erat. Suspendisse potenti. Duis lobortis mauris eget ante mollis eleifend. Nam ornare vulputate nunc, et aliquet nisi eleifend dignissim. Vivamus tincidunt libero quis nunc feugiat non tincidunt mauris lacinia. In et hendrerit mi. Phasellus convallis arcu feugiat urna molestie quis dignissim mauris tincidunt. Proin a sapien et sem pretium ullamcorper.</p>
				</header>
				
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
        
		</section><!-- /#main -->
		
		<?php woo_main_after(); ?>

        <?php get_sidebar(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>