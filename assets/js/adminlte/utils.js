function showCategoryFilterInMenu() {
    document.getElementById("filter_category").style.display = "block";
    document.getElementById("bt_editDashboardWidgetOrder").style.display = "block";
}

function hideCategoryFilterInMenu() {
    document.getElementById("filter_category").style.display = "none";
    document.getElementById("bt_editDashboardWidgetOrder").style.display = "none";
}

$(function() {
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();

    try {
        var pageCode = location.search.split("&")[1].split('p=');
        if (pageCode[1] == 'dashboard'){
            showCategoryFilterInMenu();
        } else {
            hideCategoryFilterInMenu();
        }
    }
    catch(error) {
        hideCategoryFilterInMenu();
    }
    $(".slimScrollDiv").css("overflow","");
    $(".sidebar").css("overflow","");
});
