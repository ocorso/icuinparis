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
				'featured_heading' => '',
				'featured_frame' => 'true',
				'featured_entries' => '3',
				'featured_order' => 'DESC', 
				'featured_slide_group' => '0', 
				'featured_videotitle' => 'true'
				);
				
$settings = woo_get_dynamic_values( $settings );

$query_args = array(
				'limit' => $settings['featured_entries'], 
				'order' => $settings['featured_order'], 
				'term' => $settings['featured_slide_group']
				);

/* Retrieve the slides, based on the query arguments. */
$slides = woo_featured_slider_get_slides( $query_args );

/* Media settings */
if  ( 'true' == $settings['featured_frame'] )  {
	$media_settings = array( 'width' => '884', 'height' => '498' );
	
	if ( 'true' != $settings['featured_videotitle'] ) {
		$media_settings['width'] = '884';
		$media_settings['height'] = '498'; 
	}
} else  {
	$media_settings = array( 'width' => '660', 'height' => '300' );
	
	if ( 'true' != $settings['featured_videotitle'] ) {
		$media_settings['width'] = '660';
		$media_settings['height'] = '300'; 
	}
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
	
	if  ( 'true' == $settings['featured_frame'] )  {
		$container_css_class .= ' frame';
	} else  {
		$container_css_class .= ' no-frame';
	}
	
?>
<div id="featured-slider">

	<div class="flexslider col-full <?php echo esc_attr( $container_css_class ); ?>">
	
		<span class="heading"><?php echo $settings['featured_heading']; ?></span>
	
		<ul class="slides">
	<?php
		foreach ( $slides as $k => $post ) {
			setup_postdata( $post );
			$count++;
	
			$url = get_post_meta( get_the_ID(), 'url', true );
			$layout = get_post_meta( get_the_ID(), '_layout', true );
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
				if  ( 'true' == $settings['featured_frame'] )  {
					$image = woo_image( 'width=884px&height=498px&class=slide-image&link=img&return=true' );
				} else {
					$image = woo_image( 'width=660px&noheight=true&class=slide-image&link=img&return=true' );
				}
				if ( '' != $image ) {
					$css_class .= ' has-image no-video';
					$slide_media = $image;
				} else {
					$css_class .= ' no-image';
				}
			}
			if ( $layout )  {
				$css_class .= ' ' . $layout;
				
				if ( 'true' != $settings['featured_frame'] )  {
					if  ( ( 'layout-full' == $layout ) )  {
						$image = woo_image( 'width=960px&noheight=true&class=slide-image&link=img&return=true' );
					} else {
						$image = woo_image( 'width=660px&noheight=true&class=slide-image&link=img&return=true' );
					}
					
					$slide_media = $image;
				}
				
			}
	?>
			<li class="slide <?php echo esc_attr( $css_class ); ?>">
				<?php if ( '' == $embed || ( '' != $embed && 'true' == $settings['featured_videotitle'] ) ) { ?>
				<div class="slide-content">
					<header><h1><?php echo $title; ?></h1></header>
					<div class="entry"><?php the_content(); ?></div><!--/.entry-->
				</div><!--/.slide-content-->
				<?php } ?>
				<?php if ( '' != $slide_media ) { ?>
						<div class="slide-media">
							<?php if ( $url != '' ) {
								echo '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '">';
							} ?>
							<?php echo $slide_media; ?>
							<?php if ( $url != '' ) {
								echo '</a>';
							} ?>
						</div><!--/.slide-media-->
				<?php } ?>
			</li>
	<?php } wp_reset_postdata(); ?>
		</ul>
	</div><!-- /.col-full -->
</div><!--/#featured-slider-->
<?php
} else {
	echo do_shortcode( '[box type="info"]' . __( 'Please add some slides in the WordPress admin to show in the Featured Slider.', 'woothemes' ) . '[/box]' );
}
?>