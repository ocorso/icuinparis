<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The default template for displaying content
 */

	global $woo_options;
?>

	<article <?php post_class(); ?>>

		<header>
			<h1><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
			<span class="post-date-author"><strong class="post-date"><?php the_time( get_option( 'date_format' ) ); ?></strong> by <strong class="post-author"><?php the_author_posts_link(); ?></strong></span>
			<span class="post-category"><?php the_category(' ') ?></span>
		</header>

		<?php
	    	if ( isset( $woo_options['woo_post_content'] ) && $woo_options['woo_post_content'] != 'content' ) {
	    		woo_image( 'width=100&height=100&class=thumbnail alignleft' );
	    	}
	    ?>

		<section class="entry">
			<?php the_excerpt(); ?>
		</section>

	</article><!-- /.post -->