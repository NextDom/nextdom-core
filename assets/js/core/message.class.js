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


nextdom.message = function () {
};

nextdom.message.cache = Array();

nextdom.message.all = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Message', 'all');
  ajaxParams.data['plugin'] = queryParams.plugin || '';
  $.ajax(ajaxParams);
};

nextdom.message.remove = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Message', 'removeMessage');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.message.clear = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Message', 'clearMessage');
  ajaxParams.data['plugin'] = queryParams.plugin || '';
  $.ajax(ajaxParams);
};

nextdom.message.number = function (queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    global: false,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Message', 'nbMessage');
    $.ajax(ajaxParams);
  }
};