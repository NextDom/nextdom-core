/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*
* @Support <https://www.nextdom.org>
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

$(function () {
    $('.colorpick_inline').colorpicker({
        container: true,
        inline: true
    });
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimScrollDiv").css("overflow", "");
    $(".sidebar").css("overflow", "");

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
        window.scrollTo(0, 0);
        setHeaderPosition(true);
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

    var fullUrl = document.location.toString();
    if (fullUrl.indexOf('rescue') === -1) {
        var page = fullUrl.split('p=')[1].replace('#', '').split('&')[0];
        var availableSearchPage = [
            "plugin",
            "dashboard",
            "interact",
            "scenario",
            "object",
            "realtime",
            "display",
            "database",
            "note",
            "system",
            "log",
            "market",
            "update.list",
            "update",
        ];

        if(jQuery.inArray(page, availableSearchPage) != -1) {
            $("#generalSearch").prop('disabled', false);
        } else {
            $("#generalSearch").prop('disabled', true);
        }
    }
});

if ($('[role="dialog"] .fab').length == 0) {
    $('.fab-filter').on('mouseleave',function() {
        $('.blurPanel').removeClass('blur');
    });

    $('.fab-filter').on('mouseenter',function() {
        $('.blurPanel').addClass('blur');
    });
} else {
    $('.fab').css('display', 'none');
}

window.onscroll = function () {
    var goOnTopButton = document.getElementById("bt_goOnTop");
    var sidemenuBottomPadding = 0;
    if (goOnTopButton !== undefined && goOnTopButton !== null) {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            goOnTopButton.style.display = "block";
            sidemenuBottomPadding = 75;
        } else {
            goOnTopButton.style.display = "none";
        }
    }
    if (!$('body').hasClass("sidebar-collapse")) {
        $(".sidebar-menu").css("overflow-y", "auto");
        sideMenuResize(false);
    }
    setHeaderPosition(false);
};

$('#bt_goOnTop').click(function () {
    window.scrollTo(0, 0);
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
    setTimeout(function () {
        setHeaderPosition(false);
        $('.div_displayEquipement').packery();
    }, 100);
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
    setHeaderPosition(false);
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
