// jStepper 1.5.4

// A jQuery plugin by EmKay usable for making a numeric textfield value easy to increase or decrease.

(function(jQuery) {

	jQuery.fn.jStepper = function(param1, param2, param3) {

		if (this.length > 1) {
			this.each(function() { $(this).jStepper(param1) });
			return this;
		}

		if (typeof param1 === 'string') {

			if (param1 === 'option') {

				if (param3 === null) {
					param3 = jQuery.fn.jStepper.defaults[param2];
				}

				this.data('jstepper.o')[param2] = param3;
			}

			return this;

		}

		var o = jQuery.extend({}, jQuery.fn.jStepper.defaults, param1);

		if (jQuery.metadata) {
			o = jQuery.extend({}, o, this.metadata());
		}

		this.data('jstepper.o', o);

		if (o.disableAutocomplete) {
			this.attr('autocomplete', 'off');
		}

		if (jQuery.isFunction(this.mousewheel)) {
			this.mousewheel(function(e, intDelta) {
				if (intDelta > 0) { // Up

					var objDownEvent = jQuery.Event('keydown');
					objDownEvent.keyCode = 38;

					MakeStep(1, objDownEvent, this);
					return false;
				}
				else if (intDelta < 0) { // Down

					var objDownEvent = jQuery.Event('keydown');
					objDownEvent.keyCode = 40;

					MakeStep(0, objDownEvent, this);
					return false;
				}
			});
		}

		this.blur(function() {
			CheckValue(this, null);
		});

		this.keydown(function(e) {

			var key = e.keyCode;

			if (key === 38) { // Up
				MakeStep(1, e, this);
			} else if (key === 40) { // Down
				MakeStep(0, e, this);
			} else {

				if (o.overflowMode === 'ignore') {

					var objValueToCheck = $(this).val().indexOf("-") === 0 ? o.minValue : o.maxValue;

					if (objValueToCheck) {

						if ($(this).val().length >= objValueToCheck.toString().length) {

							if (
								((key >= 48 && key <= 57) || (key >= 96 && key <= 105)) &&
								(this.selectionStart === this.selectionEnd)
								) {
								return false;
							}

						}

					}

				}

			}

		});

		this.keyup(function(e) {

			CheckValue(this, e);

		});

		var CheckValue = function(objElm, key) {

			var $objElm = jQuery(objElm);

			var strValue = $objElm.val();
			var initialStrValue = strValue;

			if (o.disableNonNumeric) {
				strValue = strValue.replace(/[^\d\.,\-]/gi, '');
				strValue = strValue.replace(/-{2,}/g, '-');
				strValue = strValue.replace(/(.+)\-+/g, '$1');
			}

			var bOverflow = false;

			if (o.maxValue !== null) {
				if (parseFloat(strValue) > o.maxValue) {
					strValue = o.maxValue;
					bOverflow = true;
				}
			}

			//TODO: This option should only have effect if the event causing the CheckValue function to run is a key up or down.
			if (!o.disableMinValueCheckOnKey) {
				if (o.minValue !== null) {
					if (strValue != '' && parseFloat(strValue) < parseFloat(o.minValue)) {
						strValue = o.minValue;
						bOverflow = true;
					}
				}
			}

			if (IsUpOrDownKey(key) === true || key === null || bOverflow === true) {
				strValue = DoTheChecks(strValue);
			}

			if (initialStrValue != strValue) {
				$objElm.val(strValue);
			}

		};

		var MakeStep = function(bDirection, key, objElm) {

			var $objElm = jQuery(objElm);

			var stepToUse;

			if (key) {

				if (key.ctrlKey) {
					stepToUse = o.ctrlStep;
				} else if (key.shiftKey) {
					stepToUse = o.shiftStep;
				} else {
					stepToUse = o.normalStep;
				}

			} else {
				stepToUse = o.normalStep;
			}

			var numValue = $objElm.val();

			var intSelectionStart = numValue.length - objElm.selectionStart;
			var intSelectionEnd = numValue.length - objElm.selectionEnd;

			numValue = numValue.replace(/,/g, '.');
			numValue = numValue.replace(o.decimalSeparator, '.');

			numValue = numValue + '';
			if (numValue.indexOf('.') != -1) {
				numValue = numValue.match(new RegExp('-{0,1}[0-9]+[\\.][0-9]*'));
			}

			numValue = numValue + '';
			if (numValue.indexOf('-') != -1) {
				numValue = numValue.match(new RegExp('-{0,1}[0-9]+[\\.]*[0-9]*'));
			}

			numValue = numValue + '';
			numValue = numValue.match(new RegExp('-{0,1}[0-9]+[\\.]*[0-9]*'));

			if (numValue === '' || numValue == '-' || numValue === null) {
				numValue = o.defaultValue;
			}

			if (bDirection === 1) {
				numValue = jQuery.fn.jStepper.AddOrSubtractTwoFloats(numValue, stepToUse, true);
			} else {
				numValue = jQuery.fn.jStepper.AddOrSubtractTwoFloats(numValue, stepToUse, false);
			}

			var bLimitReached = false;

			if (o.maxValue !== null) {
				if (numValue >= o.maxValue) {
					numValue = o.maxValue;
					bLimitReached = true;
				}
			}

			if (o.minValue !== null) {
				if (numValue <= o.minValue) {
					numValue = o.minValue;
					bLimitReached = true;
				}
			}
			
			numValue = numValue.toString().replace(/\./, o.decimalSeparator);

			$objElm.val(numValue);

			objElm.selectionStart = numValue.length - intSelectionStart;
			objElm.selectionEnd = numValue.length - intSelectionEnd;

			CheckValue(objElm, key);

			if (o.onStep) {
				o.onStep($objElm, bDirection, bLimitReached);
			}

			return false;

		};

		var DoTheChecks = function(strValue) {

			var strResult = strValue.toString();
			strResult = CheckMinDecimals(strResult);
			strResult = CheckMaxDecimals(strResult);
			strResult = CheckAllowDecimals(strResult);
			strResult = CheckMinLength(strResult);

			return strResult;

		};

		var CheckMinDecimals = function(strValue) {

			var strResult = strValue;

			if (o.minDecimals > 0) {
				var intDecimalsMissing;
				if (strResult.indexOf('.') != -1) {
					var intDecimalsNow = strResult.length - (strResult.indexOf('.') + 1);
					if (intDecimalsNow < o.minDecimals) {
						intDecimalsMissing = o.minDecimals - intDecimalsNow;
					}
				} else {
					intDecimalsMissing = o.minDecimals;
					strResult = strResult + '.';
				}
				for (var intDecimalIndex = 1; intDecimalIndex <= intDecimalsMissing; intDecimalIndex++) {
					strResult = strResult + '0';
				}
			}

			return strResult;

		};

		var CheckMaxDecimals = function(strValue) {

			var strResult = strValue;

			if (o.maxDecimals > 0) {
				var intDecimalsNow = 0;
				if (strResult.indexOf('.') != -1) {
					intDecimalsNow = strResult.length - (strResult.indexOf('.') + 1);
					if (o.maxDecimals < intDecimalsNow) {
						strResult = strResult.substring(0, strResult.indexOf('.')) + '.' + strResult.substring(strResult.indexOf('.') + 1, strResult.indexOf('.') + 1 + o.maxDecimals);
					}
				}
			}

			return strResult;

		};

		var CheckAllowDecimals = function(strValue) {

			var strResult = strValue;

			if (!o.allowDecimals) {

				strResult = strResult.toString().replace(o.decimalSeparator, '.');
				strResult = strResult.replace(new RegExp('[\\.].+'), '');

			}

			return strResult;

		};

		var CheckMinLength = function(strValue) {

			var strResult = strValue;

			if (o.minLength !== null) {
				var intLengthNow = strResult.length;

				if (strResult.indexOf('.') != -1) {
					intLengthNow = strResult.indexOf('.');
				}
				var bIsNegative = false;
				if (strResult.indexOf('-') != -1) {
					bIsNegative = true;
					strResult = strResult.replace(/-/, '');
				}

				if (intLengthNow < o.minLength) {
					for (var i = 1; i <= (o.minLength - intLengthNow) ; i++) {
						strResult = '0' + strResult;
					}
				}

				if (bIsNegative) {
					strResult = '-' + strResult;
				}

			}

			return strResult;

		};

		var IsUpOrDownKey = function(key) {

			var bResult = false;

			if (key !== null) {

				if (key.keyCode === 38 || key.keyCode === 40) {
					bResult = true;
				}

			}

			return bResult;

		};

		var GetOption = function(strOptionName) {

			return this.data('jstepper.o')[strOptionName];

		};

		return this;

	};

	jQuery.fn.jStepper.AddOrSubtractTwoFloats = function(fltValue1, fltValue2, bAddSubtract) {

		var strNumber1 = fltValue1.toString();
		var strNumber2 = fltValue2.toString();

		var strResult = '';

		if (strNumber1.indexOf('.') > -1 || strNumber2.indexOf('.') > -1) {

			// If no decimals on one of them, then put them on!
			if (strNumber1.indexOf('.') === -1) {
				strNumber1 = strNumber1 + '.0';
			}

			if (strNumber2.indexOf('.') === -1) {
				strNumber2 = strNumber2 + '.0';
			}

			// Get only decimals
			var strDecimals1 = strNumber1.substr(strNumber1.indexOf('.') + 1);
			var strDecimals2 = strNumber2.substr(strNumber2.indexOf('.') + 1);

			// Getting the integers...
			var strInteger1 = strNumber1.substr(0, strNumber1.indexOf('.'));
			var strInteger2 = strNumber2.substr(0, strNumber2.indexOf('.'));

			//Make sure that the two decimals are same length (ie .02 vs .001) and append zeros as necessary.
			var bNotSameLength = true;

			while (bNotSameLength) {

				if (strDecimals1.length !== strDecimals2.length) {
					if (strDecimals1.length < strDecimals2.length) {
						strDecimals1 += '0';
					} else {
						strDecimals2 += '0';
					}
				} else {
					bNotSameLength = false;
				}

			}

			var intOriginalDecimalLength = strDecimals1.length;

			for (var intCharIndex = 0; intCharIndex <= strDecimals1.length - 1; intCharIndex++) {
				strInteger1 = strInteger1 + strDecimals1.substr(intCharIndex, 1);
				strInteger2 = strInteger2 + strDecimals2.substr(intCharIndex, 1);
			}

			var intInteger1 = Number(strInteger1);
			var intInteger2 = Number(strInteger2);
			var intResult;

			if (bAddSubtract) {
				intResult = intInteger1 + intInteger2;
			} else {
				intResult = intInteger1 - intInteger2;
			}

			var bIsNegative = false;

			if (intResult < 0) {
				bIsNegative = true;
				intResult = Math.abs(intResult);
			}

			strResult = intResult.toString();

			for (var intZerosAdded = 0; intZerosAdded < ((intOriginalDecimalLength - strResult.length) + 1) ; intZerosAdded++) {
				strResult = '0' + strResult;
			}

			if (strResult.length >= intOriginalDecimalLength) {
				strResult = strResult.substring(0, strResult.length - intOriginalDecimalLength) + '.' + strResult.substring(strResult.length - intOriginalDecimalLength);
			}

			if (bIsNegative === true) {
				strResult = '-' + strResult;
			}

		} else {
			if (bAddSubtract) {
				strResult = Number(fltValue1) + Number(fltValue2);
			} else {
				strResult = Number(fltValue1) - Number(fltValue2);
			}

		}

		return Number(strResult);

	};

	jQuery.fn.jStepper.defaults = {
		maxValue: null,
		minValue: null,
		normalStep: 1,
		shiftStep: 5,
		ctrlStep: 10,
		minLength: null,
		disableAutocomplete: true,
		defaultValue: 1,
		decimalSeparator: ',',
		allowDecimals: true,
		minDecimals: 0,
		maxDecimals: null,
		disableNonNumeric: true,
		onStep: null,
		overflowMode: 'default',
		disableMinValueCheckOnKey: false
	};

})(jQuery);
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