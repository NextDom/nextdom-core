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
    if (!$('body').hasClass("sidebar-collapse")) {
        $(".sidebar-menu").css("overflow", "");
    } else {
        $(".sidebar-menu").css("overflow-y", "auto");
        $(".sidebar-menu").css("height", $(window).height());
    }

});

$(window).resize(function () {
    if ($(window).width() < 767) {
        $('body').removeClass("sidebar-collapse");
    }
});