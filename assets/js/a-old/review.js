// Appear jQuery
(function($){$.fn.appear=function(fn,options){var settings=$.extend({data:undefined,one:true,accX:0,accY:0},options);return this.each(function(){var t=$(this);t.appeared=false;if(!fn){t.trigger('appear',settings.data);return;}
var w=$(window);var check=function(){if(!t.is(':visible')){t.appeared=false;return;}
var a=w.scrollLeft();var b=w.scrollTop();var o=t.offset();var x=o.left;var y=o.top;var ax=settings.accX;var ay=settings.accY;var th=t.height();var wh=w.height();var tw=t.width();var ww=w.width();if(y+th+ay>=b&&y<=b+wh+ay&&x+tw+ax>=a&&x<=a+ww+ax){if(!t.appeared)t.trigger('appear',settings.data);}else{t.appeared=false;}};var modifiedFn=function(){t.appeared=true;if(settings.one){w.unbind('scroll',check);var i=$.inArray(check,$.fn.appear.checks);if(i>=0)$.fn.appear.checks.splice(i,1);}
fn.apply(this,arguments);};if(settings.one)t.one('appear',settings.data,modifiedFn);else t.bind('appear',settings.data,modifiedFn);w.scroll(check);$.fn.appear.checks.push(check);(check)();});};$.extend($.fn.appear,{checks:[],timeout:null,checkAll:function(){var length=$.fn.appear.checks.length;if(length>0)while(length--)($.fn.appear.checks[length])();},run:function(){if($.fn.appear.timeout)clearTimeout($.fn.appear.timeout);$.fn.appear.timeout=setTimeout($.fn.appear.checkAll,20);}});$.each(['append','prepend','after','before','attr','removeAttr','addClass','removeClass','toggleClass','remove','css','show','hide'],function(i,n){var old=$.fn[n];if(old){$.fn[n]=function(){var r=old.apply(this,arguments);$.fn.appear.run();return r;}}});})(jQuery);
/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);

( function($) {
	"use strict";

	if ( typeof ajax_user_rate !== 'undefined' ) {
		var get_ajax_user_rate = ajax_user_rate; //ajax_user_rate ;
	} else {
		var get_ajax_user_rate = '';
	}
	
	addslider();
	
	$('.review-item .progress-bar').appear(function(){
			var datavl = $(this).attr('aria-valuenow');
			$(this).animate({ "width" : datavl + "%"}, '300');
	});
	$('.review-item .star-over').appear(function(){
			var datavl = $(this).attr('aria-valuenow');
			$(this).animate({ "width" : datavl + "%"}, '500');
	});
	
	$('.author-reviews-btn').click(function( e ) {
		$(this).addClass('active');
		$('.author-reviews').fadeToggle('slow');
		$('.users-rate, .rate-it').css('display', 'none');
		$('.users-rate-btn, .rate-it-btn').removeClass('active');
	});
	
	$('.users-rate-btn').click(function( e ) {
		$(this).addClass('active');
		$('.users-rate').fadeToggle('slow');
		$('.author-reviews, .rate-it').css('display', 'none');
		$('.author-reviews-btn, .rate-it-btn').removeClass('active');
	});
	
	$('.rate-it-btn').click(function( e ) {
		$(this).addClass('active');
		$('.rate-it-btn').addClass('active');
		$('.rate-it').fadeToggle('slow');
		$('.author-reviews, .users-rate').css('display', 'none');
		$('.author-reviews-btn, .users-rate-btn').removeClass('active');
	});
	$('a.add-user-rate').click(function( e ) {
		e.preventDefault();
		var individual_rate = $(this).closest('.rate-criteria');
		var indiv_rate = new Array();
		var i = 0;
		individual_rate.find('.criteria-rate').each(function() {
			var slug  = $(this).find('.slider').data('slug');
			var value = $(this).find('input.value').val();
			var obj = { 'slug': slug, 'value': value };
			indiv_rate.push(obj);
			i++;
		});
		indiv_rate = JSON.stringify(indiv_rate);
		var post_id = individual_rate.data('post_id');
		individual_rate.prepend('<div class="pra-ajax-loader"><span class="spinner icon-circle-o-notch"></span></div>');
		$.ajax({
			type: "post",
			url: get_ajax_user_rate.url,
			dataType: 'JSON',
			data: {
                'action': 'user_rate_post',
                'nonce': get_ajax_user_rate.nonce,
                'post_id': post_id,
				'args': indiv_rate,
            },
			success: function( response ) {
				// If vote successful
				$(".pra-ajax-loader").remove();
				if( response.success == true ) {
					individual_rate.empty();
					individual_rate.append(response.message);
					if ( response.num_of_users == 1 ) {
						var users_rate = $('.pro-author-review .users-rate');
						users_rate.find('.not-rated-before').remove();
						users_rate.find('.review-item').removeClass('hidden');
					}

					$('.total-users-rating h3 .value').text(response.avg_rating);
					if ( response.review_type == 'percent' ) {
						$('.total-users-rating h3').append('<span>%</span>');
					}
					$('.total-users-rating .votes').text(response.text_users_num);

					var data = response.users_criteria;
					for( var key in data) {
							
						if( typeof data[key] === "object" ) {
							$( ".pro-author-review .users-rate .review-item" ).each(function( index ) {
								if ( key == index ){
									if ( response.review_type == 'star' ) {
										$(this).find('.star-over').css('width', data[key]['width'] + '%');
									} else {
										$(this).find('.progress-bar').attr('aria-valuenow',data[key]['width']).css('width', data[key]['width'] + '%');
										$(this).find('.score').text(data[key]['score']);
									}
								}
							});
						}
					}
				} else if ( response.success == false ) {
					individual_rate.empty();
					individual_rate.append(response.message);
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$(".pra-ajax-loader").remove();
				$('.rate-it').find('.criteria-rate').remove();
				$('.rate-it').find('p').remove();
				$('.rate-it').append('<p>Busted!</p>');
            }
		});
	});
	function addslider() {

		$('.rate-criteria .criteria-rate .slider').each(function() {

			var value = $(this).data('value');
			var min = $(this).data('min');
			var max = $(this).data('max');
			var step = $(this).data('step');
			var direction = 'ltr';
			if ( $('body').hasClass("rtl") ) {
				direction = 'rtl';
			}

			$(this).slider({

				isRTL: direction,

				value: value,

				range: 'min',

				min: min,

				max: max,

				step: step,

				slide: function( event, ui ) {
					$(this).closest('.criteria-rate').find('span.slider-value').text( ui.value );
					$(this).closest('.criteria-rate').find('input.value').val( ui.value );
				}
			});
		});
	}
})(jQuery);