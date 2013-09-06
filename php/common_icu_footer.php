<?php 

    $protocol = isset( $_SERVER['HTTPS'] ) ? "https://" : "http://";
    $base_url = $protocol . $_SERVER['HTTP_HOST'];


?>

<footer id="footer">
	<h4>Sign up for ICU updates</h4>
	<form id="email_signup" action="<?= $base_url; ?>/store/bfmsubscribe/subscriber/new/" method="post">
		<input 	id="newsletter"  
				class="email"
				placeholder="Your Email"
				type="email" 
				value="" 
				name="email"
				>
		<input id="newsletter_submit" type="submit" hidden="true" />
	</form>
	<div id="newsletter_modal" class="modal fade">
		<div class="modal-header">
			<a class="close" data-dismiss="modal">&times;</a>
			<h3>ICU Newsletter Sign-up</h3>
		</div>
		<div class="modal-body">
			<p>Merci!</p>
		</div>
		<div class="modal-footer">
			<a href="#" data-dismiss="modal">OK</a>
		</div>
	</div>
<script>

var emailController 	= {};
//emailController.url 	= base_url + "/store/bfmsubscribe/subscriber/new/";
emailController.url 	= jQuery("#email_signup").attr("action");
emailController.complete= function($d){
	console.log($d.result);
		jQuery(".modal-body p").text($d.result);
		jQuery('#newsletter_modal').modal('show');
};
emailController.submit	= function($e){
	console.log("SUBMIT EMAIL: "+ emailController.url);
	if($e) $e.preventDefault();
    jQuery(this).blur();
	jQuery.post(emailController.url , {email: jQuery('#newsletter').val()}, emailController.complete);
   	jQuery('#newsletter_submit').focus().click();
	jQuery('#newsletter').val("");

};

jQuery(document).ready(function($) {
	
	$("#email_signup").submit(emailController.submit);
	
});

</script>
	<p>We welcome you to contact us with questions, feedback, designer submissions and/or custom orders.</p>
	<h4>ICU - IN PARIS LLC</h4>
	<p>
		<a href="mailto:icu@icuinparis.com">icu@icuinparis.com</a>
		<br /> 
		P: 646.574.1546 (US)
		<br /> 
		P: +33 658285574 (FR)
	</p>
	<ul>
		<li><a href="<?= $base_url; ?>/store/terms" title="ICU Terms &amp; Conditions">Terms</a></li>
		<li><a href="<?= $base_url; ?>/store/privacy" title="ICU Privacy Policy">Privacy</a></li>
		<li><a href="<?= $base_url; ?>/store/shipping" title="ICU Shipping">Shipping</a></li>
		<li><a href="<?= $base_url; ?>/store/returns" title="ICU Returns">Returns</a></li>
	</ul>
</footer><!-- /#footer  -->


<script>
 (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
 })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

 ga('create', 'UA-41864761-1', 'icuinparis.com');
 ga('send', 'pageview');

</script>