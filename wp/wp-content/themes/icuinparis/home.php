<?php
/*
Template Name: Home
*/

	get_header();
?>
	<div id="featured_slider" class="carousel slide " data-interval="50">
		<ol class="carousel-indicators">
		    <li data-target="#featured_slider" data-slide-to="0" class="active"></li>
		    <li data-target="#featured_slider" data-slide-to="1"></li>
            <li data-target="#featured_slider" data-slide-to="2"></li>
		    <li data-target="#featured_slider" data-slide-to="3"></li>
		 </ol>
		 <!-- Carousel items -->
		<div class="carousel-inner">
			<a href="<?= get_bloginfo('url'); ?>/creative-community" class="active item">
				<img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0001_CREATIVE-COMMUNITY.png" alt="" class="woo-image slide-image" />			
			</a>
            <a href="<?= get_bloginfo('url'); ?>/wholesale" class="item">
                <img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0000_video.png" alt="" class="woo-image slide-image" />
            </a>
			<a href="<?= get_bloginfo('url'); ?>/store/esparta-shell-necklace.html" title="Shop the Esparata Shell Necklace" class="item">
				<img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0002_VASKOLG-ESPARATA-SHELL-NECKLACE-GIVE-THEM-SOMETHINGTHEYVE-NE.png" alt="" class="woo-image slide-image" />
			</a>
			<a href="<?= get_bloginfo('url'); ?>/store/womens?designer=44" class="item">
				<img src="<?= get_bloginfo('wpurl'); ?>/wp-content/uploads/0003_NOOT-SS_13.png" alt="" class="woo-image slide-image" />
			</a>
		</div>
	</div><!--/#featured_slider-->


    	<div id="store_featurette_header" class="row">
         	<h1 class="span8">Shop Online</h1>
         	<a class="shop-all-btn span2" href="<?= get_bloginfo('url'); ?>/store/mens" title="Shop ICU Mens">Shop Mens</a>
         	<a class="shop-all-btn span2" href="<?= get_bloginfo('url'); ?>/store/womens" title="Shop ICU Womens">Shop Womens</a>
        </div><!--/#store_featurette_header .row-->

     	<div id="homeproducts" class="row">
     		
        <?php 
        //oc: pull in main large featured product (catID = 1345)
            $args 		= array(	'category'			=> '1345',
        							'suppress_filters' 	=> true
        					);
        	$featured 	= get_posts( $args ); 

        	//oc: fake it till you make it.
    	 	$p 		= $featured[0];
    	 	$info 	= get_post_custom($p->ID); 
    	 ?>
    	 		<div id="large_featured_product" class="product span6">
    	 			<a href="<?= $info['link'][0]; ?>" title="<?= $p->post_title; ?>"><?= $p->post_content; ?><span class="price"><?= $info['price'][0]; ?></span></a>
    	 			<a class="designer-name" href="<?= $info['link'][0]; ?>" title="<?= $p->post_title; ?>"><?= $info['designer'][0]; ?></a>
    	 			<a class="product-name" href="<?= $info['link'][0]; ?>" title="<?= $p->post_title; ?>"><?= $info['name'][0]; ?></a>
    	 		</div>
    	 	<div id="small_featured_products" class="span6">
    	 		<div class="container-fluid">
    	 			<div class="row-fluid">
    	 	<?php  
        	
        //oc: pull in 'store' category posts (catID = 1342) and echo them out.
        	$args 		= array(	'category'=> '1342',
        							'posts_per_page' => 6,
        							'suppress_filters' => true
        					);
        	$featured 	= get_posts( $args ); 
        	$i = 0;//oc: counter for 3-across row.

        	//oc: fake it till you make it.
    	 	foreach ($featured as $p):  
    	 		$info 	= get_post_custom($p->ID); 
    	 		if ($i == 3) : ?> 
    	 		</div><div class='row-fluid'> 
    	 		<?php endif; ?>
    	 
    	 		<div class="product span4">
    	 			<a href="<?= $info['link'][0]; ?>" title="<?= $p->post_title; ?>"><?= $p->post_content; ?><span class="price"><?= $info['price'][0]; ?></span></a>
    	 			<a class="designer-name" href="<?= $info['link'][0]; ?>" title="<?= $p->post_title; ?>"><?= $info['designer'][0]; ?></a>
    	 			<a class="product-name" href="<?= $info['link'][0]; ?>" title="<?= $p->post_title; ?>"><?= $info['name'][0]; ?></a>
    	 		</div>
    	 		
    	 	<?php $i += 1; endforeach; ?>
    	 </div></div><!-- /end row-fluid and container fluid of small right side products -->
    	</div><!-- /end span6 for right side products -->
      	</div><!-- /end row of all products -->
	    
        <div id="homepage_callouts" class="row">
            <a class="span6" href="<?= get_bloginfo('url'); ?>/store/designs"><img alt="Submit Your Designs Image" src="<?php bloginfo('stylesheet_directory'); ?>/img/homepage/submit-your-own-design.jpg" /></a>
            <a class="span6" href="<?= get_bloginfo('url'); ?>/wholesale"><img alt="ICU Wholesale" src="<?php bloginfo('stylesheet_directory'); ?>/img/homepage/wholesale.jpg" /></a>

        </div>
	</div>
</div><!-- /#wrapper -->
<div id="footer-widget-wrapper">

	<section id="footer_widgets" class="container">
		<div id="footer_widgets_header" class="row">
        	<div class="span2"><img src="<?php echo get_bloginfo('wpurl')?>/wp-content/uploads/home-footer-call-header.png" width="200" height="123" align="left"/></div>
       		<div class="span10">We opened in 2010, selling a unique collection of designer jewelry and accessories. Always an online platform and now based between Paris and New York City, we offer a mix of European and American style and culture through our product offering. An important part of the icuinparis.com culture is our customizeable and made-to-order product. We have created strong relationships with our designers, allowing us the advantage to offer you designs and product that may not be available elsewhere, or that was never realized until you suggested it. Now in our third year, icuinparis.com has recenly opened a showroom branch: Showroom ICU. </div>
        </div>	

		<div class="row">
			<?php 
			$args = array( 	'post_type' => 'footer-widget', 
							'posts_per_page' => 4,
							'order'           => 'ASC' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post();
			
				echo '<div class="widget span3">';
					the_content();
				echo '</div>';
			endwhile;
			?>
		</div> <!-- test row -->

	</section><!-- /#footer-widgets  -->

    </div><!-- /#content -->
		
<?php get_footer(); ?>