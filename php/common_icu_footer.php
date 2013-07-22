<?php 

    $protocol = isset( $_SERVER['HTTPS'] ) ? "https://" : "http://";
    $base_url = $protocol . $_SERVER['HTTP_HOST'];


?>

<footer id="footer">
		<h4>Sign up for the ICU updates</h4>
		<input 	id="newsletter"  
				class="email"
				type="email" 
				value="Your Email" 
				onfocus="if(this.value=='Your Email')this.value=''; console.log('onfocus');" 
				onblur="if(this.value=='')this.value='Your Email';" 
				name="email"
				>
<script>
jQuery(document).ready(function($) {
console.log('email form');
});

</script>
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


<script>
//  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
//  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
// m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
//  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

//  ga('create', 'UA-41864761-1', 'icuinparis.com');
//  ga('send', 'pageview');

</script>