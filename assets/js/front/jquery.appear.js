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