<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The default template for displaying "by designer" content
 */
 
?>
<?php 
 $href = get_bloginfo('url') . '/store/designers/' . the_slug();
 ?>
	<article class="post span4 poopy">
		<a class="article-inner" href="<?= $href; ?>" title="<?php the_title_attribute(); ?>">
			<section class="rollover">
				<?php the_excerpt(); ?>
				<div class="by-designer-btn" title="Shop by designer">Shop Collection</div>
			</section>
			<?php if (has_post_thumbnail( )) the_post_thumbnail( 'medium' ); ?>
		</a><!-- .article-inner -->
		<a class="post-title" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
	</article><!-- /.post -->