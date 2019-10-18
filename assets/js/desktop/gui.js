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

/* JS file for all that talk about GUI */

// INIT, EVENT, FIRST Loading

/**
 * Tooltip activation
 */
(function($) {
    $(function() {
        $(document).tooltip({ selector: '[data-toggle="tooltip"]' });
    });
})(jQuery);

/**
 * Event for windows resizing
 */
$(window).resize(function () {
    // OBSOLETE ?
    initRowOverflow();

    // Close left menu if small resolution comming
    if ($(window).width() < 768) {
        $('body').removeClass("sidebar-collapse");
    }

    // Left menu resize
    sideMenuResize();
    limitTreeviewMenu();

    // Header repositionning
    setHeaderPosition(false);

    // Gui automatic adjusting
    adjustNextDomTheme();
});

/**
 * Event for scrolling inside display page
 */
window.onscroll = function () {
    var goOnTopButton = document.getElementById("bt_goOnTop");
    var sidemenuBottomPadding = 0;

    // GoOnTop button management
    if (goOnTopButton !== undefined && goOnTopButton !== null) {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            goOnTopButton.style.display = "block";
            sidemenuBottomPadding = 75;
        } else {
            goOnTopButton.style.display = "none";
        }
    }

    // Left menu resize
    sideMenuResize();
    limitTreeviewMenu();

    // Header repositionning
    setHeaderPosition(false);

    // Gui automatic adjusting
    adjustNextDomTheme();

    // Modals repositionning
    jQuery('#md_modal').dialog('option','position','center');
    jQuery('#md_modal2').dialog('option','position','center');
    jQuery('#md_pageHelp').dialog('option','position','center');
};

// FUNCTIONS

/**
 * Search input field activation on dedicated pages
 *
 * @param calcul true if you want to calcul dynamicly the height of menu
 */
 function sideMenuResize() {
     var lists = document.getElementsByTagName("li");
     if ($('body').hasClass("sidebar-collapse") || ($(window).width() < 768 && !$('body').hasClass("sidebar-open"))) {
        // Menu closed
        $(".sidebar-menu").css("overflow", "");
        $(".treeview-menu").css("overflow-y", "auto");

        $(".sidebar-menu").css("height", "none");
        for (var i = 0; i < lists.length; ++i) {
           if (lists[i].getAttribute("id") !== undefined && lists[i].getAttribute("id") !== null) {
               if (lists[i].getAttribute("id").match("side")) {
                   var liIndex=lists[i].getAttribute("id").slice(-1);
                   lists[i].getElementsByClassName("treeview-menu")[0].style.maxHeight=$(window).height()-50-70-(44*liIndex)+"px";
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
        if ($(window).width() < 768) {
           sidemenuDoubleHeaderPadding = 50;
        }

        // Height adjustement
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

 /**
  * Limitation overflow menu sidebar
  *
  */
 function limitTreeviewMenu () {
     var maxHeight = 0;
     $(".sidebar-menu").children(".treeview").each(function() {
         if (document.getElementsByClassName('sidebar-collapse').length == 0) {
            $(this).children(".treeview-menu").css("max-height", "auto");
         } else {
            maxHeight = window.innerHeight - document.getElementById($(this).attr('id')).offsetTop - 44 - 48 - 30;
            $(this).children(".treeview-menu").css("max-height", maxHeight);
         }
     });
 }

 /**
  * Fullscreen management
  *
  */
 function toggleFullScreen() {
     if ((document.fullScreenElement && document.fullScreenElement !== null) || (!document.mozFullScreen && !document.webkitIsFullScreen)) {
         if (document.documentElement.requestFullScreen) {
             document.documentElement.requestFullScreen();
         } else if (document.documentElement.mozRequestFullScreen) {
             document.documentElement.mozRequestFullScreen();
         } else if (document.documentElement.webkitRequestFullScreen) {
             document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
         }
         $('#togglefullscreen').removeClass('fa-expand').addClass('fa-compress');
     } else {
         if (document.cancelFullScreen) {
             document.cancelFullScreen();
         } else if (document.mozCancelFullScreen) {
             document.mozCancelFullScreen();
         } else if (document.webkitCancelFullScreen) {
             document.webkitCancelFullScreen();
         }
         $('#togglefullscreen').removeClass('fa-compress').addClass('fa-expand');
     }
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
     if ($(window).width() < 768) {
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
     var fullUrl = document.location.toString();
     if (fullUrl.indexOf('rescue') === -1) {
         if ($('*').hasClass("content-header")) {
             var scrollLimit = 14 + alertHeaderHeight;
             $(".content-header").each(function() {
                 var container = $(this).parent();
                 if (!container.hasClass("ui-dialog-content") && !container.parent().hasClass("ui-dialog-content")) {
                     $(this).css("padding-right", paddingSideClose);
                     if (init || container.css("display")!="none") {
                         if (container.css("display")=="none") {
                             container.show();
                             headerHeight = container.children('.content-header').height();
                             container.hide();
                         } else {
                             headerHeight = container.children('.content-header').height();
                         }
                         var scrollValue = document.documentElement.scrollTop;
                         if (scrollValue > scrollLimit) {
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
      } else {
          $(".content-header").css("padding-right", 15);
          $(".content-header").css("top", 50);
          $(".content").css("padding-top", 30);
          // Hide back button
          $(".content-header").find(".fa-chevron-left").parent().hide();
      }
 }

 /**
  * Automatically adjust pages to paste to the NextDom theme
  *
  */
 function adjustNextDomTheme() {
     // tabs adjustement
     $("#div_pageContainer").css('padding-top', '');
     if (!$('#div_pageContainer .nav-tabs').parent().hasClass('nav-tabs-custom')) {
         $('#div_pageContainer .nav-tabs').parent().addClass('nav-tabs-custom');
     }
     if (!$('.ui-widget-content').find('.nav-tabs').parent().hasClass("nav-tabs-custom")) {
         $('.ui-widget-content').find('.nav-tabs').parent().addClass("nav-tabs-custom");
     }
     if ($('#div_pageContainer').find('.row-overflow').children(".row").length != 0) {
         $('#div_pageContainer').find('.row-overflow').removeClass('row');
     }

     // containers adjustement
     var needContent = $("#div_pageContainer").children("section").length == 0 && $("#div_pageContainer").children().children("section").length == 0 && (getUrlVars('p') != 'plan') && (getUrlVars('p') != 'view') && (getUrlVars('p') != 'plan3d');
     if (needContent) {
         if (!$('#div_pageContainer').hasClass('content')) {
             $('#div_pageContainer').addClass('content');
         }

     } else {
         if ($('#div_pageContainer').hasClass('content')) {
             $('#div_pageContainer').removeClass('content');
         }
         $("#div_pageContainer").css('margin-left','');
         $("#div_pageContainer").css('margin-right','');
     }

     // icons adjustement
     $('#div_pageContainer').find('.fas.fa-sign-in').each(function () {
         $(this).removeClass('fa-sign-in').addClass('fa-sign-in-alt');
     });
 }

 /**
  * Refresh the message number badge in the header
  */
 function refreshMessageNumber() {
     nextdom.message.number({
         error: function (error) {
             notify("Erreur", error.message, 'error');
         },
         success : function (_number) {
             MESSAGE_NUMBER = _number;
             if (_number == 0 || _number == '0') {
                 $('.notifications-menu').find('.fa-envelope-open').removeClass('notifbadge');
                 $('#span_nbMessage').hide();
             } else {
                 $('.notifications-menu').find('.fa-envelope-open').addClass('notifbadge');
                 $('#span_nbMessage').html(_number);
                 $('#span_nbMessage').show();
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
             notify("Erreur", error.message, 'error');
         },
         success : function (_number) {
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
             notify("Core", error.message, 'error');
         },
         success: function () {
             if (notificationState) {
                 $('.notifyIcon').removeClass("fa-bell-slash").addClass("fa-bell");
                 $('.notifyIconLink').attr('onclick','switchNotify(0);')
                 notify("Core",  '{{Notification activée}}', 'success');
             } else {
                 $('.notifyIcon').removeClass("fa-bell").addClass("fa-bell-slash");
                 $('.notifyIconLink').attr('onclick','switchNotify(1);')
                 notify("Core",  '{{Notification desactivée}}', 'success');
             }
         }
     });
 }

 /**
  * Ask an autosizing of textarea
  */
 function taAutosize(){
     autosize($('.ta_autosize'));
     autosize.update($('.ta_autosize'));
 }

 /**
  * Change the theme colors and save name
  *
  * @param themeName Theme name to save and use for search colors.
  * @param reload TRUE for reloading page
  */
 function changeThemeColors(themeName, reload){
   var config = getThemeColors(themeName);
   var nextdomTheme = {};
   nextdomTheme[themeName] = 1;
   config['nextdom::theme'] = nextdomTheme;
    nextdom.config.save({
        configuration: config,
        error: function (error) {
            notify("Core", error.message, 'error');
        },
        success: function () {
            modifyWithoutSave = false;
            updateTheme(function() {
                notify("Info", '{{Thème parametré !}}', 'success');
                if (reload == true) {
                    window.location.reload();
                }
            });
        }
    });
 }

 function getThemeColors(themeName){
    var config = "";
    switch (themeName) {
      case 'dark':
        config = {
          'theme:color1' : '#33b8cc',
          'theme:color2' : '#ffffff',
          'theme:color3' : '#ffffff',
          'theme:color4' : '#222d32',
          'theme:color5' : '#1e282c',
          'theme:color6' : '#2c3b41',
          'theme:color7' : '#2c3b41',
          'theme:color8' : '#222d32',
          'theme:color9' : '#2c3b41',
          'theme:color10' : '#e6e7e8',
          'theme:color11' : '#484c52',
          'theme:color12' : '#484c52',
          'theme:color13' : '#222d32',
          'theme:color14' : '#666666',
          'theme:color15' : '#2c3b41',
          'theme:color16' : '#e6e7e8',
          'theme:color17' : '#8aa4af',
          'theme:color18' : '#222d32',
          'theme:color19' : '#263238',
          'theme:color20' : '#aaaaaa',
          'nextdom::alertAlpha' : '50'
        }
        break;
      case 'light':
        config = {
          'theme:color1' : '#33b8cc',
          'theme:color2' : '#ffffff',
          'theme:color3' : '#f4f4f5',
          'theme:color4' : '#f9fafc',
          'theme:color5' : '#dbdbdb',
          'theme:color6' : '#f4f4f5',
          'theme:color7' : '#ecf0f5',
          'theme:color8' : '#ffffff',
          'theme:color9' : '#f5f5f5',
          'theme:color10' : '#555555',
          'theme:color11' : '#ffffff',
          'theme:color12' : '#dddddd',
          'theme:color13' : '#ffffff',
          'theme:color14' : '#555555',
          'theme:color15' : '#f4f4f4',
          'theme:color16' : '#555555',
          'theme:color17' : '#555555',
          'theme:color18' : '#dddddd',
          'theme:color19' : '#fafafa',
          'theme:color20' : '#f5f5f5',
          'nextdom::alertAlpha' : '100'
        };
        break;
      case 'mix':
        config = {
          'theme:color1' : '#33b8cc',
          'theme:color2' : '#ffffff',
          'theme:color3' : '#ffffff',
          'theme:color4' : '#222d32',
          'theme:color5' : '#1e282c',
          'theme:color6' : '#2c3b41',
          'theme:color7' : '#ecf0f5',
          'theme:color8' : '#ffffff',
          'theme:color9' : '#f5f5f5',
          'theme:color10' : '#555555',
          'theme:color11' : '#ffffff',
          'theme:color12' : '#dddddd',
          'theme:color13' : '#fafafa',
          'theme:color14' : '#666666',
          'theme:color15' : '#f4f4f4',
          'theme:color16' : '#e6e7e8',
          'theme:color17' : '#8aa4af',
          'theme:color18' : '#dddddd',
          'theme:color19' : '#fafafa',
          'theme:color20' : '#f5f5f5',
          'nextdom::alertAlpha' : '100'
        };
        break;
      default:
        config = {};
        break;
    }
    return config;
}

 /**
  * Ask a new version of theme.css by ajax
  *
  * @param successFunc return function
  */
 function updateTheme(successFunc) {
     $.ajax({
         url: 'core/ajax/config.ajax.php',
         type: 'GET',
         data: {'action': 'updateTheme', 'nextdom_token': NEXTDOM_AJAX_TOKEN},
         success: successFunc
     });
 }
