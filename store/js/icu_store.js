

jQuery(document).ready(function($) {
  console.log("doc ready");
  initMenu();

  var options = {
      zoomWidth: 400,
      zoomHeight: 400,
      title: false,
      xOffset: 0,
      yOffset: 0,
      position: "right",
      zoomType: 'standard',
      lens:true,
      preloadImages: false,
      alwaysOn:false
  };
  
  var UIDropdown            = $(".b-style-dropdown");
  $(".jqzoom").jqzoom(options);
  
  $('a[rel*=facebox]').facebox();

  $('.b-style-dropdown__button').bind('click', triggerSwitcher);
});

//oc: Google Code for ICU remarketing_2 Remarketing List
var google_conversion_id    = 1008295145;
var google_conversion_language  = "fr";
var google_conversion_format  = "3";
var google_conversion_color   = "666666";
var google_conversion_label   = "FVa8CP-p6AIQ6bnl4AM";
var google_conversion_value   = 0;


var scroller              = null;
var showUIDropdownClass   = "b-style-dropdown_state_open";
var hideUIDropdownClass   = "b-style-dropdown_state_close";
var Translator            = new Translate({"Credit card number doesn't match credit card type":"Credit card number does not match credit card type","Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.":"Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in this field, first character must be a letter."});

var productAddToCartForm  = new VarienForm('product_addtocart_form');
productAddToCartForm.submit = function(){
        if (this.validator.validate()) {
            this.form.submit();
        }
    }.bind(productAddToCartForm);

if(typeof tinyMCE != 'undefined'){
  tinyMCE.init({
    // General options
    mode : "textareas",
    theme : "simple",
  });
  
}

function showBigImageDiv(largeName, smallName) {
  document.getElementById('imagebigg').src = smallName;
  document.getElementById('axss').href = largeName;
}

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
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

//Switcher Dropdown functions
function triggerSwitcher() {
  console.log("triggerSwitcher click");
    isDropDownOpen() ? closeUIDropdown() : openUIDropdown();
    return false;
}

function openUIDropdown() {
  console.log("open switcher");
    jQuery('body').bind('click', closeUIDropdown)
    UIDropdown.removeClass(hideUIDropdownClass)
    UIDropdown.addClass(showUIDropdownClass)
}

function closeUIDropdown() {
  console.log("close switcher");
    jQuery('body').unbind('click')
    UIDropdown.addClass(hideUIDropdownClass)
    UIDropdown.removeClass(showUIDropdownClass)
}

function isDropDownOpen() {
    return UIDropdown.hasClass(showUIDropdownClass) || false;
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
  var down  = skin_url + "images/down-arrow.jpg";
  var right = skin_url + "images/right-arrow.jpg";
	if(document.getElementById(e).src == down ){
		document.getElementById(e).src = right;
	}else{
		document.getElementById(e).src = down;
	}
}

function showdescriptionpanel(){
  document.getElementById('descriptionpanel').style.display = "block";
  document.getElementById('theatelierpanel').style.display = "none";
  document.getElementById('shippingpanel').style.display = "none";
  document.getElementById('inquiriespanel').style.display = "none";
}

function showtheatelierpanel(){
  document.getElementById('descriptionpanel').style.display = "none";
  document.getElementById('theatelierpanel').style.display = "block";
  document.getElementById('shippingpanel').style.display = "none";
  document.getElementById('inquiriespanel').style.display = "none";
}

function showshippingpanel(){
  document.getElementById('descriptionpanel').style.display = "none";
  document.getElementById('theatelierpanel').style.display = "none";
  document.getElementById('shippingpanel').style.display = "block";
  document.getElementById('inquiriespanel').style.display = "none";
}

function showinquiriespanel(){
  document.getElementById('descriptionpanel').style.display = "none";
  document.getElementById('theatelierpanel').style.display = "none";
  document.getElementById('shippingpanel').style.display = "none";
  document.getElementById('inquiriespanel').style.display = "block";
}
