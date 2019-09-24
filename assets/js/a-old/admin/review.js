// jStepper 1.5.4
(function( $ ) {
!function(e){e.fn.jStepper=function(t,n,a){if(this.length>1)return this.each(function(){$(this).jStepper(t)}),this;if("string"==typeof t)return"option"===t&&(null===a&&(a=e.fn.jStepper.defaults[n]),this.data("jstepper.o")[n]=a),this;var l=e.extend({},e.fn.jStepper.defaults,t);e.metadata&&(l=e.extend({},l,this.metadata())),this.data("jstepper.o",l),l.disableAutocomplete&&this.attr("autocomplete","off"),e.isFunction(this.mousewheel)&&this.mousewheel(function(t,n){if(n>0){var a=e.Event("keydown");return a.keyCode=38,i(1,a,this),!1}if(0>n){var a=e.Event("keydown");return a.keyCode=40,i(0,a,this),!1}}),this.blur(function(){r(this,null)}),this.keydown(function(e){var t=e.keyCode;if(38===t)i(1,e,this);else if(40===t)i(0,e,this);else if("ignore"===l.overflowMode){var n=0===$(this).val().indexOf("-")?l.minValue:l.maxValue;if(n&&$(this).val().length>=n.toString().length&&(t>=48&&57>=t||t>=96&&105>=t)&&this.selectionStart===this.selectionEnd)return!1}}),this.keyup(function(e){r(this,e)});var r=function(t,n){var a=e(t),r=a.val(),i=r;l.disableNonNumeric&&(r=r.replace(/[^\d\.,\-]/gi,""),r=r.replace(/-{2,}/g,"-"),r=r.replace(/(.+)\-+/g,"$1"));var s=!1;null!==l.maxValue&&parseFloat(r)>l.maxValue&&(r=l.maxValue,s=!0),null!==l.minValue&&""!=r&&parseFloat(r)<parseFloat(l.minValue)&&(r=l.minValue,s=!0),(c(n)===!0||null===n||s===!0)&&(r=u(r)),i!=r&&a.val(r)},i=function(t,n,a){var i,u=e(a);i=n?n.ctrlKey?l.ctrlStep:n.shiftKey?l.shiftStep:l.normalStep:l.normalStep;var s=u.val(),o=s.length-a.selectionStart,f=s.length-a.selectionEnd;s=s.replace(/,/g,"."),s=s.replace(l.decimalSeparator,"."),s+="",-1!=s.indexOf(".")&&(s=s.match(new RegExp("-{0,1}[0-9]+[\\.][0-9]*"))),s+="",-1!=s.indexOf("-")&&(s=s.match(new RegExp("-{0,1}[0-9]+[\\.]*[0-9]*"))),s+="",s=s.match(new RegExp("-{0,1}[0-9]+[\\.]*[0-9]*")),(""===s||"-"==s||null===s)&&(s=l.defaultValue),s=1===t?e.fn.jStepper.AddOrSubtractTwoFloats(s,i,!0):e.fn.jStepper.AddOrSubtractTwoFloats(s,i,!1);var m=!1;return null!==l.maxValue&&s>=l.maxValue&&(s=l.maxValue,m=!0),null!==l.minValue&&s<=l.minValue&&(s=l.minValue,m=!0),s=s.toString().replace(/\./,l.decimalSeparator),u.val(s),a.selectionStart=s.length-o,a.selectionEnd=s.length-f,r(a,n),l.onStep&&l.onStep(u,t,m),!1},u=function(e){var t=e.toString();return t=s(t),t=o(t),t=f(t),t=m(t)},s=function(e){var t=e;if(l.minDecimals>0){var n;if(-1!=t.indexOf(".")){var a=t.length-(t.indexOf(".")+1);a<l.minDecimals&&(n=l.minDecimals-a)}else n=l.minDecimals,t+=".";for(var r=1;n>=r;r++)t+="0"}return t},o=function(e){var t=e;if(l.maxDecimals>0){var n=0;-1!=t.indexOf(".")&&(n=t.length-(t.indexOf(".")+1),l.maxDecimals<n&&(t=t.substring(0,t.indexOf("."))+"."+t.substring(t.indexOf(".")+1,t.indexOf(".")+1+l.maxDecimals)))}return t},f=function(e){var t=e;return l.allowDecimals||(t=t.toString().replace(l.decimalSeparator,"."),t=t.replace(new RegExp("[\\.].+"),"")),t},m=function(e){var t=e;if(null!==l.minLength){var n=t.length;-1!=t.indexOf(".")&&(n=t.indexOf("."));var a=!1;if(-1!=t.indexOf("-")&&(a=!0,t=t.replace(/-/,"")),n<l.minLength)for(var r=1;r<=l.minLength-n;r++)t="0"+t;a&&(t="-"+t)}return t},c=function(e){var t=!1;return null!==e&&(38===e.keyCode||40===e.keyCode)&&(t=!0),t};return this},e.fn.jStepper.AddOrSubtractTwoFloats=function(e,t,n){var a=e.toString(),l=t.toString(),r="";if(a.indexOf(".")>-1||l.indexOf(".")>-1){-1===a.indexOf(".")&&(a+=".0"),-1===l.indexOf(".")&&(l+=".0");for(var i=a.substr(a.indexOf(".")+1),u=l.substr(l.indexOf(".")+1),s=a.substr(0,a.indexOf(".")),o=l.substr(0,l.indexOf(".")),f=!0;f;)i.length!==u.length?i.length<u.length?i+="0":u+="0":f=!1;for(var m=i.length,c=0;c<=i.length-1;c++)s+=i.substr(c,1),o+=u.substr(c,1);var d,h=Number(s),p=Number(o);d=n?h+p:h-p;var g=!1;0>d&&(g=!0,d=Math.abs(d)),r=d.toString();for(var v=0;v<m-r.length+1;v++)r="0"+r;r.length>=m&&(r=r.substring(0,r.length-m)+"."+r.substring(r.length-m)),g===!0&&(r="-"+r)}else r=n?Number(e)+Number(t):Number(e)-Number(t);return Number(r)},e.fn.jStepper.defaults={maxValue:null,minValue:null,normalStep:1,shiftStep:5,ctrlStep:10,minLength:null,disableAutocomplete:!0,defaultValue:1,decimalSeparator:",",allowDecimals:!0,minDecimals:0,maxDecimals:null,disableNonNumeric:!0,onStep:null,overflowMode:"default"}}(jQuery);
}(jQuery));

(function( $ ) {

    "use strict";
	// Add Slider Number to metabox
	function sumtotal() {
		var percent_total = 0;
		var i = 0;
		$('.custom_repeatable li:not(.pro-hidden)').each(function() {
			percent_total += parseInt($(this).find('input.percent-value').val());
			i++;
		});
		percent_total = percent_total / i;
		$('.wrap-total .percent-value').empty().html( '<strong>' + round(percent_total) + '</strong>' +  '%');
		$('.wrap-total .point-value').empty().html( '<strong>' + round(percent_total / 10, 1 ) + '</strong>' +  '/10');
		$('.wrap-total .star-value').empty().html( '<strong>' + round(percent_total / 10 / 2, 1 ) + '<strong>' + '/5');
	}

	function addslider() {

		$('.custom_repeatable li:not(.pro-hidden) .addslider').each(function() {

			var thevalue = $(this).closest('li').find('.percent-value').val();

			if ( thevalue == '' || thevalue == 0 ) thevalue = 0;

			$(this).slider({

				isRTL: true,

				value: thevalue,

				range: 'min',

				min: 10,

				max: 100,

				step: 1,

				slide: function( event, ui ) {

					$(this).closest('li').find('input.percent-value').val( ui.value );

					$(this).closest('li').find('.star-value').val( round(ui.value / 10 / 2, 1 ));

					$(this).closest('li').find('.point-value').val( ui.value / 10 );
					sumtotal();

				}
			});

			var percent = $(this).closest('li').find('input.percent-value').val();

			if ( percent != '' ) {

				$(this).closest('li').find('.star-value').val( round(percent / 10 / 2, 1 ));

				$(this).closest('li').find('.point-value').val( percent / 10 );

			}

		});
	}

	function round (value, precision, mode) {
		var m, f, isHalf, sgn; // helper variables

		precision |= 0; // making sure precision is integer

		m = Math.pow(10, precision);

		value *= m;

		sgn = (value > 0) | -(value < 0); // sign of the number

		isHalf = value % 1 === 0.5 * sgn;

		f = Math.floor(value);

		if (isHalf) {

			switch (mode) {

				case 'PHP_ROUND_HALF_DOWN':

					value = f + (sgn < 0); // rounds .5 toward zero

					break;

					case 'PHP_ROUND_HALF_EVEN':

						value = f + (f % 2 * sgn); // rouds .5 towards the next even integer

						break;

					case 'PHP_ROUND_HALF_ODD':

						value = f + !(f % 2); // rounds .5 towards the next odd integer

						break;

					default:

						value = f + (sgn > 0); // rounds .5 away from zero
			}
		}

	  return (isHalf ? value : Math.round(value)) / m;
	}
		
	function changeinputvalue() {
		$('input.percent-value').change(function() {

			var percent = $(this).val();

			$(this).closest('li').find('.star-value').val( round(percent / 10 / 2, 1 ));

			$(this).closest('li').find('.point-value').val( percent / 10 );

			$(this).closest('li').find('.slider').slider({value: percent});
			sumtotal();

		});
	
	}
	function sortelement(){
		$('.custom_repeatable').sortable({
			opacity: 0.6,
			revert: true,
			cursor: 'move',
		});
	}
	
	// Check if user using keyboard to add rate and update other fields
	function ajaxloadtemplate( tpl_id ) {
		$.ajax({
				type: 'POST',
				dataType: 'json',
				url: get_review_template.ajaxurl,
				data: {
					// The data parameter is an object which contains the data you want to pass
					'action': 'get_review_template_using_ajax', // used in wordpress add action majesty_load_more_posts
					'security': $('.pro-author-review-box #par_post_review_nonce_name').val(),
					'tpl_id': tpl_id,
					
				},
				beforeSend: function() {
					$('.pro-author-review-box').addClass('loading');
				},
				success: function( data ) {
					$('.pro-author-review-box').removeClass('loading');
					$('.pro-author-review-box .ajax-fields-wrapper').html(data.reviewtpl);
					addslider();
					changeinputvalue()
					if( ! $('.ajax-fields-wrapper').hasClass('tpl-selected') ) {
						$('.ajax-fields-wrapper').addClass('tpl-selected');
					}
					sortelement();
				},
				error: function(jqXHR, textStatus, errorThrown) {
				}
			});
	}
	addslider();
	changeinputvalue();
	sortelement();
	$(".percent-value").jStepper({minValue:10, maxValue:100});
	$( '#add-row' ).on('click', function() {
		var row = $( 'li.empty-criteria' ).clone(true);
		var count = $(".custom_repeatable li").length -1 ;
		$(row).find('.addslider').attr( "id", "slider"+ count );		
		row.removeClass( 'pro-hidden empty-row screen-reader-text empty-criteria' );
		row.insertBefore( '.custom_repeatable li:last' );
		addslider();
		return false;
		
	});
	var display_as = $('#who_can_review').val();

	if ( display_as == 'users' ) {

		$('.custom_repeatable li .label-type, .custom_repeatable li .criteria-value, .custom_repeatable li .slider, .wrap-total-criteria .total-value').addClass('pro-hidden');

	}

	$('#who_can_review').change(function () {

		var display_as = $('#who_can_review').val();

		if ( display_as == 'users' ) {

			$('.custom_repeatable li .label-type, .custom_repeatable li .criteria-value, .custom_repeatable li .slider').addClass('pro-hidden');
		} else {

			$('.custom_repeatable li .label-type, .custom_repeatable li .criteria-value, .custom_repeatable li .slider').removeClass('pro-hidden');

		}
	});

	$( '.remove-row' ).on('click', function() {
		$(this).parents('li').remove();
		return false;
	});
	
	$('.review-field-active').hide();
	$('.select-review-template').select2();
	
	// assign default tpl_id
	var old_tpl_id = '';
	$('.select-review-template').on("select2:open", function (e) {
		old_tpl_id = $(this).val();		
	});
	$('.select-review-template').on("change", function (e) { 
		//log("change");
		e.preventDefault();		
		var tpl_id = $(this).val();
		if( tpl_id == -1 ) return;
		if( $('.ajax-fields-wrapper').hasClass('tpl-selected') ) {
			if( $('.ajax-fields-wrapper').hasClass('clickedbefore') ) {
				$('.ajax-fields-wrapper').removeClass('clickedbefore');
			} else {
				var r = confirm("This is strong if you click yes you lose your template and load new one you selected");
				if (r == true) {
					ajaxloadtemplate(tpl_id);
				} else {
					// used class clickedbefore to pervent popup appear all times
					$('.ajax-fields-wrapper').addClass('clickedbefore');
					$('.select-review-template option[value='+ old_tpl_id +']').attr('selected','selected');
					$('.select-review-template').val(old_tpl_id).trigger("change");
				}
			}
		} else {
			ajaxloadtemplate(tpl_id);
		}
	});
	// Check if post have review display HTML for metabox

	var has_review = $('#has-review').is(':checked');

	if ( has_review ) {
		$('.review-field-active').show();
	}
	$("#has-review").change(function() {
		var ischecked= $(this).is(':checked');
		if(!ischecked)
			alert( $('p.when-review-unchecked').html() );
	}); 
	/*

		*	check if post have review

		*  true display HTML elements for metabox

	*/
	$('#has-review').change(function () {

		var has_review = $('#has-review').is(':checked');

		if ( has_review ) {

			$('.review-field-active').show();

		} else {

			$('.review-field-active').hide();

		}

	});
	$('.usersonly .total-value').hide();
	$('.pro-author-review-box.users .rating-review-item .pro-hidden').hide();
}(jQuery));