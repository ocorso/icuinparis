<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Blog Posts Component
 *
 * Display X recent blog posts.
 *
 * @author Matty
 * @since 1.0.0
 * @package WooFramework
 * @subpackage Component
 */
$settings = array(
				'homepage_number_of_posts' => 5,
				'homepage_posts_category' => '',
				'thumb_w' => 100,
				'thumb_h' => 100,
				'thumb_align' => 'alignleft',
				'homepage_posts_heading' => '',
				'homepage_posts_layout' => 'layout-full'
				);

$settings = woo_get_dynamic_values( $settings );
?>

<div id="blog-posts" class="blog-posts widget_woo_component">

	<div class="col-full <?php echo esc_attr( $settings['homepage_posts_layout'] ); ?>">

		<span class="heading"><?php echo $settings['homepage_posts_heading']; ?></span>

		<div id="main" class="col-left">

			<?php woo_loop_before(); ?>

			<?php
				if ( have_posts() ) {
					$count = 0;
					while ( have_posts() ) { the_post(); $count++;

						/* Include the Post-Format-specific template for the content.
						 * If you want to overload this in a child theme then include a file
						 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
						 */
						get_template_part( 'content', get_post_format() );

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

		</div><!--/#main .col-left-->
		<?php
			if ( $settings['homepage_posts_layout'] != 'layout-full' )  {
				get_sidebar();
			}
		?>
	</div>

</div>