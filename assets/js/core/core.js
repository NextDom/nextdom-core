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
$(function () {
  if (!$.mobile) {
    nextdom.init();
  }
});

function getTemplate(_folder, _version, _filename, _replace) {
  if (_folder == 'core') {
    var path = _folder + '/template/' + _version + '/' + _filename;
  } else {
    var path = 'plugins/' + _folder + '/desktop/template/' + _version + '/' + _filename;
  }
  var template = '';
  $.ajax({
    type: 'POST',
    url: path,
    async: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (isset(_replace) && _replace != null) {
        for (i in _replace) {
          var reg = new RegExp(i, "g");
          data = data.replace(reg, _replace[i]);
        }
      }
      template = data;
    }
  });
  return template;
}

function handleAjaxError(_request, _status, _error) {
  if (_request.status != '0') {
    if (init(_request.responseText, '') != '') {
      notify('Erreur', _request.responseText, 'error');
    } else {
      notify('Erreur', _request.status + ' : ' + _error, 'error');
    }
  }
}

function init(_value, _default) {
  if (!isset(_default)) {
    _default = '';
  }
  if (!isset(_value)) {
    return _default;
  }
  return _value;
}

function getUrlVars(_key) {
  var vars = [], hash, nbVars = 0;
  var hashes = window.location.search.replace('?', '').split('&');
  for (var i = 0; i < hashes.length; i++) {
    if (hashes[i] !== "" && hashes[i] !== "?") {
      hash = hashes[i].split('=');
      nbVars++;
      vars[hash[0]] = hash[1];
      if (isset(_key) && _key == hash[0]) {
        return hash[1];
      }
    }
  }
  if (isset(_key)) {
    return false;
  }
  vars.length = nbVars;
  return vars;
}

function initTooltips() {

}

function getDeviceType() {
  // Non utilisé
}
