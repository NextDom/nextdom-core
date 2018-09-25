$(function() {
    $('.colorpick').colorpicker();

$(":input").inputmask();


$('input').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' /* optional */
    });

$('[data-toggle="tooltip"]').tooltip();
});

if ($.fn.button.noConflict) {
    var bootstrapButton = $.fn.button.noConflict();
    $.fn.bootstrapBtn = bootstrapButton;
    }