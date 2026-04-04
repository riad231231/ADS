(function( $ ) {
/**
 * Zen tabs
 * Used to create tabbed layouts
 *
 *  eg jQuery("#right-tabs li").zentabs();

<div id="right-tabs" class="sidetabs">
		<ul id="right-tabs">
				<li class="active"><a href="#right-a">Featured Items</a></li>
					<li><a href="#right-b">Popular</a></li>
					<li><a href="#right-c">Comments</a></li>
			<li><a href="#right-d">Something</a></li>
		</ul>


	<div id="sidetabs_content_container">
				<div id="right-a" class="zen_tabcontent active">
						<jdoc:include type="modules" name="right-a" style="jbChrome" />
			</div>

			<div id="right-b" class="zen_tabcontent">
						<jdoc:include type="modules" name="right-b" style="jbChrome" />
			</div>

			<div id="right-c" class="zen_tabcontent">
						<jdoc:include type="modules" name="right-c" style="jbChrome" />
			</div>

			<div id="right-d" class="zen_tabcontent">
						<jdoc:include type="modules" name="right-d" style="jbChrome" />
			</div>
	</div>
</div>
 *
 */

	$.fn.zentabs = function() {

	var parent = '#' + $(this).parent().parent().attr('id') + ' .zen_tabcontent';
	$(parent).hide();

		$('.zen_tabcontent.active').show();

			this.click(function() {

					//  First remove class "active" from currently active tab
					$(this).parent().children('li').removeClass('active');

					//  Now add class "active" to the selected/clicked tab
					$(this).addClass("active");

					//  Hide all tab content
					$(parent).hide();

					//  Here we get the href value of the selected tab
					var selected_tab = $(this).find("a").attr("href");

					//  Show the selected tab content
					$(selected_tab).fadeIn();

					//  At the end, we add return false so that the click on the link is not executed
					return false;
			});
	};



/**
 * Zentoggle
 * Used to create toggle functionality
 *
 * eg jQuery("#togglemenutrigger").zentoggle();
 *
 * <div id="togglemenutrigger">Toggle</div>
 * <div id= "togglemenucontent">Content here in the next div.</div>
 *
**/

	$.fn.zentoggle = function() {
	jQuery(this).click(function(){
		jQuery(this).next().slideToggle();
		return false;
	});
	};





/**
 * Zenselect
 * Used to select functionality
 *
 * eg   jQuery('#mobilemenu').zenselect({
 *    menu: "#nav ul.menu>li>a, #nav ul.menu>li>span.mainlevel,#nav ul.menu>li>span.separator"
 *  });
 *
 *  this selector targets where the select menu is output
 *  menu determines the items to be populated there.
 *
**/


		$.fn.zenselect = function (options) {

				// Create some defaults, extending them with any options that were provided
				var settings = $.extend({
			// Elements to populate the list wifth
			menu: '#nav ul.menu>li>a, #nav ul.menu>li>span.mainlevel,#nav ul.menu>li>span.separator'
				}, options);


		// Create the select menu
				$("<select />").appendTo(this);

		// Menu Title
				var mobileMenuTitle = $(this).attr("title");

		// Output variable
		output = '#' + $(this).attr('id');

				// Create default option "Go to..."
				$("<option />", {
						"selected": "selected",
						"value": "",
						"text": mobileMenuTitle
				}).appendTo(output + ' select');

				// Populate dropdown with menu items
				$(settings.menu).each(function () {
						var el = $(this);
						$("<option />", {
								"value": el.attr("href"),
								"text": el.text()

						}).appendTo(output + ' select');
						getSubMenu(el);
				});

				function getSubMenu(el) {
						var subMenu = $('~ ul>li>a', el);
						var tab = "- ";
						if (!(subMenu.length === 0)) {
								subMenu.each(function () {
										var sel = $(this);
										var nodeval = tab + sel.text();
										$("<option />", {
												"value": sel.attr("href"),
												"text": nodeval

										}).appendTo(output + ' select');
										getSubMenu(sel);
								});
						}
				}

				// To make dropdown actually work
				$(output + ' select').change(function () {
						window.location = $(this).find("option:selected").val();
				});

		};




/**
 * Zencookietoggle
 * Remember the state of a toggle for -slide modules
 *
 * eg   jQuery('#mobilemenu').zenselect({
 *    menu: "#nav ul.menu>li>a, #nav ul.menu>li>span.mainlevel,#nav ul.menu>li>span.separator"
 *  });
 *
 *  this selector targets where the select menu is output
 *  menu determines the items to be populated there.
 *
**/

		$.fn.zencookietoggle = function (options) {

		var el=$(this);

		$(this).each(function(index){


			var slideContent=el.parent().next('div.jbslideContent');
			var slideID=slideContent.attr('id');

			if($.cookie(slideID)=='close'){
				$(slideContent).hide();
				el.addClass('close')}

			else {
				el.addClass('open')
			}

			el.click(function(){
				checkCookie(slideContent,el,slideID)})
			});


			function checkCookie(slideContent,jQuerythis,slideID){

				if(slideContent.is(':hidden')){
					slideContent.slideDown("fast");
					el.removeClass('close');
					el.addClass('open');
					cookieValue='open';
					$.cookie(slideID,cookieValue)}

				else{
					slideContent.slideUp();
					cookieValue='close';
					el.removeClass('open');
					el.addClass('close');
					$.cookie(slideID,cookieValue)}
				};
	};




/**
 * Zenpanel
 * Creates a hidden panel
 *
 * eg   jQuery('#zenpanel,#zenoverlay').zenpanel({
 *    type: "opacity"
 *  });
 *
 *  this selector targets where the select menu is output
 *  menu determines the items to be populated there.
 *
**/


		$.fn.zenpanel = function (options) {

		// Create some defaults, extending them with any options that were provided
			var settings = $.extend({
				trigger: '#zenpanelopen,#zenpanelclose,#zenpanelclose2,#zenoverlay',
				overlay: '#zenoverlay',
				type: 'opacity'
					}, options);

		var el=$(this);
		jQuery(settings.overlay).hide();
		jQuery(el).hide();

		jQuery(settings.trigger).click(function(){
					jQuery(el).animate({'opacity': "toggle"}, 400);
				jQuery(settings.trigger).toggleClass("active").fadeToggle();


			return false;
		});

		// Centers the hidden panel
		jQuery.fn.center = function () {
				 this.css("position","absolute");
				 this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
				 this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
				 return this;
		}

		jQuery(el).center();

			jQuery(window).resize(function(){
				window_width = jQuery(window).width();
				window_height = jQuery(window).height();

				jQuery(el).each(function(){
						var modal_height = jQuery(this).outerHeight();
						var modal_width = jQuery(this).outerWidth();
						var top = (window_height-modal_height)/2;
						var left = (window_width-modal_width)/2;
					jQuery(this).css({'top' : top , 'left' : left});
				});
			});

			};


/**
* Zenwidthcheck
* Ccheck the width of an element and then adds a class if appropriate
*
* eg jQuery('#rightCol').zenwidthcheck({
*   width: "300",
*   class: "thin"
* });
*
*  this selector targets where the select menu is output
*  menu determines the items to be populated there.
*
**/

	$.fn.zenwidthcheck = function (options) {

		 // Create some defaults, extending them with any options that were provided
			var settings = $.extend({
				width: '300',
				class: 'thin'
			}, options);

		var el=$(this);

		// Remove the class
		jQuery(el).removeClass(settings.class);

		// If smaller than the width assign the class
		if(jQuery(el).width() < settings.width ) {
			jQuery(el).addClass(settings.class);
		}
	};
	
	

	/**
	* ZenAccordion
	* Check the width of an element and then adds a class if appropriate
	*
	* eg jQuery('.moduletable-panelmenu').zenaccordion({
	*   openfirst: "true",
	*   showactive: "true"
	* });
	*
	*  this selector targets where the select menu is output
	*  menu determines the items to be populated there.
	*
	**/
	
	$.fn.zenaccordion = function(options) {
		
		var settings = $.extend({
			openfirst: false,
			showactive: false,
			type: 'default',
			submenu: 'hide'
		}, options);

		var el=$(this);

		 // Store variables
 		var accordion_head = $('.moduletable-panelmenu li > span');
		var accordion_body = $('.moduletable-panelmenu ul ul');
		var accordion_first = $('.moduletable-panelmenu li:first-child ul');
		var accordion_active = $('.moduletable-panelmenu li.active ul');
		
		// Closes all
		accordion_body.hide().addClass("closed");
		accordion_head.addClass("closed");
		
		// Open the first tab on load
		if(settings.openfirst) {
			accordion_first.show().addClass("open");
			accordion_first.parent().children('span').addClass("open");
		}
		if(settings.showactive) {
			accordion_active.show().addClass("open");
			accordion_active.parent().children('span').addClass("open");
		}

        // Click function
        accordion_head.on('click', function(event) {
				
           if ($(this).hasClass('closed')){
				
				if(settings.type == "accordion") {
					accordion_head.removeClass("open").addClass("closed");
	                accordion_body.slideUp('normal');
				}
					             
				$(this).next().stop(true,true).slideToggle('normal');
				
				if(settings.submenu == "show") {
					$(".moduletable-panelmenu ul ul ul").show();
				}
				
	           	$(this).removeClass('closed').addClass("open");
            }

			else{
				if(settings.type == "accordion") {
				//	accordion_head.removeClass("open");
				}
				$(this).next('ul').slideUp('normal').addClass('closed').removeClass("open");
				$(this).addClass("closed").removeClass("open");
			}
        });
	}	

})(jQuery);


/*!
 * jQuery imagesLoaded plugin v2.1.0
 * http://github.com/desandro/imagesloaded
 *
 * MIT License. by Paul Irish et al.
 */

/*jshint curly: true, eqeqeq: true, noempty: true, strict: true, undef: true, browser: true */
/*global jQuery: false */

(function(c,n){var l="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";c.fn.imagesLoaded=function(f){function m(){var b=c(i),a=c(h);d&&(h.length?d.reject(e,b,a):d.resolve(e));c.isFunction(f)&&f.call(g,e,b,a)}function j(b,a){b.src===l||-1!==c.inArray(b,k)||(k.push(b),a?h.push(b):i.push(b),c.data(b,"imagesLoaded",{isBroken:a,src:b.src}),o&&d.notifyWith(c(b),[a,e,c(i),c(h)]),e.length===k.length&&(setTimeout(m),e.unbind(".imagesLoaded")))}var g=this,d=c.isFunction(c.Deferred)?c.Deferred():
0,o=c.isFunction(d.notify),e=g.find("img").add(g.filter("img")),k=[],i=[],h=[];c.isPlainObject(f)&&c.each(f,function(b,a){if("callback"===b)f=a;else if(d)d[b](a)});e.length?e.bind("load.imagesLoaded error.imagesLoaded",function(b){j(b.target,"error"===b.type)}).each(function(b,a){var d=a.src,e=c.data(a,"imagesLoaded");if(e&&e.src===d)j(a,e.isBroken);else if(a.complete&&a.naturalWidth!==n)j(a,0===a.naturalWidth||0===a.naturalHeight);else if(a.readyState||a.complete)a.src=l,a.src=d}):m();return d?d.promise(g):
g}})(jQuery);

/*
	Breakpoints.js
	version 1.0

	Creates handy events for your responsive design breakpoints

	Copyright 2011 XOXCO, Inc
	http://xoxco.com/

	Documentation for this plugin lives here:
	http://xoxco.com/projects/code/breakpoints

	Licensed under the MIT license:
	http://www.opensource.org/licenses/mit-license.php

*/
(function($){var lastSize=0;var interval=null;$.fn.resetBreakpoints=function(){$(window).unbind('resize');if(interval){clearInterval(interval)}lastSize=0};$.fn.setBreakpoints=function(settings){var options=jQuery.extend({distinct:true,breakpoints:new Array(320,480,768,1024)},settings);interval=setInterval(function(){var w=$(window).width();var done=false;for(var bp in options.breakpoints.sort(function(a,b){return(b-a)})){if(!done&&w>=options.breakpoints[bp]&&lastSize<options.breakpoints[bp]){if(options.distinct){for(var x in options.breakpoints.sort(function(a,b){return(b-a)})){if($('body').hasClass('breakpoint-'+options.breakpoints[x])){$('body').removeClass('breakpoint-'+options.breakpoints[x]);$(window).trigger('exitBreakpoint'+options.breakpoints[x])}}done=true}$('body').addClass('breakpoint-'+options.breakpoints[bp]);$(window).trigger('enterBreakpoint'+options.breakpoints[bp])}if(w<options.breakpoints[bp]&&lastSize>=options.breakpoints[bp]){$('body').removeClass('breakpoint-'+options.breakpoints[bp]);$(window).trigger('exitBreakpoint'+options.breakpoints[bp])}if(options.distinct&&w>=options.breakpoints[bp]&&w<options.breakpoints[bp-1]&&lastSize>w&&lastSize>0&&!$('body').hasClass('breakpoint-'+options.breakpoints[bp])){$('body').addClass('breakpoint-'+options.breakpoints[bp]);$(window).trigger('enterBreakpoint'+options.breakpoints[bp])}}if(lastSize!=w){lastSize=w}},250)}})(jQuery);

/*-------------------------------------------------------------------- 
 * JQuery Cookie
--------------------------------------------------------------------*/
jQuery.cookie=function(e,t,n){if(typeof t=="undefined"){var a=null;if(document.cookie&&document.cookie!=""){var f=document.cookie.split(";");for(var l=0;l<f.length;l++){var c=jQuery.trim(f[l]);if(c.substring(0,e.length+1)==e+"="){a=decodeURIComponent(c.substring(e.length+1));break}}}return a}n=n||{};if(t===null){t="";n.expires=-1}var r="";if(n.expires&&(typeof n.expires=="number"||n.expires.toUTCString)){var i;if(typeof n.expires=="number"){i=new Date;i.setTime(i.getTime()+n.expires*24*60*60*1e3)}else i=n.expires;r="; expires="+i.toUTCString()}var s=n.path?"; path="+n.path:"",o=n.domain?"; domain="+n.domain:"",u=n.secure?"; secure":"";document.cookie=[e,"=",encodeURIComponent(t),r,s,o,u].join("")};