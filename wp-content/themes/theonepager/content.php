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
					'thumb_w' => 100,
					'thumb_h' => 100,
					'thumb_align' => 'alignleft',
					'post_content' => 'excerpt',
					'comments' => 'both'
					);

	$settings = woo_get_dynamic_values( $settings );

?>

	<article <?php post_class(); ?>>

		<header>
			<h1><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
			<span class="post-date-author"><strong class="post-date"><?php the_time( get_option( 'date_format' ) ); ?></strong> by <strong class="post-author"><?php the_author_posts_link(); ?></strong></span>
			<span class="post-category"><?php the_category( ' ' ); ?></span>
		</header>

		<?php
	    	if ( 'content' != $settings['post_content'] ) {
	    		woo_image( 'width=' . $settings['thumb_w'] . '&height=' . $settings['thumb_h'] . '&class=thumbnail ' . $settings['thumb_align'] );
	    	}
	    ?>

		<section class="entry">
		<?php if ( 'content' == $settings['post_content'] ) { the_content( __( 'Continue Reading &rarr;', 'woothemes' ) ); } else { the_excerpt(); } ?>
		</section>

		<?php if ( 'excerpt' == $settings['post_content'] ) { ?>
		<footer class="post-more">
			<?php
				if ( in_array( $settings['comments'], array( get_post_type(), 'both' ) ) && comments_open() ) {
			?>
			<span class="comments">
			<?php
				comments_popup_link( __( 'Leave a comment', 'woothemes' ), __( '1 Comment', 'woothemes' ), __( '% Comments', 'woothemes' ) );
			?>
			</span>
			<span class="post-more-sep">&bull;</span>
			<?php } ?>
			<span class="read-more"><a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Continue Reading &rarr;', 'woothemes' ); ?>"><?php _e( 'Continue Reading &rarr;', 'woothemes' ); ?></a></span>
		</footer>
		<?php } ?>

	</article><!-- /.post -->