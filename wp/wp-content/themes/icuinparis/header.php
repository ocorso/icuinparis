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
	<script>
		var base_url 	= "<?= bloginfo('url'); ?>";
		var skin_url	= "<?= get_stylesheet_directory_uri(); ?>";
	</script>
	<meta property="fb:app_id" content="567784666596189" />
	<meta property="og:title" content="<?php woo_title( '' ); ?>" />
	<meta property="og:url" content="<?= the_permalink(); ?>" />
	<meta property="og:description" content="ICU in Paris is a jewery and accessories retailer based in New York and Paris" />
	<meta property="og:site_name" content="ICU in Paris" />
<?php 

	if (has_post_thumbnail( )){
		$domsxe	= simplexml_load_string(get_the_post_thumbnail());
		$img 	= $domsxe->attributes()->src;
	}else{
		$img 	=  get_stylesheet_directory_uri() . "/img/ICU.jpg";
	}
?>
	<meta property="og:image" content="<?= $img; ?>" />
	<meta property="og:type" content="<?= is_single() ? 'article' : 'website'; ?>" />
	<?php wp_head(); ?>
	
</head>

<body <?php body_class(); ?>>

<div id="wrapper" class="container">
<?php 
define("IS_SSL", $_SERVER['HTTPS'] == "on"); 
	include_once('php/common_icu_header.php'); ?>
