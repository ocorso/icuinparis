// JavaScript Document
jQuery(document).ready(function() {
	jQuery('.preview_gallery').click(function(){
		var url = jQuery(this).attr("href");
		var order = jQuery('#mainform').serialize();
		var height = 500;
		var gallery_height = jQuery('#mainform').find('#product_gallery_height').val();
		var navbar_height = jQuery('#mainform').find('#navbar_height').val();
		var thumb_height = jQuery('#mainform').find('#thumb_height').val();
		height = parseInt(gallery_height) + parseInt(navbar_height) + parseInt(thumb_height) + 80;
		tb_show('Dynamic gallery preview', url+'&width=700&height='+height+'&action=woo_dynamic_gallery&KeepThis=false&'+order);
		return false;
	});
});	