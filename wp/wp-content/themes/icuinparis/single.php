<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Single Post Template
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a post ('post' post_type).
 * @link http://codex.wordpress.org/Post_Types#Post
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;
	
/**
 * The Variables
 *
 * Setup default variables, overriding them if the "Theme Options" have been saved.
 */
	
	$settings = array(
					'thumb_single' => 'false', 
					'single_w' => 200, 
					'single_h' => 200, 
					'thumb_single_align' => 'alignright'
					);
					
	$settings = woo_get_dynamic_values( $settings );
?>
<!-- BEGIN facebook js -->
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=567784666596189";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!-- END facebook js -->


     <div id="headline" class="row">
        <h1>Creative Community</h1>
    </div>

    <div id="post_content" class="row">
    
    	<?php woo_main_before(); ?>
    	<div class="span2">
    		<?= the_tags(); ?>
    		<nav>
	            <div class="nav-prev fl"><?php previous_post_link( '%link', 'Prev' ); ?></div>
	            <div class="nav-next fr"><?php next_post_link( '%link', 'Next' ); ?></div>
	        </nav><!-- #post-entries -->
    	</div>
		<section id="main" class="span7">
		           
        <?php
        	if ( have_posts() ) { $count = 0;
        		while ( have_posts() ) { the_post(); $count++;
        ?>
			<article <?php post_class(); ?>>

				<?php echo woo_embed( 'width=580' ); ?>
                <?php if ( $settings['thumb_single'] == 'true' && ! woo_embed( '' ) ) { woo_image( 'width=' . $settings['single_w'] . '&height=' . $settings['single_h'] . '&class=thumbnail ' . $settings['thumb_single_align'] ); } ?>

                <?php //woo_post_meta(); ?>

                <div class="article-inner">
                	
	                <header>
	                
		                <h2><?php the_title(); ?></h2>
	                	<p>by <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php printf( __( '%s', 'woothemes' ), get_the_author() ); ?></a> on <?php the_date(); ?>
	                </header>
	                
	                <section class="entry fix">
	                	<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) ); ?>
					</section>
										
				</div><!-- .article-inner -->
                                
            </article><!-- .post -->

				<?php if ( isset( $woo_options['woo_post_author'] ) && $woo_options['woo_post_author'] == 'true' ) { ?>
				<aside id="post-author" class="fix">
					
					<div class="profile-content">
					
						<?php the_author_meta( 'description' ); ?>
						<div class="profile-link">
				
						</div><!-- #profile-link	-->
					</div><!-- .post-entries -->
				</aside><!-- .post-author-box -->
				<?php } ?>

			<ul id="social_sharing">
		
				<!-- BEGIN FACEBOOK -->
				<li class="facebook-like">
					<div id="fb-root"></div>
					<div class="fb-like" data-href="http://developers.facebook.com/docs/reference/plugins/like" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true"></div>
				</li>
				<!-- END facebook -->

				<li class="google-plus">
					<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
					<g:plusone></g:plusone>
				</li>
				<li class="twitter-share">
					<a href="https://twitter.com/share" class="twitter-share-button" data-via="ocorso">Tweet</a>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
				</li>
			</ul>
			
			<!-- BEGIN facebook comments -->
			<div class="fb-comments span7" data-href="<?= get_permalink(); ?>"  data-num-posts="10"></div>
			<!-- END facebook comments -->
	        <nav class="row pull-right">
	            <div class="nav-prev fl"><?php previous_post_link( '%link', 'Prev' ); ?></div>
	            <div class="nav-next fr"><?php next_post_link( '%link', 'Next' ); ?></div>
	        </nav><!-- #post-entries -->
	       
            <?php

				} // End WHILE Loop
			} else {
		?>
			<article <?php post_class(); ?>>
            	<p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
			</article><!-- .post -->             
       	<?php } ?>  
        
		</section><!-- #main -->

    </div><!-- #content -->
		
<?php get_footer(); ?>