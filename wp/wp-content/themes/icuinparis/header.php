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
	<script>window.jQuery || document.write('<script src="<?php bloginfo('stylesheet_directory'); ?>/js/vendor/jquery.js"><\/script>')</script>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="wrapper" class="container">
<?php 
	$base_url = "http://icuinparis.dev";
	include_once('php/common_icu_header.php'); ?>
