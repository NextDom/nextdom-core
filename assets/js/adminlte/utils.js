$(function() {
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimScrollDiv").css("overflow","");
    $(".sidebar").css("overflow","");
});
$('.fab').on('mouseleave',function() {
    $('.blurPanel').removeClass('blur');
});

$('.fab').on('mouseenter',function() {
    $('.blurPanel').addClass('blur');
});