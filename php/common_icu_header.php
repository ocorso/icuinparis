<?php 


    if ($_SERVER['HTTPS'] == "on" || IS_SSL) $protocol   = "//";
    else                           $protocol   = "http://";
  	$base_url = $protocol . $_SERVER['HTTP_HOST'];

?>

<link media="all" href="<?= $base_url; ?>/php/ored-styles.css" type="text/css" rel="stylesheet">

<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="brand" href="<?= $base_url; ?>">ICU</a>
      <div class="nav-collapse collapse">
        <ul class="nav">
          <li><a href="<?= $base_url; ?>">Home</a></li>
          <li class="dropdown">
            <a href="#" title="Shop" class="dropdown-toggle " data-toggle="dropdown">Shop <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li class=""><a title="Shop Womens" href="<?= $base_url; ?>/store/womens">Womens</a></li>
              <li class=""><a href="<?= $base_url; ?>/store/mens">Mens</a></li>
              <li class=""><a href="<?= $base_url; ?>/designers" title="By Designer">By Designer</a></li>
            </ul>
          </li>
          <li><a href="<?= $base_url; ?>/wholesale/">Wholesale</a></li>
          <li><a href="<?= $base_url; ?>/creative-community/">Creative Community</a></li>
          <li><a href="<?= $base_url; ?>/about/">About</a></li>
        </ul>
        <ul id="social_account" class="nav">
            <li class="dropdown pull-right">
              <a href="#" title="Account" class="dropdown-toggle" data-toggle="dropdown">Account<span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="<?= $base_url; ?>/store/customer/account" title="My Account">My Account</a></li> 
                <li><a href="<?= $base_url; ?>/store/checkout/cart" title="My Cart">My Cart</a></li>  
                <li><a href="<?= $base_url; ?>/store/checkout/cart" title="Checkout">Checkout</a></li>
              </ul>
            </li>
            <li class="social-icon"><a href="http://www.facebook.com/icuinparis" title="ICU on Facebook" target="_blank"><img src="/wp/wp-content/themes/icuinparis/img/icons/icon-facebook.png" alt="ICU on Facebook" /></a></li>       
            <li class="social-icon"><a href="http://twitter.com/icuinparis" title="ICU on Twitter" target="_blank"><img src="/wp/wp-content/themes/icuinparis/img/icons/icon-twitter.png" alt="ICU on Twitter" /></a></li>      
            <li class="social-icon"><a href="http://instagram.com/christansummers" title="ICU on Instagram" target="_blank"><img src="/wp/wp-content/themes/icuinparis/img/icons/icon-instagram.png" alt="ICU on Instagram" /></a></li>
            <li class="social-icon"><a href="http://youtube.com/parisicu" title="ICU on YouTube" target="_blank"><img src="/wp/wp-content/themes/icuinparis/img/icons/icon-youtube.png" alt="ICU on YouTube" /></a></li>         
        </ul>
      </div><!--/.nav-collapse -->
    </div>
  </div>
</div>