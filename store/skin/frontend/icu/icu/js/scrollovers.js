/******************************************************************

					S C R O L L O V E R S
					---------------------
			 Written by Steffan Luczyn - July 2007
					 www.scrollovers.com	
	
		May be used for personal and business use, but may
		  never be sold or used in a product that is not 
				   free without my consent.

*******************************************************************/

		var scrollovers_TypeName = 'scrollover';
		var scrollovers_StartScrollLocation = 0;
		var scrollovers_EndScrollLocationTrim = 0;
		var scrollovers_ScrollSpeed = 3;
		var scrollovers_ScrollDownOnMouseOver = true;

		
		var scrollovers_ElementsInFocus = new Array();
		
		function scrollovers_Init(){
			/* DEFAULT TRIM SETTINGS */
			scrollovers_StartScrollLocation = 0;
			scrollovers_EndScrollLocationTrim = -1;
			
			if(navigator.userAgent.toLowerCase().indexOf("safari")!=-1){
				/* SAFARI TRIM SETTINGS */
				scrollovers_StartScrollLocation = 0;
				scrollovers_EndScrollLocationTrim = -1;
			}else if(navigator.userAgent.toLowerCase().indexOf("opera")!=-1){
				/* OPERA TRIM SETTINGS */				
				scrollovers_StartScrollLocation = 0;
				scrollovers_EndScrollLocationTrim = -1;
			}
			
			var aTmp = document.getElementsByTagName('a');
			var aLinks = new Array();
			for(i=0;i<aTmp.length;i++){
				if(scrollovers_TypeName == '' || aTmp[i].getAttribute('type') == scrollovers_TypeName){
					aLinks.push(aTmp[i]);
				}
			}
			aTmp = null;
		
			for(i=0;i<aLinks.length;i++){
				var sElemId = 'scrollover_'+i;
				var sExistingHTML = aLinks[i].innerHTML;
				var iWidth = parseInt(aLinks[i].scrollWidth)+1;
				var sHtml = '';
				aLinks[i].style.textDecoration = 'none';
				
				sHtml += 	'<span style="display:block; cursor:pointer; cursor:hand; height:1em; position:relative; overflow:visible; width:'+iWidth+'px; margin-top:-0.2em;">';
				sHtml +=		'<span style="display:block; position:absolute; overflow:hidden; height:1.3em; width:'+iWidth+'px; margin-bottom:-0.3em;">';
				sHtml +=			'<span style="display:block; margin-top:-0.1em; position:absolute; width:'+iWidth+'px;" class="scrollover_Nudge">';
				sHtml +=				'<em id="'+sElemId+'" style="display:block; line-height:1.4em; position:absolute; top:'+scrollovers_StartScrollLocation+'px; font-style:normal;">'+sExistingHTML+' '+sExistingHTML+'</em>';
				sHtml +=			'</span>';
				sHtml +=		'</span>';
				sHtml +=	'</span>';

				var sOriginalText = aLinks[i].innerText;
				aLinks[i].innerHTML = '';
				var oScrollover = document.createElement('fieldset');
				oScrollover.style.border = 'none';
				oScrollover.style.lineHeight = '1em';
				oScrollover.style.width = iWidth;
				oScrollover.style.textAlign = 'left';
				oScrollover.style.display = 'inline';
				oScrollover.style.margin = '0';
				oScrollover.style.padding = '0';
				oScrollover.innerHTML = sHtml;
				
				aLinks[i].appendChild(oScrollover);

				var oTag = document.getElementById(sElemId);
				
				var oElement = new Element(sElemId, oTag);
				scrollovers_ElementsInFocus.push(oElement);
				oTag.elementObject = oElement;
				oTag.onmouseover = scrollovers_MouseOver;
				oTag.onmouseout = scrollovers_MouseOut;
				
				if(scrollovers_ScrollDownOnMouseOver){
					oTag.style.top = (oElement.ScrollHeight*-1)+scrollovers_EndScrollLocationTrim+'px';
				}
			}
		}
		function scrollovers_MouseOver(oEvent){
			var oSender = null;
			if(window.event){
				oSender = window.event.srcElement;
			}else{
				oSender = oEvent.target;
			}

			scrollovers_ElementsInFocus.getById(oSender.elementObject.Id).MouseIsOver = true;
			if(scrollovers_ScrollDownOnMouseOver){
				setTimeout('scrollovers_RollDown(\''+oSender.elementObject.Id+'\')', 100);
			}else{
				setTimeout('scrollovers_RollUp(\''+oSender.elementObject.Id+'\')', 100);
			}
		}
		function scrollovers_MouseOut(oEvent){
			var oSender = null;
			if(window.event){
				oSender = window.event.srcElement;
			}else{
				oSender = oEvent.target;
			}
			oSender.elementObject.MouseIsOver = false;
			if(scrollovers_ScrollDownOnMouseOver){
				setTimeout('scrollovers_RollUp(\''+oSender.elementObject.Id+'\')', 100);
			}else{
				setTimeout('scrollovers_RollDown(\''+oSender.elementObject.Id+'\')', 100);
			}
		}
		function scrollovers_RollUp(sIdToRoll){
			var oElementToRoll = scrollovers_ElementsInFocus.getById(sIdToRoll);
			if(scrollovers_ScrollDownOnMouseOver){
				if(oElementToRoll.MouseIsOver){return;}
			}else{
				if(!oElementToRoll.MouseIsOver){return;}
			}
			if(oElementToRoll.ScrollLocation+scrollovers_ScrollSpeed < oElementToRoll.ScrollHeight){
				oElementToRoll.ScrollLocation += scrollovers_ScrollSpeed;
				oElementToRoll.TagRef.style.top = oElementToRoll.ScrollLocation*-1+'px';
				setTimeout('scrollovers_RollUp(\''+sIdToRoll+'\')', 10);
			}else{
				oElementToRoll.ScrollLocation =  oElementToRoll.ScrollHeight;
				oElementToRoll.TagRef.style.top = (oElementToRoll.ScrollHeight*-1)+scrollovers_EndScrollLocationTrim+'px';
			}
		}
		function scrollovers_RollDown(sIdToRoll){
			var oElementToRoll = scrollovers_ElementsInFocus.getById(sIdToRoll);
			if(scrollovers_ScrollDownOnMouseOver){
				if(!oElementToRoll.MouseIsOver){return;}
			}else{
				if(oElementToRoll.MouseIsOver){return;}
			}
			if(oElementToRoll.ScrollLocation-scrollovers_ScrollSpeed > scrollovers_StartScrollLocation){
				oElementToRoll.ScrollLocation -= scrollovers_ScrollSpeed;
				oElementToRoll.TagRef.style.top = oElementToRoll.ScrollLocation*-1+'px';
				setTimeout('scrollovers_RollDown(\''+sIdToRoll+'\')', 10);
			}else{
				oElementToRoll.ScrollLocation =  scrollovers_StartScrollLocation;
				oElementToRoll.TagRef.style.top = scrollovers_StartScrollLocation+'px';
			}
		}
		
		function Element(sId, oObject){
			this.Id = sId;
			this.TagRef = oObject;
			this.ScrollHeight = parseInt(this.TagRef.scrollHeight)/2-1;
			this.ScrollLocation = (scrollovers_ScrollDownOnMouseOver?this.ScrollHeight:0);
			this.MouseIsOver = false;
		}
		Array.prototype.getById = function(sId){
										for(i=0;i<this.length;i++){
											if(this[i].Id == sId){
												return this[i];
											}
										}
										return null;
								  }; 
		Array.prototype.getIndexById = function(sId){
										for(i=0;i<this.length;i++){
											if(this[i].Id == sId){
												return i;
											}
										}
										return null;
								  }; 
		
		window.onload = scrollovers_Init;
