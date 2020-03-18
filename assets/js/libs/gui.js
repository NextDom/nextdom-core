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
 * Fullscreen management
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
 * Automatically adjust pages to paste to the NextDom theme
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
  var needContent = pageContainer.children("section").length === 0 && pageContainer.children().children("section").length === 0 && (getUrlVars('p') != 'plan') && (getUrlVars('p') != 'view') && (getUrlVars('p') != 'plan3d');
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
 * Adjust size and position of jquery modales
 */
function modalsAdjust() {
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

/**
 * Calcul modal width depend of width screen
 */
function goOnTopDisplay() {
  var goOnTopButton = document.getElementById('bt_goOnTop');
  // GoOnTop button management
  if (goOnTopButton !== undefined && goOnTopButton !== null) {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
      goOnTopButton.style.display = 'block';
    } else {
      goOnTopButton.style.display = 'none';
    }
  }
}

/**
 * Load content in page
 * @param url
 */
function loadPageContent(url) {
  $('#div_pageContainer').empty().load(url, function () {
    // Page title formatting
    var title = getUrlVars('p');
    if (title !== false) {
      document.title = title[0].toUpperCase() + title.slice(1) + ' - NextDom';
    }

    // Inits launch
    initPage();
    // Post Inits launch
    postInitPage();
  });
}

function closeAll() {
  // Unload current page before loading
  if (typeof(unload_page) !== 'undefined') {
    unload_page();
  }

  // Closing modals
  $('#md_modal').dialog('close');
  $('#md_modal2').dialog('close');
  if (document.getElementById('mod_insertCmdValue').length !== 0) {
    $('#mod_insertCmdValue').dialog('close');
  }
  if (document.getElementById('mod_insertDataStoreValue').length !== 0) {
    $('#mod_insertDataStoreValue').dialog('close');
  }
  if (document.getElementById('mod_insertEqLogicValue').length !== 0) {
    $('#mod_insertEqLogicValue').dialog('close');
  }
  if (document.getElementById('mod_insertCronValue').length !== 0) {
    $('#mod_insertCronValue').dialog('close');
  }
  if (document.getElementById('mod_insertActionValue').length !== 0) {
    $('#mod_insertActionValue').dialog('close');
  }
  if ($("#mod_insertScenarioValue").length !== 0) {
    $('#mod_insertScenarioValue').dialog('close');
  }

  // Closing question boxs
  if (isset(bootbox)) {
    bootbox.hideAll();
  }

  // Closing alerts
  $.hideAlert();
}

/**
 * Page loading when navigation by link
 *
 * @param pageUrl url of the page to load
 * @param noPushHistory TRUE to not have the new page in history, so go back to previous page if F5
 */
function loadPage(pageUrl, noPushHistory) {
  console.log('Load page');
  // Catch a page leaving when setting not saved
  if (modifyWithoutSave) {
    if (!confirm('{{Attention vous quittez une page ayant des données modifiées non sauvegardées. Voulez-vous continuer ?}}')) {
      return;
    }
    modifyWithoutSave = false;
  }

  closeAll();

  // Navigator history management
  if (!isset(noPushHistory) || noPushHistory == false) {
    window.history.pushState('', '', pageUrl);
  }

  // Variables reset
  nextdom.cmd.update = [];
  nextdom.scenario.update = [];

  // GUI reset
  $('main').css('padding-right', '').css('padding-left', '').css('margin-right', '').css('margin-left', '');
  $('#div_pageContainer').add('#div_pageContainer *').off();
  $('.bt_pluginTemplateShowSidebar').remove();

  // Remove a equipements context
  removeContextualFunction();

  // Url ajax adjusting
  if (pageUrl.indexOf('#') === -1) {
    var url = pageUrl + '&ajax=1';
  } else {
    var anchorPos = pageUrl.lastIndexOf("#");
    var url = pageUrl.substring(0, anchorPos) + "&ajax=1" + pageUrl.substring(anchorPos)
  }

  loadPageContent(url);

  $(function () {
      nextdom.init();
  });
}
