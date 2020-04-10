// iOS Style Checkbox https://www.jqueryscript.net/form/iOS-Style-Checkbox-Plugin-with-jQuery-CSS3-iosCheckbox-js.html
!function (e) { e.fn.extend({ iosCheckbox: function () { if (this.destroy = function () { e(this).each(function () { e(this).next(".ios-ui-select").remove() }) }, "true" !== e(this).attr("data-ios-checkbox")) return e(this).attr("data-ios-checkbox", "true"), e(this).each(function () { var c = e(this), i = jQuery("<div>", { class: "ios-ui-select" }).append(jQuery("<div>", { class: "inner" })); if (c.is(":checked") && i.addClass("checked"), c.hide().after(i), c.is(":disabled")) return i.css("opacity", "0.6"); i.click(function () { i.toggleClass("checked"), i.hasClass("checked") ? c.prop("checked", !0) : c.prop("checked", !1), c.click() }) }), this } }) }(jQuery);

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

  // Email Preferences
  $('.woocommerce-EmailPreferencesForm .ios-checkbox').iosCheckbox();
  $('.woocommerce-EmailPreferencesForm label').click(function (e) {
    if (e.target === this || $(this).find(':not(.ios-ui-select, .ios-ui-select *)').index(e.target) > -1) {
      $(this).find('.ios-ui-select').click();
    }
  });
});
