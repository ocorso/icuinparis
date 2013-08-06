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
 * Template Name: By Designer
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */
	
?>

<?php
	if ( have_posts() ) : while ( have_posts() ) : the_post(); 
?>                                                           
    	<div id="headline" class="row">
    		<h1>Shop By Designer</h1>
    	</div>
    	<div id="description" class="row">
    		<p><?= the_content(); ?></p>
  		</div>
  		
<div id="content" class="row">	

			<?php 
			$args = array( 	'post_type' => 'designers', 
							'posts_per_page' => 25,
							'order'           => 'ASC' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post();
			
				
					get_template_part( 'content', 'designer' );
                                                
				
			endwhile;
			?>
</div><!-- /#content -->
	<?php endwhile; endif; ?>
		         
<div class="submit">
	<p>If you are a designer that would like to be featured on ICU in Paris, please fill out this
	<a href="<?= bloginfo('url'); ?>/store/designs" title="Submit Your Designs">Designer Submission Form</a> and we will get back to you shortly.</p>	
</div>
<?php get_footer(); ?>