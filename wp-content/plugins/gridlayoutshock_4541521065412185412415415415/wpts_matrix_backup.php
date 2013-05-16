<?php
/*    
    Plugin Name: Pluginterest by Themeshock version 1.3 (Full Version)
    Plugin URI: http://www.themeshock.com
    Description: Pluginterest pinterest plugin by Themeshock
    Version: 1.0
    Author: Theme Shock
    Author URI: http://www.themeshock.com
*/
add_action('admin_menu', 'create_useful_matix_menu');
function create_useful_matix_menu() {
    load_plugin_textdomain('usefulmatrixint', false, basename( dirname( __FILE__ ) ) . '/languages' );
    add_menu_page(__('Pluginterest pinterest plugin by Themeshock','usefulmatrixint'), __('Pluginterest pinterest plugin by Themeshock','usefulmatrixint'), 'administrator', __FILE__, 'wpts_useful_matrix_settings_page',plugins_url('/img/ts1.png', __FILE__));    
}
function wpts_useful_matrix_settings_page(){
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>css/bootstrap.min.css" media="all" type="text/css" />
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>css/wpts_matrix.css" media="all" type="text/css" />
<script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>js/wpts_matrix.js"></script>
<style type="text/css" media="all">
    .gen_shortcode{
        width: 30em;
        height: 30px;
    }
    .gen_shortcode.no_w{
        width: auto;
    }
</style>
<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php echo __('Pluginterest pinterest plugin by Themeshock','usefulmatrixint');?></h2> 
    <div style="height: 20px">
        <div class="updated_custom" id="message_custom001" style="display: none;">&nbsp;</div>
    </div>
    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">
            <div id="postbox-container-1" class="postbox-container" style="width:100%;">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h3 style="cursor: default"><span>Powered by: <a href="http://www.wpthemegenerator.com" target="blank">WordPress Theme Generator</a> and <a href="http://www.wordpressthemeshock.com/" target="blank">Theme Shock</a></span></h3>
                        <div class="inside">
                            <div>
                                <p>
                                    <?php echo __('One theme, a thousand posibilities: Create amazing and unlimited themes by playing with 1000+ pre-designed elements (or uploading your own designs) and then download in fully working WP or HTML/CSS.','usefulmatrixint'); ?>
                                </p>
                                <h4>
                                    <a href="http://themeshock.com">100 Free Sample Themes</a> &nbsp;&nbsp;&nbsp; <a id='show-video' href="#">Check 1 Minute Video</a>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="postbox ">                            
                        <h3 style="cursor: default"><span><?php echo __('Generate your Useful Posts Matrix Shortcode','usefulmatrixint');?></span></h3>
                        <input type="hidden" id="categ_rss_select" name="categ_rss_select" value="1" />
                        <div class="inside">
                            <table>                                
                                <tr>
                                    <td colspan="2" style="width: 400px;">
                                        <label for="boxwidth"><?php echo __("Box Width (in pixels):",'usefulmatrixint'); ?></label>
                                    </td>
                                    <td>
                                        <input type="text" value="150" class="gen_shortcode numericfield" id="boxwidth" name="boxwidth" maxlength="4" />
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 20px">
                                        <input onclick="jQuery('#categ_rss_select').val('1');jQuery('.gen_shortcode').change();jQuery('#category_item').focus()" checked class="gen_shortcode no_w" type="radio" id="categ_rss1" name="categ_rss" value="1" />
                                    </td>
                                    <td>
                                        <label for="categ_rss1"><?php echo __("Category:",'usefulmatrixint'); ?></label>
                                    </td>
                                    <td>
                                        <?php 
                                            $args = array(
                                                'show_option_all'    => __('All Categories','usefulmatrixint'),
                                                'show_option_none'   => '',
                                                'orderby'            => 'ID', 
                                                'order'              => 'ASC',
                                                'show_count'         => 0,
                                                'hide_empty'         => 0,
                                                'child_of'           => 0,
                                                'exclude'            => '',
                                                'echo'               => 1,
                                                'hierarchical'       => 0, 
                                                'name'               => 'category_item',
                                                'id'                 => 'category_item',
                                                'class'              => 'postform gen_shortcode',
                                                'depth'              => 0,
                                                'tab_index'          => 0,
                                                'taxonomy'           => 'category',
                                                'hide_if_empty'      => false
                                            );
                                            wp_dropdown_categories( $args );
                                        ?>
                                    </td>
                                </tr>
                               	<!-- taxonomies-->
								<tr>
                                  <td style="width: 20px">
                                    <?php 
                                      $args=array('public'   => true,'_builtin' => false	); 
                                      $get_public_taxonomies=get_taxonomies($args,'objects');
                                    ?>
                                    <input <?php echo (count($get_public_taxonomies)===0)?'disabled="disabled"':''; ?> onclick="jQuery('#categ_rss_select').val('4');jQuery('.gen_shortcode').change();jQuery('#taxonomy_item').focus()"  class="gen_shortcode no_w" type="radio" id="categ_rss4" name="categ_rss" value="4" />
                                  </td>
                                  <td>
									<label for="categ_rss4"><?php echo __("Taxonomies:",'usefulmatrixint'); ?></label>
                                  </td>
                                  <td>
									<?php 
                                      if( count($get_public_taxonomies)===0){
                                        echo "no taxonomies found";
                                      }else{
                                    ?>
                                    <select id="taxonomy_item" class="gen_shortcode">
                                      <?php
                                        foreach(	$get_public_taxonomies as $name_tax=>$value){
                                      ?>
                                      <option><?php echo $name_tax; ?></option>
                                      <?php 
											}
									  ?>
                                    </select>
                                    <?php
                                    	} 
									?>
                                  </td>
                                </tr>																
                                <!-- post types -->
								<tr>
                                  <td style="width: 20px">
                                    <?php 
                                      $args=array('public'   => true,'_builtin' => false); 
                                      $get_post_types=get_post_types($args,'objects');
                                    ?>
                                    <input <?php echo (count($get_post_types)===0)?'disabled="disabled"':''; ?> onclick="jQuery('#categ_rss_select').val('5');jQuery('.gen_shortcode').change();jQuery('#posttype_item').focus()"  class="gen_shortcode no_w" type="radio" id="categ_rss5" name="categ_rss" value="5" />
                                  </td>
                                  <td>
										<label for="categ_rss5"><?php echo __("Post type:",'usefulmatrixint'); ?></label>
                                  </td>
                                  <td>
									<?php 
                                      if( count($get_post_types)===0){
                                        echo "no post types found";
                                      }else{
                                    ?>
                                    <select id="posttype_item" class="gen_shortcode">
                                      <?php
                                        foreach(	$get_post_types as $name_ptype=>$value){
                                      ?>
                                      <option><?php echo $name_ptype; ?></option>
                                      <?php 
										}
									  ?>
                                    </select>
                                    <?php
                                    	} 
									?>
                                  </td>
                                </tr>                                
                                <!--Only Avalaible on Full Version-->
                                <tr>
                                    <td style="width: 20px">
                                        <input type="radio" onclick="jQuery('#categ_rss_select').val('2');jQuery('.gen_shortcode').change();jQuery('#external_rss').focus()" class="gen_shortcode no_w" id="categ_rss2" name="categ_rss" value="2">
                                    </td>
                                    <td>
                                        <label for="categ_rss2"><?php echo __("External RSS Feed:",'usefulmatrixint'); ?></label>
                                    </td>
                                    <td>
                                        <input type="text" id="external_rss" name="external_rss" class="gen_shortcode" />
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 20px">
                                        <input type="radio" onclick="jQuery('#categ_rss_select').val('6');jQuery('.gen_shortcode').change();jQuery('#categories').focus()" class="gen_shortcode no_w" id="categ_rss10" name="categ_rss" value="6">
                                    </td>
                                    <td>
                                        <label for="categ_rss10"><?php echo __("Categories: (Create grid to show categories in boxes mode)",'usefulmatrixint'); ?></label>
                                    </td>
                                    <td>                                    	
                                    	<select id="categories"  name="categories" class="gen_shortcode" multiple="multiple">
    
										    <?php
										        $arg  = array(
											        'type'                     => 'post',
											        'child_of'                 => 0,
											        'parent'                   => '',
											        'orderby'                  => 'name',
											        'order'                    => 'ASC',
											        'hide_empty'               => 0,
											        'taxonomy'                 => 'category'
												);
										    
										        $cats = get_categories($arg); 
										        foreach($cats as $cat) { ?>
										         	<option value="<?php echo $cat->cat_ID; ?>"	<?php if ( get_settings( ('categories'.'[]') ) == $cat->cat_ID) {
										            echo ' selected="selected"'; 
										            } ?>><?php echo $cat->cat_name; ?></option>
										        <?php } ?>
										</select>
                                    </td>
                                </tr>
                                <tr>                                    
                                    <td colspan="3" style="height: 50px">
                                        Earn money promoting freebies with our <a href="http://www.wordpressthemeshock.com/afiliates/">Affiliate Program</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 20px">
                                        <input type="radio" onclick="jQuery('#categ_rss_select').val('3');jQuery('.gen_shortcode').change();jQuery('#aff_prd').focus()" class="gen_shortcode no_w" id="categ_rss3" name="categ_rss" value="3">
                                    </td>
                                    <td colspan="2">
                                        <label for="categ_rss3"><?php echo __("Design Shock Freebies",'usefulmatrixint'); ?></label>
                                    </td>                           
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="categ_rss3"><?php echo __("Affiliate Code (Optional)",'usefulmatrixint'); ?></label>
                                    </td>
                                    <td>
                                        <input type="text" class="gen_shortcode" id="aff_prd" name="aff_prd" maxlength="20" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="posts_number"><?php echo __("Number of Posts:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="text" value="1" class="gen_shortcode numericfield" id="posts_number" name="posts_number" maxlength="4" />
                                    </td>
                                </tr>
                                <!--Only Avalaible on Full Version-->
                                <tr>
                                    <td colspan="2">
                                        <label for="posts_limit"><?php echo __("Limit of posts (pagination/slider) use only if you want to limit the shown posts:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="text" value="1" class="gen_shortcode numericfield" id="posts_limit" name="posts_limit" maxlength="4" />
                                    </td>
                                </tr>
                                <!--this attribute sets the order of POSTS-->
                                <tr>
                                    <td colspan="2">
                                        <label for="posts_order"><?php echo __("Order of posts (pagination/slider) use only if you want to order the post ascendent or descendent:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                    	<select id="posts_order" name="posts_order" class="gen_shortcode">
                                        	<option value="ASC">Ascendent</option>
                                            <option value="">Descendent</option>
                                        </select>
                                    </td>
                                </tr>
                                <!--Some Box Styles Only Avalaible on Full Version-->
                                <tr>
                                    <td colspan="2">
                                        <label for="box_style"><?php echo __("Box Style:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <select id="box_style" name="box_style" class="gen_shortcode">
                                            <?php for($i=1;$i<=8;$i++): ?>
                                            <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                            <?php endfor; ?>
                                        </select>                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                    <td><span id="screen_pre" class="screen1">&nbsp;</span></td>
                                </tr>
                                <tr>
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                                <!--Some Button Colors Only Avalaible on Full Version-->
                                <tr>
                                    <td colspan="2">
                                        <label for="readmore_color"><?php echo __("'Read More' Button Color:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <select id="readmore_color" name="readmore_color" class="gen_shortcode">
                                            <option value="nobutton"><?php echo __("No Button",'usefulmatrixint'); ?></option>
                                            <option value="white"><?php echo __("White",'usefulmatrixint'); ?></option>
                                            <option value="blue"><?php echo __("Blue",'usefulmatrixint'); ?></option>
                                            <option value="cyan"><?php echo __("Cyan",'usefulmatrixint'); ?></option>
                                            <option value="green"><?php echo __("Green",'usefulmatrixint'); ?></option>
                                            <option value="yellow"><?php echo __("Yellow",'usefulmatrixint'); ?></option>
                                            <option value="red"><?php echo __("Red",'usefulmatrixint'); ?></option>
                                            <option value="black"><?php echo __("Black",'usefulmatrixint'); ?></option>
                                        </select>                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="readmore_txt"><?php echo __("'Read More' Button Text:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                    	<input type="text" name="readmore_txt" id="readmore_txt" class="gen_shortcode" value="Read More"/>                               
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="title_fontsize"><?php echo __("Title Font Size (in pixels):",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="text" value="15" id="title_fontsize" name="title_fontsize" class="gen_shortcode numericfield" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="content_fontsize"><?php echo __("Content Font Size (in pixels):",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="text" value="11" id="content_fontsize" name="content_fontsize" class="gen_shortcode numericfield" />
                                    </td>
                                </tr>                                
                                <?php $fonts=array("Arial"=>"Arial","Verdana"=>"Verdana","Trebuchet"=>"Trebuchet MS","Tahoma"=>"Tahoma","Calibri"=>"Calibri","Helvetica"=>"Helvetica","Lucida Console"=>"Lucida Console");
                                asort($fonts);
                                ?>
                                <tr>
                                    <td colspan="2">
                                        <label for="title_fontstyle"><?php echo __("Title Font Style:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <select id="title_fontstyle" name="title_fontstyle" class="gen_shortcode">
                                            <option selected value="default"><?php echo __("Theme Default",'usefulmatrixint'); ?></option>
                                            <?php foreach($fonts as $value=>$name): ?>
                                            <option value="<?php echo $value ?>"><?php echo $name; ?></option>
                                            <?php endforeach; ?>
                                        </select>                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="content_fontstyle"><?php echo __("Content Font Style:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <select id="content_fontstyle" name="content_fontstyle" class="gen_shortcode" value="0">
                                            <option selected value="default"><?php echo __("Theme Default",'usefulmatrixint'); ?></option>
                                            <?php foreach($fonts as $value=>$name): ?>
                                            <option value="<?php echo $value ?>"><?php echo $name; ?></option>
                                            <?php endforeach; ?>
                                        </select>                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="show_title"><?php echo __("Show Title:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="checkbox" checked value="1" id="show_title" name="show_title" class="gen_shortcode chkgrp no_w" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="show_excerpt"><?php echo __("Show Excerpt:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="show_excerpt" name="show_excerpt" class="gen_shortcode chkgrp no_w" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="show_image"><?php echo __("Show Image:",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="show_image" name="show_image" class="gen_shortcode chkgrp no_w" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="enable_masonry"><?php echo __("Enable Masonry JS For responsive boxes (if masonry is disabled, boxes will adjust responsively by css):",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="enable_masonry" name="enable_masonry" class="gen_shortcode no_w" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label for="show_menu"><?php echo __("Show menu",'usefulmatrixint')?></label>
                                    </td>
                                    <td>
                                        <input type="checkbox" value="0" id="show_menu" name="show_menu" class="gen_shortcode no_w" />
                                    </td>
                                </tr>                                
                                <tr>
                                    <td colspan="2">
                                        <label for="matrix_shortcode"><?php echo __("Your Useful Post Matrix Generated Shortcode is:",'usefulmatrixint'); ?></label>
                                    </td>                                    
                                    <td>
                                        <input style="width: 80em;height: 30px;font-size: 11px" type="text" value="[wpts_matriz boxwidth='150' category='0' posts='1' boxstyle='1' buttoncolor='nobutton' buttontext='Read More' titlesize='15' contentsize='11' titlefont='default' contentfont='default' showtitle='1' masonry='0']" readonly="true" id="matrix_shortcode" name="matrix_shortcode" />
                                    </td>
                                </tr>                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class='modal-frame'>
<iframe width="640" height="360" src="http://www.youtube.com/embed/wVNmXzCblrw" frameborder="0" allowfullscreen=""></iframe>
</div>
<div class='backdroop'></div>
<?php
}
function wpts_useful_matrix_shortcode($atts=array()){
    load_plugin_textdomain('usefulmatrixint', false, basename( dirname( __FILE__ ) ) . '/languages' );
    global $post,$script_pt_ts;
    $tmp_post=$post;    
    static $sh_count;
		static $pt_menu;
    if(!$sh_count){
        $sh_count=0;
    }
    if(!isset($atts['boxwidth'])){
        $atts['boxwidth']=150;
    }    
    if(!isset($atts['category'])){
        $atts['category']=0;
    }
	if(!isset($atts['categories'])){
        $atts['categories']=0;
    }
    if(!isset($atts['posts'])){
        $atts['posts']=1;
    }
	if(!isset($atts['order'])){
		$atts['order'] = '';
	}
    if(!isset($atts['boxstyle'])){
        $atts['boxstyle']=1;
    }
    if(!isset($atts['buttoncolor'])){
        $atts['buttoncolor']="nobutton";
    }
	if(!isset($atts['buttontext'])){
        $atts['buttontext']="Read More";
    }
    if(!isset($atts['titlesize'])){
        $atts['titlesize']=15;
    }
    if(!isset($atts['contentsize'])){
        $atts['contentsize']=11;
    }
    if(!isset($atts['titlefont'])){
        $atts['titlefont']="default";
    }
    if(!isset($atts['contentfont'])){
        $atts['contentfont']="default";
    }
    if(!isset($atts['prd'])){
        $atts['prd']="";
    }
    if(!isset($atts['masonry'])){
        $atts['masonry']="0";
    }
    if(!isset($atts['externalrss'])){
        if(empty($atts['prd'])){
            $atts['externalrss']="http://designshock.com/feed";
        }else{
            $atts['externalrss']="http://designshock.com/feed?prd={$atts['prd']}";
        }
    }   
    if(!isset($atts['showtitle'])){
        if(!isset($atts['showexcerpt'])&&!isset($atts['showimage'])){
            $atts['showtitle']="1";
        }else{
            $atts['showtitle']="0";
        }
    }    
    if(!isset($atts['showimage'])){
        $atts['showimage']="0";
    }
    if(!isset($atts['showexcerpt'])){
        $atts['showexcerpt']="0";
    }
    $sh_count++;
		$atts['sh_count']=$sh_count;
    extract($atts);
    if($boxwidth<=150){
        $imgc="thumbnail";        
    }else if($boxwidth<=300){
        $imgc="medium";        
    }else if($boxwidth>300){
        $imgc="large";        
    }
    $titlef="";
    if($titlefont!="default"){
        $titlef="font-family: \"$titlefont\"";
    }
    $contentf="";
    if($contentfont!="default"){
        $contentf="font-family: \"$contentfont\"";
    }
    $colors=array("white"=>"","blue"=>"btn-primary","cyan"=>"btn-info","green"=>"btn-success","yellow"=>"btn-warning","red"=>"btn-danger","black"=>"btn-inverse");
    $bwt=$boxwidth;    
    $imgbh=100;

    $content1="";
    if($sh_count<=1){
        $content1.= "<link rel='stylesheet' href='".plugin_dir_url(__FILE__)."css/bootstrap.min.css'  />";
        $content1.= "<link rel='stylesheet' href='".plugin_dir_url(__FILE__)."css/wpts_matrix.css'  />";
        add_action('wp_print_footer_scripts', 'ts_pinterest_js', 20);       
    }
    if($category!='R'&&$category!='E'){?>
    <?php 
				switch($category){
					case 'none':
							$args=array('public'   => true,'_builtin' => false,'name'	=>$taxonomy); 
							$get_tax=get_taxonomies($args,'objects');
							$get_cat=get_categories(array('taxonomy'=>$taxonomy));
							$q_posts=get_posts(array( 'order'=>$order,'post_type'=> $get_tax[$taxonomy]->object_type, 'numberposts' => $posts));
							$category_asigned=$taxonomy;
							$taxo_html=$taxonomy;
					break;
					case 'nonep':
                            if ($posttype==='wtstestimonial'){
                               $atts['showimage']=0;
                            }
							$q_posts=get_posts(array('order'=>$order,'post_type'=> $posttype, 'numberposts' => $posts));
					break;
					default:
                        $get_cat=get_categories();
                        $q_posts=get_posts("cat=$category&order=$order&numberposts=$posts");
                        $category_asigned='categories';
                        $taxo_html='category';
					break;
				}
				$limit_tmp=(isset($limit))?$limit:false;
				$pack_html=pint_make_html($get_cat,$category_asigned,$taxo_html,$q_posts,$sh_count,$pt_menu,$atts,false);
				$content1.=$pack_html['html'];
				$jQuery_st=$pack_html['jQuery'];
				$pt_menu=false;
    }else{
        if(!empty($limit)){
            $masonry=false;//deshabilitar mansonry en caso rss con limite
        }
        include_once(ABSPATH . WPINC . '/rss.php');
				add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 1;' ) );
        //$rss = new DOMDocument();
        $rss = fetch_feed($externalrss);
        //if(!is_wp_error($rss)){
            $rss->set_timeout(5);
            $rssq = $rss->get_item_quantity($posts);
            $rssItems = $rss->get_items(0,$rssq);
                                    $limit_p=(isset($limit))?(int)$limit:'';/// limite de post por carrousel
                                    $counter=0;///conteador de elementos
                                    if ($limit_p===0 or $rssq<=$limit_p){
                                            unset($limit);
                                    }

                                    $col_st=1;
                                    $jQuery_st="#wpts_container$sh_count";//selectores de jQuery
            if($rssq>0){

                                                    if (!isset($limit)){                               
                        $content1.= "\n<div id='wpts_container$sh_count' style='width: 100%;text-align:center;vertical-align: top'>";
                                                    }else{
                                                            $id_ct='#ts_Carousel'.$sh_count;
                                                            $content1.='<div id="ts_Carousel'.$sh_count.'" class="carousel slide">';
                                                            $content1.='<div class="carousel-inner">';                                                           
                                                    }
                                                    foreach($rssItems as $index => $item){
                                                                    if (($counter===0 && isset($limit)) or ($counter===$limit_p && isset($limit))){

                                                                            $user_status=($counter===0)?'active':'';
                                                                            if($counter>0){
                                                                                    $content1.= "</div>";										
                                                                                    $counter=0;
                                                                            }
                                        $content1.= "\n<div id='wpts_container$sh_count".$col_st."' class='item ".$user_status."' style='width: 100%'>";                                       
                                                                            $jQuery_st.=", #wpts_container".$sh_count.$col_st;
                                                                            $col_st++;
                                                                    }
                    $tmb = "";                    
                    $output = preg_match_all('/<img[^>]+\>/i', $item->get_content(), $matches);
                    $content1.= "\n<div class='item$sh_count useful_box$boxstyle' style='width: {$bwt}px;'>";
                    foreach($item->data['child'] as $value){
                        if(isset($value['featured-image'])){
                            $tmb=$value['featured-image'][0]['data'];                      
                        }
                    }
                    //$content1.= "<pre>".htmlentities(print_r($matches,true)." - ".$item->get_content())."</pre>";
                    if($tmb!=''){
                        $bwt2=$bwt-10;
                        $tmb = "<div class='img_shadow' style='width: ".$bwt2."px;height: ".$bwt2."px;text-align:center;margin:auto'><a title='".$item->get_title()."' href='".$item->get_permalink()."'><img src='".$tmb."' height='$bwt2' width='$bwt2' title='".$item->get_title()."' /></a></div>";
                    }else if($matches[0][0]!=''){
                        $bwt2=$bwt-10;
                        $mm=  str_replace(" />", "style='width: {$bwt2}px;height: {$bwt2}px' />", $matches[0][0]);
                        $tmb = "<div class='img_shadow' style='width: ".$bwt2."px;height: ".$bwt2."px;text-align:center;margin:auto'><a title='".$item->get_title()."' href='{$item->get_permalink()}'>".$mm."</a></div>";
                    }
                    if($showtitle=="1"){
                        $content1.= "\n\t<div style='font-size: ".$titlesize."px;font-weight: bold;$titlef;padding: 5px'>".$item->get_title()."</div>";
                    }
                    if($showimage=="1"){
                        $content1.= "$tmb";

                    }
                    if($showexcerpt=="1"){
                        $desc=str_replace("<p>","<p style='font-size: ".$contentsize."px;$contentf'>",strip_tags($item->get_description()));
                        $content1.= "<div class='pa' style='font-size: ".$contentsize."px;$contentf'>".$desc."</div>";
                    }

                    if(isset($buttoncolor)){
                        if($buttoncolor!='nobutton'){
                        $content1.= "<div class='readmore'><br /><a class='btn btn-small {$colors[$buttoncolor]}' href='".$item->get_permalink()."'>".__($buttontext,'usefulmatrixint')."</a></div>";                
                        }else{
                            $content1.= "<br /><a style='font-size: ".$contentsize."px;$contentf' href='".$item->get_permalink()."'>".__($buttontext,'usefulmatrixint')."</a>";
                        }
                    }else{
                        $content1.= "<br /><a style='font-size: ".$contentsize."px;$contentf' href='".$item->get_permalink()."'>".__($buttontext,'usefulmatrixint')."</a>";
                    }                    
                    $content1.= "</div>";                    
                                                                    $counter++;

                    //$content1.= "<pre>".htmlentities($item->get_content())."</pre>";
                }

                                                    if (!isset($limit)){
                        //$content1.= "<div style='clear: both'></div>";
                        $content1.= "</div>";
                                                    }else{     
                        //$content1.= "<div style='clear: both'></div>";                        
                        $content1.= "</div>";
                        $content1.= "</div>";
                        $content1.='<div class="arrows_align">';
                        if($masonry=='1'){
                            $content1.='  <a class="carousel-control masonry-arrows left" href="'.$id_ct.'" data-slide="prev">&lsaquo;</a>
                               <a class="carousel-control masonry-arrows right" href="'.$id_ct.'" data-slide="next">&rsaquo;</a>';
                        }else{
                        $content1.='  <a class="carousel-control left" href="'.$id_ct.'" data-slide="prev">&lsaquo;</a>
                               <a class="carousel-control right" href="'.$id_ct.'" data-slide="next">&rsaquo;</a>';
                        }
                        $content1.="</div>";
                        $content1.= "</div>";														
                                                    }
                                                                   
            }
        //}
    }      
	  
        $script_pt_ts.= "<script type='text/javascript'>";
        $script_pt_ts.= "\njQuery(document).ready(function(){                        
                     ";
/*										 var_dump($masonry);
										 exit;										 */
        if($masonry=="1"){            
            $script_pt_ts.= "\n\t\tvar \$cont$sh_count= jQuery(\"$jQuery_st\");"
                            ."\n\t\t\$mms".$sh_count."=\$cont$sh_count.masonry({".
                                "\n\t\t\titemSelector : '.item$sh_count'"
                            . "\n\t\t});";
                        
    //		$content1.='jQuery("'.$jQuery_st.'").removeClass("active");';
        }        
        if(isset($limit) && $limit>0 ){

        $script_pt_ts.="\n                    
            var car_ev$sh_count=jQuery('#ts_Carousel$sh_count').carousel({
                interval: 30000
            });                    
            ";

        }

        $script_pt_ts.=$pack_html['ajax_script'];                
        $script_pt_ts.= "\n});";
        $script_pt_ts.= "</script>";  
        add_action('wp_print_footer_scripts', 'ts_pt_script_execute', 20);  		
    return $content1;
}
add_shortcode("wpts_matriz", "wpts_useful_matrix_shortcode");
function pint_make_html($get_cat,$category_asigned,$taxo_html,$q_posts,$sh_count,$pt_menu,$atts,$ajax_mode){
  global $post;
	static $pt_menu_ct;
	extract($atts);
	if($boxwidth<=150){
			$imgc="thumbnail";        
	}else if($boxwidth<=300){
			$imgc="medium";        
	}else if($boxwidth>300){
			$imgc="large";        
	}
	$titlef="";
	if($titlefont!="default"){
			$titlef="font-family: \"$titlefont\"";
	}
	$contentf="";
	if($contentfont!="default"){
			$contentf="font-family: \"$contentfont\"";
	}	
	$colors=array("white"=>"","blue"=>"btn-primary","cyan"=>"btn-info","green"=>"btn-success","yellow"=>"btn-warning","red"=>"btn-danger","black"=>"btn-inverse");
	$bwt=$boxwidth;    
	$imgbh=100;	
	$content='';
	ob_start();
	$limit_p=(isset($limit))?(int)$limit:'';/// limite de post por carrousel
	$counter=0;///conteador de elementos
    $id_ct='#ts_Carousel'.$sh_count;
	if ($limit_p===0 or count($q_posts)<=$limit_p){
        $pint_result['noarrow']=true;
		unset($limit);
	}
	$col_st=1;
	/*taxnomy or categories ajax*/
	if($show_menu==='1'&& $ajax_mode==false){
		$pt_menu=true;
    switch ($category) {
        case 'none':
            $name_default='All '.$category_asigned;
        break;
        default:
            if ($category=='0'){
                $name_default='All '.$category_asigned;
            }else{
                $name_default=get_cat_name($category);                    
            }
        break;
    }
  ?>
  <div class="btn-group pt_main_drp pt_grp_<?php echo $sh_count; ?>" style="text-align: center; width: 100%; left: 47%; top: -8px;">
    <a class="btn btn-primary" href="#"><?php echo $name_default;?></a>
    <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
      <span class="caret" style="height:7px;"></span>
      <img class="pint_loading" src="<?php echo plugin_dir_url(__FILE__);?>/img/loading.gif" />
    </a>
    <ul class="dropdown-menu">
      <?php 
        if(!empty($get_cat)){
            foreach($get_cat as $index => $category_pack){
              if($category_pack->name!==$category_asigned){
                if (!isset($limit)){
                  $data_handler="#wpts_container$sh_count";
                  $data_limit="0";
                }else{
                  $data_handler="#pint_ts_car_$sh_count";
                  $data_limit=$limit;																	
                }
      ?>
      <li>
        <?php
        switch ($category) {
            case 'none':
                $data_name=$category_pack->slug;
            break;
            default:
                $data_name= $category_pack->term_id;
            break;
        }
         ?>
      	<a href="#" data-base='false' data-menu=".pt_grp_<?php echo $sh_count; ?>" data-name="<?php echo $data_name; ?>" data-input="data_sh_<?php echo $sh_count; ?>" data-handler="<?php echo $data_handler;  ?>"   data-taxo="<?php echo $taxo_html; ?>" data-limit="<?php echo $data_limit;?>"><?php echo $category_pack->name; ?></a>
      </li>
      <?php	

              }
            }
        }            
      ?>
      <li>
     	 <a href="#" data-menu=".pt_grp_<?php echo $sh_count; ?>" data-name="<?php echo $category_asigned; ?>" data-input="data_sh_<?php echo $sh_count; ?>" data-handler="<?php echo $data_handler;  ?>"   data-taxo="<?php echo $taxo_html; ?>" data-base='true' data-limit="<?php echo $data_limit;?>">All <?php echo $category_asigned; ?></a>
      </li>
    </ul>
   <input type="hidden" name="data_sh_<?php echo $sh_count; ?>" value="<?php echo urlencode(base64_encode(utf8_encode(serialize($atts)))); ?>" />
  </div>
<?php 
        
		if ($pt_menu===true){
			$pt_menu_ct++;
            $content.=ob_get_clean();            
            ob_start();
?>
      jQuery('div.pt_grp_<?php echo $sh_count;?> .dropdown-toggle').dropdown();
      jQuery('div.pt_grp_<?php echo $sh_count;?> ul.dropdown-menu a').click(function(e) {
        jQuery('div.pt_grp_<?php echo $sh_count;?> img.pint_loading').show();
        var cur_ele=jQuery(this);
		var button_st=jQuery(cur_ele.attr('data-menu')).children(':first-child');
		jQuery(cur_ele.attr('data-menu')).removeClass('open');
        var cat=cur_ele.attr('data-name');
        var handle=cur_ele.attr('data-handler');
        var datalimit=cur_ele.attr('data-limit');
        var taxo=cur_ele.attr('data-taxo');
				var data_sht=jQuery('input[name="'+cur_ele.attr('data-input')+'"]').val();
				var data_base=cur_ele.attr('data-base');
				jQuery.ajax({
					url:'<?php echo get_bloginfo('wpurl');?>/wp-admin/admin-ajax.php',
					data:"action=pint_get_ctns&pinttg_taxo="+taxo+"&pinttg_category="+cat+"&pinttg_datalimit="+datalimit+'&pinttg_sht='+data_sht+'&pint_base='+data_base,
					type:'POST',
                    dataType:'json',
					success:function(msg){
						button_st.html(cur_ele.html());
						jQuery('img.pint_loading').hide();
                        if (msg.noarrow===true){
                            jQuery('a[href="<?php echo $id_ct; ?>"]').hide();
                        }else{
                            jQuery('a[href="<?php echo $id_ct; ?>"]').show();
                        }
						jQuery(handle).html(msg.html);
                        if (msg.masonry===true){
                            $mms<?php echo $sh_count;?>.masonry('reload');
                        }

					}
				});
        return false;
      });
        <?php
        $script_ajax=ob_get_clean();
        ob_start();
		}
	}
	$jQuery_st="#wpts_container$sh_count";//selectores de jQuery
	if ($ajax_mode===false){
		if (!isset($limit)){            
		?>
		<div id='wpts_container<?php echo $sh_count;?>' data-menu='pt_dr_menu_<?php echo $sh_count;?>' style='width: 100%;text-align: center;vertical-align: top'>
		<?php
		}else{
		?>
			<div id="ts_Carousel<?php echo $sh_count;?>" data-menu="pt_dr_menu_<?php echo $sh_count;?>" class="carousel slide">
				<div class="carousel-inner" id="pint_ts_car_<?php echo $sh_count;?>">
		<?php 
		}
	}
/*Comienzo del loop*/
$counter=0;
	foreach($q_posts as $post):
		setup_postdata($post);
		if (($counter===0 && isset($limit)) or ($counter===$limit_p && isset($limit))){
			$user_status=($counter===0)?'active':'';
			if($counter>0){
				echo  "</div>";
				//	exit;
					$counter=0;
			}
	?>
				<div id='wpts_container<?php echo $sh_count.$col_st;?>.' class='item <?php echo $user_status; ?>' style='width: 100%'>
	<?php
			$jQuery_st.=", #wpts_container".$sh_count.$col_st;
			$col_st++;
		}
		$tmb = "";
		$output = preg_match('/<img[^>]+\>/i', $post->post_content, $matches);            
	?>
    <div class="<?php echo "item$sh_count useful_box$boxstyle";?>" style="width:<?php echo $bwt;?>px;">
	<?php 
		$bwt2=$bwt-10;
		$tmb=get_the_post_thumbnail(get_the_ID(),$imgc,array("title"=>get_the_title(),"style"=>"width: {$bwt2}px;height: {$bwt2}px;")); 
	?>
	<?php
		if($showtitle=="1"||empty($tmb)){
			$imgbh-=20;
	?>
			<div style='font-size: <?php echo $titlesize;?>px;font-weight: bold;<?php echo $titlef;?>;padding: 5px'>
				<?php the_title();?>
			</div>
	<?php 
		}
		if($showimage=="1"){?>
			<div class='img_shadow' style='width: <?php echo $bwt2;?>px;height:<?php echo $bwt2;?>px;text-align:center;margin:auto'>
				<a title='<?php the_title();?>' href='<?php the_permalink();?>'>
					<?php 
						if(empty($tmb)){                
							if(isset($matches[1][0])){?>
					<img src='<?php echo $matches[1][0];?>' width='<?php echo $bwt2?>' height='<?php echo $bwt2 ?>' title='<?php the_title();?>' />
						<?php 
							}
						}else{
							echo $tmb;
						}
					?>
				</a>
			</div>
			<?php
		}
		if($showexcerpt=="1"){
			$imgbh-=20;
			?> 
			<p style='font-size:<?php echo $contentsize;?>px;<?php echo $contentf; ?>'>
				<?php echo  ($masonry=='1') ? get_the_excerpt()  : substr(strip_tags(get_the_content()),0,140); ?>
			</p>
			<?php
		}
		if($GLOBALS['tg_shp_cart']==='true' && ($taxonomy==='catalogs' or $posttype==='wtsproduct')){
			$content.=ob_get_clean();
			ob_start();
			$width_bx_tg=(int) $boxwidth;
			$btn_shp_style=(!isset($buttoncolor) or $buttoncolor==='nobutton' )?'':"btn btn-small {$colors[$buttoncolor]}";
			$clas_btn=($width_bx_tg<=190)?' shp_p_small':'shp_p_big';//define buttons
		?>
      <a class="<?php echo $btn_shp_style; ?> shp_cart_button <?php echo $clas_btn; ?>"  style='font-size: <?php echo $contentsize."px;$contentf";?>'  tg_id_product="<?php the_ID(); ?>" tg_shp_type="tg_shp_add"  onclick="return false;" ondblclick="return false;" href="#"> 
        <?php echo get_post_meta(get_the_ID(), 'item_value',true).' '.$GLOBALS['tg_shp_currency'];  ?> (+) Cart
      </a>
		<?php 
			 echo ($width_bx_tg<=190)?'<br />':'';
			$shp_cart_button=ob_get_clean();
			ob_start();						
		}else{
			$shp_cart_button='';
		}
		$br_control=($showexcerpt=="1")?'':'<br />';
		if(!isset($buttoncolor) or $buttoncolor==='nobutton'){
			echo $br_control.$shp_cart_button;
		?>
		 <a style='font-size: <?php echo $contentsize;?>px;<?php echo $contentf;?>' href='<?php the_permalink();?>'><?php echo __($buttontext,'usefulmatrixint')?></a>                 
				 <?php
		}else{
			?>
			<div class='readmore'>	
				<?php echo $br_control.$shp_cart_button?><a class='btn btn-small <?php echo $colors[$buttoncolor];?>' href='<?php the_permalink()?>'><?php echo __($buttontext,'usefulmatrixint')?></a>
			</div>
			<?php 
		}            
		?>
		</div>
		<?php
		$counter++;            
	endforeach;
	if ($ajax_mode===false){	
		if (!isset($limit)){?>
				</div>
  <?php
		}else{
		//$content1.= "<div style='clear: both'></div>";

	?>
			</div>
		</div>
        <div class="arrows_align">
    		<?php
    		//echo '</div>';
      	  if($masonry=='1'){
        ?>  
          <a class="carousel-control masonry-arrows left" href="<?php echo $id_ct;?>" data-slide="prev">&lsaquo;</a>
          <a class="carousel-control masonry-arrows right" href="<?php echo $id_ct;?>" data-slide="next">&rsaquo;</a>
         <?php
      	  }else{
          ?> 
          <a class="carousel-control left" href="<?php echo $id_ct;?>" data-slide="prev">&lsaquo;</a>
          <a class="carousel-control right" href="<?php echo $id_ct;?>" data-slide="next">&rsaquo;</a>
         <?php
    	    }
        ?>
        </div>        
	</div>
	<?php
		}
	}
	$content.=ob_get_clean();
	$pint_result['html']=$content;
	$pint_result['jQuery']=$jQuery_st;
    $pint_result['ajax_script']=$script_ajax;
	return $pint_result;
}
function pint_get_ctns_ajax(){
	global $post;
    $get_atts=unserialize(base64_decode(urldecode($_POST['pinttg_sht'])));    
    switch ( $get_atts['category']) {
        case 'none':
            $get_tax=get_taxonomies($args,'objects');
            $args = array(
               'numberposts' =>$get_atts['posts'],
               'order'=>'DESC',
               'post_type' => $get_tax[$_POST['pinttg_taxo']]->object_type[0],
               $get_tax[$_POST['pinttg_taxo']]->name => $_POST['pinttg_category'],
               'post_status' => 'publish'
            );
            if ($_POST['pint_base']!=='false'){
                unset($args[$_POST['pinttg_taxo']]);
            }            
        break;
        default:
            $args="cat=".$_POST['pinttg_category']."&order=ASC&numberposts=".$get_atts['posts'];
        break;
    }


    $q_posts=get_posts($args);
    $pint_result= pint_make_html('','','',$q_posts,'','',$get_atts,true);
    $pint_result['masonry']=($get_atts['masonry']=='1')?true:false;
	echo json_encode($pint_result);
	exit;
}
add_action('wp_ajax_pint_get_ctns', 'pint_get_ctns_ajax');
add_action('wp_ajax_nopriv_pint_get_ctns', 'pint_get_ctns_ajax');

if ( ! function_exists('ts_pinterest_js') ){
    function ts_pinterest_js() {
    ?>
        <script type='text/javascript'>        
            if (typeof jQuery === "undefined" ){
                document.write("<script type=\'text/javascript\' src=\'http://code.jquery.com/jquery-1.7.2.js\'></scr"+"ipt>");
            }  
       </script>
       <script type="text/javascript">
            if(typeof jQuery.fn.masonry === "undefined"){        
                document.write("<script type=\'text/javascript\' src=\'<?php echo plugin_dir_url(__FILE__)."js/jquery.masonry.js"; ?>\'></scr"+"ipt>");                
            }
        </script>
         <script type="text/javascript">
            if(typeof jQuery.fn.carousel === "undefined"){
                document.write("<script type=\'text/javascript\' src=\'<?php echo plugin_dir_url(__FILE__);?>js/bootstrap-carousel.js\'></scr"+"ipt>");                     
            }
        </script> 
        <script type="text/javascript">
            if(typeof jQuery.fn.dropdown === "undefined"){
              document.write("<script type=\'text/javascript\' src=\'<?php echo plugin_dir_url(__FILE__);?>js/bootstrap-dropdown.js\'></scr"+"ipt>");                       
            }
        </script>
    <?php
    }
}
if ( ! function_exists('ts_pt_script_execute') ){  
    function ts_pt_script_execute(){
        global $script_pt_ts;
        echo $script_pt_ts;
    }
}
/*Init for add media and featured image implementation bby danjavia*/
//hook for add more custom fields into category form :: edit_tags.php
// Add term page
function feature_img_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="upload_image"> Feature image: </label>
		<input id="upload_image" size="36" name="upload_image[custom_term_meta]" type="text" />
		<div id="wp-content-wrap" class="wp-editor-wrap hide-if-no-js wp-media-buttons">
			<a href="#" class="button insert-media add_media" data-editor="content" id="upload_image_button" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>		
		</div>
		<p class="description">Enter a valid Url (http://domain.com/path/to/your/category/img.png)</p>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function() {
				jQuery('#upload_image_button').click(function() {
				 formfield = jQuery('#upload_image').attr('name');
				 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
				 return false;
				});
				
				window.send_to_editor = function(html) {
				 imgurl = jQuery('img',html).attr('src');
				 jQuery('#upload_image').val(imgurl);
				 tb_remove();
				}
			
			});
		</script>
	</div>
<?php
	
		 
}
add_action( 'category_add_form_fields', 'feature_img_add_new_meta_field', 10, 2 );
function my_admin_scripts() {
		 wp_enqueue_script('media-upload'); 
		 wp_enqueue_script('thickbox'); 
		 wp_register_script('my-upload', WP_PLUGIN_URL.'/js/upload.js', array('jquery','media-upload','thickbox')); 
		 wp_enqueue_script('my-upload'); 
	}  
function my_admin_styles() {
	 wp_enqueue_style('thickbox');
}  
/*add necessary for include media library*/
add_action('admin_print_scripts', 'my_admin_scripts');
add_action('admin_print_styles', 'my_admin_styles');
/*end for add_action*/ 
// Edit term page
function feature_img_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="upload_image"> Feature image: </label></th>
		<td>
			<input id="upload_image" size="36" name="upload_image[custom_term_meta]" type="text" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>"/>
			<div id="wp-content-wrap" class="wp-editor-wrap hide-if-no-js wp-media-buttons">
				<a href="#" class="button insert-media add_media" data-editor="content" id="upload_image_button" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>		
			</div>
			<p class="description">Enter a valid Url (http://domain.com/path/to/your/category/img.png)</p>
			<script type="text/javascript" charset="utf-8">
				jQuery(document).ready(function() {
					jQuery('#upload_image_button').click(function() {
					 formfield = jQuery('#upload_image').attr('name');
					 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
					 return false;
					});
					
					window.send_to_editor = function(html) {
					 imgurl = jQuery('img',html).attr('src');
					 jQuery('#upload_image').val(imgurl);
					 tb_remove();
					}
				
				});
			</script>
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top">Your Selected image:</th>
		<td>
			<img src="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>" alt="" />
		</td>
	</tr>
<?php
}
add_action( 'category_edit_form_fields', 'feature_img_edit_meta_field', 10, 2 );
// Save extra taxonomy fields callback function.
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['upload_image'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['upload_image'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['upload_image'][$key] ) ) {
				$term_meta[$key] = $_POST['upload_image'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_category', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_category', 'save_taxonomy_custom_meta', 10, 2 );
/*end add media implementation*/

?>
