(function( $ ) {
	'use strict';

	$(function () {
		// Select variations tab by default
		$('ul.wc-tabs a[href="#variable_product_options"]').click();
		$('#variable_product_options').trigger('reload');



		var block = function () {
			$('#woocommerce-product-data').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		}
		var unblock = function () {
			$('#woocommerce-product-data').unblock();
		}

		// setup all variations button
		$('#fontimator_setup_variations').click(function (e) {
			e.preventDefault();
			if (!confirm("Are you sure you want to set-up all variations?\n**Warning: this will erase all current download URLs and prices!**")) return;
			block();
			
			var data = {
				action: 'fontimator_setup_variations',
				post_id: woocommerce_admin_meta_boxes_variations.post_id
			};
			
			$.post(woocommerce_admin_meta_boxes_variations.ajax_url, data, function (response) {
				if (response.result == 'success') {
					alert(response.amount + ' Variations were set-up successfully. Refreshing...');
					// window.location.reload();
					$('#variable_product_options').trigger('reload');
				} else {
					alert('There was an error. Maybe this is a membership product?');
				}
				unblock();
			});

			return false;
		});
	});

})( jQuery );
