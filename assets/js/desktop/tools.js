
$(".btn-app").on('click', function (event) {
    $('#'+$(this).attr('data-id')).show();
    $('#div_MenuList').hide();
    $('#div_MenuList2').hide();
    $('#div-menuPerf').hide();
});
$(".btn-close").on('click', function (event) {
    $('#'+$(this).parent().parent().parent().attr('id')).hide();
    $('#div_MenuList').show();
    $('#div_MenuList2').show();
    $('#div-menuPerf').show();
});
