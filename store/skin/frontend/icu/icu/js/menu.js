/* 
   Simple JQuery Accordion menu.
   HTML structure to use:

   <ul id="menu">
     <li><a href="#">Sub menu heading</a>
     <ul>
       <li><a href="http://site.com/">Link</a></li>
       <li><a href="http://site.com/">Link</a></li>
       <li><a href="http://site.com/">Link</a></li>
       ...
       ...
     </ul>
     <li><a href="#">Sub menu heading</a>
     <ul>
       <li><a href="http://site.com/">Link</a></li>
       <li><a href="http://site.com/">Link</a></li>
       <li><a href="http://site.com/">Link</a></li>
       ...
       ...
     </ul>
     ...
     ...
   </ul>

Copyright 2007 by Marco van Hylckama Vlieg

web: http://www.i-marco.nl/weblog/
email: marco@i-marco.nl

Free for non-commercial use


function initMenu() {
  jQuery('#menu ul').hide();
 // jQuery('#menu ul:first').show();
  jQuery('#menu li a').click(
    function() {
      var checkElement = jQuery(this).next();
      if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
        return false;
        }
      if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
        jQuery('#menu ul:visible').slideUp('normal');
        checkElement.slideDown('normal');
        return false;
        }
      }
    );
  }
jQuery(document).ready(function() {initMenu();}); */
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
function initMenu() {
  jQuery('#menu ul').hide();
  jQuery('#menu li a').click(
    function() {	
		var checkElement = jQuery(this).next();
        if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
		  jQuery(this).next().slideToggle('medi');
          return false;
        }
        if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
		  jQuery(this).next().slideToggle('normal');
		  //jQuery('#menu ul:visible').slideUp('normal');
          //checkElement.slideDown('normal');
          return false;
        }
      }
    );
  jQuery('#menu2222 li a').click(
    function() {	
		var checkElement = jQuery(this).next();
        if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
		  jQuery(this).next().slideToggle('medi');
          return false;
        }
        if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
		  jQuery(this).next().slideToggle('normal');
		  //jQuery('#menu ul:visible').slideUp('normal');
          //checkElement.slideDown('normal');
          return false;
        }
      }
    );
}

function swaparrow(e){
	if(document.getElementById(e).src == "http://www.icuinparis.com/skin/frontend/icu/icu/images/down-arrow.jpg"){
		document.getElementById(e).src = "http://www.icuinparis.com/skin/frontend/icu/icu/images/right-arrow.jpg";
	}else{
		document.getElementById(e).src = "http://www.icuinparis.com/skin/frontend/icu/icu/images/down-arrow.jpg";
	}
}

jQuery(document).ready(function() {initMenu();});