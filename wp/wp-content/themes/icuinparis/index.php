<?php
// File Security Check
if ( ! function_exists( 'wp' ) && ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}
?><?php
/**
 * Index Template
 *
 * Here we setup all logic and XHTML that is required for the index template, used as both the homepage
 * and as a fallback template, if a more appropriate template file doesn't exist for a specific context.
 *
 * @package icuinparis
 * @subpackage Template
 */
	get_header();
	
	$settings = array(
					'custom_intro_message' => 'true',
					'portfolio_area' => 'true', 
					'shop_area' => 'true'
					);
					
	$settings = woo_get_dynamic_values( $settings );
	if ( get_query_var( 'page' ) > 1 ) { $paged = get_query_var( 'page' ); } elseif ( get_query_var( 'paged' ) > 1 ) { $paged = get_query_var( 'paged' ); } else { $paged = 1; } 
?>
	<div id="featured-slider" class="flexslider flexslider default-width-slide">
	<ul class="slides">
		<li class="slide slide-number-1 has-image no-video">
			<div class="slide-wrapper">
				<div class="slide-media"><img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0000_video.png" alt=""  width="1300"  class="woo-image slide-image" /></div><!--/.slide-media-->
								<div class="slide-content">
					<div class="slide-content-inner">
						<header><h1><a href="http://gibson.loc/c3x-icu/wordpress/home-3/">Home 3</a></h1></header>
						<footer class="post-more">
						By <a href="http://gibson.loc/c3x-icu/wordpress/author/Web Team/" title="Posts by Web Team" rel="author">Web Team</a> - <span>May 9, 2013</span>
						</footer>
					</div><!--/.slide-content-inner-->
				</div><!--/.slide-content-->
							</div><!--/.slide-wrappper-->
		</li>
		<li class="slide slide-number-2 has-image no-video">
			<div class="slide-wrapper">
				<div class="slide-media"><img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0001_CREATIVE-COMMUNITY.png" alt=""  width="1300"  class="woo-image slide-image" /></div><!--/.slide-media-->
								<div class="slide-content">
					<div class="slide-content-inner">
						<header><h1><a href="http://gibson.loc/c3x-icu/wordpress/home-2/">Home 2</a></h1></header>
						<footer class="post-more">
						By <a href="http://gibson.loc/c3x-icu/wordpress/author/Web Team/" title="Posts by Web Team" rel="author">Web Team</a> - <span>May 9, 2013</span>
						</footer>
					</div><!--/.slide-content-inner-->
				</div><!--/.slide-content-->
							</div><!--/.slide-wrappper-->
		</li>
		<li class="slide slide-number-3 has-image no-video">
			<div class="slide-wrapper">
				<div class="slide-media"><img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0002_VASKOLG-ESPARATA-SHELL-NECKLACE-GIVE-THEM-SOMETHINGTHEYVE-NE.png" alt=""  width="1300"  class="woo-image slide-image" /></div><!--/.slide-media-->
								<div class="slide-content">
					<div class="slide-content-inner">
						<header><h1><a href="http://gibson.loc/c3x-icu/wordpress/home-1/">Home 1</a></h1></header>
						<footer class="post-more">
						By <a href="http://gibson.loc/c3x-icu/wordpress/author/Web Team/" title="Posts by Web Team" rel="author">Web Team</a> - <span>May 7, 2013</span>
						</footer>
					</div><!--/.slide-content-inner-->
				</div><!--/.slide-content-->
							</div><!--/.slide-wrappper-->
		</li>
		<li class="slide slide-number-2 has-image no-video">
			<div class="slide-wrapper">
				<div class="slide-media"><img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0003_NOOT-SS_13.png" alt=""  width="1300"  class="woo-image slide-image" /></div><!--/.slide-media-->
								<div class="slide-content">
					<div class="slide-content-inner">
						<header><h1><a href="http://gibson.loc/c3x-icu/wordpress/home-2/">Home 2</a></h1></header>
						<footer class="post-more">
						By <a href="http://gibson.loc/c3x-icu/wordpress/author/Web Team/" title="Posts by Web Team" rel="author">Web Team</a> - <span>May 9, 2013</span>
						</footer>
					</div><!--/.slide-content-inner-->
				</div><!--/.slide-content-->
							</div><!--/.slide-wrappper-->
		</li>
	</ul>
	<div class="flexslider-container"></div>
	</div><!--/#featured-slider-->

    <div id="content">
    	<div id="home-cont">
         	<div id="shopcall">
              	<img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/call_shop.jpg" width="480" height="65" />
          	</div>
          	<div id="shopcall2">
              <img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/call_shopall.jpg" width="480" height="65" />
          	</div>
			<div style="clear:both;"></div>
         	<div id="homeproducts">
            <?php if ( woo_active_sidebar( 'homeproducts-widget' ) ) { ?>
        		<?php woo_sidebar( 'homeproducts-widget' ); ?>
 			<?php } // End IF Statement ?>
          	</div>
			<div style="clear:both;height:10px;"></div>
          	<div id="call-home-lower-left">
              	<a href="<?= get_bloginfo('url'); ?>/store/designs"><img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/call_home_lower_left.jpg" width="100%"/></a>
            </div>    
          	<div id="call-home-lower-right">
              	<a href="<?= get_bloginfo('url'); ?>/representation"><img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/call_home_lower_right.jpg" width="100%" /></a>
          	</div>
			<div style="clear:both;height:10px;"></div>
        </div>
	</div>
</div><!-- /#wrapper -->
<div id="footer-widget-wrapper">
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Footer Template
 *
 * Here we setup all logic and XHTML that is required for the footer section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
	global $woo_options;

	$total = 4;
	if ( isset( $woo_options['woo_footer_sidebars'] ) && ( $woo_options['woo_footer_sidebars'] != '' ) ) {
		$total = $woo_options['woo_footer_sidebars'];
	}

	if ( ( woo_active_sidebar( 'footer-1' ) ||
		   woo_active_sidebar( 'footer-2' ) ||
		   woo_active_sidebar( 'footer-3' ) ||
		   woo_active_sidebar( 'footer-4' ) ) && $total > 0 ) {

?>
	
	<?php woo_footer_before(); ?>
		
	<section id="footer-widgets" class="container">

		<div class="col-full col-<?php echo $total; ?> fix">

		<div class="home-footer-header">
        	<div class="row">
        		<div class="span2"><img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/home-footer-call-header.jpg" width="200" height="123" align="left"/></div>
        		<div class="span10">We opened in 2010, selling a unique collection of designer jewelry and accessories. Always an online platform and now based between Paris and New York City, we offer a mix of European and American style and culture through our product offering. An important part of the icuinparis.com culture is our customizeable and made-to-order product. We have created strong relationships with our designers, allowing us the advantage to offer you designs and product that may not be available elsewhere, or that was never realized until you suggested it. Now in our third year, icuinparis.com has recenly opened a showroom branch: Showroom ICU. </div>
        	</div>
        </div>	

		<div style="clear:both;"></div>
		<div class="row">
			<?php $i = 0; while ( $i < $total ) { $i++; ?>
				<?php if ( woo_active_sidebar( 'footer-' . $i ) ) { ?>

			<div class=" span3 block footer-widget-<?php echo $i; ?>">
	        	<?php woo_sidebar( 'footer-' . $i ); ?>
			</div>

		        <?php } ?>
			<?php } // End WHILE Loop ?>
		</div><!-- /.row  -->
		</div><!-- /.col-full  -->

	</section><!-- /#footer-widgets  -->
<?php } // End IF Statement ?>


    	<?php woo_main_before(); ?>
		
		<?php woo_main_after(); ?>
    </div><!-- /#content -->
		
<?php get_footer(); ?>