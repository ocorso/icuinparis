jQuery(document).ready(function(){
    jQuery(".gen_shortcode").live('change',function(){
        var $categ_rss=jQuery("#categ_rss_select").val();
        var $select_cat;
        var $external_rss="";
		var taxonomy='';
		var posttype='';
		var $show_menu="";		
        switch($categ_rss){
            case "1":
							$select_cat=jQuery("#category_item").val();
							$select_categories="none";
							$show_menu=jQuery("#show_menu").attr("checked")?" show_menu='1'":"";
							$select_cat=jQuery("#show_menu").attr("checked")?"0":$select_cat;								
							jQuery('.show_menu_table').show();							
							break;
            case "2":
							$select_cat="E";
							$select_categories="none";
							$external_rss=" externalrss='"+jQuery("#external_rss").val()+"'";
							jQuery('.show_menu_table').hide();							
							break;
            case "3":
							$select_cat="R";
							$select_categories="none";
							$external_rss=" prd='"+jQuery('#aff_prd').val()+"'";
								jQuery('.show_menu_table').hide();							
							break;
            case "4":
							$select_cat="none";
							$select_categories="none";
							taxonomy=jQuery('#taxonomy_item').val();
							$external_rss=" prd='"+jQuery('#aff_prd').val()+"'";
							$show_menu=jQuery("#show_menu").attr("checked")?" show_menu='1'":"";
							$select_cat=jQuery("#show_menu").attr("checked")?"0":$select_cat;
							jQuery('.show_menu_table').show();							
							break;
            case "5":
							$select_cat="none";
							$select_categories="none";
							posttype=jQuery('#posttype_item').val();
							$external_rss=" prd='"+jQuery('#aff_prd').val()+"'";
							jQuery('.show_menu_table').hide();
						break;
			case "6":
							$select_cat="none";
							$select_categories=jQuery("#categories").val();
						if($select_categories==null){
							$select_categories="all";
						}else{
							$select_categories=jQuery("#categories").val();
							$external_rss=" prd='"+jQuery('#aff_prd').val()+"'";
							jQuery('.show_menu_table').hide();
						}
						break;
        }
        $show_title=jQuery("#show_title").attr("checked")?" showtitle='1'":"";
        $show_excerpt=jQuery("#show_excerpt").attr("checked")?" showexcerpt='1'":"";
        $show_image=jQuery("#show_image").attr("checked")?" showimage='1'":"";
        $masonry=jQuery("#enable_masonry").attr("checked")?" masonry='1'":"";
        jQuery("#matrix_shortcode").val("[wpts_matriz boxwidth='"+jQuery("#boxwidth").val()+"' pintimg='"+jQuery("#pint_img").val()+"' category='"+$select_cat+"' categories='"+$select_categories+"' "+$show_menu+" posttype='"+posttype+"' taxonomy='"+taxonomy+"' posts='"+jQuery("#posts_number").val()+"' limit='"+jQuery("#posts_limit").val()+" 'order='"+jQuery('#posts_order option:selected').val()+"' boxstyle='"+jQuery("#box_style").val()+"' buttoncolor='"+jQuery("#readmore_color").val()+"' buttontext='"+jQuery("#readmore_txt").val()+"' titlesize='"+jQuery("#title_fontsize").val()+"' contentsize='"+jQuery("#content_fontsize").val()+"' titlefont='"+jQuery("#title_fontstyle").val()+"' contentfont='"+jQuery("#content_fontstyle").val()+"'"+$external_rss+$show_title+$show_excerpt+$show_image+$masonry+"]");
        //alert($select_categories)
    });
    jQuery(".numericfield").live('keypress',function(event){    
        if(isNaN(String.fromCharCode(event.charCode))){
            event.preventDefault();
        }
    });
    jQuery(".numericfield").live('blur',function(){
        if(jQuery(this).val()==''||parseInt(jQuery(this).val())<=0){
					if(jQuery(this).attr('id')=='posts_limit'){
						jQuery(this).val("0"); 
					}else{
            jQuery(this).val("1");
					}
        }else{
            jQuery(this).val(parseInt(jQuery(this).val()));
        }
    });
    jQuery("#box_style").live('change',function(){
        jQuery("#screen_pre").removeClass();
        jQuery("#screen_pre").addClass("screen"+jQuery(this).val());
    });
	jQuery("#posts_order").live('change',function(){
        
    });
    
    jQuery(".chkgrp").live('change',function(){
        var cnt=0;
       jQuery(".chkgrp").each(function(){           
           if(jQuery(this).attr("checked")){
               cnt++
           }       
       });
       if(cnt==0){
           jQuery("#show_title").attr('checked',true);
           jQuery(".gen_shortcode").change();
       }
    });
    jQuery("#columns_num").live('change',function(){
        jQuery("#boxwidth").attr("readonly",jQuery(this).val()=='auto'?false:true);
    });
    $showVideo  = jQuery('#show-video');
    $modalFrame = jQuery('.modal-frame');
    $backdroop  = jQuery('.backdroop');
    $backdroop.appendTo('body');

    $showVideo.click(function(e){
        e.preventDefault();
        $backdroop.fadeIn('fast');
        $modalFrame.fadeIn('fast');
        //alert('asd');    
    });

    jQuery(document).mouseup(function(e)
    {
        if ( $modalFrame.has(e.target).length === 0 )
        {   
            $backdroop.fadeOut('fast');
            $modalFrame.fadeOut('fast');

        }
    });
     jQuery('input#matrix_shortcode').click(function(e) {
        jQuery(this).select();
    });
});
/*
$(window).load(function(){
    var $cont= jQuery("#wpts_container");
    $cont.each(function(){
    if($cont.masonry){
        $cont.masonry({
            itemSelector : '.item'
        });
    }
    });
});
*/