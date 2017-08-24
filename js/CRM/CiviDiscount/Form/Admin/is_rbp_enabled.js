CRM.$(function ($) {
  var checkbox = $('.crm-discount-item-form-block-is_rdp_enabled');

  // move the checkbox into the main form
  $('.crm-discount-item-form-block-event-types').after(checkbox);
});