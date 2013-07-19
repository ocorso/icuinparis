<?php 
$settings = array(
					'custom_intro_message' => 'true',
					'custom_intro_message_text' => __( 'Say hello to Athena. A brand new WordPress theme by WooThemes.', 'woothemes' )
					);
					
$settings = woo_get_dynamic_values( $settings );
?>
<?php if ( $settings['custom_intro_message_text'] != '' ) { ?>
<div id="intro-message">
	
	<div class="col-full">
		<header>
			<h1><?php echo $settings['custom_intro_message_text']; ?></h1>
		</header>
	</div><!--/.col-full-->

</div><!--/#intro-message-->
<?php } ?>