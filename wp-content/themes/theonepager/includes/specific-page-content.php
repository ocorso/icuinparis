<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Page Content Component
 *
 * Display content from a specified page.
 *
 * @author Matty
 * @since 1.0.0
 * @package WooFramework
 * @subpackage Component
 */
global $post;

$settings = array(
				'homepage_page_id' => '',
				'thumb_single' => 'false',
				'single_w' => 200,
				'single_h' => 200,
				'thumb_single_align' => 'alignleft',
				'homepage_posts_heading' => '',
				'homepage_posts_layout' => 'layout-full'
				);

$settings = woo_get_dynamic_values( $settings );

if ( 0 < intval( $settings['homepage_page_id'] ) ) {
$query = new WP_Query( array( 'page_id' => $settings['homepage_page_id'] ) );
?>

<div id="page-content" class="widget_woo_component">
	<div class="col-full <?php echo esc_attr( $settings['homepage_posts_layout'] ); ?>">
		<span class="heading"><?php echo $settings['homepage_posts_heading']; ?></span>
		<div id="main" class="col-left">
		<?php
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) { $query->the_post();
		?>

			<article <?php  if ( has_post_thumbnail() ) { echo 'class="has-featured-image"'; } ?>>

				<?php if ( has_post_thumbnail() ) { ?>
					<div class="featured-image">
						<?php woo_image( 'width=500&noheight=true' ); ?>
					</div>
				<?php } ?>

				<div class="article-content">

					<header>
						<h1><?php the_title(); ?></h1>
					</header>

					<section class="entry">
						<?php the_content( __( 'Continue Reading &rarr;', 'woothemes' ) ); ?>
					</section>

				</div>

			</article>

			<div class="fix"></div>

		<?php
				} // End WHILE Loop
				wp_reset_postdata();

			} else {
		?>
		    <article <?php post_class(); ?>>
		        <p><?php _e( 'Selected home page content not found.', 'woothemes' ); ?></p>
		    </article><!-- /.post -->
		<?php } ?>
		</div><!--/#main .col-left-->
		<?php
			if ( $settings['homepage_posts_layout'] != 'layout-full' )  {
				get_sidebar();
			}
		?>
	</div>
</div>
<?php } // End the main check ?>