var main 				= {};

//Doc Ready.
$( function($){
	//start the carousel (if there is one)
	$('.carousel').carousel("cycle");
	
	if ( navigator.userAgent.match(/Android/i) ){
		$('body').addClass( 'android' );
	}

	if ( navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i) ){
		$('body').addClass( 'is_mobile' );
		main.is_mobile = true;
	} else{
		main.is_mobile = false;
	}
$(window).resize( main.onresize);
	console.log("is_mobile : ", main.is_mobile );
	console.log("user agent : " + navigator.userAgent );
});

main.onresize = function(){

	console.log($(window).width());
};

