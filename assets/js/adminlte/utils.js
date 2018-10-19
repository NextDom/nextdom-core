$(function() {
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimScrollDiv").css("overflow","");
    $(".sidebar").css("overflow","");
});
$('.fab').on('mouseleave',function() {
    $('#dashPanel').removeClass('dashBlur');
});

$('.fab').on('mouseenter',function() {
    $('#dashPanel').addClass('dashBlur');
});