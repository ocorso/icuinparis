<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Featured Slider Template
 *
 * Here we setup all HTML pertaining to the featured slider.
 *
 * @package WooFramework
 * @subpackage Template
 */

/* Retrieve the settings and setup query arguments. */
$settings = array(
				'featured_entries' => '3',
				'featured_order' => 'DESC', 
				'featured_slide_tag' => '0', 
				'featured_videotitle' => 'true', 
				'featured_pagination' => 'true'
				);
				
$settings = woo_get_dynamic_values( $settings );

$query_args = array(
				'limit' => $settings['featured_entries'], 
				'order' => $settings['featured_order'], 
				'term' => $settings['featured_slide_tag']
				);

if ( 0 < $settings['featured_slide_tag'] ) { $query_args['taxonomy'] = 'post_tag'; }

/* Retrieve the slides, based on the query arguments. */
$slides = woo_featured_slider_get_slides( $query_args );

/* Media settings */
$media_settings = array( 'width' => '960', 'height' => '540' );

if ( 'true' != $settings['featured_videotitle'] ) {
	$media_settings['width'] = '960';
	$media_settings['height'] = '540'; 
}

/* Begin HTML output. */
if ( false != $slides ) {
	$count = 0;

	$container_css_class = 'flexslider';

	if ( 'true' == $settings['featured_videotitle'] ) {
		$container_css_class .= ' default-width-slide';
	} else {
		$container_css_class .= ' full-width-slide';
	}
?>
<div id="featured-slider" class="flexslider <?php echo esc_attr( $container_css_class ); ?>">
	<ul class="slides">
<?php
	foreach ( $slides as $k => $post ) {
		setup_postdata( $post );
		$count++;

		$url = get_post_meta( get_the_ID(), 'url', true );
		$title = get_the_title();
		if ( $url != '' ) {
			$title = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '">' . $title . '</a>';
		}

		$css_class = 'slide-number-' . esc_attr( $count );

		$slide_media = '';
		$embed = woo_embed( 'width=' . intval( $media_settings['width'] ) . '&height=' . intval( $media_settings['height'] ) . '&class=slide-video' );
		if ( '' != $embed ) {
			$css_class .= ' has-video';
			$slide_media = $embed;
		} else {
			$image = woo_image( 'width=1300&height=350&noheight=true&class=slide-image&link=img&return=true' );
			if ( '' != $image ) {
				$css_class .= ' has-image no-video';
				$slide_media = $image;
			} else {
				$css_class .= ' no-image';
			}
		}
?>
		<li class="slide <?php echo esc_attr( $css_class ); ?>">
			<div class="slide-wrapper">
				<?php
					if ( '' != $slide_media ) {
						echo '<div class="slide-media">' . $slide_media . '</div><!--/.slide-media-->' . "\n";
					}
				?>
				<?php if ( '' == $embed || ( '' != $embed && 'true' == $settings['featured_videotitle'] ) ) { ?>
				<div class="slide-content">
					<div class="slide-content-inner">
						<header><h1><a href="<?php the_permalink(); ?>"><?php echo $title; ?></a></h1></header>
						<footer class="post-more">
						<?php _e('By' , 'woothemes') ?> <?php the_author_posts_link(); ?> - <span><?php the_time( get_option( 'date_format' ) ); ?></span>
						</footer>
					</div><!--/.slide-content-inner-->
				</div><!--/.slide-content-->
				<?php } ?>
			</div><!--/.slide-wrappper-->
		</li>
<?php } wp_reset_postdata(); ?>
	</ul>
	<div class="flexslider-container"></div>
	<?php if ( 'true' == $settings['featured_pagination'] ) { ?>
	<div class="flexslider-pagination-controls">
		<ol class="flex-control-nav flex-control-paging">
			<?php
				$html = '';
				for ( $i = 0; $i < $count; $i++ ) {
					$html .= '<li><a>' . ( $i + 1 ) . '</a></li>' . "\n";
				}
				echo $html;
			?>
		</ol>
	</div><!--/.flexslider-pagination-controls-->
	<?php } ?>
</div><!--/#featured-slider-->
<?php
} else {
	echo do_shortcode( '[box type="info"]' . __( 'Please add some slides in the WordPress admin to show in the Featured Slider.', 'woothemes' ) . '[/box]' );
}
?>