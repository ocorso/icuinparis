<?php

	function mt_manage_options()
	{
		global $mt_option;
		
		$mt_option = mt_get_option();
		
		if ( $_POST ) {
				  $state = $_POST['state']; 
  			  if (!$state)   { $mt_option['state'] = 'live';  } 
						else { $mt_option['state'] = 'maintenance'; }
			
			$mt_option['lib_options'] = $_POST['lib_options'];
			
			/* trigger theme activation */
			if ( file_exists( dirname( __FILE__ ).'/'.LIB_DIR.'/functions.php' ) ) {
			 include_once dirname( __FILE__ ).'/'.LIB_DIR.'/functions.php';
   		     $mt_option = apply_filters( 'lib_update', $mt_option );
			}
			
			/* counter */
			$mt_option['expiry_date'] = $_POST['lib_options']['expiry_date'] ? $_POST['lib_options']['expiry_date'] : '';
			if ( !$mt_option['expiry_date'] ) {
	   unset( $mt_option['expiry_date'] );
														}
			mt_update_option($mt_option);
		}
		
		mt_get_lib_var();
		?>
		
		<div id="maintenance-options" class="wrap">	
			<form method="post" action="" enctype="multipart/form-data" name="options-form">
					<div id="general">
						<div class="title">
							<img src="<?php echo PLUGIN_URL ?>images/icon.png" alt="Logo" class="logo" />
							<h1>Maintenance -</h1>						
							<input name="state" type="checkbox" id="ch_location" name="ch_location"  <?php if ( $mt_option['state'] == 'maintenance' )  echo 'checked="true"' ?> />
						</div>						
				
						<div class="theme-options">
							<?php  include_once dirname( __FILE__ ).'/'.LIB_DIR.'/options.php';  ?>
							 <input type="submit" id="mt-submit" name="save_changes" class="button-primary" value="<?php _e('Save changes', 'maintenance');?>" />
						</div>  
					</div>					 
			</form>
			
			<!-- Contact Support-->
			<div id="contact-support">
				<a href="mailto:support@fruitfulcode.com" title="<?php _e('Contact Support', 'maintenance');?>" target="_blank">
					<?php _e('Contact Support', 'maintenance');?>
				</a><br> 
				<a href="http://codecanyon.net/item/maintenance-wordpress-plugin/2781350?ref=fruitfulcode" title="<?php _e('Premium version', 'maintenance');?>" target="_blank">
					<?php _e('PURCHASE EXTENDED VERSION', 'maintenance');?>
				</a><br>
			</div>
			<!-- End Contact Support-->		
			
		</div>
		
		<?php
	
	}

?>