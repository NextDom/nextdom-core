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

// Page init
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
  $(document).ready(function () {
    $('pre').height($(window).height() - 300);
    $('#ul_object').height($(window).height() - 279);
    $('#ul_object').css("overflow-y", "auto");
    $('#ul_object').css("padding-right", "5px");
    sortList("#ul_object", "timing");
    $('.li_log').first().addClass('active');
    getLogDisplay($('.li_log').first().attr('data-log'));
  });
}

/**
 * Init events on the profils page
 */
function initEvents() {
  // Download button
  $('#bt_downloadLog').click(function () {
    var logFile = $('.li_log.active').attr('data-log')
    if (logFile !== undefined) {
      window.open('src/Api/downloadFile.php?pathfile=log/' + logFile, "_blank", null);
    }
  });

  // Log choose
  $(".li_log").on('click', function () {
    $('#div_logDisplay').show();
    $('#pre_globallog').empty();
    $(".li_log").removeClass('active');
    $(this).addClass('active');
    $('#bt_globalLogStopStart').removeClass('btn-success').addClass('btn-warning');
    $('#bt_globalLogStopStart').html('<div><i class="fas fa-pause spacing-right"></i>{{Pause}}</div>');
    $('#bt_globalLogStopStart').attr('data-state', 1);
    getLogDisplay($(this).attr('data-log'));
    $('#bt_downloadLog').removeAttr('disabled');
  });

  // Clear log button
  $("#bt_clearLog").on('click', function (event) {
    nextdom.log.clear({
      log: $('.li_log.active').attr('data-log'),
      success: function (data) {
        $('.li_log.active a').html($('.li_log.active').attr('data-log') + ' (0 Ko)');
        $('.li_log.active i').removeClass().addClass('fa fa-check');
        $('.li_log.active i').css('color', 'green');
        if ($('#bt_globalLogStopStart').attr('data-state') == 0) {
          $('#bt_globalLogStopStart').click();
        }
      }
    });
  });

  // Remove log button
  $("#bt_removeLog").on('click', function (event) {
    nextdom.log.remove({
      log: $('.li_log.active').attr('data-log'),
      success: function (data) {
        loadPage('index.php?v=d&p=log');
      }
    });
  });

  // Remove all log button
  $("#bt_removeAllLog").on('click', function (event) {
    bootbox.confirm("{{Êtes-vous sûr de vouloir supprimer tous les logs ?}}", function (result) {
      if (result) {
        nextdom.log.removeAll({
          error: function (error) {
            notify("Core", error.message, "error");
          },
          success: function (data) {
            loadPage('index.php?v=d&p=log');
          }
        });
      }
    });
  });

  // Display log by alphabetic sort
  $("#bt_LogAlphabetic").on('click', function (event) {
    sortList("#ul_object", "log");
    $(".li_log").removeClass('active');
    $('.li_log').first().addClass('active');
    $('#bt_LogAlphabetic').removeClass('btn-action').addClass('btn-info');
    $('#bt_LogChronologic').removeClass('btn-info').addClass('btn-action');
    getLogDisplay($('.li_log').first().attr('data-log'));
  });

  // Display log by timing sort
  $("#bt_LogChronologic").on('click', function (event) {
    sortList("#ul_object", "timing");
    $(".li_log").removeClass('active');
    $('.li_log').first().addClass('active');
    $('#bt_LogChronologic').removeClass('btn-action').addClass('btn-info');
    $('#bt_LogAlphabetic').removeClass('btn-info').addClass('btn-action');
    getLogDisplay($('.li_log').first().attr('data-log'));
  });
}

/**
 * Display note datas
 *
 * @param _id note id
 */
function getLogDisplay(_id) {
  nextdom.log.autoupdate({
    log: _id,
    display: $('#pre_globallog'),
    search: $('#in_globalLogSearch'),
    control: $('#bt_globalLogStopStart'),
  });
}

/**
 * Sort the list by data-XXX
 *
 * @param selector list element
 * @param filter XXX name of data-XXX
 */
function sortList(selector, filter) {
  var items = $(selector).find("li");
  items.sort(function (itemA, itemB) {
    var a = $(itemA).data(filter).toString().toLowerCase();
    var b = $(itemB).data(filter).toString().toLowerCase();
    if (a < b) {
      return -1;
    }
    if (a > b) {
      return 1;
    }
    return 0;
  });
  items.appendTo(selector);
}
