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
(function ($) {

  // Detect touch support
  $.support.touch = 'ontouchend' in document;

  // Ignore browsers without touch support
  if (!$.support.touch) {
    return;
  }

  var mouseProto = $.ui.mouse.prototype,
      _mouseInit = mouseProto._mouseInit,
      _mouseDestroy = mouseProto._mouseDestroy,
      touchHandled;

  /**
   * Simulate a mouse event based on a corresponding touch event
   * @param {Object} event A touch event
   * @param {String} simulatedType The corresponding mouse event
   */
  function simulateMouseEvent (event, simulatedType) {

    // Ignore multi-touch events
    if (event.originalEvent.touches.length > 1) {
      return;
    }

    event.preventDefault();

    var touch = event.originalEvent.changedTouches[0],
        simulatedEvent = document.createEvent('MouseEvents');
    
    // Initialize the simulated mouse event using the touch event's coordinates
    simulatedEvent.initMouseEvent(
      simulatedType,    // type
      true,             // bubbles                    
      true,             // cancelable                 
      window,           // view                       
      1,                // detail                     
      touch.screenX,    // screenX                    
      touch.screenY,    // screenY                    
      touch.clientX,    // clientX                    
      touch.clientY,    // clientY                    
      false,            // ctrlKey                    
      false,            // altKey                     
      false,            // shiftKey                   
      false,            // metaKey                    
      0,                // button                     
      null              // relatedTarget              
    );

    // Dispatch the simulated event to the target element
    event.target.dispatchEvent(simulatedEvent);
  }

  /**
   * Handle the jQuery UI widget's touchstart events
   * @param {Object} event The widget element's touchstart event
   */
  mouseProto._touchStart = function (event) {

    var self = this;

    // Ignore the event if another widget is already being handled
    if (touchHandled || !self._mouseCapture(event.originalEvent.changedTouches[0])) {
      return;
    }

    // Set the flag to prevent other widgets from inheriting the touch event
    touchHandled = true;

    // Track movement to determine if interaction was a click
    self._touchMoved = false;

    // Simulate the mouseover event
    simulateMouseEvent(event, 'mouseover');

    // Simulate the mousemove event
    simulateMouseEvent(event, 'mousemove');

    // Simulate the mousedown event
    simulateMouseEvent(event, 'mousedown');
  };

  /**
   * Handle the jQuery UI widget's touchmove events
   * @param {Object} event The document's touchmove event
   */
  mouseProto._touchMove = function (event) {

    // Ignore event if not handled
    if (!touchHandled) {
      return;
    }

    // Interaction was not a click
    this._touchMoved = true;

    // Simulate the mousemove event
    simulateMouseEvent(event, 'mousemove');
  };

  /**
   * Handle the jQuery UI widget's touchend events
   * @param {Object} event The document's touchend event
   */
  mouseProto._touchEnd = function (event) {

    // Ignore event if not handled
    if (!touchHandled) {
      return;
    }

    // Simulate the mouseup event
    simulateMouseEvent(event, 'mouseup');

    // Simulate the mouseout event
    simulateMouseEvent(event, 'mouseout');

    // If the touch interaction did not move, it should trigger a click
    if (!this._touchMoved) {

      // Simulate the click event
      simulateMouseEvent(event, 'click');
    }

    // Unset the flag to allow other widgets to inherit the touch event
    touchHandled = false;
  };

  /**
   * A duck punch of the $.ui.mouse _mouseInit method to support touch events.
   * This method extends the widget with bound touch event handlers that
   * translate touch events to mouse events and pass them to the widget's
   * original mouse event handling methods.
   */
  mouseProto._mouseInit = function () {
    
    var self = this;

    // Delegate the touch handlers to the widget's element
    self.element.bind({
      touchstart: $.proxy(self, '_touchStart'),
      touchmove: $.proxy(self, '_touchMove'),
      touchend: $.proxy(self, '_touchEnd')
    });

    // Call the original $.ui.mouse init method
    _mouseInit.call(self);
  };

  /**
   * Remove the touch event handlers
   */
  mouseProto._mouseDestroy = function () {
    
    var self = this;

    // Delegate the touch handlers to the widget's element
    self.element.unbind({
      touchstart: $.proxy(self, '_touchStart'),
      touchmove: $.proxy(self, '_touchMove'),
      touchend: $.proxy(self, '_touchEnd')
    });

    // Call the original $.ui.mouse destroy method
    _mouseDestroy.call(self);
  };

})(jQuery);
// Appear jQuery
(function ($) {
  $.fn.appear = function (fn, options) {
      var settings = $.extend({
          data: undefined,
          one: true,
          accX: 0,
          accY: 0
      }, options);
      return this.each(function () {
          var element = $(this);
          element.appeared = false;
          if (!fn) {
            element.trigger('appear', settings.data);
            return;
          }
          var el_window = $(window);
          var check = function () {
              if (!element.is(':visible')) {
                element.appeared = false;
                  return;
              }
              /*var a = el_window.scrollLeft();
              var b = el_window.scrollTop();
              var o = element.offset();
              var x = o.left;
              var y = o.top;
              var ax = settings.accX;
              var ay = settings.accY;
              var th = element.height();
              var wh = el_window.height();
              var tw = element.width();
              var ww = el_window.width();*/
              var el_scroll_left = el_window.scrollLeft();
              var el_scroll_top = el_window.scrollTop();
              var el_offset = element.offset();
              var el_offset_left = el_offset.left;
              var el_offset_top = el_offset.top;
              var settings_x = settings.accX;
              var settings_y = settings.accY;
              var el_height = element.height();
              var el_window_height = el_window.height();
              var el_width = element.width();
              var el_window_width = el_window.width();
              if (el_offset_top + el_height + settings_y >= el_scroll_top && el_offset_top <= el_scroll_top + el_window_height + settings_y && el_offset_left + el_width + settings_x >= el_scroll_left && el_offset_left <= el_scroll_left + el_window_width + settings_x) {
                  if (!element.appeared) element.trigger('appear', settings.data);
              } else {
                element.appeared = false;
              }
          };
          var modifiedFn = function () {
              element.appeared = true;
              if (settings.one) {
                  el_window.unbind('scroll', check);
                  var i = $.inArray(check, $.fn.appear.checks);
                  if (i >= 0) $.fn.appear.checks.splice(i, 1);
              }
              fn.apply(this, arguments);
          };
          if (settings.one) element.one('appear', settings.data, modifiedFn);
          else element.bind('appear', settings.data, modifiedFn);
          el_window.scroll(check);
          $.fn.appear.checks.push(check);
          (check)();
      });
  };
  $.extend($.fn.appear, {
      checks: [],
      timeout: null,
      checkAll: function () {
          var length = $.fn.appear.checks.length;
          if (length > 0)
              while (length--)($.fn.appear.checks[length])();
      },
      run: function () {
          if ($.fn.appear.timeout) clearTimeout($.fn.appear.timeout);
          $.fn.appear.timeout = setTimeout($.fn.appear.checkAll, 20);
      }
  });
  $.each(['append', 'prepend', 'after', 'before', 'attr', 'removeAttr', 'addClass', 'removeClass', 'toggleClass', 'remove', 'css', 'show', 'hide'], function (i, n) {
      var old = $.fn[n];
      if (old) {
          $.fn[n] = function () {
              var r = old.apply(this, arguments);
              $.fn.appear.run();
              return r;
          }
      }
  });
})(jQuery);
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
		var datavl = $(this)[0].style.width;
		console.log( datavl);
		$(this).css('width', '0');
		$(this).animate({ "width" : datavl }, '1000');
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