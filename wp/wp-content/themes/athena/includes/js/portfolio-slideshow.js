jQuery( window ).load( function() {
   	jQuery( '#portfolio-slideshow .flexslider' ).flexslider({
      animation: woo_portfolio_data.animation,
      animationLoop: woo_portfolio_data.animationLoop,
      itemWidth: woo_portfolio_data.itemWidth,
      itemMargin: woo_portfolio_data.itemMargin,
      maxItems: woo_portfolio_data.maxItems,
      controlsContainer: woo_portfolio_data.controlsContainer
   	});
});