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

nextdom.note = function () {
};

nextdom.note.remove = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Note', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.note.byId = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Note', 'byId');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.note.save = function (queryParams) {
  var paramsRequired = ['note'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Note', 'save');
    ajaxParams.data['note'] = json_encode(queryParams.note);
    $.ajax(ajaxParams);
  }
};

nextdom.note.all = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Note', 'all');
  $.ajax(ajaxParams);
};
