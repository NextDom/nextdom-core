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


nextdom.update = function () {
};


nextdom.update.doAll = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Update', 'updateAll');
  ajaxParams.data['options'] = json_encode(queryParams.options) || '';
  $.ajax(ajaxParams);
};

nextdom.update.do = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Update', 'update', queryParams, ['id']);
};

nextdom.update.remove = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Update', 'remove', queryParams, ['id']);
};

nextdom.update.checkAll = function (queryParams) {
  nextdom.private.simpleAjax('Update', 'checkAllUpdate', queryParams);
};

nextdom.update.check = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Update', 'checkUpdate', queryParams, ['id']);
};

nextdom.update.get = function (queryParams) {
  nextdom.private.simpleAjax('Update', 'all', queryParams);
};

nextdom.update.save = function (queryParams) {
  var paramsRequired = ['update'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Update', 'save');
    ajaxParams.data['update'] = json_encode(queryParams.update);
    $.ajax(ajaxParams);
  }
};

nextdom.update.saves = function (queryParams) {
  var paramsRequired = ['updates'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Update', 'saves');
    ajaxParams.data['updates'] = json_encode(queryParams.updates);
    $.ajax(ajaxParams);
  }
};

nextdom.update.number = function (queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    global: false,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Update', 'nbUpdate');
    $.ajax(ajaxParams);
  }
};