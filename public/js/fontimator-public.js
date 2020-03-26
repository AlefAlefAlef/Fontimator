(function( $ ) {
	'use strict';
	$(function(){
		$(document).on('woocommerce_update_variation_values', 'form.variations_form', function (event) {
			$(this).find("optgroup:empty").remove();
		});

		$(document).on('found_variation reset_data', 'form.variations_form', 
			function (event, variation) {
				var $container = $('.licenseapp-field');
				if(variation && variation.attributes) {
					var licenseAttr = variation.attributes['attribute_pa_' + FontimatorPublic.licenseAttributeName].split('-')[0];
					if (['web', 'app'].indexOf(licenseAttr) > -1) {
						$container.show()
							.find('input').prop('required', true)
							.attr('placeholder', FontimatorPublic.placeholders[licenseAttr]);
						$container.find('span').hide()
							.filter('[data-license="' + licenseAttr + '"]').show();

						// Here for editing variation in cart, where there might be a few tooltips
						$('.licenseapp-field .info-tooltip').aToolTip({ inSpeed: 350, yOffset: -55, fixed: false });
					} else {
						$container.hide()
							.find('input').prop('required', false).val('');
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
			} else if ( current_hour >= 12 && current_hour <= 16 ) {
				return 'afternoon';
			} else if ( current_hour >= 17 && current_hour <= 20 ) {
				return 'evening';
			} else if ( current_hour >= 21 || current_hour <= 4 ) {
				return 'night';
			}
		}
		
		$('.fontimator-timed-message-greeting').each(function(){
			if ((new Date()).getDay() === 6) {
				var greeting = FontimatorTimedMessages.greetings['saturday'];
			} else {
				var greeting = FontimatorTimedMessages.greetings[get_part_of_day()];
			}
			var name = $(this).data('name') || '';
			$(this).html(greeting.replace('%s', name));
		});

		$('.fontimator-timed-message-welcome').each(function(){
			var greeting = FontimatorTimedMessages.welcome[get_part_of_day()];
			$(this).html(greeting);
		});


		// Copyable Links
		function copyText(str) {
			var el = document.createElement('textarea');
			el.value = str;
			el.setAttribute('readonly', '');
			el.style.position = 'absolute';
			el.style.left = '-9999px';
			document.body.appendChild(el);
			el.select();
			document.execCommand('copy');
			document.body.removeChild(el);
		};

		
		$('a.copyable-link').click(function(e){
			e.preventDefault();
			copyText($(this).attr('href'));

			if ($(this).data('success-text')) {
				$(this).text($(this).data('success-text'));
			}
		});

		$(".fontimator-free-download a.open").each(function() {
			$(this).click(function (e) {
				$(this).hide();
				$(this).parent().children('form').slideDown();
				e.preventDefault();
			});
		});

		$('.fontimator-free-download form').submit(function(e){
			$(this).find('.success-overlay').css('display', 'flex').hide().fadeIn(300);
			if ($(this).find('input.tipotip-checkbox').prop('checked') ) {
				$(this).find('p.tipotip-message').show();
			}
		}).find('.close').click(function(event){
			event.preventDefault();
			$(this).parent().fadeOut(200);


		});

		// DevTools detect
		// Get notified when it's opened/closed or orientation changes
		window.addEventListener('devtoolschange', event => {
			if (event.detail.isOpen) {
				$('#devtools-pop-up').slideDown(100).parent().show();
				window.disableDevToolsDetection = true;
			} else {
				$('#devtools-pop-up').hide().parent().hide();
			}
		});
	});
})( jQuery );
