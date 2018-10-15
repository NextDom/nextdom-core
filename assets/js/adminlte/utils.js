$(function() {
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();

    var y = location.search.split("&")[1].split('p=');
    if( y[1] == 'dashboard'){
        document.getElementById("filter_category").style.display = "block";
        document.getElementById("bt_editDashboardWidgetOrder").style.display = "block";
    }else{
        document.getElementById("filter_category").style.display = "none";
        document.getElementById("bt_editDashboardWidgetOrder").style.display = "none";
    }
    $(".slimScrollDiv").css("overflow","");
    $(".sidebar").css("overflow","");
});
