// iOS Style Checkbox https://www.jqueryscript.net/form/iOS-Style-Checkbox-Plugin-with-jQuery-CSS3-iosCheckbox-js.html
!function (e) { e.fn.extend({ iosCheckbox: function () { if (this.destroy = function () { e(this).each(function () { e(this).next(".ios-ui-select").remove() }) }, "true" !== e(this).attr("data-ios-checkbox")) return e(this).attr("data-ios-checkbox", "true"), e(this).each(function () { var c = e(this), i = jQuery("<div>", { class: "ios-ui-select" }).append(jQuery("<div>", { class: "inner" })); if (c.is(":checked") && i.addClass("checked"), c.hide().after(i), c.is(":disabled")) return i.css("opacity", "0.6"); i.click(function () { i.toggleClass("checked"), i.hasClass("checked") ? c.prop("checked", !0) : c.prop("checked", !1), c.click() }) }), this } }) }(jQuery);

// Email Preferences
$(function(){
  $('.woocommerce-EmailPreferencesForm .ios-checkbox').iosCheckbox();
  $('.woocommerce-EmailPreferencesForm label').click(function (e) {
    if (e.target === this || $(this).find(':not(.ios-ui-select, .ios-ui-select *)').index(e.target) > -1) {
      $(this).find('.ios-ui-select').click();
    }
  });
});