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


nextdom.report = function () {
};


nextdom.report.list = function (queryParams) {
  var paramsRequired = ['type', 'id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Report', 'list');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['type'] = queryParams.type;
    $.ajax(ajaxParams);
  }
};

nextdom.report.get = function (queryParams) {
  var paramsRequired = ['type', 'id', 'report'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Report', 'get');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['report'] = queryParams.report;
    $.ajax(ajaxParams);
  }
};

nextdom.report.remove = function (queryParams) {
  var paramsRequired = ['type', 'id', 'report'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Report', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['report'] = queryParams.report;
    $.ajax(ajaxParams);
  }
};

nextdom.report.removeAll = function (queryParams) {
  var paramsRequired = ['type', 'id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Report', 'removeAll');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['type'] = queryParams.type;
    $.ajax(ajaxParams);
  }
};
