<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The default template for displaying content
 */

	global $woo_options;
 
/**
 * The Variables
 *
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */

 	$settings = array(
					'thumb_w' => 340, 
					'thumb_h' => 266, 
				//	'thumb_align' => 'alignleft'
					);
					
	$settings = woo_get_dynamic_values( $settings );
 
?>

	<article class="post span4">

		<section class="popup">
			Posted: <?php the_time('m/j/y g:i A'); ?>
			<?php if ( isset( $woo_options['woo_post_content'] ) && $woo_options['woo_post_content'] == 'content' ) { the_content( __( 'Continue Reading &rarr;', 'woothemes' ) ); } else { the_excerpt(); } ?>
		</section>
		<div class="article-inner poopy">
			<?php 
			if ( isset( $woo_options['woo_post_content'] ) && $woo_options['woo_post_content'] != 'content' ) 
				woo_image( 'width=' . $settings['thumb_w'] . '&height=' . $settings['thumb_h'] . '&class=thumbnail ' . $settings['thumb_align'] );
		    ?>
			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>

		</div><!-- .article-inner -->

	</article><!-- /.post -->