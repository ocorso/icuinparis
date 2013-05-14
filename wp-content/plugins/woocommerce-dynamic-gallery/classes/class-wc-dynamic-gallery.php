<?php
/**
 * WooCommerce Gallery Display Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * html2rgb()
 * rgb2html()
 * wc_dynamic_gallery_display()
 */
class WC_Gallery_Display_Class{
	function html2rgb($color,$text = false){
		if ($color[0] == '#')
			$color = substr($color, 1);
	
		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;
	
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		if($text){
			return $r.','.$g.','.$b;
		}else{
			return array($r, $g, $b);
		}
	}
	function rgb2html($r, $g=-1, $b=-1){
		if (is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;
	
		$r = intval($r); $g = intval($g);
		$b = intval($b);
	
		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));
	
		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return '#'.$color;
	}
	function wc_dynamic_gallery_display(){
		/**
		 * Single Product Image
		 */
		global $post, $woocommerce;
		$current_db_version = get_option( 'woocommerce_db_version', null );
		$lightbox_class = 'lightbox';
		
		// Get all attached images to this product
						
		$featured_img_id = (int)get_post_meta($post->ID, '_thumbnail_id', true);
		$attached_images = (array)get_posts( array(
			'post_type'   => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID ,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			'exclude'	  => array($featured_img_id),
		) );
		
		
		$attached_thumb = array();
		if ($featured_img_id > 0) {
			$feature_image_data = get_post( $featured_img_id );
				
			if ( $feature_image_data && $feature_image_data->post_parent == $post->ID ) {
				if ( get_post_meta( $featured_img_id, '_woocommerce_exclude_image', true ) != 1 ) {
					$attached_thumb[0] = $feature_image_data;
				}
			} else {
				$attached_thumb[0] = $feature_image_data;
			}
		}
		if($attached_images && count($attached_images) > 0 ){
			$i = 0;
			foreach($attached_images as $key=>$object){
				if (get_post_meta( $object->ID, '_woocommerce_exclude_image', true ) == 1) continue;
				
				$i++;
				$attached_thumb[$i] = $object;
			}	
		}
		ksort($attached_thumb);
		$product_id = '_'.rand(100,10000);
		$have_image = false;
		$attached_images = array();
		if(is_array($attached_thumb) && count($attached_thumb) > 0) {
			$attached_images = $attached_thumb;
			$have_image = true;
		}
		?>
        <div class="images gallery_container">
          <div class="product_gallery">
            <?php
            $g_width = get_option('product_gallery_width');
            $g_height = get_option('product_gallery_height');
            
            $g_thumb_width = get_option('thumb_width');
            $g_thumb_height = get_option('thumb_height');
            $g_thumb_spacing = get_option('thumb_spacing');
                
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
				$zoom_label = '';
				$lightbox_class = '';
			}
			
			$bg_des = WC_Gallery_Display_Class::html2rgb($product_gallery_bg_des,true);
			$des_background =str_replace('#','',$product_gallery_bg_des);
			                
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

					margin: 0 0 '.$mg.'px !important;
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
				}
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
			
			if ($popup_gallery == 'deactivate') echo '#gallery_'.$post->ID.' .ad-image-wrapper .ad-image img{cursor: default;} #gallery_'.$post->ID.' .icon_zoom{cursor: default;}';
			
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
			
			echo '<img style="width: 0px ! important; height: 0px ! important; display: none ! important; position: absolute;" src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL . '/blank.gif">';
			
            echo '<div id="gallery_'.$post->ID.'" class="ad-gallery">
                <div class="ad-image-wrapper"></div>
                <div class="ad-controls"> </div>
                  <div class="ad-nav">
                    <div class="ad-thumbs">
                      <ul class="ad-thumb-list">';                        
                        
                        $script_colorbox = '';
						$script_fancybox = '';
						$script_prettyPhoto = '';
						$prettyPhoto_images = '[';
						$prettyPhoto_titles = '[';
                        if ( !empty( $attached_images ) ){	
                            $i = 0;
                            $display = '';
			
                            if(is_array($attached_images) && count($attached_images)>0){
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
									var idx = $("#gallery_'.$post->ID.' .ad-image img").attr("idx");';
								$script_fancybox .= '$(".ad-gallery .lightbox").live("click",function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
									var idx = $("#gallery_'.$post->ID.' .ad-image img").attr("idx");';
								$script_prettyPhoto .= '$(".ad-gallery .lightbox").live("click",function(ev) { if( $(this).attr("rel") == "gallery_'.$post->ID.'") {
									var idx = $("#gallery_'.$post->ID.' .ad-image img").attr("idx");';
                                if(count($attached_images) <= 1 ){
                                    $script_colorbox .= '$(".gallery_product_'.$post->ID.'").colorbox({open:true, maxWidth:"100%", title: function() { return "&nbsp;";} });';
									$script_fancybox .= '$.fancybox(';
                                }else{
                                    $script_colorbox .= '$(".gallery_product_'.$post->ID.'").colorbox({rel:"gallery_product_'.$post->ID.'", maxWidth:"100%", title: function() { return "&nbsp;";} }); $(".gallery_product_'.$post->ID.'_"+idx).colorbox({open:true, maxWidth:"100%", title: function() { return "&nbsp;";} });';
									$script_fancybox .= '$.fancybox([';
                                }
								if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
									$script_prettyPhoto .= '$().prettyPhoto({modals: "true", social_tools: false, theme: "light_square"}); $.prettyPhoto.open(';
								} else {
									$script_prettyPhoto .= '$().prettyPhoto({modals: "true", social_tools: false, theme: "pp_woocommerce"}); $.prettyPhoto.open(';
								}
                                $common = '';
                                
                                
								$idx = 0;
                                foreach($attached_images as $item_thumb){
                                    $li_class = '';
                                    if($i == 0){ $li_class = 'first_item';}elseif($i == count($attached_images)-1){$li_class = 'last_item';}
                                    $image_attribute = wp_get_attachment_image_src( $item_thumb->ID, 'full');
                                    $image_lager_default_url = $image_attribute[0];
									
									$image_thumb_attribute = wp_get_attachment_image_src( $item_thumb->ID, 'wc-dynamic-gallery-thumb');
                                    $image_thumb_default_url = $image_thumb_attribute[0];
									
                                    $thumb_height = $g_thumb_height;
                                    $thumb_width = $g_thumb_width;
                                    $width_old = $image_thumb_attribute[1];
                                    $height_old = $image_thumb_attribute[2];
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
                                    
                                    
                                        
                                   $alt = get_post_meta($item_thumb->ID, '_wp_attachment_image_alt', true);
								   $img_description = $item_thumb->post_excerpt;
                                            
                                    echo '<li class="'.$li_class.'"><a alt="'.$alt.'" class="gallery_product_'.$post->ID.' gallery_product_'.$post->ID.'_'.$idx.'" title="'.$img_description.'" rel="gallery_product_'.$post->ID.'" href="'.$image_lager_default_url.'"><div><img idx="'.$idx.'" style="width:'.$thumb_width.'px !important;height:'.$thumb_height.'px !important" src="'.$image_thumb_default_url.'" alt="'.$img_description.'" class="image'.$i.'" width="'.$thumb_width.'" height="'.$thumb_height.'"></div></a></li>';
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
																
                                if(count($attached_images) <= 1 ){
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
								
								if (!$have_image) {
									$script_colorbox = '';
									$script_fancybox = '';
									$script_prettyPhoto = '';
									echo '<li style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;"> <a style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;" class="" rel="gallery_product_'.$product_id.'" href="'.WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png"> <div><img style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;" src="'.WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png" class="image" alt=""> </div></a> </li>';
								}
                            }
                        }else{
                            echo '<li style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;"> <a style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;" class="" rel="gallery_product_'.$post->ID.'" href="'.WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png"> <div><img style="width:'.$g_thumb_width.'px;height:'.$g_thumb_height.'px;" src="'.WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png" class="image" alt=""> </div></a> </li>';	
								
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
	}
}
?>