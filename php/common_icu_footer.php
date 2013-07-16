<?php 

    $protocol = isset( $_SERVER['HTTPS'] ) ? "https://" : "http://";
    $base_url = $protocol . $_SERVER['HTTP_HOST'];


?>

<footer id="footer">
		<img src="<?= $base_url; ?>/wp/wp-content/uploads/footer_icu.png" width="397" />
		<br /> 
		<br /> 
		We welcome you to contact us with questions, feedback, designer submissions and/or custom orders.
		<br /> 
		<br /> 
		ICU - IN PARIS LLC
		<br /> 
		<a href="mailto:icu@icuinparis.com">icu@icuinparis.com</a>
		<br /> 
		P: 646.574.1546 (US)
		<br /> 
		P: +33 658285574 (FR)
		<br /> 
		<br /> 
		E-Commerce Site Hours:
		<br /> 
		Monday-Friday 9:00–7:00		
</footer><!-- /#footer  -->