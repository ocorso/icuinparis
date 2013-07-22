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
 * Template Name: Wholesale
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */
	
?>

<?php
	if ( have_posts() ) : while ( have_posts() ) : the_post(); 
?>                                                           
    	<div id="headline" class="row">
    		<h1><?php the_title(); ?></h1>
    	</div>
    	<div id="description" class="row">
	    	<p class="span12">Showroom ICU is the business-to-business division of the ICU e-shop. We represent a curated selection of designers for international retail distribution. To receive look books and linesheets for any of the designers listed below, <a title="Christan Summers" href="mailto:christan@icuinparis.com">please contact us</a>. </p>
  		</div>
  		
<div id="content">	
    	<?php the_content(); ?>
</div><!-- /#content -->
		         
	<?php endwhile; endif; ?>

		
<?php get_footer(); ?>