jQuery(function($) {
  // Downloads Table
  $(".fontimator-table .font-family-header").click(function() {
    $(this).toggleClass("open");
  });

  // Download Buttons
  if ($("input[name='fontimator-downloads']").length < 2) {
    $(".fontimator-buttons .download-buttons").hide();
    $("th.download-select, td.download-select").hide(); //avraham added Oct 21, 2017
  }

  $(".fontimator-bulk-download").click(function(e) {
    var downloadsString = "";
    $.each($("input[name='fontimator-downloads']:checked"), function() {
      if (downloadsString.length) {
        downloadsString += ",";
      }
      downloadsString += $(this).val();
    });

    window.open(
      FontimatorDownloadCheckboxesButtons.zipomatorBaseURL +
        "/" +
        downloadsString +
        "?_wpnonce=" +
        FontimatorDownloadCheckboxesButtons.zipomatorNonce
    );
  });

  FontimatorDownloadCheckboxesButtons.updateBulkStatus = function() {
    if ($("input[name='fontimator-downloads']:checked").length < 1) {
      $(".fontimator-bulk-download")
        .prop("disabled", true)
        .attr("title", FontimatorDownloadCheckboxesButtons.disabledText);
    } else {
      $(".fontimator-bulk-download")
        .prop("disabled", false)
        .attr("title", "");
    }
  };

  $(".fontimator-select-all").click(function(e) {
    $("input[name='fontimator-downloads']").prop("checked", true);
    FontimatorDownloadCheckboxesButtons.updateBulkStatus();
  });
  $(".fontimator-unselect-all").click(function(e) {
    $("input[name='fontimator-downloads']").prop("checked", false);
    FontimatorDownloadCheckboxesButtons.updateBulkStatus();
  });

  $("input[name='fontimator-downloads']").change(
    FontimatorDownloadCheckboxesButtons.updateBulkStatus
  );

  // Subscriptions
  $(".subscription_details a.button.cancel").click(function(e) {
    if (!confirm(FontimatorSubscriptionActions.cancelConfirmationText)) {
      e.preventDefault();
    }
  });

  // Complete Family Banner
  $(".complete-family-banner").each(function() {
    var $fontName = $(this)
      .closest("tbody")
      .find(".font-family-header .font-name-with-preview");
    $fontName.addClass("font-name-with-banner-inside");
  });


  // Reseller Domains Table
  $('.reseller_domains form select').select2({
    maximumSelectionLength: FontimatorResellerDomains.maximumFamiliesLimit,
    language: {
      maximumSelected: function (n) {
        return FontimatorResellerDomains.maximumFamiliesSelectedError;
      }
    },
  });
});
