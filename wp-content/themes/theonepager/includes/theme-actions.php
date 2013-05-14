<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

- Theme Setup
- Load style.css in the <head>
- Load responsive <meta> tags in the <head>
- Add custom styling to HEAD
- Add custom typograhpy to HEAD
- Add layout to body_class output
- woo_feedburner_link
- Optionally load top ad section into the header.
- Optionally load top navigation.
- Optionally load custom logo.
- Add custom CSS class to the <body> tag if the lightbox option is enabled.
- Load PrettyPhoto JavaScript and CSS if the lightbox option is enabled.
- Customise the default search form
- Load responsive IE scripts
- Add custom header styling based on theme options.
- Control homepage content
- Contact Form processing logic
- Filter the navigation menu to remove the active state from home links with a #
- Add the headings to the features and testimonials widgets, when using them in the "Homepage" widget area.

-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* Theme Setup */
/*-----------------------------------------------------------------------------------*/
/**
 * Theme Setup
 *
 * This is the general theme setup, where we add_theme_support(), create global variables
 * and setup default generic filters and actions to be used across our theme.
 *
 * @package WooFramework
 * @subpackage Logic
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */

if ( ! isset( $content_width ) ) $content_width = 640;

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support for post thumbnails.
 *
 * To override woothemes_setup() in a child theme, add your own woothemes_setup to your child theme's
 * functions.php file.
 *
 * @uses add_theme_support() To add support for automatic feed links.
 * @uses add_editor_style() To style the visual editor.
 */

add_action( 'after_setup_theme', 'woothemes_setup' );

if ( ! function_exists( 'woothemes_setup' ) ) {
	function woothemes_setup () {

		// This theme styles the visual editor with editor-style.css to match the theme style.
		if ( locate_template( 'editor-style.css' ) != '' ) { add_editor_style(); }

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );
	}
}

/**
 * Set the default Google Fonts used in theme.
 *
 * Used to set the default Google Fonts used in the theme, when Custom Typography is disabled.
 */

global $default_google_fonts;
$default_google_fonts = array( 'Open Sans', 'Bitter' );


/*-----------------------------------------------------------------------------------*/
/* Load style.css and layout.css in the <head> */
/*-----------------------------------------------------------------------------------*/

if ( ! is_admin() ) { add_action( 'wp_enqueue_scripts', 'woo_load_frontend_css', 20 ); }

if ( ! function_exists( 'woo_load_frontend_css' ) ) {
	function woo_load_frontend_css () {
		wp_register_style( 'theme-stylesheet', get_stylesheet_uri() );
		wp_register_style( 'woo-layout', get_template_directory_uri() . '/css/layout.css' );
		wp_enqueue_style( 'theme-stylesheet' );
		wp_enqueue_style( 'woo-layout' );
	} // End woo_load_frontend_css()
}



/*-----------------------------------------------------------------------------------*/
/* Load responsive <meta> tags in the <head> */
/*-----------------------------------------------------------------------------------*/

add_action( 'wp_head', 'woo_load_responsive_meta_tags', 10 );

if ( ! function_exists( 'woo_load_responsive_meta_tags' ) ) {
	function woo_load_responsive_meta_tags () {
		$html = '';

		$html .= "\n" . '<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->' . "\n";
		$html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />' . "\n";

		/* Remove this if not responsive design */
		$html .= "\n" . '<!--  Mobile viewport scale | Disable user zooming as the layout is optimised -->' . "\n";
		$html .= '<meta content="initial-scale=1.0; maximum-scale=1.0; user-scalable=no" name="viewport"/>' . "\n";

		echo $html;
	} // End woo_load_responsive_meta_tags()
}

/*-----------------------------------------------------------------------------------*/
/* Add Custom Styling to HEAD */
/*-----------------------------------------------------------------------------------*/

add_action( 'woo_head', 'woo_custom_styling', 10 ); // Add custom styling to HEAD

if ( ! function_exists( 'woo_custom_styling' ) ) {
	function woo_custom_styling () {

		$output = '';
		// Get options
		$settings = array(
						'body_color' => '',
						'body_img' => '',
						'body_repeat' => '',
						'body_pos' => '',
						'body_attachment' => '',
						'link_color' => '',
						'link_hover_color' => '',
						'button_color' => ''
						);
		$settings = woo_get_dynamic_values( $settings );

		// Type Check for Array
		if ( is_array($settings) ) {

			// Add CSS to output
			if ( $settings['body_color'] != '' ) {
				$output .= 'body { background: ' . $settings['body_color'] . ' !important; }' . "\n";
			}

			if ( $settings['body_img'] != '' ) {
				$body_image = $settings['body_img'];
				if ( is_ssl() ) { $body_image = str_replace( 'http://', 'https://', $body_image ); }
				$output .= 'body { background-image: url( ' . esc_url( $body_image ) . ' ) !important; }' . "\n";
			}

			if ( ( $settings['body_img'] != '' ) && ( $settings['body_repeat'] != '' ) && ( $settings['body_pos'] != '' ) ) {
				$output .= 'body { background-repeat: ' . $settings['body_repeat'] . ' !important; }' . "\n";
			}

			if ( ( $settings['body_img'] != '' ) && ( $settings['body_pos'] != '' ) ) {
				$output .= 'body { background-position: ' . $settings['body_pos'] . ' !important; }' . "\n";
			}

			if ( ( $settings['body_img'] != '' ) && ( $settings['body_attachment'] != '' ) ) {
				$output .= 'body { background-attachment: ' . $settings['body_attachment'] . ' !important; }' . "\n";
			}

			if ( $settings['link_color'] != '' ) {
				$output .= '#wrapper a { color: ' . $settings['link_color'] . ' ; }' . "\n";
			}

			if ( $settings['link_hover_color'] != '' ) {
				$output .= 'a:hover, .post-more a:hover, .post-meta a:hover, .post p.tags a:hover { color: ' . $settings['link_hover_color'] . ' !important; }' . "\n";
			}

			if ( $settings['button_color'] != '' ) {
				$output .= 'a.button, a.comment-reply-link, #commentform #submit, #contact-page .submit { background: ' . $settings['button_color'] . ' !important; border-color: ' . $settings['button_color'] . ' !important; }' . "\n";
				$output .= 'a.button:hover, a.button.hover, a.button.active, a.comment-reply-link:hover, #commentform #submit:hover, #contact-page .submit:hover { background: ' . $settings['button_color'] . ' !important; opacity: 0.9; }' . "\n";
			}

		} // End If Statement

		// Output styles
		if ( isset( $output ) && $output != '' ) {
			$output = strip_tags( $output );
			$output = "\n" . "<!-- Woo Custom Styling -->\n<style type=\"text/css\">\n" . $output . "</style>\n";
			echo $output;
		}

	} // End woo_custom_styling()
}

/*-----------------------------------------------------------------------------------*/
/* Add custom typograhpy to HEAD */
/*-----------------------------------------------------------------------------------*/

add_action( 'woo_head','woo_custom_typography', 10 ); // Add custom typography to HEAD

if ( ! function_exists( 'woo_custom_typography' ) ) {
	function woo_custom_typography() {

		// Get options
		global $woo_options;

		// Reset
		$output = '';
		$default_google_font = false;

		// Type Check for Array
		if ( is_array($woo_options) ) {

			// Add Text title and tagline if text title option is enabled
			if ( isset( $woo_options['woo_texttitle'] ) && $woo_options['woo_texttitle'] == 'true' ) {

				if ( $woo_options['woo_font_site_title'] )
					$output .= 'body #wrapper #header .site-title a {'.woo_generate_font_css($woo_options['woo_font_site_title']).'}' . "\n";
				if ( $woo_options['woo_tagline'] == "true" AND $woo_options['woo_font_tagline'] )
					$output .= 'body #wrapper #header .site-description {'.woo_generate_font_css($woo_options[ 'woo_font_tagline']).'}' . "\n";
			}

			if ( isset( $woo_options['woo_typography'] ) && $woo_options['woo_typography'] == 'true' ) {

				if ( isset( $woo_options['woo_font_body'] ) && $woo_options['woo_font_body'] )
					$output .= 'body { '.woo_generate_font_css($woo_options['woo_font_body'], '1.5').' }' . "\n";

				if ( isset( $woo_options['woo_font_nav'] ) && $woo_options['woo_font_nav'] )
					$output .= '#wrapper #top .nav a { '.woo_generate_font_css($woo_options['woo_font_nav'], '1.4').' }' . "\n";

				if ( isset( $woo_options['woo_font_page_title'] ) && $woo_options['woo_font_page_title'] )
					$output .= '#wrapper .page header h1 { '.woo_generate_font_css($woo_options[ 'woo_font_page_title' ]).' }' . "\n";

				if ( isset( $woo_options['woo_font_post_title'] ) && $woo_options['woo_font_post_title'] )
					$output .= '#wrapper .post header h1, #wrapper .post header h1 a:link, #wrapper .post header h1 a:visited { '.woo_generate_font_css($woo_options[ 'woo_font_post_title' ]).' }' . "\n";

				if ( isset( $woo_options['woo_font_post_meta'] ) && $woo_options['woo_font_post_meta'] )
					$output .= '#wrapper .post-date-author { '.woo_generate_font_css($woo_options[ 'woo_font_post_meta' ]).' }' . "\n";

				if ( isset( $woo_options['woo_font_post_entry'] ) && $woo_options['woo_font_post_entry'] )
					$output .= '#wrapper .entry, #wrapper .entry p { '.woo_generate_font_css($woo_options[ 'woo_font_post_entry' ], '1.5').' } h1, h2, h3, h4, h5, h6 { font-family: '.stripslashes($woo_options[ 'woo_font_post_entry' ]['face']).', arial, sans-serif; }'  . "\n";

				if ( isset( $woo_options['woo_font_widget_titles'] ) && $woo_options['woo_font_widget_titles'] )
					$output .= '#wrapper .widget h3 { '.woo_generate_font_css($woo_options[ 'woo_font_widget_titles' ]).' }'  . "\n";

				if ( isset( $woo_options['woo_font_widget_titles'] ) && $woo_options['woo_font_widget_titles'] )
					$output .= '#wrapper .widget h3 { '.woo_generate_font_css($woo_options[ 'woo_font_widget_titles' ]).' }'  . "\n";
					
				if ( isset( $woo_options['woo_font_homepage_widget_titles'] ) && $woo_options['woo_font_homepage_widget_titles'] )
					$output .= '#wrapper #content.home-widgets .widget h2.widget-title { '.woo_generate_font_css($woo_options[ 'woo_font_homepage_widget_titles' ]).' }'  . "\n";

				// Component titles
				if ( isset( $woo_options['woo_font_component_titles'] ) && $woo_options['woo_font_component_titles'] )
					$output .= '#wrapper .component h2.component-title { '.woo_generate_font_css($woo_options[ 'woo_font_component_titles' ]).' }'  . "\n";

			// Add default typography Google Font
			} else {

				// Load default Google Fonts
				global $default_google_fonts;
				if ( is_array( $default_google_fonts) and count( $default_google_fonts ) > 0 ) :

					$count = 0;
					foreach ( $default_google_fonts as $font ) {
						$count++;
						$woo_options[ 'woo_default_google_font_'.$count ] = array( 'face' => $font );
					}
					$default_google_font = true;

				endif;

			}

		} // End If Statement

		// Output styles
		if (isset($output) && $output != '') {

			// Load Google Fonts stylesheet in HEAD
			if (function_exists( 'woo_google_webfonts')) woo_google_webfonts();

			$output = "\n" . "<!-- Woo Custom Typography -->\n<style type=\"text/css\">\n" . $output . "</style>\n";
			echo $output;

		// Check if default google font is set and load Google Fonts stylesheet in HEAD
		} elseif ( $default_google_font ) {

			// Enable Google Fonts stylesheet in HEAD
			if (function_exists( 'woo_google_webfonts')) woo_google_webfonts();

		}

	} // End woo_custom_typography()
}

// Returns proper font css output
if (!function_exists( 'woo_generate_font_css')) {
	function woo_generate_font_css($option, $em = '1') {

		// Test if font-face is a Google font
		global $google_fonts;

		// Type Check for Array
		if ( is_array($google_fonts) ) {

			foreach ( $google_fonts as $google_font ) {

				// Add single quotation marks to font name and default arial sans-serif ending
				if ( $option[ 'face' ] == $google_font[ 'name' ] )
					$option[ 'face' ] = "'" . $option[ 'face' ] . "', arial, sans-serif";

			} // END foreach

		} // End If Statement

		if ( !@$option["style"] && !@$option["size"] && !@$option["unit"] && !@$option["color"] )
			return 'font-family: '.stripslashes($option["face"]).';';
		else
			return 'font:'.$option["style"].' '.$option["size"].$option["unit"].'/'.$em.'em '.stripslashes($option["face"]).';color:'.$option["color"].';';
	}
}

// Output stylesheet and custom.css after custom styling
remove_action( 'wp_head', 'woothemes_wp_head' );
add_action( 'woo_head', 'woothemes_wp_head' );


/*-----------------------------------------------------------------------------------*/
/* Add layout to body_class output */
/*-----------------------------------------------------------------------------------*/

add_filter( 'body_class','woo_layout_body_class', 10 );		// Add layout to body_class output

if ( ! function_exists( 'woo_layout_body_class' ) ) {
	function woo_layout_body_class( $classes ) {

		global $woo_options;

		$layout = 'two-col-left';

		if ( isset( $woo_options['woo_site_layout'] ) && ( $woo_options['woo_site_layout'] != '' ) ) {
			$layout = $woo_options['woo_site_layout'];
		}

		// Set main layout on post or page
		if ( is_singular() ) {
			global $post;
			$single = get_post_meta($post->ID, '_layout', true);
			if ( $single != "" AND $single != "layout-default" )
				$layout = $single;
		}

		// Add layout to $woo_options array for use in theme
		$woo_options['woo_layout'] = $layout;

		if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'top-menu' ) ) {
			$classes[] = 'has-top-nav';
		}

		// Add classes to body_class() output
		$classes[] = $layout;
		return $classes;

	} // End woo_layout_body_class()
}


/*-----------------------------------------------------------------------------------*/
/* woo_feedburner_link() */
/*-----------------------------------------------------------------------------------*/
/**
 * woo_feedburner_link()
 *
 * Replace the default RSS feed link with the Feedburner URL, if one
 * has been provided by the user.
 *
 * @package WooFramework
 * @subpackage Filters
 */

add_filter( 'feed_link', 'woo_feedburner_link', 10 );

function woo_feedburner_link ( $output, $feed = null ) {

	global $woo_options;

	$default = get_default_feed();

	if ( ! $feed ) $feed = $default;

	if ( isset($woo_options[ 'woo_feed_url']) && $woo_options[ 'woo_feed_url' ] && ( $feed == $default ) && ( ! stristr( $output, 'comments' ) ) ) $output = esc_url( $woo_options[ 'woo_feed_url' ] );

	return $output;

} // End woo_feedburner_link()

/*-----------------------------------------------------------------------------------*/
/* Optionally load top ad section into the header. */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_top_ad' ) ) {
function woo_top_ad () {
	global $woo_options;

	if ( isset( $woo_options['woo_ad_top'] ) && $woo_options['woo_ad_top'] == 'true' ) {
?>
        <div id="topad">
			<?php
				if ( isset( $woo_options['woo_ad_top_adsense'] ) && $woo_options['woo_ad_top_adsense'] != '' ) {
					echo stripslashes( $woo_options['woo_ad_top_adsense'] );
				} else {
					if ( isset( $woo_options['woo_ad_top_url'] ) && isset( $woo_options['woo_ad_top_image'] ) )
			?>
				<a href="<?php echo esc_url( $woo_options['woo_ad_top_url'] ); ?>"><img src="<?php echo esc_url( $woo_options['woo_ad_top_image'] ); ?>" width="468" height="60" alt="advert" /></a>
			<?php } ?>
		</div><!-- /#topad -->
<?php
	}
} // End woo_top_ad()
}

add_action( 'woo_nav_before', 'woo_top_ad' );

/*-----------------------------------------------------------------------------------*/
/* Optionally load top navigation. */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_top_nav' ) ) {
function woo_top_nav () {
	if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'top-menu' ) ) {
?>

	<div id="top">
		<nav class="col-full" role="navigation">
			<?php wp_nav_menu( array( 'depth' => 6, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'top-nav', 'menu_class' => 'nav', 'theme_location' => 'top-menu' ) ); ?>
		</nav>
	</div><!-- /#top -->

 <?php
	}
} // End woo_top_nav()
}

add_action( 'woo_header_before', 'woo_top_nav' );

/*-----------------------------------------------------------------------------------*/
/* Optionally load custom logo. */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_logo' ) ) {
function woo_logo () {
	global $woo_options;
	if ( isset( $woo_options['woo_texttitle'] ) && $woo_options['woo_texttitle'] == 'true' ) return; // Get out if we're not displaying the logo.

	$logo = esc_url( get_template_directory_uri() . '/images/logo.png' );
	if ( isset( $woo_options['woo_logo'] ) && $woo_options['woo_logo'] != '' ) { $logo = $woo_options['woo_logo']; }
	if ( is_ssl() ) { $logo = str_replace( 'http://', 'https://', $logo ); }
?>

	<a id="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>">
		<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
	</a>
<?php
} // End woo_logo()
}

add_action( 'woo_header_inside', 'woo_logo' );

/*-----------------------------------------------------------------------------------*/
/* Add custom CSS class to the <body> tag if the lightbox option is enabled. */
/*-----------------------------------------------------------------------------------*/

add_filter( 'body_class', 'woo_add_lightbox_body_class', 10 );

function woo_add_lightbox_body_class ( $classes ) {
	global $woo_options;

	if ( isset( $woo_options['woo_enable_lightbox'] ) && $woo_options['woo_enable_lightbox'] == 'true' ) {
		$classes[] = 'has-lightbox';
	}

	return $classes;
} // End woo_add_lightbox_body_class()

/*-----------------------------------------------------------------------------------*/
/* Load PrettyPhoto JavaScript and CSS if the lightbox option is enabled. */
/*-----------------------------------------------------------------------------------*/

add_action( 'woothemes_add_javascript', 'woo_load_prettyphoto', 10 );
add_action( 'woothemes_add_css', 'woo_load_prettyphoto', 10 );

function woo_load_prettyphoto () {
	global $woo_options;

	if ( ! isset( $woo_options['woo_enable_lightbox'] ) || $woo_options['woo_enable_lightbox'] == 'false' ) { return; }

	$filter = current_filter();

	switch ( $filter ) {
		case 'woothemes_add_javascript':
			wp_enqueue_script( 'enable-lightbox' );
		break;

		case 'woothemes_add_css':
			wp_enqueue_style( 'prettyPhoto' );
		break;

		default:
		break;
	}
} // End woo_load_prettyphoto()

/*-----------------------------------------------------------------------------------*/
/* Customise the default search form */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_customise_search_form' ) ) {
function woo_customise_search_form ( $html ) {
  // Add the "search_main" and "fix" classes to the wrapping DIV tag.
  $html = str_replace( '<form', '<div class="search_main fix"><form', $html );
  // Add the "searchform" class to the form.
  $html = str_replace( ' method=', ' class="searchform" method=', $html );
  // Add the placeholder attribute and CSS classes to the input field.
  $html = str_replace( ' name="s"', ' name="s" class="field s" placeholder="' . esc_attr( __( 'Search...', 'woothemes' ) ) . '"', $html );
  // Wrap the end of the form in a closing DIV tag.
  $html = str_replace( '</form>', '</form></div>', $html );
  // Add the "search-submit" class to the button.
  $html = str_replace( ' id="searchsubmit"', ' class="search-submit" id="searchsubmit"', $html );

  return $html;
} // End woo_customise_search_form()
}

add_filter( 'get_search_form', 'woo_customise_search_form' );

/*-----------------------------------------------------------------------------------*/
/* Load responsive IE scripts */
/*-----------------------------------------------------------------------------------*/

add_action( 'wp_footer', 'woo_load_responsive_IE_footer', 10 );

if ( ! function_exists( 'woo_load_responsive_IE_footer' ) ) {
function woo_load_responsive_IE_footer () {
	$html = '';
	echo '<!--[if lt IE 9]>'. "\n";
	echo '<script src="' . get_template_directory_uri() . '/includes/js/respond.js"></script>'. "\n";
	echo '<![endif]-->'. "\n";

	echo $html;
} // End woo_load_responsive_IE_footer()
}

/*-----------------------------------------------------------------------------------*/
/* Add custom header styling based on theme options. */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_display_header_styles' ) ) {
function woo_display_header_styles () {

	$settings = array(
		'header_typography' => '',
		'header_shadow' => 'true',
		'header_shadow_color' => '#333333',
		'header_bg_color' => '',
		'header_bg_img' => '',
		'header_bg_repeat' => '',
		'header_bg_pos' => '',
		'header_bg_attachment' => ''
	);

	$settings = woo_get_dynamic_values( $settings );

	global $woo_options;

?>
<style type="text/css">
	#header  {
		<?php if ( $settings['header_bg_color'] != '' ) { ?>
			background-color: <?php echo $settings['header_bg_color']; ?>!important;
		<?php } ?>
		<?php if ( $settings['header_bg_img'] != '' ) { ?>
			background-image: url(<?php echo esc_url( $settings['header_bg_img'] ); ?>);
		<?php } ?>
		<?php if ( $settings['header_bg_repeat'] != '' ) { ?>
			background-repeat: <?php echo $settings['header_bg_repeat']; ?>;
		<?php } ?>
		<?php if ( $settings['header_bg_pos'] != '' ) { ?>
			background-position: <?php echo $settings['header_bg_pos']; ?>;
		<?php } ?>
		<?php if ( $settings['header_bg_attachment'] != '' ) { ?>
			background-attachment: <?php echo $settings['header_bg_attachment']; ?>;
		<?php } ?>
	}

	<?php
		if ( isset( $woo_options['woo_header_typography'] ) ) {
			echo '#header p { ' . woo_generate_font_css( $woo_options[ 'woo_header_typography' ] ) . ' }'  . "\n";
		}
	?>

	<?php if ( $settings['header_shadow'] == 'true' ) { ?>
		#header p  {

			<?php if ( $settings['header_shadow_color'] != '' ) { ?>
				text-shadow: 1px 1px 0 <?php echo $settings['header_shadow_color']; ?>;
			<?php } ?>

		}
	<?php } ?>

</style>
<?php
} // End woo_display_header_styles()
}

add_action( 'wp_head', 'woo_display_header_styles' );

if ( ! function_exists( 'woo_adjust_features_tesitmonials_widget_markup' ) ) {
/**
 * Filter the widget markup to make sure the "Features" and "Testimonials" widgets have the same markup as the default homepage components.
 * @since  1.0.0
 * @param  array $instance  Widget arguments.
 * @param  object $thisobj  The instance of the widget.
 * @param  array $args      Arguments for constructing the widget markup.
 * @return array            $instance
 */
function woo_adjust_features_tesitmonials_widget_markup ( $instance, $thisobj, $args ) {
	if ( ! isset( $args['id'] ) || 'homepage' != $args['id'] ) return $instance; // This is not the widget area you are looking for.

	$to_filter = array( 'woothemes_features' => 'features', 'woothemes_testimonials' => 'testimonials' );
	if ( ! isset( $thisobj->id_base ) || ! in_array( $thisobj->id_base, array_keys( $to_filter ) ) ) return $instance; // This is not one of the widgets you are looking for.

	// Remove "widget" CSS class.
	$args['before_widget'] = str_replace( 'widget ', '', $args['before_widget'] );

	// Replace the ID with the appropriate value from the above array.
	$args['before_widget'] = str_replace( $args['widget_id'], $to_filter[$thisobj->id_base], $args['before_widget'] );

	return $instance;
} // End woo_adjust_features_tesitmonials_widget_markup()
}

add_filter( 'widget_display_callback', 'woo_adjust_features_tesitmonials_widget_markup', 10, 3 );

/*-----------------------------------------------------------------------------------*/
/* Control homepage content */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_customise_homepage_query' ) ) {
/**
 * Modify the homepage query, if set to display blog posts, based on the theme options.
 * @since  1.0.0
 * @param  object $q The query object.
 * @return object    The modified query object.
 */
function woo_customise_homepage_query ( $q ) {
	if ( $q->is_admin ) return $q; // We don't want to act in the admin.

	if ( $q->is_home && $q->is_main_query() ) {
		$settings = woo_get_dynamic_values( array( 'homepage_content_type' => 'posts', 'homepage_number_of_posts' => get_option( 'posts_per_page' ), 'homepage_posts_category' => '' ) );
		if ( 'posts' != $settings['homepage_content_type'] ) return $q; // If we're not displaying blog posts, don't modify the query.

		$q->set( 'posts_per_page', intval( $settings['homepage_number_of_posts'] ) );

		if ( 0 < intval( $settings['homepage_posts_category'] ) ) {
			$q->set( 'cat', intval( $settings['homepage_posts_category'] ) );
		}

		$q->parse_query();
	}

	return $q;
} // End woo_customise_homepage_query()
}

add_filter( 'pre_get_posts', 'woo_customise_homepage_query' );

/*-----------------------------------------------------------------------------------*/
/* Contact form processing logic. */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_process_contact_form' ) ) {
function woo_process_contact_form () {
	if ( is_admin() || ( ! isset( $_POST['contact-form-submit'] ) || ( 'yes' != $_POST['contact-form-submit'] ) ) ) return;
	if ( ( ! isset( $_POST['contact-verify'] ) || ( 19 != intval( $_POST['contact-verify'] ) ) ) ) {
		$url = add_query_arg( 'message', 'invalid-verify' );
		wp_safe_redirect( $url );
		exit;
	}
	$is_sent = false;

	$missing_fields = array();

	// Preserve the current URL.
	$url = add_query_arg( 'send-attempt', 'yes' );

	// Detect empty fields.
	foreach ( array( 'contact-name', 'contact-email', 'contact-message' ) as $k => $v ) {
		if ( !isset( $_POST[$v] ) || '' == $_POST[$v] ) {
			$missing_fields[] = $v; // Track empty fields.
		}
	}

	if ( 0 >= count( $missing_fields ) && is_email( $_POST['contact-email'] ) ) {
		$settings = woo_get_dynamic_values( array( 'contactform_email' => get_option( 'admin_email' ) ) );

		// We have all the fields, so lets send.
		$to = $settings['contactform_email'];
		$from_name = strip_tags( $_POST['contact-name'] );
		$from_email = esc_attr( strip_tags( trim( strtolower( $_POST['contact-email'] ) ) ) );
		$subject = sprintf( apply_filters( 'woo_contact_form_subject', __( 'Contact form message via %s.' ) ), get_bloginfo( 'name' ) );
		$message = esc_html( $_POST['contact-message'] );
		$headers = 'From: ' . esc_html( $from_name ) . ' <' . $from_email . '>' . "\r\n";

		$is_sent = wp_mail( $to, $subject, $message, $headers );
	}

	if ( true == $is_sent ) {
		$url = add_query_arg( 'message', 'success', $url );
		$url = remove_query_arg( 'contact-name', $url );
		$url = remove_query_arg( 'contact-email', $url );
		$url = remove_query_arg( 'contact-message', $url );
		$url = remove_query_arg( 'send-attempt', $url );
	} else {
		foreach ( array( 'contact-name', 'contact-email', 'contact-message' ) as $k => $v ) {
			if ( isset( $_POST[$v] ) && '' != $_POST[$v] ) {
				$url = add_query_arg( $v, $_POST[$v], $url );
			}
		}

		$error_type = 'error';
		if ( 0 < count( $missing_fields ) ) $error_type = 'fields-missing';

		$url = add_query_arg( 'message', $error_type, $url );
	}

	wp_safe_redirect( $url );
	exit;
} // End woo_process_contact_form()
}

add_action( 'init', 'woo_process_contact_form' );


/*-----------------------------------------------------------------------------------*/
/* Filter the navigation menu to remove the active state from home links with a # */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_filter_nav_menu_css_classes' ) ) {
/**
 * For homepage links with a #, remove the "current" CSS classes.
 * @since  1.0.0
 * @param  array 	$classes CSS classes
 * @param  object 	$item    The menu item
 * @param  array 	$args    Arguments for displaying the menu item.
 * @return array          	 CSS classes (modified array)
 */
function woo_filter_nav_menu_css_classes ( $classes, $item, $args ) {
	if ( is_home() && isset( $item->url ) && stristr( $item->url, '#' ) ) {
		$to_remove = array( 'current-menu-item', 'current_page_item' ); // Classes we want to remove.

		$split = explode( '#', $item->url );
		if ( home_url( '/' ) == trailingslashit( $split[0] ) ) {
			foreach ( $classes as $k => $v ) {
				if ( in_array( $v, $to_remove ) ) {
					unset( $classes[$k] );
					if ( ! in_array( 'hash-item', $classes ) ) $classes[] = 'hash-item';
				}
			}
		}
	}

	return $classes;
} // End woo_filter_nav_menu_css_classes()
}

add_filter( 'nav_menu_css_class', 'woo_filter_nav_menu_css_classes', 10, 3 );

/*-----------------------------------------------------------------------------------*/
/* Add the headings to the features and testimonials widgets, when using them in the "Homepage" widget area. */
/*-----------------------------------------------------------------------------------*/

add_filter( 'woothemes_features_args', 'woo_add_widget_heading' );
add_filter( 'woothemes_testimonials_args', 'woo_add_widget_heading' );

if ( ! function_exists( 'woo_add_widget_heading' ) ) {
function woo_add_widget_heading ( $args ) {
	$settings = woo_get_dynamic_values( array( 'homepage_features_area_heading' => '', 'homepage_testimonials_area_heading' => '' ) );

	switch ( current_filter() ) {
		case 'woothemes_features_args':
		if ( ! woo_is_widget_in_sidebar( 'woothemes_features', 'homepage' ) || ! is_home() ) return $args;

		if ( '' != $settings['homepage_features_area_heading'] ) {
			$args['before_title'] = '<span class="heading">' . esc_html( $settings['homepage_features_area_heading'] ) . '</span>' . $args['before_title'];
		}

		break;

		case 'woothemes_testimonials_args':
		if ( ! woo_is_widget_in_sidebar( 'woothemes_testimonials', 'homepage' ) || ! is_home() ) return $args;

		if ( '' != $settings['homepage_testimonials_area_heading'] ) {
			$args['before_title'] = '<span class="heading">' . esc_html( $settings['homepage_testimonials_area_heading'] ) . '</span>' . $args['before_title'];
		}

		break;

		default:
		break;
	}
	return $args;
} // End woo_add_widget_heading()
}

if ( ! function_exists( 'woo_is_widget_in_sidebar' ) ) {
/**
 * Check if a specified widget exists within a specified sidebar.
 * @since  1.0.0
 * @param  string $widget  Widget ID base.
 * @param  string $sidebar Sidebar ID.
 * @return boolean
 */
function woo_is_widget_in_sidebar ( $widget, $sidebar ) {
	global $sidebars_widgets;
	if ( isset( $sidebars_widgets[$sidebar] ) && 0 < count( $sidebars_widgets[$sidebar] ) )
		foreach ( $sidebars_widgets[$sidebar] as $k => $v )
			if ( strpos( $v, $widget ) !== false )
				return true;
			else
				return false;
} // End woo_is_widget_in_sidebar()
}

/*-----------------------------------------------------------------------------------*/
/* END */
/*-----------------------------------------------------------------------------------*/
?>