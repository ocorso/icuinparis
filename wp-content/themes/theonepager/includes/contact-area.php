<?php
// File Security Check
if ( ! function_exists( 'wp' ) && ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}
?><?php
/**
 * Contact Area Component
 *
 * Here we setup all logic and XHTML that is required for the contact area component, used on the homepage.
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;

	$settings = array(
				'homepage_contact_area_heading' => '',
				'homepage_contact_area_title' => '',
                'homepage_enable_social' => 'true',
                'homepage_enable_contactform' => 'true'
				);

	$settings = woo_get_dynamic_values( $settings );

?>

<div id="contact-area" class="widget_woo_component">

    <div class="col-full">

    	<span class="heading"><?php echo esc_html( $settings['homepage_contact_area_heading'] ); ?></span>

		<h2 class="widget-title"><?php echo esc_html( $settings['homepage_contact_area_title'] ); ?></h2>
    	<?php
            if ( 'true' == $settings['homepage_enable_social'] ) {
               woo_display_social_icons();
            }

            if ( 'true' == $settings['homepage_enable_contactform'] ) {
            if ( isset( $_GET['message'] ) && 'success' == $_GET['message'] ) {
                echo do_shortcode( '[box type="tick"]' . __( 'Your message has been sent successfully.', 'woothemes' ) . '[/box]' );
            }
            if ( isset( $_GET['message'] ) && 'fields-missing' == $_GET['message'] ) {
                echo do_shortcode( '[box type="alert"]' . __( 'There are fields missing or incorrect. Please try again.', 'woothemes' ) . '[/box]' );
            }
            if ( isset( $_GET['message'] ) && 'invalid-verify' == $_GET['message'] ) {
                echo do_shortcode( '[box type="alert"]' . __( 'The verification code entered is incorrect. Please try again.', 'woothemes' ) . '[/box]' );
            }
            if ( isset( $_GET['message'] ) && 'error' == $_GET['message'] ) {
                echo do_shortcode( '[box type="alert"]' . __( 'There was an error sending your message. Please try again.', 'woothemes' ) . '[/box]' );
            }

            // If the form has errors, get the form data back.
            $data = woo_get_posted_contact_form_data();
        ?>
    	<form id="homepage-contact-form" action="" method="post">
    		<textarea name="contact-message" value="<?php esc_attr_e( 'Your Message', 'woothemes' ); ?>" onfocus="if ( this.value == '<?php esc_attr_e( 'Your Message', 'woothemes' ); ?>' ) { this.value = ''; }" onblur="if ( this.value == '' ) { this.value = '<?php esc_attr_e( 'Your Message', 'woothemes' ); ?>'; }"></textarea>
    		<div class="col-right">
    			<input type="text" name="contact-name" value="<?php esc_attr_e( 'Name and Surname', 'woothemes' ); ?>" onfocus="if ( this.value == '<?php esc_attr_e( 'Name and Surname', 'woothemes' ); ?>' ) { this.value = ''; }" onblur="if ( this.value == '' ) { this.value = '<?php esc_attr_e( 'Name and Surname', 'woothemes' ); ?>'; }" />
    			<input type="text" name="contact-email" value="<?php esc_attr_e( 'Your email address', 'woothemes' ); ?>" onfocus="if ( this.value == '<?php esc_attr_e( 'Your email address', 'woothemes' ); ?>' ) { this.value = ''; }" onblur="if ( this.value == '' ) { this.value = '<?php esc_attr_e( 'Your email address', 'woothemes' ); ?>'; }" />
                <input type="text" name="contact-verify" value="<?php esc_attr_e( '7 + 12 = ?', 'woothemes' ); ?>" onfocus="if ( this.value == '<?php esc_attr_e( '7 + 12 = ?', 'woothemes' ); ?>' ) { this.value = ''; }" onblur="if ( this.value == '' ) { this.value = '<?php esc_attr_e( '7 + 12 = ?', 'woothemes' ); ?>'; }" />
                <input type="hidden" name="contact-form-submit" value="yes" />
    			<input type="submit" value="<?php esc_attr_e( 'Send that email!', 'woothemes' ); ?>" />
    		</div>
    	</form>
    	<?php } ?>
    </div>

</div>