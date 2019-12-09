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


nextdom.backup = function () {
};

nextdom.backup.backup = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'NextDom', 'backup');
  $.ajax(ajaxParams);
};


nextdom.backup.restoreLocal = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'NextDom', 'restore');
  ajaxParams.data['backup'] = queryParams.backup;
  $.ajax(ajaxParams);
};

nextdom.backup.remove = function (queryParams) {
  var paramsRequired = ['backup'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'NextDom', 'removeBackup');
    ajaxParams.data['backup'] = queryParams.backup;
    $.ajax(ajaxParams);
  }
};

nextdom.backup.uploadCloud = function (queryParams) {
  var paramsRequired = ['backup'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'uploadCloud');
    ajaxParams.data['backup'] = queryParams.backup;
    ajaxParams.data['repo'] = queryParams.repo;
    $.ajax(ajaxParams);
  }
};

nextdom.backup.restoreCloud = function (queryParams) {
  var paramsRequired = ['backup', 'repo'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'restoreCloud');
    ajaxParams.data['backup'] = queryParams.backup;
    ajaxParams.data['repo'] = queryParams.repo;
    $.ajax(ajaxParams);
  }
};

nextdom.backup.list = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'NextDom', 'listBackup');
  $.ajax(ajaxParams);
};