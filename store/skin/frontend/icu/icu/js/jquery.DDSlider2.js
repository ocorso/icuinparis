/*
 * DDSlider v1.7 - http://codecanyon.net/item/ddslider-10-transitions-inline-content-support/104797
 * 
 * Copyright Â© 2010 Guilherme Salum
 * All rights reserved.
 * 
 * You may not modify and/or redistribute this file
 * save cases where Extended License has been purchased
 *
*/

(function($){ 
    $.fn.extend({ 
        DDSlider: function() { 
			
			var DDCont = this;
			
			isPlaying = false;
			
			var DDefaults = {
				
				trans: 'random',
				delay: 50,
				waitTime: 5000,
				duration: 500,
				stopSlide: 1,
				bars: 15,
				columns: 10,
				rows: 3,
				ease: 'swing'
				
				
			};			
			
			attr = arguments[0] || {};
			
			if(attr.trans === undefined) { attr.trans = DDefaults.trans; }
			if(attr.delay === undefined) { attr.delay = DDefaults.delay; }
			if(attr.duration === undefined) { attr.duration = DDefaults.duration; }
			if(attr.waitTime === undefined) { attr.waitTime = DDefaults.waitTime; }
			if(attr.stopSlide === undefined) { attr.stopSlide = DDefaults.stopSlide; }
			if(attr.bars === undefined) { attr.bars = DDefaults.bars; }
			if(attr.columns === undefined) { attr.columns = DDefaults.columns; }
			if(attr.rows === undefined) { attr.rows = DDefaults.rows; }
			if(attr.ease === undefined) { attr.ease = DDefaults.ease; }
			
			attr.width = this.width();
			attr.height = this.height();			
			
			//Sets up the slider structure
			this.children('li:first').addClass('current');
			
			var iNum = 1;
				
				//Add Selector container
				if(attr.selector === true) {
					
					this.append('<ul class="slider_selector"></ul>');
					
				}
			
				//Adds Selectors and set slider classes
				this.children('li').each(function() {
												 
					$(this).addClass('slider_'+iNum);
					
					//sets the selectors
					
					if(attr.selector === undefined) {
						
					} else {
						
						if(iNum == 1) {
							$(attr.selector).append('<li class="current sel_'+iNum+'"></li>');
						} else {
							$(attr.selector).append('<li class="sel_'+iNum+'"></li>');
						}
						
					}
					
					iNum++;
												 
				});	
				
			var isClicked = 0;
			
			//If user has only one <li> stops the slide
			if(this.children('li').length == 1) {
				
				stopAll = 1;
				
			} else {
				
				stopAll = 0;
				
			}
			
			if(stopAll === 0) {
			
				//Sets arrows events
				if(attr.prevSlide === undefined) {  } else {
					
					$(attr.prevSlide).click(function(){
						
						if(isPlaying === false) {
							
							DDCont.prevSlide(attr); isClicked = 1;
							
						}
						
					});
					
				}
				if(attr.nextSlide === undefined) {  } else {
					
					$(attr.nextSlide).click(function(){
						
						if(isPlaying === false) {
							
							DDCont.nextSlide(attr); isClicked = 1;
							
						}
						
					});
					
				}
			
						
				$(attr.selector).children('li').click(function() { var itemId = $(this).attr('class').split(' ');
					
					if(itemId[0] == 'current' || itemId[1] == 'current') {
						
						//do nothing
						
					} else {
						
						itemId = itemId[0].split('_');
						
						if(isPlaying === false) {
							
							isClicked = 1;
							DDCont.callSlide(itemId[1], attr);
							
						}
						
						
					}						   
				});
				
				//AutoSlider
				var isHovered = 0;
				//check if user is hovering the slide
				$(this).hover(function() { // Whenever an item is hovered
					isHovered = 1; //Setting isHovered 1, we stop the autsliding from going on
				}, function() {
					isHovered = 0;//Setting isHovered 1, we make the autsliding go on
				});
				
				//Events
				setInterval(function() {
					
					if(attr.stopSlide == 1) {
						
						if(isHovered === 0 && isClicked === 0) { DDCont.nextSlide(attr); }
						
					} else {
						
						 if(isClicked === 0) { DDCont.nextSlide(attr); }
						 
					}
								 
				}, attr.waitTime);
			
			}
		
        },
		
		nextSlide: function() {
	
			//finds out the current slider and the next one
			var currentItem = this.children('li.current');
			var nextItem = currentItem.next('li');
			
			
			//finds selectors
			var currentSel = $(attr.selector).children('li.current');
			var nextSel = $(attr.selector).children('li.current').next();
			
			//if the is no next one, choose the first
			if(nextItem.length > 0) {
				//Do nothing. The  $next element exists
			} else {
				nextItem = this.children('li:first');
				nextSel = $(attr.selector).children('li:first');
			}
			
			this.nextTransition(attr, nextItem, currentItem, nextSel, currentSel);
			
		},
		
		prevSlide: function() {
			
			//finds out the current slider and the next one
			var currentItem = this.children('li.current');
			var prevItem = currentItem.prev('li');
			
			
			//finds selectors
			var currentSel = $(attr.selector).children('li.current');
			var prevSel = $(attr.selector).children('li.current').prev();
			
			//if the is no next one, choose the first
			if(prevItem.length > 0) {
				//Do nothing. The  $next element exists
			} else {
				prevItem = this.children('li:last');
				prevSel = $(attr.selector).children('li:last');
			}
			
			this.nextTransition(attr, prevItem, currentItem, prevSel, currentSel);
			
		},
		
		callSlide: function(slideID) {
			
			var currentItem = this.children('li.current');
			var nextItem = this.children('li.slider_'+slideID);
			
			var currentSel = $(attr.selector).children('li.current');
			var nextSel = $(attr.selector).children('li.sel_'+slideID);
			
			this.nextTransition(attr, nextItem, currentItem, nextSel, currentSel);
			
		},
		
		nextTransition: function(attr, transNext, transCur, transSelNext, transSelCur) {
						
			var nextTransitionTemp = transNext.attr('class').split(' ');
			var nextTransition = nextTransitionTemp[0];
			if(nextTransition == '') { nextTransition = attr.trans; }
			
			
			
			if(nextTransition == 'random' || nextTransition == 'fading' || nextTransition == 'barTop' || nextTransition == 'barBottom' || nextTransition == 'square' || nextTransition == 'squareMoving' || nextTransition == 'barFade' || nextTransition == 'barFadeRandom' || nextTransition == 'squareRandom' || nextTransition == 'squareOut' || nextTransition == 'squareOutMoving' || nextTransition == 'rowInterlaced') {  } else { nextTransition = 'random'; }
			
			if(nextTransition == 'random') {
				
				var transitionArray = ['barTop','fading','barBottom','square', 'squareRandom', 'squareMoving', 'barFade', 'barFadeRandom', 'squareOut', 'squareOutMoving', 'rowInterlaced'];
				var arr_trans = [0,1,2,3,4,5,6,7,8,9,10];
				
				var nextTransShuffle = $.shuffle(arr_trans);
				
				nextTransition = transitionArray[nextTransShuffle[0]];
				
			}
			
			if(nextTransition == 'fading') { this.DDFading(attr, transNext, transCur, transSelNext, transSelCur);  }
			else if(nextTransition == 'barTop') { this.DDBarTop(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'barBottom') { this.DDBarBottom(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'square') { this.DDSquare(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'squareRandom') { this.DDSquareRandom(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'squareMoving') { this.DDSquareMoving(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'barFade') { this.DDBarFade(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'barFadeRandom') { this.DDBarFadeRandom(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'squareOut') { this.DDSquareOut(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'squareOutMoving') { this.DDSquareOutMoving(attr, transNext, transCur, transSelNext, transSelCur); }
			else if(nextTransition == 'rowInterlaced') { this.DDRowInterlaced(attr, transNext, transCur, transSelNext, transSelCur); }
			else { this.DDFading(attr, transNext, transCur, transSelNext, transSelCur); }
			
		},
		
		DDFading: function(attr, fadeNext, fadeCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			fadeNext.css({ opacity: 1 });

			
			//adds the next class
			fadeNext.addClass('next');
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			//animates the current so it disappears
			fadeCur.stop().animate({ opacity: 0 }, attr.duration, function() {
																				  
				fadeNext.addClass('current').removeClass('next');
				fadeCur.removeClass('current').css({ opacity: 1 });
				
				//enables all selectors
				ddx.enableSelectors();
																				  
			});
			
		},
		
		DDBarTop: function(attr, barNext, barCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			barNext.css({ opacity: 1 });
						
			//set vars
			var bar_width = Math.round(attr.width / attr.bars);
			
			var bar_height = attr.height;
			var bar_top = (bar_height - (bar_height * 2));
			
			//Let's create the bar divs
			var iNum = 1;
			while(iNum <= attr.bars) {
				
				var position = (iNum * bar_width) - bar_width;
				this.append('<div class="slider_bar slider_bar_'+iNum+'" style="position: absolute; overflow: hidden;'+barNext.attr('style')+'"></div>');
				this.children('.slider_bar_'+iNum).css({ left: position, height: bar_height, width: bar_width, top: bar_top, 'z-index': 3, 'background-position': '-'+position+'px top' });
				iNum++;
				
			}
			
			//lets put the content in the bar and animate it
			
			//set vars
			var iNum2 = 1;
			
			while(iNum2 <= attr.bars) {
				
				var position2 = (iNum2 * bar_width) - bar_width;
				var delay = (iNum2 * attr.delay);
				this.children('.slider_bar_'+iNum2).append('<div style="position: absolute; left: -'+position2+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+barNext.html()+'</div>');
				this.children('.slider_bar_'+iNum2).animate({ opacity: 1 }, delay).animate({ top: 0 }, {duration: attr.duration, easing: attr.ease});
				iNum2++;
				
				
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			//let's do stuff after the animation is over
			var totalDelay = (attr.bars * attr.delay);
			barNext.animate({ opacity: 0 }, totalDelay).animate({opacity: 0}, attr.duration, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				barCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_bar').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		DDBarBottom: function(attr, barPrev, barCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			barPrev.css({ opacity: 1 });
			
			//set vars
			var bar_width = Math.round(attr.width / attr.bars);
			var bar_height = attr.height;
			var bar_top = bar_height;
			
			//Let's create the bar divs
			var iNum = 1;
			while(iNum <= attr.bars) {
				
				var position = (iNum * bar_width) - bar_width;	
				this.append('<div class="slider_bar slider_bar_'+iNum+'" style="position: absolute; overflow: hidden;'+barPrev.attr('style')+'"></div>');
				this.children('.slider_bar_'+iNum).css({ left: position, height: bar_height, width: bar_width, top: bar_top, 'z-index': 3, 'background-position': '-'+position+'px top' });
				iNum++;
				
			}
			
			//lets put the images in the bar and animate it
			
			//set vars
			var iNum2 = (1);
			var iNum3 = attr.bars;
			bar_width = Math.round(attr.width / attr.bars);
			bar_height = attr.height;
			
			while(iNum2 <= attr.bars) {
				
				var position2 = (iNum2 * bar_width) - bar_width;
				var delay = (iNum2 * attr.delay);
				this.children('.slider_bar_'+iNum2).append('<div style="position: absolute; left: -'+position2+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+barPrev.html()+'</div>');
				this.children('.slider_bar_'+iNum3).animate({ opacity: 1 }, delay).animate({ top: 0 }, {duration: 500, easing: attr.ease});
				iNum2++; iNum3--;
				
				
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			//let's do stuff after the animation is over
			var totalDelay = (attr.bars * attr.delay);
			barPrev.animate({ opacity: 0 }, totalDelay).animate({opacity: 0}, attr.duration, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				barCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_bar').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});	
				
			});
			
		},
		
		DDBarFade: function(attr, barNext, barCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			barNext.css({ opacity: 1 });
			
			//set vars
			var bar_width = Math.round(attr.width / attr.bars);
			var bar_height = attr.height;
			
			//Let's create the bar divs
			var i = 1;
			while(i <= attr.bars) {
				
				var position = (i * bar_width) - bar_width;
				this.append('<div class="slider_bar slider_bar_'+i+'" style="position: absolute; overflow: hidden;'+barNext.attr('style')+'"></div>');
				this.children('.slider_bar_'+i).css({ left: position, opacity: 0, height: bar_height, width: bar_width, 'z-index': 3, 'background-position': '-'+position+'px top' });
				i++;
				
			}
			
			//lets put the content in the bar and animate it
			
			//set vars
			var ii = 1;
			
			while(ii <= attr.bars) {
				
				var position2 = (ii * bar_width) - bar_width;
				delay = (ii * attr.delay);
				this.children('.slider_bar_'+ii).append('<div style="position: absolute; left: -'+position2+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+barNext.html()+'</div>');
				this.children('.slider_bar_'+ii).animate({opacity: 0}, delay).animate({ opacity: 1 }, {duration: attr.duration, easing: attr.ease});
				ii++;
				
				
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			//let's do stuff after the animation is over
			var totalDelay = (attr.bars * attr.delay);
			barNext.animate({opacity: 0}, totalDelay).animate({opacity: 0}, attr.duration, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				barCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_bar').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		DDBarFadeRandom: function(attr, barNext, barCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			barNext.css({ opacity: 1 });
			
			//set vars
			var bar_width = Math.round(attr.width / attr.bars);
			var bar_height = attr.height;
			
			//create array of number of bars so we can shuffle it
			var bars_array = [];
			
			//Let's create the bar divs
			var i = 1;
			while(i <= attr.bars) {
				
				var position = (i * bar_width) - bar_width;
				this.append('<div class="slider_bar slider_bar_'+i+'" style="position: absolute; overflow: hidden;'+barNext.attr('style')+'"></div>');
				this.children('.slider_bar_'+i).css({ left: position, opacity: 0, height: bar_height, width: bar_width, 'z-index': 3, 'background-position': '-'+position+'px top' });
				
				//inserts content in our array of bars
				bars_array[(i- 1)] = [i];
				
				i++;
				
			}
			
			
			//shuffles the array of bars
			var bars_array_shuffle = $.shuffle(bars_array);
			
			//lets put the content in the bar and animate it
			//set vars
			var ii = 1;
			
			while(ii <= attr.bars) {
				
				var position2 = (ii * bar_width) - bar_width;
				var delay = (ii * attr.delay);
				this.children('.slider_bar_'+ii).append('<div style="position: absolute; left: -'+position2+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+barNext.html()+'</div>');
				
				this.children('.slider_bar_'+bars_array_shuffle[(ii) - 1]).animate({ opacity: 0 }, delay).animate({ opacity: 1 }, {duration: attr.duration, easing: attr.ease});
				ii++;
				

				
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			//let's do stuff after the animation is over
			var totalDelay = (attr.bars * attr.delay);
			barNext.animate({opacity: 0}, totalDelay).animate({opacity: 0}, attr.duration, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				barCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_bar').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		DDSquare: function(attr, squareNext, squareCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			squareNext.css({ opacity: 1 });
			
			//set vars
			var row_width = Math.round(attr.width / attr.columns);
			var row_height = Math.round(attr.height / attr.rows);
			
			//Let's create the block divs
			var i_row_numbers = 1;
			var i_column_numbers = (1);
			
			//create each row
			while(i_row_numbers <= attr.rows) {
				
				var initial = i_row_numbers;
				var class_row = 'block_row_'+i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					var class_block = 'slider_block_'+(initial++);
					var class_column = 'block_column_'+i_column_numbers;
					
					var block_top = ((i_row_numbers * row_height) - row_height);
					var block_left = ((i_column_numbers * row_width) - row_width);
					
					var position_left = (row_width * i_column_numbers) - row_width;
					var position_top = (row_height * i_row_numbers) - row_height;
					
					if(squareNext.attr('style') === undefined) {
						
						this.append('<div class="slider_block '+block_ID_name+' '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;"></div>');
						
					} else {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;'+squareNext.attr('style')+'"></div>');
					}
					
					this.children('.'+block_ID_name).css({ width: row_width, height: row_height, 'z-index': 4, top:block_top+'px', left: block_left+'px', opacity: 0, 'background-position': '-'+position_left+'px -'+position_top+'px' }).append('<div style="position: absolute; left: -'+position_left+'px; top: -'+position_top+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+squareNext.html()+'</div>');
					
					i_column_numbers++; initial++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
			
			//Let's reset the block divs
			i_row_numbers = 1;
			i_column_numbers = 1;
			
			
				
			
			while(i_row_numbers <= attr.rows) {
				
				var initial2 = i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var animated_class = '.slider_block_'+(initial2++);
					
					delay = (attr.delay * initial2);
					
					$(animated_class).animate({ width: row_width }, delay).animate({ opacity: 1 }, {duration: attr.duration, easing: attr.ease});
					
					i_column_numbers++; initial2++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			var delay_total = (delay + attr.duration);
			
			squareNext.animate({ opacity: 0 }, delay_total).animate({ opacity: 0 }, 1, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				squareCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_block').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		DDSquareRandom: function(attr, squareNext, squareCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			squareNext.css({ opacity: 1 });
			
			//set vars
			var row_width = Math.round(attr.width / attr.columns);
			var row_height = Math.round(attr.height / attr.rows);
			
			//Let's create the block divs
			var i_row_numbers = 1;
			var i_column_numbers = 1;
			
			var square_arr = [];
			var square_total = 0;
			
			//create each row
			while(i_row_numbers <= attr.rows) {
				
				var initial = i_row_numbers;
				var class_row = 'block_row_'+i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					square_arr[square_total] = (square_total + 1);
					square_total++;
					
					var block_ID_name = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					var class_block = 'slider_block_'+(initial++);
					var class_column = 'block_column_'+i_column_numbers;
					
					var block_top = ((i_row_numbers * row_height) - row_height);
					var block_left = ((i_column_numbers * row_width) - row_width);
					
					var position_left = (row_width * i_column_numbers) - row_width;
					var position_top = (row_height * i_row_numbers) - row_height;
					
					if(squareNext.attr('style') === undefined) {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;"></div>');
						
					} else {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;'+squareNext.attr('style')+'"></div>');
					}
					
					this.children('.'+block_ID_name).css({ width: row_width, height: row_height, 'z-index': 4, top: block_top+'px', left: block_left+'px', opacity: 0, 'background-position': '-'+position_left+'px -'+position_top+'px' }).append('<div style="position: absolute; left: -'+position_left+'px; top: -'+position_top+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+squareNext.html()+'</div>');
					
					i_column_numbers++; initial++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
			
			var squareArrShuffle = $.shuffle(square_arr);
			
			//Let's create the block divs
			i_row_numbers = 1;
			i_column_numbers = 1;
			var squareAnimate = 0;		
			
			while(i_row_numbers <= attr.rows) {
				
				var initial2 = i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var animated_class = '.block_ID_'+(squareArrShuffle[squareAnimate]);
					
					delay = (attr.delay * initial2);
					
					$(animated_class).animate({ width: row_width }, delay).animate({ opacity: 1 }, {duration: attr.duration, easing: attr.ease});
					
					i_column_numbers++; initial2++; squareAnimate++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
				
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			var delay_total = delay + attr.duration;
			
			squareNext.animate({ opacity: 0 }, delay_total).animate({ opacity: 0 }, 1, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				squareCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_block').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		DDSquareMoving: function(attr, squareNext, squareCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			squareNext.css({ opacity: 1 });
			
			//set vars
			var row_width = Math.round(attr.width / attr.columns);
			var row_height = Math.round(attr.height / attr.rows);
			
			//Let's create the block divs
			var i_row_numbers = 1;
			var i_column_numbers = 1;
			
			//create each row
			while(i_row_numbers <= attr.rows) {
				
				var initial = i_row_numbers;
				var class_row = 'block_row_'+i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					var class_block = 'slider_block_'+(initial++);
					var class_column = 'block_column_'+i_column_numbers;
					
					var block_top = (i_row_numbers * row_height)+80;
					var block_left = (i_column_numbers * row_width)+80;
					
					var position_left = (row_width * i_column_numbers) - row_width;
					var position_top = (row_height * i_row_numbers) - row_height;
					
					if(squareNext.attr('style') === undefined) {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;"></div>');
						
					} else {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;'+squareNext.attr('style')+'"></div>');
						
					}
					
					this.children('.'+block_ID_name).css({ width: row_width, height: row_height, 'z-index': 4, opacity: 0, top: block_top+'px', left: block_left+'px', 'background-position': '-'+position_left+'px -'+position_top+'px' }).append('<div style="position: absolute; left: -'+position_left+'px; top: -'+position_top+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+squareNext.html()+'</div>');
					
					i_column_numbers++; initial++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
			
			//Let's create the block divs
			i_row_numbers = 1;
			i_column_numbers = 1;
				
			
			while(i_row_numbers <= attr.rows) {
				
				var initial2 = i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name2 = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					
					var block_top2 = ((i_row_numbers * row_height) - row_height) + 'px';
					var block_left2 = ((i_column_numbers * row_width) - row_width) + 'px';
					
					delay = (attr.delay * initial2);
					
					this.children('.'+block_ID_name2).animate({ width: row_width }, delay).animate({ opacity: 1, top: block_top2, left: block_left2 }, {duration: attr.duration, easing: attr.ease});
					
					i_column_numbers++; initial2++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			//once the animation is finished
			var delay_total = delay + attr.duration;
			squareNext.animate({ opacity: 0 }, delay_total).animate({ opacity: 0 }, 1, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				squareCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_block').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		DDSquareOut: function(attr, squareNext, squareCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			//set vars
			var row_width = Math.round(attr.width / attr.columns);
			var row_height = Math.round(attr.height / attr.rows);
			
			//Let's create the block divs
			var i_row_numbers = 1;
			var i_column_numbers = 1;
			
			//create each row
			while(i_row_numbers <= attr.rows) {
				
				var initial = i_row_numbers;
				var class_row = 'block_row_'+i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					var class_block = 'slider_block_'+(initial++);
					var class_column = 'block_column_'+i_column_numbers;
					
					var block_top = ((i_row_numbers * row_height) - row_height);
					var block_left = ((i_column_numbers * row_width) - row_width);
					
					var position_left = (row_width * i_column_numbers) - row_width;
					var position_top = (row_height * i_row_numbers) - row_height;
					
					if(squareNext.attr('style') === undefined) {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;"></div>');
						
					} else {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;'+squareCur.attr('style')+'"></div>');
						
					}
					
					this.children('.'+block_ID_name).css({ width: row_width, height: row_height, 'z-index': 4, top: block_top+'px', left: block_left+'px', opacity: 1, 'background-position': '-'+position_left+'px -'+position_top+'px' }).append('<div style="position: absolute; left: -'+position_left+'px; top: -'+position_top+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+squareCur.html()+'</div>');
					
					i_column_numbers++; initial++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
			
			squareNext.addClass('current').css({ opacity: 0 }).animate({ opacity: 1 }, 200);
			squareCur.css({ opacity: 0 });
			
			//Let's create the block divs
			i_row_numbers = 1;
			i_column_numbers = 1;
			
			
				
			
			while(i_row_numbers <= attr.rows) {
				
				var initial2 = i_row_numbers;

				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name2 = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					
					delay = (attr.delay * initial2)*3;
					
					var position_left2 = (((row_width * i_column_numbers) - row_width)+80)+'px';
					var position_top2 = (((row_height * i_row_numbers) - row_height)+80)+'px';
					
					this.children('.'+block_ID_name2).animate({ width: row_width }, delay).animate({ left: position_left2, top: position_top2, opacity: 0 }, {duration: attr.duration, easing: attr.ease});
					
					i_column_numbers++; initial2++;
					
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			var delay_total = (delay + attr.duration);
			
			squareNext.animate({ opacity: 1 }, delay_total).animate({ opacity: 0 }, 1, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				squareCur.removeClass('current').css({ opacity: 1 });
				ddx.children('.slider_block').remove();
				
				//enables all selectors
				ddx.enableSelectors();
				
			});
			
		},
		
		DDSquareOutMoving: function(attr, squareNext, squareCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			//set vars
			var row_width = Math.round(attr.width / attr.columns);
			var row_height = Math.round(attr.height / attr.rows);
			
			//Let's create the block divs
			var i_row_numbers = 1;
			var i_column_numbers = 1;
			
			//create each row
			while(i_row_numbers <= attr.rows) {
				
				var initial = i_row_numbers;
				var class_row = 'block_row_'+i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					var class_block = 'slider_block_'+(initial++);
					var class_column = 'block_column_'+i_column_numbers;
					
					var block_top = ((i_row_numbers * row_height) - row_height);
					var block_left = ((i_column_numbers * row_width) - row_width);
					
					var position_left = (row_width * i_column_numbers) - row_width;
					var position_top = (row_height * i_row_numbers) - row_height;
					
					if(squareNext.attr('style') === undefined) {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;"></div>');
						
					} else {
						
						this.append('<div class="'+block_ID_name+' slider_block '+class_block+' '+class_row+' '+class_column+'" style="position: absolute; overflow: hidden;'+squareCur.attr('style')+'"></div>');
						
					}
					
					this.children('.'+block_ID_name).css({ width: row_width, height: row_height, 'z-index': 4, top: block_top+'px', left: block_left+'px', opacity: 1, 'background-position': '-'+position_left+'px -'+position_top+'px' }).append('<div style="position: absolute; left: -'+position_left+'px; top: -'+position_top+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+squareCur.html()+'</div>');
					
					i_column_numbers++; initial++;
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
			
			squareNext.addClass('current').css({ opacity: 0 }).animate({ opacity: 1 }, 200);
			squareCur.css({ opacity: 0 });
			
			//Let's create the block divs
			i_row_numbers = 1;
			i_column_numbers = 1;
			
			
				
			
			while(i_row_numbers <= attr.rows) {
				
				var initial2 = i_row_numbers;
				
				//create each column of each row
				while(i_column_numbers <= attr.columns) {
					
					var block_ID_name2 = 'block_ID_'+((attr.columns * i_row_numbers)-(attr.columns - i_column_numbers));
					
					delay = (attr.delay * initial2)*2;
					
					var position_left2 = (((row_width * i_column_numbers) - row_width)-80)+'px';
					var position_top2 = (((row_height * i_row_numbers) - row_height)-80)+'px';
					
					this.children('.'+block_ID_name2).animate({ width: row_width }, delay).animate({ left: position_left2, top: position_top2, opacity: 0 }, {duration: attr.duration, easing: attr.ease});
					
					i_column_numbers++; initial2++;
					
					
				}
				
				i_row_numbers++;
				i_column_numbers = 1;
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			var delay_total = (delay + attr.duration);
			
			squareNext.animate({ opacity: 1 }, delay_total).animate({ opacity: 0 }, 1, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				squareCur.removeClass('current').css({ opacity: 1 });
				ddx.children('.slider_block').remove();
				
				//enables all selectors
				ddx.enableSelectors();
				
			});
			
		},
		
		DDRowInterlaced: function(attr, squareNext, squareCur, nextSel, curSel) {
			
			var ddx = this;
			
			//lets disable all buttons
			this.disableSelectors();
			
			squareNext.css({ opacity: 1 });
			
			//set vars
			var row_width = attr.width;
			var row_height = Math.round(attr.height / attr.rows);
			
			//Let's create the block divs
			var i_row_numbers = 1;
			
			var initial = 1;
			
			//create each row
			while(i_row_numbers <= attr.rows) {
				
				var class_row = 'block_row_'+i_row_numbers;
				
				var block_ID_name = 'block_ID_'+initial;
				
				var position_top = (row_height * i_row_numbers) - row_height;
				var position_left = attr.width+'px';
				var block_top = ((i_row_numbers * row_height) - row_height);
				
				if(squareNext.attr('style') === undefined) {
				
					this.append('<div class="slider_row '+block_ID_name+' '+class_row+'" style="position: absolute; overflow: hidden;"></div>');
				
				} else {
				
					this.append('<div class="'+block_ID_name+' slider_row '+class_row+'" style="position: absolute; overflow: hidden;'+squareNext.attr('style')+'"></div>');
					
				}
				
				this.children('.'+block_ID_name).css({ width: row_width, height: row_height, 'z-index': 4, top:block_top+'px', opacity: 0, 'background-position': '0 -'+position_top+'px', left: position_left }).append('<div style="position: absolute; top: -'+position_top+'px; width: '+attr.width+'px; height: '+attr.height+'px;">'+squareNext.html()+'</div>');
				
				initial++; i_row_numbers++;
			}
			
			var interLeft = '-'+attr.width+'px';
			this.children('.slider_row:even').css({ left: interLeft });
			
			//Let's reset the block divs
			i_row_numbers = 1;
			var initial2 = 1;
			
			
			while(i_row_numbers <= attr.rows) {
					
				var animated_class = '.block_ID_'+initial2;
					
				delay = (attr.delay * initial2);
					
				$(animated_class).animate({ opacity: 0 }, delay).animate({ left: 0, opacity: 1 }, {duration: attr.duration, easing: attr.ease});
					
				i_row_numbers++; initial2++;
			}
				
			curSel.removeClass('current');
			nextSel.addClass('current');
			
			var delay_total = (delay + attr.duration);
			
			squareNext.animate({ opacity: 0 }, delay_total).animate({ opacity: 0 }, 1, function() {
				
				$(this).addClass('current').css({ opacity: 1 });
				
				squareCur.animate({ opacity: 0 }, 200, function() {
					
					$(this).removeClass('current');
					
					//removes the transition containers
					ddx.children('.slider_row').remove();
					
					//Enables the selectors
					ddx.enableSelectors();
					
				});
				
			});
			
		},
		
		disableSelectors: function() {
			
			isPlaying = true;
			
		},
		
		enableSelectors: function() {
			
			isPlaying = false;
			
		}
		
    });
	
	$.fn.shuffle = function() {
		return this.each(function(){
			var items = $(this).children();
			return (items.length) ? $(this).html($.shuffle(items)) : this;
		});
	};
	
	$.shuffle = function(arr) {
		for(var j, x, i = arr.length; i; j = parseInt(Math.random() * i, 10), x = arr[--i], arr[i] = arr[j], arr[j] = x) {  }
		return arr;
	};
	
})(jQuery);