<?php
/**
 * WooCommerce Gallery Preview Display Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * wc_dynamic_gallery_preview()
 */
class WC_Gallery_Preview_Display{
		
	function wc_dynamic_gallery_preview($request = ''){
		$request = $_REQUEST;
		/**
		 * Single Product Image
		 */
		global $post, $woocommerce;
		$current_db_version = get_option( 'woocommerce_db_version', null );
		check_ajax_referer( 'woo_dynamic_gallery', 'security' );
		$woo_a3_gallery_settings = $request;
		$lightbox_class = 'lightbox';
		?>
        <div class="images" style="width:<?php echo $woo_a3_gallery_settings['product_gallery_width'].'px';?>;margin:30px auto;">
          <div class="product_gallery">
            <?php
			
            
            $g_width = $woo_a3_gallery_settings['product_gallery_width'];
            $g_height = $woo_a3_gallery_settings['product_gallery_height'];
            
            $g_thumb_width = $woo_a3_gallery_settings['thumb_width'];
            $g_thumb_height = $woo_a3_gallery_settings['thumb_height'];
            $g_thumb_spacing = $woo_a3_gallery_settings['thumb_spacing'];
                
            $g_auto = get_option('product_gallery_auto_start');
            $g_speed = get_option('product_gallery_speed');
            $g_effect = get_option('product_gallery_effect');
            $g_animation_speed = get_option('product_gallery_animation_speed');
			
			$bg_nav_color = get_option('bg_nav_color');
			$bg_nav_text_color = get_option('bg_nav_text_color');
			
			$bg_image_wrapper = get_option('bg_image_wrapper');
			$border_image_wrapper_color = get_option('border_image_wrapper_color');
			$border_image_wrapper_color = get_option('border_image_wrapper_color');
			
			$product_gallery_text_color = get_option('product_gallery_text_color');
			$product_gallery_bg_des = get_option('product_gallery_bg_des');
			
			$enable_gallery_thumb = get_option('enable_gallery_thumb');
			
			
			$product_gallery_nav = get_option('product_gallery_nav');
			
			$transition_scroll_bar = get_option('transition_scroll_bar');
			
			$lazy_load_scroll = get_option('lazy_load_scroll');
			
			$caption_font = get_option('caption_font');
			$caption_font_size = get_option('caption_font_size');
			$caption_font_style = get_option('caption_font_style');
			
			$navbar_font = get_option('navbar_font');
			$navbar_font_size = get_option('navbar_font_size');
			$navbar_font_style = get_option('navbar_font_style');
			$navbar_height = get_option('navbar_height');
			
			if($product_gallery_nav == 'yes'){
				$display_ctrl = 'display:block !important;';
				$mg = $navbar_height;
				$ldm = $navbar_height;
				
			}else{
				$display_ctrl = 'display:none !important;';
				$mg = '0';
				$ldm = '0';
			}
			
			$popup_gallery = get_option('popup_gallery');
			$zoom_label = __('ZOOM +', 'woo_dgallery');
			if ($popup_gallery == 'deactivate') {
				$lightbox_class = '';
				$zoom_label = '';
			}
			
			
			$bg_des = WC_Gallery_Display_Class::html2rgb($product_gallery_bg_des,true);
			$des_background =str_replace('#','',$product_gallery_bg_des);
			
           
            $post->ID = rand(10,10000);
            echo '<style>
			#TB_window{width:auto !important;}
                .ad-gallery {
                        width: '.$g_width.'px;
						position:relative;
                }
                .ad-gallery .ad-image-wrapper {
					background:#'.$bg_image_wrapper.';
                    width: '.($g_width-2).'px;
                    height: '.($g_height-2).'px;
                    margin: 0px;
                    position: relative;
                    overflow: hidden !important;
                    padding:0;
                    border:1px solid #'.$border_image_wrapper_color.';
					z-index:8 !important;
                }
				.ad-gallery .ad-image-wrapper .ad-image{width:100% !important;text-align:center;}
                .ad-image img{
                    max-width:'.$g_width.'px !important;
                }
                .ad-gallery .ad-thumbs li{
                    padding-right: '.$g_thumb_spacing.'px !important;
                }
                .ad-gallery .ad-thumbs li.last_item{
                    padding-right: '.($g_thumb_spacing+13).'px !important;
                }
                .ad-gallery .ad-thumbs li div{
                    height: '.$g_thumb_height.'px !important;
                    width: '.$g_thumb_width.'px !important;
                }
                .ad-gallery .ad-thumbs li a {
                    width: '.$g_thumb_width.'px !important;
                    height: '.$g_thumb_height.'px !important;	
                }
                * html .ad-gallery .ad-forward, .ad-gallery .ad-back{
                    height:	'.($g_thumb_height).'px !important;
                }
				
				/*Gallery*/
				.ad-image-wrapper{
					overflow:inherit !important;
				}
				
				.ad-gallery .ad-controls {
					background: #'.$bg_nav_color.' !important;
					border:1px solid #'.$bg_nav_color.';
					color: #FFFFFF;
					font-size: 12px;
					height: 22px;
					margin-top: 20px !important;
					padding: 8px 2% !important;
					position: relative;
					width: 95.8%;
					-khtml-border-radius:5px;
					-webkit-border-radius: 5px;
					-moz-border-radius: 5px;
					border-radius: 5px;display:none;
				}
				
				.ad-gallery .ad-info {
					float: right;
					font-size: 14px;
					position: relative;
					right: 8px;
					text-shadow: 1px 1px 1px #000000 !important;
					top: 1px !important;
				}
				.ad-gallery .ad-nav .ad-thumbs{
					margin:7px 4% 0 !important;
				}
				.ad-gallery .ad-nav{
					margin-top:20px !important;
				}
				.ad-gallery .ad-thumbs .ad-thumb-list {
					margin-top: 0px !important;
				}
				.ad-thumb-list{
				}
				.ad-thumb-list li{
					background:none !important;
					padding-bottom:0 !important;
					padding-left:0 !important;
					padding-top:0 !important;
				}
				.ad-gallery .ad-image-wrapper .ad-image-description {
					background: rgba('.$bg_des.',0.5);
					filter:progid:DXImageTransform.Microsoft.Gradient(GradientType=1, StartColorStr="#88'.$des_background.'", EndColorStr="#88'.$des_background.'");
					bottom: '.$mg.'px !important;
					color: '.$product_gallery_text_color.' !important;
					font-family:'.$caption_font.' !important;
					font-size: '.$caption_font_size.';';
					if($caption_font_style == 'bold'){
						echo 'font-weight:bold !important;';
					}elseif($caption_font_style == 'normal'){
						echo 'font-weight:normal !important;';
					}elseif($caption_font_style == 'italic'){
						echo 'font-style:italic !important;';
					}elseif($caption_font_style == 'bold_italic'){
						echo 'font-weight:bold !important;';
						echo 'font-style:italic !important;';
					}
					echo '
					left: 0;
					line-height: 1.4em;
					margin: 0 !important;
					padding:2% 2% 2% !important;
					position: absolute;
					text-align: left;
					width: 96.1% !important;
					z-index: 10;
					font-weight:normal;
				}
				.product_gallery .ad-gallery .ad-image-wrapper {
					background: none repeat scroll 0 0 '.$bg_image_wrapper.';
					border: 1px solid '.$border_image_wrapper_color.' !important;
					padding-bottom:'.$mg.'px;
				}';
				if($lazy_load_scroll == 'yes'){
					echo '.ad-gallery .lazy-load{
						background:'.$transition_scroll_bar.' !important;
						top:'.($g_height + 9).'px !important;
						opacity:1 !important;
						margin-top:'.$ldm.'px !important;
					}';
				}else{
					echo '.ad-gallery .lazy-load{display:none!important;}';
				}
				echo'
				.product_gallery .slide-ctrl, .product_gallery .icon_zoom {
					'.$display_ctrl.';
					font-family:'.$navbar_font.' !important;
					font-size: '.$navbar_font_size.';
					height: '.($navbar_height-16).'px !important;
					line-height: '.($navbar_height-16).'px !important;';
					
					if($navbar_font_style == 'bold'){
						echo 'font-weight:bold !important;';
					}elseif($navbar_font_style == 'normal'){
						echo 'font-weight:normal !important;';
					}elseif($navbar_font_style == 'italic'){
						echo 'font-style:italic !important;';
					}elseif($navbar_font_style == 'bold_italic'){
						echo 'font-weight:bold !important;';
						echo 'font-style:italic !important;';
					}
				echo '
				}
				.ad-gallery .lazy-load{
					background:'.$transition_scroll_bar.' !important;
					top:'.($g_height + 9).'px !important;
					opacity:1 !important;
					margin-top:'.$ldm.'px !important;
				}
				.product_gallery .icon_zoom {
					background: '.$bg_nav_color.';
					border-right: 1px solid '.$bg_nav_color.';
					border-top: 1px solid '.$border_image_wrapper_color.';
				}
				.product_gallery .slide-ctrl {
					background:'.$bg_nav_color.';
					border-left: 1px solid '.$border_image_wrapper_color.';
					border-top: 1px solid '.$border_image_wrapper_color.';
				}
				.product_gallery .slide-ctrl .ad-slideshow-stop-slide,.product_gallery .slide-ctrl .ad-slideshow-start-slide,.product_gallery .icon_zoom{
					color:'.$bg_nav_text_color.';
					line-height: '.($navbar_height-16).'px !important;
				}
				.product_gallery .ad-gallery .ad-thumbs li a {
					border:1px solid '.$border_image_wrapper_color.' !important;
				}
				.ad-gallery .ad-thumbs li a.ad-active {
					border: 1px solid '.$bg_nav_color.' !important;
				}';
			if($enable_gallery_thumb == 'no'){
				echo '.ad-nav{display:none;}.woocommerce .images { margin-bottom: 15px;}';
			}	
			
			if($product_gallery_nav == 'no'){
				echo '
				.ad-image-wrapper:hover .slide-ctrl{display: block !important;}
				.product_gallery .slide-ctrl {
					background: none repeat scroll 0 0 transparent;
					border: medium none;
					height: 50px !important;
					left: 41.5% !important;
					top: 38% !important;
					width: 50px !important;
				}';
				echo '.product_gallery .slide-ctrl .ad-slideshow-start-slide {background: url('.WOO_DYNAMIC_GALLERY_JS_URL.'/mygallery/play.png) !important;height: 50px !important;text-indent: -999em !important; width: 50px !important;}';
				echo '.product_gallery .slide-ctrl .ad-slideshow-stop-slide {background: url('.WOO_DYNAMIC_GALLERY_JS_URL.'/mygallery/pause.png) !important;height: 50px !important;text-indent: -999em !important; width: 50px !important;}';
			}
			
			if ($popup_gallery == 'deactivate') echo '.ad-gallery .ad-image-wrapper .ad-image img{cursor: default;} .ad-gallery .icon_zoom{cursor: default;}';
			
			echo '
			/* Pretty Photo style */
			.pp_content_container .pp_gallery {
				display:block !important;
				opacity: 1 !important;
				filter: alpha(opacity = 100) !important;
			}
            </style>';
            
            echo '<script type="text/javascript">
                jQuery(function() {
                    var settings_defaults_'.$post->ID.' = { loader_image: \''.WOO_DYNAMIC_GALLERY_JS_URL.'/mygallery/loader.gif\',
                        start_at_index: 0,
                        gallery_ID: \''.$post->ID.'\',
						lightbox_class: "'.$lightbox_class.'",
                        description_wrapper: false,
                        thumb_opacity: 0.5,
                        animate_first_image: false,
                        animation_speed: '.$g_animation_speed.'000,
                        width: false,
                        height: false,
                        display_next_and_prev: true,
                        display_back_and_forward: true,
                        scroll_jump: 0, // If 0, it jumps the width of the container
                        slideshow: {
                            enable: true,
                            autostart: '.$g_auto.',
                            speed: '.$g_speed.'000,
                            start_label: "'.__('START SLIDESHOW', 'woo_dgallery').'",
                            stop_label: "'.__('STOP SLIDESHOW', 'woo_dgallery').'",
							zoom_label: "'.$zoom_label.'",
                            stop_on_scroll: true,
                            countdown_prefix: \'(\',
                            countdown_sufix: \')\',
                            onStart: false,
                            onStop: false
                        },
                        effect: \''.$g_effect.'\', 
                        enable_keyboard_move: true,
                        cycle: true,
                        callbacks: {
                        init: false,
                        afterImageVisible: false,
                        beforeImageVisible: false
                    }
                };
                jQuery("#gallery_'.$post->ID.'").adGallery(settings_defaults_'.$post->ID.');
            });
            </script>';
            echo '<div id="gallery_'.$post->ID.'" class="ad-gallery">
                <div class="ad-image-wrapper"></div>
                <div class="ad-controls"> </div>
                  <div class="ad-nav">
                    <div class="ad-thumbs">
                      <ul class="ad-thumb-list">';
						
						$url_demo_img =  '/assets/js/mygallery/images/';
                        $imgs = array($url_demo_img.'image_1.jpg',$url_demo_img.'image_2.jpg',$url_demo_img.'image_3.jpg',$url_demo_img.'image_4.jpg');
                        
                        $script_colorbox = '';
						$script_fancybox = '';
						$script_prettyPhoto = '';
						$prettyPhoto_images = '[';
						$prettyPhoto_titles = '[';
                        if ( !empty( $imgs ) ){	
                            $i = 0;
                            $display = '';
			
                            if(is_array($imgs) && count($imgs)>0){
                                $script_colorbox .= '<script type="text/javascript">';
								$script_fancybox .= '<script type="text/javascript">';
								$script_prettyPhoto .= '<script type="text/javascript">';
                                $script_colorbox .= '(function($){';		  
								$script_fancybox .= '(function($){';
								$script_prettyPhoto .= '(function($){';		  
                                $script_colorbox .= '$(function(){';
								$script_fancybox .= '$(function(){';
								$script_prettyPhoto .= '$(function(){';
                                $script_colorbox .= '$(".ad-gallery .lightbox").live("click",function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
								var idx = $(".ad-image img").attr("idx");';
								$script_fancybox .= '$(".ad-gallery .lightbox").live("click",function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
								var idx = $(".ad-image img").attr("idx");';
								$script_prettyPhoto .= '$(".ad-gallery .lightbox").live("click",function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
								var idx = $(".ad-image img").attr("idx");';
                                if(count($imgs) <= 1 ){
                                    $script_colorbox .= '$(".gallery_product_'.$post->ID.'").colorbox({open:true, maxWidth:"100%", title: function() { return "&nbsp;";} });';
									$script_fancybox .= '$.fancybox(';
                                }else{
                                    $script_colorbox .= '$(".gallery_product_'.$post->ID.'").colorbox({rel:"gallery_product_'.$post->ID.'", maxWidth:"100%", title: function() { return "&nbsp;";} }); $(".gallery_product_'.$post->ID.'_"+idx).colorbox({open:true, maxWidth:"100%", title: function() { return "&nbsp;";} });';
									$script_fancybox .= '$.fancybox([';
                                }
								//if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
									$script_prettyPhoto .= '$().prettyPhoto({modals: "true", social_tools: false, theme: "light_square"}); $.prettyPhoto.open(';
								//} else {
								//	$script_prettyPhoto .= '$().prettyPhoto({modals: "true", social_tools: false, theme: "pp_woocommerce"}); $.prettyPhoto.open(';
								//}
                                $common = '';
                                $idx = 0;
                                foreach($imgs as $item_thumb){
                                    $li_class = '';
                                    if($i == 0){ $li_class = 'first_item';}elseif($i == count($imgs)-1){$li_class = 'last_item';}
                                    $image_attribute = getimagesize( WOO_DYNAMIC_GALLERY_DIR.$item_thumb);
                                    $image_lager_default_url = WOO_DYNAMIC_GALLERY_URL.$item_thumb;
									
									
                                    $thumb_height = $g_thumb_height;
                                    $thumb_width = $g_thumb_width;
                                    $width_old = $image_attribute[0];
                                    $height_old = $image_attribute[1];
                                     if($width_old > $g_thumb_width || $height_old > $g_thumb_height){
                                        if($height_old > $g_thumb_height) {
                                            $factor = ($height_old / $g_thumb_height);
                                            $thumb_height = $g_thumb_height;
                                            $thumb_width = $width_old / $factor;
                                        }
                                        if($thumb_width > $g_thumb_width){
                                            $factor = ($width_old / $g_thumb_width);
                                            $thumb_height = $height_old / $factor;
                                            $thumb_width = $g_thumb_width;
                                        }elseif($thumb_width == $g_thumb_width && $width_old > $g_thumb_width){
                                            $factor = ($width_old / $g_thumb_width);
                                            $thumb_height = $height_old / $factor;
                                            $thumb_width = $g_thumb_width;
                                        }						
                                    }else{
                                         $thumb_height = $height_old;
                                        $thumb_width = $width_old;
                                    }
                                    
                                    
                                        
                                    $img_description = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
                                            
                                    echo '<li class="'.$li_class.'"><a class="gallery_product_'.$post->ID.' gallery_product_'.$post->ID.'_'.$idx.'" title="'.$img_description.'" rel="gallery_product_'.$post->ID.'" href="'.$image_lager_default_url.'"><div><img idx="'.$idx.'" style="width:'.$thumb_width.'px !important;height:'.$thumb_height.'px !important" src="'.$image_lager_default_url.'" alt="'.$img_description.'" class="image'.$i.'" width="'.$thumb_width.'" height="'.$thumb_height.'"></div></a></li>';
                                    $img_description = trim(strip_tags(stripslashes(str_replace("'","", str_replace('"', '', $img_description)))));
                                    if($img_description != ''){
										$script_fancybox .= $common.'{href:\''.$image_lager_default_url.'\',title:\''.$img_description.'\'}';
                                    }else{
										$script_fancybox .= $common.'{href:\''.$image_lager_default_url.'\',title:\'\'}';
                                    }
									$prettyPhoto_images .= $common.'"'.$image_lager_default_url.'"';
									$prettyPhoto_titles .= $common.'"'.$img_description.'"';
                                    $common = ',';
                                    $i++;
									$idx++;
                                 }
								$prettyPhoto_images .= ']';
								$prettyPhoto_titles .= ']';
								
								 //$.fancybox([ {href : 'img1.jpg', title : 'Title'}, {href : 'img2.jpg', title : 'Title'} ])
                                if(count($imgs) <= 1 ){
									$script_fancybox .= ');';
									$script_prettyPhoto .= $prettyPhoto_images. ', '. $prettyPhoto_titles .');';
                                }else{
									$script_fancybox .= '],{
        \'index\': idx
      });';
	  								$script_prettyPhoto .= $prettyPhoto_images. ', '. $prettyPhoto_titles .'); $.prettyPhoto.changePage( parseInt(idx) );';
                                }
                                $script_colorbox .= 'ev.preventDefault();';
                                $script_colorbox .= '} });';
								$script_fancybox .= '} });';
								$script_prettyPhoto .= '} });';
                                $script_colorbox .= '});';
								$script_fancybox .= '});';
								$script_prettyPhoto .= '});';
                                $script_colorbox .= '})(jQuery);';
								$script_fancybox .= '})(jQuery);';
								$script_prettyPhoto .= '})(jQuery);';
                                $script_colorbox .= '</script>';
								$script_fancybox .= '</script>';
								$script_prettyPhoto .= '</script>';
                            }
                        }else{
                            echo '<li> <a class="lightbox" rel="gallery_product_'.$post->ID.'" href="'.WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png"> <img src="'.WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png" class="image" alt=""> </a> </li>';
							
							
									
                        }
						
						if ($popup_gallery == 'deactivate') {
							$script_colorbox = '';
							$script_fancybox = '';
							$script_prettyPhoto = '';
						} else if($popup_gallery == 'colorbox'){
                        	echo $script_colorbox;
						} elseif($popup_gallery == 'fb') {
							echo $script_fancybox;
						} else {
							echo $script_prettyPhoto;
						}
                        echo '</ul>
                        </div>
                      </div>
                    </div>';
                  ?>
          </div>
        </div>
	<?php
	die();
	}
}
?>