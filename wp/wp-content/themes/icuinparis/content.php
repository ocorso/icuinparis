<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The default template for displaying content
 */
 
?>

	<article class="post span4">
		<a class="article-inner" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
			<section class="rollover">
				<h5>Posted: <?php the_time('m/j/y g:i A'); ?></h5>
				<?php the_excerpt(); ?>
			</section>
			<?php if (has_post_thumbnail( )) the_post_thumbnail( 'medium' ); ?>
		</a<!-- .article-inner -->
		<a class="post-title" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
	</article><!-- /.post -->