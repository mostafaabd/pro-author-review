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