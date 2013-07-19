<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Search Form Template
 *
 * This template is a customised search form.
 *
 * @package WooFramework
 * @subpackage Template
 */
?>
<div class="search_main fix">
    <form method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" >
        <input type="text" class="field s" name="s" placeholder="<?php esc_attr_e( 'Search...', 'woothemes' ); ?>" />
    </form>    
</div><!--/.search_main-->