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

nextdom.config = function () {
};


nextdom.config.save = function (queryParams) {
  var paramsRequired = ['configuration'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Config', 'addKey');
    ajaxParams.data['value'] = json_encode(queryParams.configuration);
    ajaxParams.data['plugin'] = queryParams.plugin || 'core';
    $.ajax(ajaxParams);
  }
};

nextdom.config.load = function (queryParams) {
  var paramsRequired = ['configuration'];
  var paramsSpecifics = {global: queryParams.global || true};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Config', 'getKey');
    ajaxParams.data['key'] = ($.isArray(queryParams.configuration) || $.isPlainObject(queryParams.configuration)) ? json_encode(queryParams.configuration) : queryParams.configuration;
    ajaxParams.data['plugin'] = queryParams.plugin || 'core';
    ajaxParams.data['convertToHumanReadable'] = queryParams.convertToHumanReadable || false;
    $.ajax(ajaxParams);
  }
};

nextdom.config.remove = function (queryParams) {
  var paramsRequired = ['configuration'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Config', 'removeKey');
    ajaxParams.data['action'] = 'removeKey';
    ajaxParams.data['key'] = ($.isArray(queryParams.configuration) || $.isPlainObject(queryParams.configuration)) ? json_encode(queryParams.configuration) : queryParams.configuration;
    ajaxParams.data['plugin'] = queryParams.plugin || 'core';
    $.ajax(ajaxParams);
  }
};