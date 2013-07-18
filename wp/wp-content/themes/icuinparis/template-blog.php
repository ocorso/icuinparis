<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template Name: ICU Blog
 *
 * The blog page template displays the "blog-style" template on a sub-page. 
 *
 * @package ICU
 * @subpackage Template
 */

 global $woo_options;
 get_header();
 
?>
    <!-- #content Starts -->
    <div id="headline" class="row">
        <h1>Creative Community</h1>
    </div>
    <div id="description" class="row">
        <p class="span12">Wherever we go, whomever we meet, we are inspired. The Creative Community page is our digital journal. Are you in the creative industry and have something to share? An expo or event you think we'll enjoy? <a href="mailto:submissions@icuinparis.com">Tell us</a>.</p>
    </div>
    <?php get_sidebar(); ?>

    <div id="posts" class="row">
    
        <?php woo_main_before(); ?>
        
        <section id="main" class="col-left pooper">       
		
		<?php woo_loop_before(); ?>

        <?php
        	if ( get_query_var( 'paged') ) { $paged = get_query_var( 'paged' ); } elseif ( get_query_var( 'page') ) { $paged = get_query_var( 'page' ); } else { $paged = 1; }
        	
        	$query_args = array(
        						'post_type' => 'post', 
        						'paged' => $paged
        					);
        	
        	$query_args = apply_filters( 'woo_blog_template_query_args', $query_args ); // Do not remove. Used to exclude categories from displaying here.
        	
        	remove_filter( 'pre_get_posts', 'woo_exclude_categories_homepage' );
        	
        	query_posts( $query_args );
        	
        	if ( have_posts() ) {
        		$count = 0;
        		while ( have_posts() ) { the_post(); $count++;
        ?>                                                            
           
                <?php get_template_part( 'content', 'post' ); ?>
                                                
        <?php
        		} // End WHILE Loop
        	
        	} else {
        ?>
            <article <?php post_class(); ?>>
                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->
        <?php } // End IF Statement ?> 
        
        <?php woo_loop_after(); ?> 
    
        <?php woo_pagenav(); ?>
		<?php wp_reset_query(); ?>                

        </section><!-- /#main -->
        
        <?php woo_main_after(); ?>
            
		
    </div><!-- /#content -->    
		
<?php get_footer(); ?>