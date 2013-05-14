jQuery(window).load(function($){

/*-----------------------------------------------------------------------------------*/
/* PrettyPhoto (lightbox) */
/*-----------------------------------------------------------------------------------*/

	jQuery( 'a[rel^="lightbox"]' ).prettyPhoto({ 'social_tools': false });

/*-----------------------------------------------------------------------------------*/
/* Portfolio thumbnail hover effect */
/*-----------------------------------------------------------------------------------*/

	jQuery('#portfolio img, .widget-portfolio-snapshot img').mouseover(function() {
		jQuery(this).stop().fadeTo(300, 0.5);
	});
	jQuery('#portfolio img, .widget-portfolio-snapshot img').mouseout(function() {
		jQuery(this).stop().fadeTo(400, 1.0);
	});

/*-----------------------------------------------------------------------------------*/
/* Portfolio tag toggle on page load, based on hash in URL */
/*-----------------------------------------------------------------------------------*/

	if ( jQuery( '.port-cat a' ).length ) {
		var currentHash = '';
		currentHash = window.location.hash;
		
		// If we have a hash, begin the logic.
		if ( currentHash != '' ) {
			currentHash = currentHash.replace( '#', '' );
			
			if ( jQuery( '#portfolio .' + currentHash ).length ) {
				
				// Move the "last" CSS class appropriately.
				var itemSelector = '.' + currentHash;

				woo_move_last_class( itemSelector, '#portfolio', 4 );
				
				// Select the appropriate item in the category menu.
				jQuery( '.port-cat a.current' ).removeClass( 'current' );
				jQuery( '.port-cat a[rel="' + currentHash + '"]' ).addClass( 'current' );
				
				// Show only the items we want to show.
				jQuery( '#portfolio .portfolio-item' ).hide();
				jQuery( '#portfolio .' + currentHash ).fadeIn( 400 );
			
			}
		}

	}

/*-----------------------------------------------------------------------------------*/
/* Portfolio tag sorting */
/*-----------------------------------------------------------------------------------*/
								
	jQuery('.has-filtering .port-cat a').click(function(evt){
		var clicked_cat = jQuery(this).attr('rel');
		
		jQuery( '.port-cat a.current' ).removeClass( 'current' );
		jQuery( this ).addClass( 'current' );
		
		// Move the "last" CSS class appropriately.
		var itemSelector = '.portfolio-item';
		if ( clicked_cat != 'all' ) {
			itemSelector = '.' + clicked_cat;
		}
		
		woo_move_last_class( itemSelector, '#portfolio', 4 );
		
		if( clicked_cat == 'all' ) {
			jQuery( '#portfolio .portfolio-item' ).hide().fadeIn(200);
		} else {
			jQuery( '#portfolio .portfolio-item' ).hide();
			jQuery( '#portfolio .' + clicked_cat ).fadeIn(400);
		}
		
		//eq_heights();
		evt.preventDefault();
	})	

														
});

jQuery( window ).load( function ( $ ) {

	// Thanks @johnturner, I owe you a beer!
	
	var postMaxHeight = 0;
	jQuery("#portfolio-gallery .portfolio-item").each(function (i) {
		 var elHeight = jQuery(this).height();
		 
		 if(parseInt(elHeight) > postMaxHeight){
			 postMaxHeight = parseInt(elHeight);
		 }
	});
	jQuery("#portfolio-gallery .portfolio-item").each(function (i) {
		jQuery(this).css('height',postMaxHeight+'px');
	});

});

/**
 * woo_move_last_class function.
 *
 * @description Move the "last" CSS class according to the number of items per row.
 * @access public
 * @param string itemSelector
 * @param string containerSelector
 * @param int perRow
 * @return void
 */
function woo_move_last_class ( itemSelector, containerSelector, perRow ) {
	jQuery( containerSelector + ' .last' ).removeClass( 'last' );
	jQuery( containerSelector + ' ' + itemSelector ).each( function ( i ) {
		if ( i % ( perRow - 1 ) == 0 && i > 0 ) {
			jQuery( this ).addClass( 'last' );
		}
	});
} // End woo_move_last_class()