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


nextdom.interact = function () {
};

nextdom.interact.remove = function (queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Interact', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};


nextdom.interact.get = function (queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Interact', 'byId');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.interact.save = function (queryParams) {
  var paramsRequired = ['interact'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Interact', 'save');
    ajaxParams.data['interact'] = json_encode(queryParams.interact);
    $.ajax(ajaxParams);
  }
};

nextdom.interact.regenerateInteract = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Interact', 'regenerateInteract');
  $.ajax(ajaxParams);
};

nextdom.interact.execute = function (queryParams) {
  var paramsRequired = ['query'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Interact', 'execute');
    ajaxParams.data['query'] = queryParams.query;
    $.ajax(ajaxParams);
  }
};