jQuery(function ($) {
	// Downloads Table
	if ($("input[name='fontimator-downloads']").length < 2) {
		$(".fontimator-buttons").hide();
		$("th.download-select, td.download-select").hide(); //avraham added Oct 21, 2017
	}

	$('.fontimator-bulk-download').click(function (e) {
		var downloadsString = '';
		$.each($("input[name='fontimator-downloads']:checked"), function () {
			if ( downloadsString.length ) {
				downloadsString += ',';
			}
			downloadsString += $(this).val();
		});

		window.open( FontimatorDownloadCheckboxesButtons.zipomatorBaseURL + '/' + downloadsString + '?_wpnonce=' + FontimatorDownloadCheckboxesButtons.zipomatorNonce );
	});

	FontimatorDownloadCheckboxesButtons.updateBulkStatus = function() {
		if ($("input[name='fontimator-downloads']:checked").length < 1) {
			$('.fontimator-bulk-download')
				.prop('disabled', true)
				.attr('title', FontimatorDownloadCheckboxesButtons.disabledText);
		} else {
			$('.fontimator-bulk-download')
				.prop('disabled', false)
				.attr('title', '');
		}
	}
	
	$('.fontimator-select-all').click(function (e) {
		$("input[name='fontimator-downloads']").prop('checked', true);
		FontimatorDownloadCheckboxesButtons.updateBulkStatus();
	});
	$('.fontimator-unselect-all').click(function (e) {
		$("input[name='fontimator-downloads']").prop('checked', false);
		FontimatorDownloadCheckboxesButtons.updateBulkStatus();

	});

	$("input[name='fontimator-downloads']").change(FontimatorDownloadCheckboxesButtons.updateBulkStatus);


	// Subscriptions
	$('.subscription_details a.button.cancel').click(function (e) {
		if (!confirm(FontimatorSubscriptionActions.cancelConfirmationText)) {
			e.preventDefault();
		}
	});
});