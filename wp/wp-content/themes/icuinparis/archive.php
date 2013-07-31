<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>
    
    <div id="headline" class="row">
        <h1>Creative Community</h1>
    </div>

    <div id="post_content" class="row">
     <?php get_sidebar(); ?>

		<?php if (have_posts()) : $count = 0; ?>
        
            <?php if (is_category()) { ?>
        	<header class="archive-header">
        		<h2 class="fl"><?php _e( 'Archive', 'woothemes' ); ?> | <?php single_cat_title( '', true ); ?></h2> 
        	</header>        
        
            <?php } elseif (is_day()) { ?>
            <header class="archive-header">
            	<h2><?php _e( 'Archive', 'woothemes' ); ?> | <?php the_time( get_option( 'date_format' ) ); ?></h2>
            </header>

            <?php } elseif (is_month()) { ?>
            <header class="archive-header">
            	<h2><?php _e( 'Archive', 'woothemes' ); ?> | <?php the_time( 'F, Y' ); ?></h2>
            </header>

            <?php } elseif (is_year()) { ?>
            <header class="archive-header">
            	<h2><?php _e( 'Archive', 'woothemes' ); ?> | <?php the_time( 'Y' ); ?></h2>
            </header>

            <?php } elseif (is_author()) { ?>
            <header class="archive-header">
            	<h2><?php _e( 'Archive by Author', 'woothemes' ); ?></h2>
            </header>

            <?php } elseif (is_tag()) { ?>
            <header class="archive-header">
            	<h2><?php _e( 'Tag Archives:', 'woothemes' ); ?> <?php single_tag_title( '', true ); ?></h2>
            </header>
            
            <?php } ?>


        	
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); $count++; ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
				?>

			<?php endwhile; ?>
            
	        <?php else: ?>
	        
	            <article <?php post_class(); ?>>
	                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
	            </article><!-- /.post -->
	        
	        <?php endif; ?>  
	        
	        <?php woo_loop_after(); ?>
    
			<?php woo_pagenav(); ?>
                
		</section><!-- /#main -->
		
		<?php woo_main_after(); ?>

   

    </div><!-- /#content -->
		
<?php get_footer(); ?>