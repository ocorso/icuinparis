<?php 
	if(!isset($base_url))
		$base_url = "http://icuinparis.dev"; 

?>

<header>

<div id="logoposition">
	<a href="<?= $base_url; ?>">	
    	<img class="headerlogo" src="<?= $base_url; ?>/wp/wp-content/uploads/logo_icu.jpg">
    </a>    
</div>
<div id="top">
	<nav class="col-full" role="navigation">
		<ul id="top-nav" class="nav fl">
		<li class="menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home menu-item-9207"><a title="Home" href="<?= $base_url; ?>">Home</a></li>
		<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-9446">
			<span class="unclickable-nav-item">Shop</span>
			<ul class="sub-menu">
				<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-9447"><a title="Shop Womens" href="<?= $base_url; ?>/store/womens">Womens</a></li>
				<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-9448"><a href="<?= $base_url; ?>/store/mens">Mens</a></li>
				<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-9455"><a href="<?= $base_url; ?>/store/for-designers" title="For Designers">For Designers</a></li>
			</ul>
		</li>
		<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8938"><a href="<?= $base_url; ?>/representation/">Representation</a></li>
		<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8937"><a title="Creative Community" href="<?= $base_url; ?>/creative-community/">Creative Community</a></li>
		<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8936"><a title="About Us" href="<?= $base_url; ?>/about/">About</a></li>
		</ul>		
	</nav>
</div>

</header>