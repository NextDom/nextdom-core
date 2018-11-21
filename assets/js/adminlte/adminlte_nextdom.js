$.fn.bootstrapBtn = $.fn.button.noConflict();

$(function () {
    'use strict'

    /**
     * Get access to plugins
     */

    $('[data-toggle="push-menu"]').pushMenu();
    var $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu');
    var $layout = $('body').data('lte.layout');
    $(window).on('load', function () {
        // Reinitialize variables on load
        $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu');
        $layout = $('body').data('lte.layout')
    });

    /**
     * Toggles layout classes
     *
     * @param String cls the layout class to toggle
     * @returns void
     */
    function changeLayout(cls) {
        $('body').toggleClass(cls);
        $layout.fixSidebar();
        if ($('body').hasClass('fixed') && cls == 'fixed') {
            $pushMenu.expandOnHover();
            $layout.activate()
        }
    }


    /**
     * Retrieve default settings and apply them to the template
     *
     * @returns void
     */
    function setup() {
        // Add the layout manager
        $('[data-layout]').on('click', function () {
            changeLayout($(this).data('layout'));
        });

        $('[data-enable="expandOnHover"]').on('click', function () {
            $(this).attr('disabled', true);
            $pushMenu.expandOnHover();
            if (!$('body').hasClass('sidebar-collapse'))
                $('[data-layout="sidebar-collapse"]').click();
        });
    }

    setup();

});

$('.sidebar-toggle').on("click", function () {
    if ($('body').hasClass("sidebar-collapse") || ($(window).width() < 768 && !$('body').hasClass("sidebar-open"))) {
        $(".treeview-menu").css("overflow", "");
        $(".sidebar-menu").css("overflow-y", "auto");
        sideMenuResize(false);
    } else {
        $(".sidebar-menu").css("overflow", "");
        $(".treeview-menu").css("overflow-y", "auto");
        sideMenuResize(true);
    }
});

$(window).resize(function () {
    if ($(window).width() < 768) {
        $('body').removeClass("sidebar-collapse");
    }
    if ($('body').hasClass("sidebar-collapse")) {
        sideMenuResize(true);
    } else {
        sideMenuResize(false);
    }
});

function sideMenuResize(_calcul) {
    var lists = document.getElementsByTagName("li");
    if (_calcul==true) {
        $(".sidebar-menu").css("height", "none");
        for (var i = 0; i < lists.length; ++i) {
            if (lists[i].getAttribute("id") !== undefined && lists[i].getAttribute("id") !== null) {
                if (lists[i].getAttribute("id").match("side")) {
                  var liIndex=lists[i].getAttribute("id").slice(-1);
                  lists[i].getElementsByClassName("treeview-menu")[0].style.maxHeight=$(window).height()-50-70-(44*liIndex)+"px";
                }
            }
        }
    }else{
        var goOnTopButton = document.getElementById("bt_goOnTop");
        var sidemenuBottomPadding = 0;
        var sidemenuDoubleHeaderPadding = 0;
        if (goOnTopButton !== undefined && goOnTopButton !== null) {
          if (goOnTopButton.style.display == "block") {
            sidemenuBottomPadding = 75;
          }
        }
        if ($(window).width() < 768) {
            sidemenuDoubleHeaderPadding = 50;
        }
        $(".sidebar-menu").css("height", $(window).height()-50-70-sidemenuBottomPadding-sidemenuDoubleHeaderPadding);
        for (var i = 0; i < lists.length; ++i) {
            if (lists[i].getAttribute("id") !== undefined && lists[i].getAttribute("id") !== null) {
                if (lists[i].getAttribute("id").match("side")) {
                  lists[i].getElementsByClassName("treeview-menu")[0].style.maxHeight="none";
                }
            }
        }
    }
}
