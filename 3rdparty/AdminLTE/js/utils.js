$(function() {
    $('.colorpick').colorpicker();

$(":input").inputmask();

$('[data-toggle="tooltip"]').tooltip();

$(document).ready(function(){
    $('input').iCheck({
      checkboxClass: 'icheckbox_minimal-blue',
      radioClass: 'iradio_minimal-blue',
      increaseArea: '20%' // optional
    });
  });
});

if ($.fn.button.noConflict) {
    var bootstrapButton = $.fn.button.noConflict();
    $.fn.bootstrapBtn = bootstrapButton;
}

$(document).on('icheck', function(){
    $('input[type=checkbox], input[type=radio]').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue',
        increaseArea: '20%' // optional
    });
    }).trigger('icheck'); // trigger it for page load