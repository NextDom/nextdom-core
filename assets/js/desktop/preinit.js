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

/* JS file for page and modals loading management */

// INIT, EVENT, FIRST Loading

/**
 * Event called when Ajax start request
 */
$(document).ajaxStart(function () {
  nbActiveAjaxRequest++;
  Pace.restart();
});

/**
 * Event called when Ajax stop request
 */
$(document).ajaxStop(function () {
  nbActiveAjaxRequest--;
  if (nbActiveAjaxRequest <= 0) {
    nbActiveAjaxRequest = 0;
  }
});

$(function () {
  entryPoint()
});

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

  var modals = [$('#md_modal'), $('#md_modal2')];
  modals.forEach(function (modal) {
    if (modal.is(':ui-dialog')) {
      modal.dialog('option', 'width', getModalWidth());
      modal.dialog('option', 'position', {my: 'center', at: 'center', of: window});
    }
  });
});

/**
 * Event for scrolling inside display page
 */
window.onscroll = function () {
  var goOnTopButton = document.getElementById('bt_goOnTop');

  // GoOnTop button management
  if (goOnTopButton !== undefined && goOnTopButton !== null) {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
      goOnTopButton.style.display = 'block';
    } else {
      goOnTopButton.style.display = 'none';
    }
  }

  // Left menu resize
  sideMenuResize();
  limitTreeviewMenu();
  // Header repositionning
  setHeaderPosition(false);
  // Gui automatic adjusting
  adjustNextDomTheme();
};

/**
 * Event for first page loading or F5 loading
 */
function entryPoint() {
  console.log('Entry point');
  // Todo: ???
  $.alertTrigger = function () {
    initRowOverflow();
  };

  // Todo: ???
  $.fn.modal.Constructor.prototype.enforceFocus = function () {
  };

  initSideBar();
  initTopMenu();

  // History push listener declaration
  window.addEventListener('popstate', function (event) {
    if (event.state === null) {
      return;
    }
    var url = window.location.href.split("index.php?");
    loadPage('index.php?' + url[1], true)
  });

  // Go on top fab button link event handler declaration
  $('#bt_goOnTop').click(function () {
    window.scrollTo(0, 0);
  });

  initLinkInterception();

  // Modal opening event handler
  $('body').on('show', '.modal', function () {
    document.activeElement.blur();
    $(this).find(".modal-body :input:visible:first").focus();
  });

  initBootbox();
  initModals();

  // Prevent close event handler declaration to advise user for exit without saving
  $(window).bind('beforeunload', function (e) {
    if (modifyWithoutSave) {
      return '{{Attention vous quittez une page ayant des données modifiées non sauvegardées. Voulez-vous continuer ?}}';
    }
  });

  // Summary link event handler declaration
  $('body').on('click', '.objectSummaryParent', function () {
    loadPage('index.php?v=d&p=dashboard&summary=' + $(this).data('summary') + '&object_id=' + root_object_id);
  });

  // Inits launch
  initPage();

  // Post Inits launch
  $(window).on('load', function () {
    postInitPage();
  });
}

function initBootbox() {
  // Define question box language
  if (isset(nextdom_language)) {
    bootbox.setDefaults({
      locale: nextdom_language.substr(0, 2),
    });
  }
}

function initModals() {
  var modalOptions = {
    autoOpen: false,
    modal: false,
    closeText: '',
    height: (jQuery(window).height() - 100),
    width: getModalWidth(),
    show: {effect: 'blind', duration: 200},
    resizable: false,
    open: function () {
      $('body').css({overflow: 'hidden'});
      $(this).dialog('option', 'position', {my: 'center', at: 'center', of: window});
    },
    beforeClose: function (event, ui) {
      $('body').css({overflow: 'inherit'});
      $(this).empty();
    }
  };
  // Help modal trigger declaration
  $("#md_pageHelp").dialog(modalOptions);
  $("#md_modal").dialog(modalOptions);
  $("#md_modal2").dialog(modalOptions);
}

function initLinkInterception() {
  console.log('init link interception');
  $('body').on('click', 'a', function (e) {
    if ($(this).hasClass('noOnePageLoad')
      || $(this).attr('href') == undefined
      || $(this).attr('href') == ''
      || $(this).attr('href') == '#'
      || $(this).attr('href').match('^http')
      || $(this).attr('href').match('^#')
      || $(this).attr('target') === '_blank') {
      return;
    }
    console.log('Link interception');
    $('li.dropdown.open').click();
    if ($(this).data('reload') === 'yes') {
      window.location.href = window.location.protocol + '//' + window.location.hostname + ':' + window.location.port + $(this).attr('href');
    } else {
      loadPage($(this).attr('href'));
    }
    e.preventDefault();
    e.stopPropagation();
  });
}
