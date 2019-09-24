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