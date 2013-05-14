<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * WooThemes Component Widget
 *
 * A WooThemes standardized component widget.
 *
 * @package WordPress
 * @subpackage WooFramework
 * @category Widgets
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * protected $woo_widget_cssclass
 * protected $woo_widget_description
 * protected $woo_widget_idbase
 * protected $woo_widget_title
 * 
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - load_component()
 */
class Woo_Widget_Component extends WP_Widget {
	protected $woo_widget_cssclass;
	protected $woo_widget_description;
	protected $woo_widget_idbase;
	protected $woo_widget_title;

	/**
	 * Constructor function.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct() {
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_woo_component';
		$this->woo_widget_description = __( 'This is a WooThemes standardized component loading widget. Intended primarily for use in the "Homepage" widget region.', 'woothemes' );
		$this->woo_widget_idbase = 'woo_component';
		$this->woo_widget_title = __( 'Woo - Component', 'woothemes' );

		$this->woo_widget_componentslist = array(
												'posts-timeline' => __( 'Posts Timeline', 'woothemes' ),
												'blog-posts' => __( 'Blog Posts', 'woothemes' ),
												'page-content' => __( 'Page Content', 'woothemes' ),
												'contact-area' => __( 'Contact Area', 'woothemes' )
												);

		if ( is_woocommerce_activated() ) {
			$this->woo_widget_componentslist['hero-product'] = __( 'Hero Product', 'woothemes' );
			$this->woo_widget_componentslist['featured-products'] = __( 'Featured Products', 'woothemes' );
		}

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->woo_widget_idbase );

		/* Create the widget. */
		$this->WP_Widget( $this->woo_widget_idbase, $this->woo_widget_title, $widget_ops, $control_ops );	
	} // End __construct()

	/**
	 * Display the widget on the frontend.
	 * @since  1.0.0
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Widget settings for this instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {  
		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );
			
		/* Before widget (defined by themes). */
		//echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) { echo $before_title . $title . $after_title; }
		
		/* Widget content. */
		// Add actions for plugins/themes to hook onto.
		do_action( $this->woo_widget_cssclass . '_top' );
		
		if ( in_array( $instance['component'], array_keys( $this->woo_widget_componentslist ) ) ) {
			$this->load_component( esc_attr( $instance['component'] ) );
		}

		// Add actions for plugins/themes to hook onto.
		do_action( $this->woo_widget_cssclass . '_bottom' );

		/* After widget (defined by themes). */
		//echo $after_widget;

	} // End widget()

	/**
	 * Method to update the settings from the form() method.
	 * @since  1.0.0
	 * @param  array $new_instance New settings.
	 * @param  array $old_instance Previous settings.
	 * @return array               Updated settings.
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* The select box is returning a text value, so we escape it. */
		$instance['component'] = esc_attr( $new_instance['component'] );

		return $instance;
	} // End update()

	/**
	 * The form on the widget control in the widget administration area.
	 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff.
	 * @since  1.0.0
	 * @param  array $instance The settings for this instance.
	 * @return void
	 */
    public function form( $instance ) {       
   
		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'component' => ''
					);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
?>
		<!-- Widget Component: Select Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'component' ); ?>"><?php _e( 'Component:', 'woothemes' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'component' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'component' ); ?>">
			<?php foreach ( $this->woo_widget_componentslist as $k => $v ) { ?>
				<option value="<?php echo $k; ?>"<?php selected( $instance['component'], $k ); ?>><?php echo $v; ?></option>
			<?php } ?>       
			</select>
		</p>
		<p><small><?php printf( __( 'The settings for these components are controlled via the %sTheme Options%s screen.', 'woothemes' ), '<a href="' . esc_url( admin_url( 'admin.php?page=woothemes' ) ) . '">', '</a>' ); ?></small></p>
<?php
	} // End form()

	/**
	 * Load the desired component, if a method is available for it.
	 * @param  string $component The component to potentially be loaded.
	 * @since  5.0.8
	 * @return void
	 */
	protected function load_component ( $component ) {
		switch ( $component ) {
			case 'posts-timeline':
				get_template_part( 'includes/posts-timeline' );
			break;
			
			case 'blog-posts':
				get_template_part( 'includes/blog-posts' );
			break;
			
			case 'page-content':
				get_template_part( 'includes/specific-page-content' );
			break;
			
			case 'contact-area':
				get_template_part( 'includes/contact-area' );
			break;

			case 'featured-products':
				get_template_part( 'includes/featured-products' );
			break;

			case 'hero-product':
				get_template_part( 'includes/hero-product' );
			break;

			default:
			break;
		}
	} // End load_component()
} // End Class

/* Register the widget. */
add_action( 'widgets_init', create_function( '', 'return register_widget("Woo_Widget_Component");' ), 1 ); 
?>