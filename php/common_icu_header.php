<?php 

    $protocol = isset( $_SERVER['HTTPS'] ) ? "https://" : "http://";
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
            <a href="#" title="Shop" class="dropdown-toggle " data-toggle="dropdown">Shop</a>
            <ul class="dropdown-menu">
              <li class=""><a title="Shop Womens" href="<?= $base_url; ?>/store/womens">Shop Womens</a></li>
              <li class=""><a href="<?= $base_url; ?>/store/mens">Shop Mens</a></li>
              <li class=""><a href="<?= $base_url; ?>/designers" title="By Designer">Shop By Designer</a></li>
            </ul>
          </li>
          <li><a href="<?= $base_url; ?>/wholesale/">Wholesale</a></li>
          <li><a href="<?= $base_url; ?>/creative-community/">Creative Community</a></li>
          <li><a href="<?= $base_url; ?>/about/">About</a></li>
        </ul>
      </div><!--/.nav-collapse -->
    </div>
  </div>
</div>