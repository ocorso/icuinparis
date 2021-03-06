<?php

if ( function_exists( 'add_theme_support' ) ) {
  add_theme_support( 'post-thumbnails' );
  set_post_thumbnail_size( 370, 289 );
}

add_filter( 'post_thumbnail_html', 'remove_thumbnail_dimensions', 10, 3 );

function remove_thumbnail_dimensions( $html, $post_id, $post_image_id ) {
    $html = preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );
    return $html;
}

function custom_excerpt_length( $length ) {
  return 25;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

function create_post_types() {
  register_post_type(   'footer-widget', 
                        array( 'labels' => array(
                                'name' => __( 'Footer Widgets' ),
                                'singular_name' => __( 'footer-widget' )
                              ),
                        'public' => true,
                        'has_archive' => false,
                      )
  );
  register_post_type(   'designers', 
                        array(  'labels' => array(
                                'name' => __( 'Designers' ),
                                'singular_name' => __( 'designer' )
                              ),
                        'public' => true,
                        'has_archive' => false,
                        'supports'=>array('title', 'editor','thumbnail')
                      )
  );
  register_post_type(   'carousel', 
                        array(  'labels' => array(
                                'name' => __( 'Carousel' ),
                                'singular_name' => __( 'carousel' )
                              ),
                        'public' => true,
                        'has_archive' => false,
                        'supports'=>array('title', 'editor','thumbnail')
                      )
  );
  

}

function ored_pre_get_posts($query){

        //oc: don't include the following categories:
        //  - 1342 featured products, 
        //  - 1345 store,
        //  - 1344 footer widget
  if($query->is_home()) 
    $query->set( 'cat', '-1342,-1345,-1344' );
}

function icu_scripts() {
	wp_enqueue_script('modernizr', get_stylesheet_directory_uri() . '/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js');
  wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/js/vendor/bootstrap.min.js', array( 'jquery','modernizr' ));
	wp_enqueue_script('icu', get_stylesheet_directory_uri() . '/js/main.js', array( 'jquery','modernizr','bootstrap' ));


  if( !is_admin() ){
    wp_deregister_script('jquery');
    wp_register_script('jquery', get_stylesheet_directory_uri() . '/js/vendor/jquery.js', false, '1.10.1', true);
    wp_enqueue_script('jquery');
  }

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
  wp_register_style('ored-responsive', get_stylesheet_directory_uri() . '/css/ored-responsive.css', array(), '20130701','all');

  // enqueing:
  wp_enqueue_style( 'bootstrap-styles' );
  wp_enqueue_style( 'responsive-styles' );
  wp_enqueue_style( 'ored-styles' );
  wp_enqueue_style( 'ored-responsive' );
}

function the_slug() {

  global $post;

  if ( is_single() || is_page() ) {
  return $post->post_name;
  }
  else {
  return "";
  }

}

// add actions
add_action('wp_enqueue_scripts', 'icu_styles');
add_action( 'wp_enqueue_scripts', 'icu_scripts' );
add_action( 'pre_get_posts', 'ored_pre_get_posts' );
add_action( 'init', 'create_post_types' );

//oc: remove feed and other extras...
remove_action('wp_head',              'wp_generator');
remove_action( 'wp_head',             'feed_links',                      2     );
remove_action( 'wp_head',             'feed_links_extra',                3     );
remove_action( 'wp_head',             'rsd_link'                               );
remove_action( 'wp_head',             'wlwmanifest_link'                       );
remove_action( 'wp_head',             'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action( 'wp_head',             'rel_canonical'                          );

//oc: remove filters
remove_filter( 'the_content', 'wpautop' );