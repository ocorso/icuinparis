<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The default template for displaying content
 */

	global $woo_options;
?>

	<article <?php post_class(); ?>>

		<?php woo_post_meta(); ?>

		<div class="article-inner">

			<?php 
				if ( isset( $woo_options['woo_post_content'] ) && $woo_options['woo_post_content'] != 'content' ) { 
					woo_image( 'width=525&height=245&class=thumbnail alignleft' ); 
				} 
			?>    

			<header>
				<h1><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
			</header>

			<section class="entry">
				<?php the_excerpt(); ?>
			</section>

		</div><!-- /.article-inner -->

	</article><!-- /.post -->