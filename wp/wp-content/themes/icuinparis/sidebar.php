<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
/**
 * Sidebar Template
 *
 * If a `primary` widget area is active and has widgets, display the sidebar.
 *
 * @package ICU
 * @subpackage Template
 */

?>	
<aside id="sidebar" class="row">
	<?php 

	if ( woo_active_sidebar( 'primary' ) ) 
		woo_sidebar( 'primary' );	                
		woo_sidebar_inside_after(); 
	?> 
</aside><!-- /#sidebar -->