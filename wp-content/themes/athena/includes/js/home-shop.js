if ( jQuery( window ).width() > 768 ) {
	jQuery(window).load(function() {
		jQuery('#home-shop .col-full').flexslider({
			controlsContainer: woo_shop_data.controlsContainer,
			animation: woo_shop_data.animation,
		    animationLoop: woo_shop_data.animationLoop,
		    itemWidth: woo_shop_data.itemWidth,
		    controlNav: woo_shop_data.controlNav,
		    maxItems: woo_shop_data.maxItems,
		    move: woo_shop_data.move
		});
	});
} else {
	jQuery('#home-shop').addClass("mobile");
}