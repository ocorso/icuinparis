<?php
remove_action('wp_head', 'wp_generator');

function icu_scripts() {
	wp_enqueue_script('modernizr', get_stylesheet_directory_uri() . '/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js');
	wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/js/vendor/bootstrap.min.js', array( 'jquery','modernizr' ));
}



function icu_styles()  
{ 
  // Register the style like this for a theme:  
  // (First the unique name for the style (custom-style) then the src, 
  // then dependencies and ver no. and media type)

  wp_register_style( 'bootstrap-styles', get_stylesheet_directory_uri() . '/css/bootstrap.min.css', array(), '20130621', 'all' );
  wp_register_style( 'responsive-styles', get_stylesheet_directory_uri() . '/css/bootstrap-responsive.min.css', array(), '20130621', 'all' );
  wp_register_style( 'concatenated-styles', get_stylesheet_directory_uri() . '/css/concate-style.css', array(), '20130621', 'all' );

  wp_register_style('ored-styles', get_stylesheet_directory_uri() . '/style.css', array(), '20130621','all');

  // enqueing:
  wp_enqueue_style( 'bootstrap-styles' );
  wp_enqueue_style( 'responsive-styles' );
  //wp_enqueue_style( 'concatenated-styles' );
  wp_enqueue_style( 'ored-styles' );
}

// add actions
add_action('wp_enqueue_scripts', 'icu_styles');
add_action( 'wp_enqueue_scripts', 'icu_scripts' );


//oc: remove feed and other extras...
remove_action( 'wp_head',             'feed_links',                      2     );
remove_action( 'wp_head',             'feed_links_extra',                3     );
remove_action( 'wp_head',             'rsd_link'                               );
remove_action( 'wp_head',             'wlwmanifest_link'                       );
remove_action( 'wp_head',             'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action( 'wp_head',             'rel_canonical'                          );