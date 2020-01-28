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

nextdom.log = function() {
};

nextdom.log.timeout = null;
nextdom.log.currentAutoupdate = [];

nextdom.log.list = function(queryParams) {
  nextdom.private.ajax('Log', 'list', queryParams, false, false, queryParams.global || true);
};

nextdom.log.removeAll = function(queryParams) {
  nextdom.private.ajax('Log', 'removeAll', queryParams, false, false, queryParams.global || true);
};

nextdom.log.get = function(queryParams) {
  nextdom.private.ajax('Log', 'get', queryParams, ['log'], false, queryParams.global || true);
};

nextdom.log.remove = function(queryParams) {
  nextdom.private.ajax('Log', 'remove', queryParams, ['log'], false, queryParams.global || true);
};

nextdom.log.clear = function(queryParams) {
  nextdom.private.ajax('Log', 'clear', queryParams, ['log'], false, queryParams.global || true);
};

nextdom.log.autoupdate = function(queryParams) {
  if (!isset(queryParams.callCount)) {
    queryParams.callCount = 0;
  }
  if (!isset(queryParams.log)) {
    console.log('[nextdom.log.autoupdate] No logfile');
    return;
  }
  if (!isset(queryParams.display)) {
    console.log('[nextdom.log.autoupdate] No display');
    return;
  }
  if (!queryParams['display'].is(':visible')) {
    return;
  }
  if (queryParams.callCount > 0 && isset(queryParams['control']) && queryParams['control'].attr('data-state') != 1) {
    return;
  }
  if (queryParams.callCount > 0 && isset(nextdom.log.currentAutoupdate[queryParams.display.uniqueId().attr('id')]) && nextdom.log.currentAutoupdate[queryParams.display.uniqueId().attr('id')].log != queryParams.log) {
    return;
  }
  if (queryParams.callCount === 0) {
    if (isset(queryParams.default_search)) {
      queryParams['search'].value(queryParams.default_search);
    } else {
      queryParams['search'].value('');
    }
    queryParams.display.scrollTop(queryParams.display.height() + 200000);
    if (queryParams['control'].attr('data-state') == 0) {
      queryParams['control'].attr('data-state', 1);
    }
    queryParams['control'].off('click').on('click', function () {
      if ($(this).attr('data-state') == 1) {
        $(this).attr('data-state', 0);
        $(this).removeClass('btn-warning').addClass('btn-success');
        $(this).html('<i class="fas fa-play spacing-right"></i>{{Reprendre}}');
      } else {
        $(this).removeClass('btn-success').addClass('btn-warning');
        $(this).html('<i class="fas fa-pause spacing-right"></i>{{Pause}}');
        $(this).attr('data-state', 1);
        queryParams.display.scrollTop(queryParams.display.height() + 200000);
        nextdom.log.autoupdate(queryParams);
      }
    });

    queryParams['search'].off('keypress').on('keypress', function () {
      if (queryParams['control'].attr('data-state') == 0) {
        queryParams['control'].trigger('click');
      }
    });
  }
  queryParams.callCount++;
  nextdom.log.currentAutoupdate[queryParams.display.uniqueId().attr('id')] = {log: queryParams.log};

  if (queryParams.callCount > 0 && (queryParams.display.scrollTop() + queryParams.display.innerHeight() + 1) < queryParams.display[0].scrollHeight) {
    if (queryParams['control'].attr('data-state') == 1) {
      queryParams['control'].trigger('click');
    }
    return;
  }
  nextdom.log.get({
    log: queryParams.log,
    slaveId: queryParams.slaveId,
    global: (queryParams.callCount === 1),
    success: function (result) {
      var log = '';
      var regex = /<br\s*[\/]?>/gi;
      if ($.isArray(result)) {
        for (var i in result.reverse()) {
          if (!isset(queryParams['search']) || queryParams['search'].value() == '' || result[i].toLowerCase().indexOf(queryParams['search'].value().toLowerCase()) != -1) {
            log += $.trim(result[i]) + "\n";
          }
        }
      }
      queryParams.display.text(log);
      queryParams.display.scrollTop(queryParams.display.height() + 200000);
      if (nextdom.log.timeout !== null) {
        clearTimeout(jeedom.log.timeout);
      }
      nextdom.log.timeout = setTimeout(function () {
        nextdom.log.autoupdate(queryParams)
      }, 1000);
    },
    error: function () {
      if (nextdom.log.timeout !== null) {
        clearTimeout(jeedom.log.timeout);
      }
      nextdom.log.timeout = setTimeout(function () {
        nextdom.log.autoupdate(queryParams)
      }, 1000);
    },
  });
};
