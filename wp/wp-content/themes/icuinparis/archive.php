<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>
    
    <div id="headline" class="row">
        <h1>Creative Community</h1>
    </div>
    <div id="description" class="row">
        <p>Wherever we go, whomever we meet, we are inspired. The Creative Community page is our digital journal. Are you in the creative industry and have something to share? An expo or event you think we'll enjoy? <a href="mailto:submissions@icuinparis.com">Tell us</a>.</p>
    </div>
     <?php get_sidebar(); ?>


		<?php if (have_posts()) : $count = 0; ?>
        
         

    <div id="posts" class="row">
        	
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); $count++; ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content' );
				?>

			<?php endwhile; ?>
            
	        <?php else: ?>
	        
	            <article <?php post_class(); ?>>
	                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
	            </article><!-- /.post -->
	        
	        <?php endif; ?>  


    </div><!-- /#post_content -->
    <div id="pagination" class="row pull-right">
	<?php woo_pagenav(); ?>
	</div>
<?php get_footer(); ?>