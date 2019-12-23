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


nextdom.plan = function () {
};

nextdom.plan.cache = Array();

nextdom.plan.remove = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'remove');
  ajaxParams.data['id'] = queryParams.id || '';
  ajaxParams.data['link_type'] = queryParams.link_type || '';
  ajaxParams.data['link_id'] = queryParams.link_id || '';
  ajaxParams.data['planHeader_id'] = queryParams.planHeader_id || '';
  $.ajax(ajaxParams);
};

nextdom.plan.execute = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {global: false};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'execute');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};


nextdom.plan.save = function (queryParams) {
  var paramsRequired = ['plans'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'save');
    ajaxParams.data['plans'] = json_encode(queryParams.plans);
    $.ajax(ajaxParams);
  }
};


nextdom.plan.byId = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Plan', 'get', queryParams, ['id']);
};

nextdom.plan.getObjectPlan = function (queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'getObjectPlan');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['version'] = queryParams.version || 'dplan';
    $.ajax(ajaxParams);
  }
};

nextdom.plan.create = function (queryParams) {
  var paramsRequired = ['plan'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'create');
    ajaxParams.data['plan'] = json_encode(queryParams.plan);
    ajaxParams.data['version'] = queryParams.version;
    $.ajax(ajaxParams);
  }
};

nextdom.plan.copy = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'copy');
  ajaxParams.data['id'] = queryParams.id || '';
  ajaxParams.data['link_type'] = queryParams.link_type || '';
  ajaxParams.data['link_id'] = queryParams.link_id || '';
  ajaxParams.data['planHeader_id'] = queryParams.planHeader_id || '';
  $.ajax(ajaxParams);
};

nextdom.plan.byPlanHeader = function (queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'planHeader');
    ajaxParams.data['planHeader_id'] = queryParams.id;
    ajaxParams.data['noHtml'] = queryParams.noHtml;
    ajaxParams.data['version'] = queryParams.version || 'dplan';
    $.ajax(ajaxParams);
  }
};

nextdom.plan.removeImageHeader = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Plan', 'removeImageHeader', queryParams, ['planHeader_id']);
};

nextdom.plan.saveHeader = function (queryParams) {
  var paramsRequired = ['planHeader'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'savePlanHeader');
    ajaxParams.data['planHeader'] = json_encode(queryParams.planHeader);
    $.ajax(ajaxParams);
  }
};

nextdom.plan.copyHeader = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Plan', 'copyPlanHeader', queryParams, ['id', 'name']);
};

nextdom.plan.removeHeader = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Plan', 'removePlanHeader', queryParams, ['id']);
};

nextdom.plan.getHeader = function (queryParams) {
  nextdom.private.simpleAjaxWithRequiredParams('Plan', 'getPlanHeader', queryParams, ['id', 'code']);
};

nextdom.plan.allHeader = function (queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.plan.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    if (isset(nextdom.plan.cache.all)) {
      params.success(nextdom.plan.cache.all);
      return;
    }
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Plan', 'allHeader');
    $.ajax(ajaxParams);
  }
};
