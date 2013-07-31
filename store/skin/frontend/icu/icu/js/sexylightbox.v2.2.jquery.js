/**

 * Sexy LightBox - for jQuery 1.3

 * @name sexylightbox.v2.2.js

 * @author Eduardo D. Sada - http://www.coders.me/web-html-js-css/javascript/sexy-lightbox-2

 * @version 2.2

 * @date 1-Jun-2009

 * @copyright (c) 2009 Eduardo D. Sada (www.coders.me)

 * @license MIT - http://es.wikipedia.org/wiki/Licencia_MIT

 * @example http://www.coders.me/ejemplos/sexy-lightbox-2/

*/



jQuery.bind = function(object, method){

  var args = Array.prototype.slice.call(arguments, 2);  

  return function() {

    var args2 = [this].concat(args, jQuery.makeArray( arguments ));  

    return method.apply(object, args2);  

  };  

};  



(function(jQuery) {

  SexyLightbox = {

    getOptions: function() {

      return {

        name          : 'SLB',

        zIndex        : 65555,

        color         : 'black',

        find          : 'sexylightbox',

        imagesdir     : 'http://192.168.1.28/icuinparis/js',

        background    : 'bgSexy.png',

        backgroundIE  : 'bgSexy.gif',

        closeButton   : 'SexyClose.png',

        displayed     : 0,

        modal         : 0,

        showDuration  : 200,

        showEffect    : 'linear',

        closeDuration : 400,

        closeEffect   : 'linear',

        moveDuration  : 800,

        moveEffect    : 'easeOutBack',

        resizeDuration: 800,

        resizeEffect  : 'easeOutBack',

        shake         : { distance: 10,

                          duration: 100,

                          transition: 'easeInOutBack',

                          loops: 2

                        },

        BoxStyles     : { 'width' : 486, 'height': 320 },

        Skin          : { 'white' : { 'hexcolor': '#FFFFFF', 'captionColor': '#000000', 'background-color': '#000', 'opacity': 0.6 },

                          'black' : { 'hexcolor': '#000000', 'captionColor': '#FFFFFF', 'background-color': '#fff', 'opacity': 0.6 }}

      };

    },



    initialize: function(options) {

      this.options = jQuery.extend(this.getOptions(), options);

      this.options.OverlayStyles = jQuery.extend(this.options.Skin[this.options.color], this.options.OverlayStyles || {});



      var strBG = this.options.imagesdir+'/'+this.options.color+'/'+((((window.XMLHttpRequest == undefined) && (ActiveXObject != undefined)))?this.options.backgroundIE:this.options.background);

      var name  = this.options.name;



			jQuery('body').append('<div id="'+name+'-Overlay"></div><div id="'+name+'-Wrapper"><div id="'+name+'-Background"></div><div id="'+name+'-Contenedor"><div id="'+name+'-Top" style="background-image: url('+strBG+')"><a id="'+name+'-CloseButton" href="#"><img src="'+this.options.imagesdir+'/'+this.options.color+'/'+this.options.closeButton+'" alt="Close"></a><div id="'+name+'-TopLeft" style="background-image: url('+strBG+')"></div></div><div id="'+name+'-Contenido"></div><div id="'+name+'-Bottom" style="background-image: url('+strBG+')"><div id="'+name+'-BottomRight" style="background-image: url('+strBG+')"><div id="'+name+'-Navegador"><strong id="'+name+'-Caption"></strong></div></div></div></div></div>');

      

      this.Overlay      = jQuery('#'+name+'-Overlay');

      this.Wrapper      = jQuery('#'+name+'-Wrapper');

      this.Background   = jQuery('#'+name+'-Background');

      this.Contenedor   = jQuery('#'+name+'-Contenedor');

      this.Top          = jQuery('#'+name+'-Top');

      this.CloseButton  = jQuery('#'+name+'-CloseButton');

      this.Contenido    = jQuery('#'+name+'-Contenido');

      this.bb           = jQuery('#'+name+'-Bottom');

      this.innerbb      = jQuery('#'+name+'-BottomRight');

      this.Nav          = jQuery('#'+name+'-Navegador');

      this.Descripcion  = jQuery('#'+name+'-Caption');



      this.Overlay.css({

        'position'  : 'absolute',

        'top'       : 0,

        'left'      : 0,

        'opacity'   : this.options.OverlayStyles['opacity'],

        'height'    : jQuery(document).height(),

        'width'     : jQuery(document).width(),

        'z-index'   : this.options.zIndex,

        'background-color': this.options.OverlayStyles['background-color']

      }).hide();

      

      this.Wrapper.css({

        'z-index'   : this.options.zIndex,

        'top'       : (-this.options.BoxStyles['height']-280)+'px',

        'left'      : ( (jQuery(document).width() - this.options.BoxStyles['width']) / 2)

      }).hide();

      

      this.Background.css({

        'z-index'   : this.options.zIndex + 1

      });

      

      this.Contenedor.css({

        'position'  : 'absolute',

        'width'     : this.options.BoxStyles['width'] + 'px',

        'z-index'   : this.options.zIndex + 2

      });

      

      this.Contenido.css({

        'height'            : this.options.BoxStyles['height'] + 'px',

        'border-left-color' : this.options.Skin[this.options.color].hexcolor,

        'border-right-color': this.options.Skin[this.options.color].hexcolor

      });

      

      this.Nav.css({

        'color'     : this.options.Skin[this.options.color].captionColor

      });



      this.Descripcion.css({

        'color'     : this.options.Skin[this.options.color].captionColor

      });





          

      /**

       * AGREGAMOS LOS EVENTOS

       ************************/



      this.CloseButton.bind('click', jQuery.bind(this, function(){

        this.close();

        return false;

      }));

      

      this.Overlay.bind('click', jQuery.bind(this, function(){

        if (!this.options.modal) {

          this.close();

        }

      }));





       jQuery(document).bind('keydown', jQuery.bind(this, function(obj, event){

        if (this.options.displayed == 1) {

          if (event.keyCode == 27){

            this.close();

          }



          if (event.keyCode == 37){

            if (this.prev) {

              this.prev.trigger('click', event);

            }

          }



          if (event.keyCode == 39){

            if (this.next) {

              this.next.trigger('click', event);

            }

          }

        }

      }));



      jQuery(window).bind('resize', jQuery.bind(this, function(){

        if(this.options.displayed == 1) {

          this.replaceBox();

        } else {

          this.Overlay.css({'height': '0px', 'width': '0px'});

        }

      }));



      jQuery(window).bind('scroll', jQuery.bind(this, function(){

        if(this.options.displayed == 1) {

          this.replaceBox();

        }          

      }));



      this.refresh();



    },

    

    hook: function(enlace) {

      enlace = jQuery(enlace);

      enlace.blur();

      this.show((enlace.attr("title") || enlace.attr("name") || ""), enlace.attr("href"), (enlace.attr('rel') || false));

    },

    

    close: function() {

      this.display(0);

      this.modal = 0;

    },

    

    refresh: function() {

      var self = this;

      this.anchors = [];



      jQuery("a, area").each(function() {

        if (jQuery(this).attr('rel') && new RegExp("^"+self.options.find).test(jQuery(this).attr('rel'))){

          jQuery(this).click(function(event) {

            event.preventDefault();

            event.stopImmediatePropagation();

            self.hook(this);

          });



          if (!(jQuery(this).attr('id')==self.options.name+"Left" || jQuery(this).attr('id')==self.options.name+"Right")) {

            self.anchors.push(this);

          }

        }

      });

    },

    

    display: function(option) {

      if(this.options.displayed == 0 && option != 0 || option == 1) {



        jQuery('embed, object, select').css({ 'visibility' : 'hidden' });



        if (this.options.displayed == 0) {

          this.Wrapper.css({

            'top'     : (-this.options.BoxStyles['height']-280)+'px',

            'height'  : (this.options.BoxStyles['height']-80)+'px',

            'width'   : this.options.BoxStyles['width']+'px'

          }).hide();

        }



        this.options.displayed = 1;

        this.Overlay.stop();

        this.Overlay.fadeIn(this.options.showDuration, jQuery.bind(this, function(){

          this.Wrapper.show();

          this.Overlay.css({

            'opacity'   : this.options.OverlayStyles['opacity']

          });

        }));



      }

       //Cerrar el Lightbox

      else

      {



        jQuery('embed, object, select').css({ 'visibility' : 'visible' });



        this.Wrapper.css({

          'top'     : (-this.options.BoxStyles['height']-280)+'px',

          'height'  : (this.options.BoxStyles['height']-80)+'px',

          'width'   : this.options.BoxStyles['width']+'px'

        }).hide();



        this.options.displayed = 0;



        this.Overlay.stop();

        this.Overlay.fadeOut(this.options.closeDuration, jQuery.bind(this, function(){

          if (this.Image)

            this.Image.remove();

          this.Overlay.css({'height': 0, 'width': 0 });

        }));

      }			

    },

    

    replaceBox: function(data) {



      data = jQuery.extend({

        'width'  : this.ajustarWidth,

        'height' : this.ajustarHeight,

        'resize' : 0

      }, data || {});



      if (this.MoveBox)

        this.MoveBox.stop();



      this.MoveBox = this.Wrapper.animate({

        left  : ( jQuery(window).scrollLeft()  + ((jQuery(window).width()  - data.width) / 2)),

        top   : ( jQuery(window).scrollTop()   + (jQuery(window).height() - (data.height + ((this.MostrarNav)?80:48))) / 2 )

      }, {

        duration  : this.options.moveDuration,

        easing    : this.options.moveEffect

      });



      if (data.resize) {

        

        if (this.ResizeBox2)

          this.ResizeBox2.stop();

        this.ResizeBox2 = this.Contenido.animate({

          height   : data.height

        }, {

          duration  : this.options.resizeDuration,

          easing    : this.options.resizeEffect

        });



        if (this.ResizeBox)

          this.ResizeBox.stop();



        this.ResizeBox = this.Contenedor.animate({

          width     : data.width

        }, {

          duration  : this.options.resizeDuration,

          easing    : this.options.resizeEffect,

          complete  : jQuery.bind(this, function(){

            this.Wrapper.css({'width' : data.width});

            this.ResizeBox.trigger('onComplete');

          })

        });

      }



      if (window.opera) { //Opera Bug :(

        this.Overlay.css({'height': 0, 'width': 0 });

      }



      this.Overlay.css({

        'height'    : jQuery(document).height(),

        'width'     : jQuery(window).width()

      });

    },

    

    getInfo: function (image, id) {

      image=jQuery(image);

      IEuta = jQuery('<a id="'+this.options.name+id+'" title="'+image.attr('title')+'" rel="'+image.attr('rel')+'"><img class="bt'+id+'" src="'+this.options.imagesdir+'/'+this.options.color+'/SexyBt'+id+'.png'+'" /></a>');

      IEuta.attr('href', image.attr('href')); //IE fix

      return IEuta;

    },

    

    show: function(caption, url, rel) {

      this.MostrarNav = false;

      this.showLoading();

      

      var baseURL = url.match(/(.+)?/)[1] || url;



      var imageURL = /\.(jpe?g|png|gif|bmp)/gi;



      if (this.ResizeBox) {

        this.ResizeBox.unbind('onComplete'); //fix for jQuery

      }



      if (caption) {

        this.MostrarNav = true;

      }

      // check for images

      if ( baseURL.match(imageURL) ) {

          /**

           * Cargar Imagen.

           *****************/

          this.imgPreloader = new Image();

          this.imgPreloader.onload = jQuery.bind(this, function(){

              this.imgPreloader.onload=function(){};



              //Resizing large images

              var x = jQuery(window).width() - 100;

              var y = jQuery(window).height() - 100;



              var imageWidth = this.imgPreloader.width;

              var imageHeight = this.imgPreloader.height;



              if (imageWidth > x)

              {

                imageHeight = imageHeight * (x / imageWidth);

                imageWidth = x;

                if (imageHeight > y)

                {

                  imageWidth = imageWidth * (y / imageHeight);

                  imageHeight = y;

                }

              }

              else if (imageHeight > y)

              {

                imageWidth = imageWidth * (y / imageHeight);

                imageHeight = y;

                if (imageWidth > x)

                {

                  imageHeight = imageHeight * (x / imageWidth);

                  imageWidth = x;

                }

              }

              //End Resizing

              

              //Ajustar el tamaño del lightbox

              if (this.MostrarNav || caption){

                this.ajustarHeight = (imageHeight-21);

              }else{

                this.ajustarHeight = (imageHeight-35);

              };



              this.ajustarWidth = (imageWidth+14);



              this.replaceBox({

                'width'  :this.ajustarWidth,

                'height' :this.ajustarHeight,

                'resize' : 1

              });

              

              //Mostrar la imagen, solo cuando la animacion de resizado se ha completado

              this.ResizeBox.bind('onComplete', jQuery.bind(this, function(){

                this.showImage(this.imgPreloader.src, {'width':imageWidth, 'height': imageHeight});

              }));

          });



          this.imgPreloader.onerror = jQuery.bind(this, function(){

            this.show('', this.options.imagesdir+'/'+this.options.color+'/404.png', this.options.find);

          });



          this.imgPreloader.src = url;

          

      } else { //code to show html pages

          var queryString = url.match(/\?(.+)/)[1];

          var params = this.parseQuery( queryString );

          params['width']   = parseInt(params['width']);

          params['height']  = parseInt(params['height']);

          params['modal']   = params['modal'];

          

          this.options.modal = params['modal'];

          

          this.ajustarHeight = parseInt(params['height'])+(window.opera?2:0);

          this.ajustarWidth  = parseInt(params['width'])+14;



          this.replaceBox({

            'width'  : this.ajustarWidth,

            'height' : this.ajustarHeight,

            'resize' : 1

          });

        

        

          if (url.indexOf('TB_inline') != -1) //INLINE ID

          {

            this.ResizeBox.bind('onComplete', jQuery.bind(this, function(){

              this.showContent(jQuery('#'+params['inlineId']).html(), {'width': params['width']+14, 'height': this.ajustarHeight}, params['background']);

            }));

          }

          else if(url.indexOf('TB_iframe') != -1) //IFRAME

          {

            var urlNoQuery = url.split('TB_');

            this.ResizeBox.bind('onComplete', jQuery.bind(this, function(){

              this.showIframe(urlNoQuery[0], {'width': params['width']+14, 'height': params['height']}, params['background']);

            }));

          }

          else //AJAX

          {

            this.ResizeBox.bind('onComplete', jQuery.bind(this, function(){

              jQuery.ajax({

                url: url,

                type: "GET",

                cache: false,

                error: jQuery.bind(this, function(){this.show('', this.options.imagesdir+'/'+this.options.color+'/404html.png', this.options.find)}),

                success: jQuery.bind(this, this.handlerFunc)

              });

            }));

          }



      }

      



      this.next       = false;

      this.prev       = false;

       //Si la imagen pertenece a un grupo

      if (rel.length > this.options.find.length)

      {

          this.MostrarNav = true;

          var foundSelf   = false;

          var exit        = false;

          var self        = this;



          jQuery.each(this.anchors, function(index){

            if (jQuery(this).attr('rel') == rel && !exit) {

              if (jQuery(this).attr('href') == url) {

                  foundSelf = true;

              } else {

                  if (foundSelf) {

                      self.next = self.getInfo(this, "Right");

                       //stop searching

                      exit = true;

                  } else {

                      self.prev = self.getInfo(this, "Left");

                  }

              }

            }

          });

      }



      this.addButtons();

      this.showNav(caption);

      this.display(1);

    },// end function



    handlerFunc: function(obj, html) {

      this.showContent(html, {'width':this.ajustarWidth, 'height': this.ajustarHeight});

    },



    showLoading: function() {

      this.Background.empty().removeAttr('style').css({'width':'auto', 'height':'auto'});

      this.Contenido.empty().css({

        'background-color'  : 'transparent',

        'padding'           : '0px',

        'width'             : 'auto'

      });



      this.Contenedor.css({

        'background' : 'url('+this.options.imagesdir+'/'+this.options.color+'/loading.gif) no-repeat 50% 50%'

      });



      this.Contenido.empty().css({

          'background-color': 'transparent',

          'padding'         : '0px',

          'width'           : 'auto'

      });



      this.replaceBox({

        'width'  : this.options.BoxStyles.width,

        'height' : this.options.BoxStyles.height,

        'resize' : 1

      });



    },



    addButtons: function(){

        if(this.prev) this.prev.bind('click', jQuery.bind(this, function(obj, event) {event.preventDefault();this.hook(this.prev);}));

        if(this.next) this.next.bind('click', jQuery.bind(this, function(obj, event) {event.preventDefault();this.hook(this.next);}));

    },

  

   /**

    * Mostrar navegacion.

    *****************/

    showNav: function(caption) {

        if (this.MostrarNav || caption) {

          this.bb.addClass("SLB-bbnav");

          this.Nav.empty();

          this.innerbb.empty();

          this.innerbb.append(this.Nav);

          this.Descripcion.html(caption);

          this.Nav.append(this.prev);

          this.Nav.append(this.next);

          this.Nav.append(this.Descripcion);

        }

        else

        {

          this.bb.removeClass("SLB-bbnav");

          this.innerbb.empty();

        }

    },

  

    showImage: function(image, size) {

      this.Background.empty().removeAttr('style').css({'width':'auto', 'height':'auto'}).append('<img id="'+this.options.name+'-Image"/>');

      this.Image = jQuery('#'+this.options.name+'-Image');

      this.Image.attr('src', image).css({

        'width'  : size['width'],

        'height' : size['height']

      });

    

      this.Contenedor.css({

        'background' : 'none'

      });



      this.Contenido.empty().css({

          'background-color': 'transparent',

          'padding'         : '0px',

          'width'           : 'auto'

      });

    },



    showContent: function(html, size, bg) {

      this.Background.empty().css({

        'width'            : size['width']-14,

        'height'           : size['height']+35,

        'background-color' : bg || '#ffffff'

      });

      

      this.Contenido.empty().css({

        'width'             : size['width']-14,

        'background-color'  : bg || '#ffffff'

      }).append('<div id="'+this.options.name+'-Image"/>');



      this.Image = jQuery('#'+this.options.name+'-Image');

      this.Image.css({

        'width'       : size['width']-14,

        'height'      : size['height'],

        'overflow'    : 'auto',

        'background'  : bg || '#ffffff'

      }).append(html);



      this.Contenedor.css({

        'background': 'none'

      });

      var wId = jQuery(this.Wrapper).attr('id');

      jQuery('#'+wId+' select, #'+wId+' object, #'+wId+' embed').css({ 'visibility' : 'visible' });

    },



    showIframe: function(src, size, bg) {

      this.Background.empty().css({

        'width'           : size['width']-14,

        'height'          : size['height']+35,

        'background-color': bg || '#ffffff'

      });



      var id = "if_"+new Date().getTime()+"-Image";



      this.Contenido.empty().css({

        'width'             : size['width']-14,

        'background-color'  : bg || '#ffffff',

        'padding'           : '0px'

      }).append('<iframe id="'+id+'" frameborder="0"></iframe>');

      

      this.Image = jQuery('#'+id);

      this.Image.css({

          'width'       : size['width']-14,

          'height'      : size['height'],

          'background'  : bg || '#ffffff'

      }).attr('src', src);



      this.Contenedor.css({

        'background' : 'none'

      });

    },

          

    parseQuery: function (query) {

      if( !query )

        return {};

      var params = {};



      var pairs = query.split(/[;&]/);

      for ( var i = 0; i < pairs.length; i++ ) {

        var pair = pairs[i].split('=');

        if ( !pair || pair.length != 2 )

          continue;

        params[unescape(pair[0])] = unescape(pair[1]).replace(/\+/g, ' ');

       }

       return params;

    },



    shake: function() {

      var d=this.options.shake.distance;

      var l=this.Wrapper.position();

      l=l.left;

      for(x=0;x<this.options.shake.loops;x++) {

       this.Wrapper.animate({left: l+d}, this.options.shake.duration, this.options.shake.transition)

       .animate({left: l-d}, this.options.shake.duration, this.options.shake.transition);

      }

       this.Wrapper.animate({"left": l+d}, this.options.shake.duration, this.options.shake.transition)

       .animate({"left": l}, this.options.shake.duration, this.options.shake.transition);

    }

    

  }

})(jQuery);