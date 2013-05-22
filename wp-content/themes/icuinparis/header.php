<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Header Template
 *
 * Here we setup all logic and XHTML that is required for the header section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php woo_title( '' ); ?></title>
<?php woo_meta(); ?>
<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
<?php
wp_head();
woo_head();
?>
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>
</head>
<body <?php body_class(); ?>>
<?php woo_top(); ?>

<div id="logoposition">
	<a href="http://staging.click3x.com/icuparis/">	
    	<img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/logo_icu.jpg" class="headerlogo">
    </a>    
</div>

<div id="wrapper">

    <?php woo_header_before(); ?>

	<header id="header">

		<div class="col-full">

			<?php woo_header_inside(); ?>

		    <hgroup>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
				<h3 class="nav-toggle"><a href="#navigation"><img src="<?php echo esc_url( get_bloginfo( 'template_directory' ) ); ?>/images/ico-nav-toggle.png" /> <span><?php _e( 'Navigation', 'woothemes' ); ?></span></a></h3>
			</hgroup>

	        <?php woo_nav_before(); ?>

			<nav id="navigation" role="navigation">

				<?php
				if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'primary-menu' ) ) {
					wp_nav_menu( array( 'depth' => 6, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'main-nav', 'menu_class' => 'nav fl', 'theme_location' => 'primary-menu' ) );
				} else {
				?>
		        <ul id="main-nav" class="nav fl">
					<?php if ( is_page() ) $highlight = 'page_item'; else $highlight = 'page_item current_page_item'; ?>
					<li class="<?php echo $highlight; ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'woothemes' ); ?></a></li>
					<?php wp_list_pages( 'sort_column=menu_order&depth=6&title_li=&exclude=' ); ?>
				</ul><!-- /#nav -->
		        <?php } ?>

				<?php if ( isset( $woo_options['woo_header_search'] ) && ( $woo_options['woo_header_search'] == 'true' ) ): ?>
				<section class="search_main">
				    <form method="get" class="searchform" action="<?php echo home_url( '/' ); ?>" >
				        <input type="text" class="field s" name="s" placeholder="<?php esc_attr_e( 'Search...', 'woothemes' ); ?>" />
				    </form>
				</section><!--/.search_main-->
				<?php endif; ?>

			</nav><!-- /#navigation -->

			<?php woo_nav_after(); ?>

		</div><!-- /.col-full -->

	</header><!-- /#header -->

	<?php woo_content_before(); ?>