<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: Tags
 *
 * The tags page template displays a user-friendly tag cloud of the
 * post tags used on your website.
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options; 
 get_header();
?>
       
    <div id="content" class="page col-full">
    
    	<?php woo_main_before(); ?>
    	
		<section id="main" class="fullwidth">
                                                                        
            <article <?php post_class(); ?>>
				
				<header>
					<h1><?php the_title(); ?></h1>
				</header>
                
	            <?php if ( have_posts() ) { the_post(); ?>
            	<section class="entry">
            		<?php the_content(); ?>
            	</section>	            	
	            <?php } ?>  
	            
                <div class="tag_cloud">
        			<?php wp_tag_cloud( 'number=0' ); ?>
    			</div><!--/.tag-cloud-->

            </article><!-- /.post -->
        
		</section><!-- /#main -->
		
		<?php woo_main_after(); ?>
		
    </div><!-- /#content -->
		
<?php get_footer(); ?>