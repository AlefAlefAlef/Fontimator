(function( $ ) {
	'use strict';
	$(function(){
		$("form.variations_form").on("woocommerce_update_variation_values", function (event) {
			$(this).find("optgroup:empty").remove();
		});

		$('.licenseapp-field .info-tooltip').aToolTip({ inSpeed: 350, yOffset: -55, fixed: false });

		$('form.variations_form').on('found_variation reset_data',
			function (event, variation) {
				if(variation && variation.attributes) {
					var licenseAttr = variation.attributes['attribute_pa_' + FontimatorPublic.licenseAttributeName].split('-')[0];
					var $container = $('.licenseapp-field');
					if (['web', 'app'].indexOf(licenseAttr) > -1) {
						$container.show()
							.find('input').prop('required', true)
							.attr('placeholder', FontimatorPublic.placeholders[licenseAttr]);
						$container.find('span').hide()
							.filter('[data-license="' + licenseAttr + '"]').show();
					} else {
						$container.hide()
							.find('input').prop('required', false);
					}
				} else {
					$container.hide()
						.find('input').prop('required', false);
				}
			}
		);

		// Timed Messages
		function get_part_of_day() {
			var current_hour = new Date().getHours();
			if ( current_hour >= 5 && current_hour <= 11 ) {
				return 'morning';
			} else if ( current_hour >= 12 && current_hour <= 17 ) {
				return 'afternoon';
			} else if ( current_hour >= 18 || current_hour <= 4 ) {
				return 'evening';
			} else {
				return 'morning';
			}
		}
		
		$('.fontimator-timed-message-greeting').each(function(){
			var greeting = FontimatorTimedMessages.greetings[get_part_of_day()];
			var name = $(this).data('name') || '';
			$(this).html(greeting.replace('%s', name));
		});

		$('.fontimator-timed-message-welcome').each(function(){
			var greeting = FontimatorTimedMessages.welcome[get_part_of_day()];
			$(this).html(greeting);
		});
	});
})( jQuery );
