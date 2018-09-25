$(function() {
    $('.colorpick').colorpicker();

$(":input").inputmask();

$('[data-toggle="tooltip"]').tooltip();
});

if ($.fn.button.noConflict) {
    var bootstrapButton = $.fn.button.noConflict();
    $.fn.bootstrapBtn = bootstrapButton;
    }