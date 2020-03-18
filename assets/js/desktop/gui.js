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
(function ($) {
  $(function () {
    $(document).tooltip({selector: '[data-toggle="tooltip"]'});
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

  // Modale resize
  modalesAdjust();
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
  $('#md_modal').dialog('option', 'position', 'center');
  $('#md_modal2').dialog('option', 'position', 'center');
  $('#md_pageHelp').dialog('option', 'position', 'center');
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
          var liIndex = lists[i].getAttribute("id").slice(-1);
          lists[i].getElementsByClassName("treeview-menu")[0].style.maxHeight = $(window).height() - 50 - 70 - (44 * liIndex) + "px";
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
    if ($(window).width() < 767) {
      sidemenuDoubleHeaderPadding = 50;
    }

    // Height adjustement
    $(".sidebar-menu").css("height", $(window).height() - 50 - 70 - sidemenuBottomPadding - sidemenuDoubleHeaderPadding);
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
 * Limitation overflow menu sidebar
 *
 */
function limitTreeviewMenu() {
  var maxHeight = 0;
  $(".sidebar-menu").children(".treeview").each(function () {
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
          if (scrollValue > scrollLimit && $(window).width() >= 768) {
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
 * Automatically adjust pages to paste to the NextDom theme
 *
 */
function adjustNextDomTheme() {
  var pageContainer = $('#div_pageContainer');
  // tabs adjustement
  pageContainer.css('padding-top', '');
  if (!$('#div_pageContainer .nav-tabs').parent().hasClass('nav-tabs-custom')) {
    $('#div_pageContainer .nav-tabs').parent().addClass('nav-tabs-custom');
  }
  if (!$('.ui-widget-content').find('.nav-tabs').parent().hasClass("nav-tabs-custom")) {
    $('.ui-widget-content').find('.nav-tabs').parent().addClass("nav-tabs-custom");
  }
  if (pageContainer.find('.row-overflow').children(".row").length !== 0) {
    pageContainer.find('.row-overflow').removeClass('row');
  }

  // containers adjustement
  var needContent = pageContainer.children("section").length === 0 && pageContainer.children().children("section").length == 0 && (getUrlVars('p') != 'plan') && (getUrlVars('p') != 'view') && (getUrlVars('p') != 'plan3d');
  if (needContent) {
    if (!pageContainer.hasClass('content')) {
      pageContainer.addClass('content');
    }

  } else {
    if (pageContainer.hasClass('content')) {
      pageContainer.removeClass('content');
    }
    pageContainer.css('margin-left', '');
    pageContainer.css('margin-right', '');
  }

  // icons adjustement
  pageContainer.find('.fas.fa-sign-in').each(function () {
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
      notify("Erreur", error.message, 'error');
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
  if($('#spanNbUpdates').length){
    nextdom.update.number({
      filter: ['core', 'plugin', 'widget', 'script'],
      error: function (error) {
        notify("Erreur", error.message, 'error');
      },
      success: function (updatesData) {
        var updateSum = 0;
        for (var updateIndex in updatesData) {
          var target = $('#spanNbUpdates-' + updatesData[updateIndex].type);
          var targetCount = updatesData[updateIndex].count;
          updateSum += targetCount;
          if (targetCount == 0) {
            target.hide();
          }
          else {
            target.html(targetCount);
            target.show();
          }
        }
        if (updateSum == 0) {
          $('#spanNbUpdates').hide();
        }
        else {
          $('#spanNbUpdates').html(updateSum);
          $('#spanNbUpdates').hide();
        }
      }
    });
  }
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
        $('.notifyIconLink').attr('onclick', 'switchNotify(0);');
        notify("Core", '{{Notification activée}}', 'success');
      } else {
        $('.notifyIcon').removeClass("fa-bell").addClass("fa-bell-slash");
        $('.notifyIconLink').attr('onclick', 'switchNotify(1);');
        notify("Core", '{{Notification desactivée}}', 'success');
      }
    }
  });
}

/**
 * Ask an autosizing of textarea
 */
function taAutosize() {
  var textAreaAutosize = $('.ta_autosize');
  autosize(textAreaAutosize);
  autosize.update(textAreaAutosize);
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
 * Adjust size and position of jquery modales
 */
function modalesAdjust() {
  var modals = [$('#md_modal'), $('#md_modal2'), $('#md_pageHelp')];
  modals.forEach(function (modal) {
    if (modal.is(':ui-dialog')) {
      modal.dialog('option', 'width', getModalWidth());
      modal.dialog('option', 'height', getModalHeight());
      modal.dialog('option', 'position', {my: 'center', at: 'center', of: window});
    }
  });
}

/**
 * Calcul modal width depend of width screen
 */
function getModalWidth() {
  if (jQuery(window).width() < 1000) {
    return '96%';
  }
  return '80%';
}

/**
 * Calcul modal width depend of width screen
 */
function getModalHeight() {
  return (jQuery(window).height() - 100);
}
