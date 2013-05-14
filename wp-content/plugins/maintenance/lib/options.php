	<?php global $mt_option; $lib_options = ''; $lib_options = $mt_option['lib_options']; ?>
		<div class="option-box">
			<label for="page_title"><?php _e('Page title', 'maintenance');?></label>
				<input type="text" id="page_title" name="lib_options[page_title]" value="<?php echo ( isset( $lib_options['page_title'] ) ) ? stripslashes($lib_options['page_title']) : _e('Website is under construction', 'maintenance'); ?>" />
		</div>			
		<div class="option-box">
			<label for="heading"><?php _e('Headline', 'maintenance');?></label>
				<input type="text" id="heading" name="lib_options[heading]" value="<?php echo ( isset( $lib_options['heading'] ) ) ? stripslashes($lib_options['heading']) : _e('Customer login', 'maintenance'); ?>" />
		</div>	
		<div class="option-box">
			<label for="time_text"><?php _e('Description', 'maintenance');?></label>
				<input type="text" id="time_text" name="lib_options[time_text]" value="<?php echo ( isset( $lib_options['time_text'] ) ) ? stripslashes($lib_options['time_text']) : _e('Please login to see website', 'maintenance'); ?>" />
		<div class="option-box">	
		<label for="theme_logo"><?php _e('Logo', 'maintenance');?></label>
			<input type="file" id="theme_logo" name="lib_options[logo]" />
			<?php if ( $lib_options['logo'] ): ?><input class="button remove" type="submit" name="remove_logo" value="x" /><?php endif; ?>
		</div>	
		<div class="option-box">
		<label for="body_bg"><?php _e('Background image', 'maintenance');?></label>
			<input type="file" id="body_bg" name="lib_options[body_bg]" />
			<?php if ( $lib_options['body_bg'] ): ?><input class="button remove" type="submit" name="remove_bg" value="x" /><?php endif; ?>
		</div>	
		<div class="option-box">
			<label for="body_bg_color"><?php _e('Background color', 'maintenance');?></label>
				<input type="text" id="body_bg_color" name="lib_options[body_bg_color]" value="<?php echo ( isset( $lib_options['body_bg_color'] ) ) ? stripslashes($lib_options['body_bg_color']) : '#fff'; ?>" />
		</div>		
		<div class="option-box">
			<label for='Admin Bar'><?php _e('Admin Bar', 'maintenance');?></label>
				<input type="checkbox"  id="admin_bar_enabled" name="lib_options[admin_bar_enabled]" value="1" <?php if (isset($lib_options['admin_bar_enabled'])) { if  ($lib_options['admin_bar_enabled'] == "1") { echo 'checked="checked"'; }} ?>/>
		</div>		
