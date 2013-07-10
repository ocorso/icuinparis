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
?>

<?php
	if ( have_posts() ) : while ( have_posts() ) : the_post(); 
?>                                                           
    	
<div id="headline" class="row">
	<h1><?php the_title(); ?></h1>
</div>
<div id="description" class="row">
	<p class="span12">Wherever we go, whomever we meet, we are inspired. The Creative Community page is our digital journal. Are you in the creative industry and have something to share? An expo or event you think we'll enjoy? <a href="mailto:submissions@icuinparis.com">Tell us</a>.</p>
</div>
		
<div id="content">		
	<?php the_content(); ?>
</div><!-- /#content -->

<?php endwhile; endif; ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>