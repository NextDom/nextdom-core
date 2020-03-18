/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

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

function test(toTest) {
    if (!toTest.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone/i)) {
        console.log('OK')
    }
}
/**
 * Initialise sidebar events
 */
function initSideBar() {
    // Dropdown menu event handler declaration for tactile utilisation
    if (!navigator.userAgent.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone/i)) {
        $('ul.dropdown-menu [data-toggle=dropdown]').on('mouseenter', function (event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).parent().siblings().removeClass('open');
            $(this).parent().toggleClass('open');
        });
    }

    // About buttons event handler declaration
    $('#bt_nextdomAbout,#bt_nextdomAbout2, #bt_nextdomAboutSide').on('click', function () {
        $('#md_modal').dialog({title: "{{A propos}}"});
        $('#md_modal').load('index.php?v=d&modal=about').dialog('open');
    });

    // Quick note link event handler declaration
    $('#bt_quickNote').on('click',function(){
        $('#md_modal').dialog({title: "{{Quick Notes}}"});
        $('#md_modal').load('index.php?v=d&modal=note.manager').dialog('open');
    });

    // Quick note link event handler declaration
    $('#bt_showExpressionTest').on('click',function(){
        $('#md_modal').dialog({title: "{{Testeur d'expression}}"});
        $('#md_modal').load('index.php?v=d&modal=expression.test').dialog('open');
    });

    // Restart event handler declaration
    // Todo: Doublon
    $('#bt_rebootSystem').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir redémarrer le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=reboot';
            }
        });
    });

    // Shutdown event handler declaration
    // Todo: Doublon
    $('#bt_haltSystem').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir arrêter le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=shutdown';
            }
        });
    });

    // Dropdown menu event handler declaration
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open');
    });

    // Help triggers declaration
    $('#bt_getHelpPage').on('click',function(){
        // Init help button
        var pageName = getUrlVars('p');
        var pluginName = getUrlVars('m');
        if (pluginName === false) {
            pluginName = '';
        }
        nextdom.getDocumentationUrl({
            plugin: pluginName,
            page: pageName,
            error: function(error) {
                notify('Erreur', error.message, 'error');
            },
            success: function(url) {
                window.open(url,'_blank');
            }
        });
    });
}

/**
 * Update display clock
 */
function displayClock() {
    var date = new Date();
    var locale = 'en-EN';
    // Get NextDom language for format
    if (isset(nextdom_language)) {
        locale = nextdom_language.replace('_', '-');
    }
    // Date
    var dateFormat = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
    $('#horloge_date').text(date.toLocaleDateString(locale, dateFormat));
    // Time
    $('#horloge_time').text(date.toLocaleTimeString(locale));
}

/**
 * Initialise top menu events
 */
function initTopMenu() {
    displayClock();
    setInterval(function () {
        displayClock();
    }, 1000);

    // Messages link event handler declaration
    $('#bt_messageModal').on('click',function() {
        var tableMessageContainer = $('#table_message .menu');
        tableMessageContainer.html('<i class="fas fa-circle-notch fa-spin"></i>');
        nextdom.message.all({
            error: function (error) {
                notify('Core', error.message, 'error');
            },
            success: function (messages) {
                tableMessageContainer.html('');
                for (var messageIndex in messages) {
                    tableMessageContainer.append('' +
                      '<li data-message_id="' + messages[messageIndex]['id'] + '">' +
                      '<a href="#">' +
                      '<div class="pull-left">' +
                      '<img class="' + messages[messageIndex]['iconClass'] + '" src="' + messages[messageIndex]['icon'] + '">' +
                      '</div>' +
                      '<h4>' + messages[messageIndex]['plugin'] +
                      '<div class="btn btn-sm btn-danger removeMessage pull-right"><i class="fas fa-trash no-spacing"></i></div>' +
                      '<small class="pull-right"><i class="fas fa-clock spacing-right"></i>' + messages[messageIndex]['date'] + '</small>' +
                      '</h4>' +
                      '<p>' + decodeHtmlEntities(messages[messageIndex]['message']) + '</p>' +
                      '<p>' +  messages[messageIndex]['action'] + '</p>' +
                      '</a>' +
                      '</li>');
                }
            }
        });
    });

    // adminLTE left menu toggle link event handler declaration
    $('.sidebar-toggle').on("click", function () {
        setTimeout(function () {
            // Resize menu
            sideMenuResize();
            limitTreeviewMenu();
            // Header repositionning
            setHeaderPosition(false);
            // Gui automatic adjusting
            adjustNextDomTheme();
            // Equipement reorganisation
            $('.div_displayEquipement').packery();
        }, 100);
    });
}

/**
 * Limitation overflow menu sidebar
 *
 */
function limitTreeviewMenu() {
    $(".sidebar-menu").children(".treeview").each(function () {
        if (document.getElementsByClassName('sidebar-collapse').length === 0) {
            $(this).children(".treeview-menu").css("max-height", "auto");
        } else {
            var maxHeight = window.innerHeight - document.getElementById($(this).attr('id')).offsetTop - 44 - 48 - 30;
            $(this).children(".treeview-menu").css("max-height", maxHeight);
        }
    });
}

/**
 * Actionbar (header) position and size adjustement
 *
 * @param init true for first display
 */
function setHeaderPosition(init) {
    var headerHeight = 15;
    var alertHeaderHeight = 0;
    var alertHeaderMargin = 0;
    var headerSize;
    var paddingSideClose;
    if (window.innerWidth < 768) {
        headerSize = 100;
        if ($('body').hasClass("sidebar-open")) {
            paddingSideClose = 245;
        } else {
            paddingSideClose = 15;
        }
    } else {
        headerSize = 50;
        if ($('body').hasClass("sidebar-collapse")) {
            paddingSideClose = 65;
        } else {
            paddingSideClose = 245;
        }
    }
    if ($('*').hasClass("alert-header")) {
        alertHeaderHeight = $('.alert-header').height();
        alertHeaderMargin = 15;
    }
    if ($('*').hasClass("content-header")) {
        var scrollLimit = 14 + alertHeaderHeight;
        $(".content-header").each(function () {
            var container = $(this).parent();
            if (!container.hasClass("ui-dialog-content") && !container.parent().hasClass("ui-dialog-content")) {
                $(this).css("padding-right", paddingSideClose);
                if (init || container.css("display") != "none") {
                    if (container.css("display") == "none") {
                        container.show();
                        headerHeight = container.children('.content-header').height();
                        container.hide();
                    } else {
                        headerHeight = container.children('.content-header').height();
                    }
                    var scrollValue = document.documentElement.scrollTop;
                    if (scrollValue > scrollLimit && window.innerWidth >= 768) {
                        container.children(".content-header").css("top", headerSize - 15);
                        container.children(".content").css("padding-top", headerHeight + 30);
                        container.children(".content-header").children("div").removeClass('scroll-shadow').addClass('fixed-shadow');
                    } else {
                        container.children(".content-header").css("top", headerSize - scrollValue + alertHeaderHeight + alertHeaderMargin);
                        container.children(".content").css("padding-top", headerHeight + 30 - alertHeaderMargin);
                        container.children(".content-header").children("div").removeClass('fixed-shadow').addClass('scroll-shadow');
                    }
                    container.children(".content-header").show();
                }
            }
        });
    } else {
        $(".content").css("padding-top", 15);
    }
}

/**
 * Search input field activation on dedicated pages
 */
function sideMenuResize() {
    var lists = document.getElementsByTagName("li");
    var bodyClassList = document.getElementsByTagName('body')[0].classList;
    if (bodyClassList.contains('sidebar-collapse') || (window.innerWidth < 768 && !bodyClassList.contains('sidebar-open'))) {
        // Menu closed
        // Todo : Merge css {}
        $('.sidebar-menu').css('overflow', '');
        $('.sidebar-menu').css('height', 'none');
        $('.treeview-menu').css('overflow-y', 'auto');
        for (var menuItemIndex = 0; menuItemIndex < lists.length; ++menuItemIndex) {
            if (lists[menuItemIndex].getAttribute("id") !== undefined && lists[menuItemIndex].getAttribute("id") !== null) {
                if (lists[menuItemIndex].getAttribute("id").match("side")) {
                    var liIndex = lists[menuItemIndex].getAttribute("id").slice(-1);
                    lists[menuItemIndex].getElementsByClassName("treeview-menu")[0].style.maxHeight = window.innerHeight - 50 - 70 - (44 * liIndex) + "px";
                }
            }
        }
    } else {
        // Menu opened
        $(".sidebar-menu").css("overflow-y", "auto");
        $(".treeview-menu").css("overflow", "");

        var goOnTopButton = document.getElementById("bt_goOnTop");
        var sidemenuBottomPadding = 0;
        var sidemenuDoubleHeaderPadding = 0;
        // If bt_goOnTop visible
        if (goOnTopButton !== undefined && goOnTopButton !== null) {
            if (goOnTopButton.style.display == "block") {
                sidemenuBottomPadding = 75;
            }
        }

        // If double header because of little resolution
        if (window.innerWidth < 767) {
            sidemenuDoubleHeaderPadding = 50;
        }

        // Height adjustement
        $(".sidebar-menu").css("height", window.innerHeight - 50 - 70 - sidemenuBottomPadding - sidemenuDoubleHeaderPadding);
        for (var i = 0; i < lists.length; ++i) {
            if (lists[i].getAttribute("id") !== undefined && lists[i].getAttribute("id") !== null) {
                if (lists[i].getAttribute("id").match("side")) {
                    lists[i].getElementsByClassName("treeview-menu")[0].style.maxHeight = "none";
                }
            }
        }
    }
}

/**
 * Refresh the message number badge in the header
 */
function refreshMessageNumber() {
    nextdom.message.number({
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (_number) {
            MESSAGE_NUMBER = _number;
            if (_number === 0 || _number === '0') {
                $('.notifications-menu').find('.fa-envelope-open').removeClass('notifbadge');
                $('.spanNbMessage').hide();
                $('#table_message .header').hide();
            } else {
                $('.notifications-menu').find('.fa-envelope-open').addClass('notifbadge');
                $('.spanNbMessage').html(_number);
                $('.spanNbMessage').show();
                $('#table_message .header').show();
            }
        }
    });
}

/**
 * Refresh the updates number badge in the header
 */
function refreshUpdateNumber() {
    nextdom.update.number({
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (_number) {
            UPDATE_NUMBER = _number;
            if (_number == 0 || _number == '0') {
                $('.tasks-menu').find('.fa-download').removeClass('notifbadge');
                $('#span_nbUpdate').hide();
            } else {
                $('.tasks-menu').find('.fa-download').addClass('notifbadge');
                $('#span_nbUpdate').html(_number);
                $('#span_nbUpdate').show();
            }
        }
    });
}

/**
 * Toggle between showing and hiding notifications
 *
 * @param notificationState 1 for notification showed or 0 for hide.
 */
function switchNotify(notificationState) {
    nextdom.config.save({
        configuration: {'notify::status': notificationState},
        error: function (error) {
            notify('Core', error.message, 'error');
        },
        success: function () {
            if (notificationState) {
                $('.notifyIcon').removeClass("fa-bell-slash").addClass("fa-bell");
                $('.notifyIconLink').attr('onclick', 'switchNotify(0);');
                notify('Core', '{{Notification activée}}', 'success');
            } else {
                $('.notifyIcon').removeClass("fa-bell").addClass("fa-bell-slash");
                $('.notifyIconLink').attr('onclick', 'switchNotify(1);');
                notify('Core', '{{Notification desactivée}}', 'success');
            }
        }
    });
}

