$(function() {
    $('.colorpick_inline').colorpicker({
        container: true,
        inline: true
    });
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimScrollDiv").css("overflow","");
    $(".sidebar").css("overflow","");
});
if ($('[role="dialog"] .fab').length == 0) {
    $('.fab').on('mouseleave',function() {
        $('.blurPanel').removeClass('blur');
    });

    $('.fab').on('mouseenter',function() {
        $('.blurPanel').addClass('blur');
    });
} else {
    $('.fab').css('display', 'none');
}
