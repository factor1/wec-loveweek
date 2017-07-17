
/**
 * Global JS
 */

jQuery(document).ready(function($) {

  $('input.date').datepicker({ dateFormat: 'yy-mm-dd' });
  $('input.time').timepicker();
  $('.table-sort').tablesorter();

  setTimeout(function() {
      $('div.alert').fadeTo('slow', 0.01, function() {
          $(this).slideUp('fast');
      });
  }, 6000);

  $('a[href*="#scroll-"]').click(function() {
    $('html, body').animate({
        scrollTop: $($(this).attr('href')).offset().top
    }, 1000);
  });


  /**
   * Placeholder Fix
   */

  var objTestInput = document.createElement('input');

  if (('placeholder' in objTestInput) == false) {

    jQuery('[placeholder]').focus(function() {
      var input = jQuery(this);
      if (input.val() == input.attr('placeholder')) {
        input.val('');
        input.removeClass('placeholder');
      }
    }).blur(function() {
      var input = jQuery(this);
      if (input.val() == '' || input.val() == input.attr('placeholder')) {
        input.addClass('placeholder');
        input.val(input.attr('placeholder'));
      }
    }).blur();

    jQuery('[placeholder]').parents('form').submit(function() {
      jQuery(this).find('[placeholder]').each(function() {
        var input = jQuery(this);
        if (input.val() == input.attr('placeholder')) {
          input.val('');
        }
      })
    });
  }

});