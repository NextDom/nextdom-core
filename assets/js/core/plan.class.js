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

nextdom.plan = function() {
};

nextdom.plan.cache = Array();

nextdom.plan.remove = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'remove');
  ajaxParams.data['id'] = queryParams.id || '';
  ajaxParams.data['link_type'] = queryParams.link_type || '';
  ajaxParams.data['link_id'] = queryParams.link_id || '';
  ajaxParams.data['planHeader_id'] = queryParams.planHeader_id || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.plan.execute = function(queryParams) {
  nextdom.private.ajax('Plan', 'execute', queryParams, ['id'], false, false);
};

nextdom.plan.save = function(queryParams) {
  nextdom.private.ajax('Plan', 'save', queryParams, ['plans'], true, queryParams.global || true);
};

nextdom.plan.byId = function(queryParams) {
  nextdom.private.ajax('Plan', 'get', queryParams, ['id']);
};

nextdom.plan.getObjectPlan = function(queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'getObjectPlan');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['version'] = queryParams.version || 'dplan';
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan.create = function(queryParams) {
  var paramsRequired = ['plan'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'create');
    ajaxParams.data['plan'] = json_encode(queryParams.plan);
    ajaxParams.data['version'] = queryParams.version;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan.copy = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'copy');
  ajaxParams.data['id'] = queryParams.id || '';
  ajaxParams.data['link_type'] = queryParams.link_type || '';
  ajaxParams.data['link_id'] = queryParams.link_id || '';
  ajaxParams.data['planHeader_id'] = queryParams.planHeader_id || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.plan.byPlanHeader = function(queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'planHeader');
    ajaxParams.data['planHeader_id'] = queryParams.id;
    ajaxParams.data['noHtml'] = queryParams.noHtml;
    ajaxParams.data['version'] = queryParams.version || 'dplan';
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan.removeImageHeader = function(queryParams) {
  var paramsRequired = ['planHeader_id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'removeImageHeader');
    ajaxParams.data['id'] = queryParams.planHeader_id;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan.saveHeader = function(queryParams) {
  nextdom.private.ajax('Plan', 'savePlanHeader', queryParams, ['planHeader'], true);
};

nextdom.plan.copyHeader = function(queryParams) {
  nextdom.private.ajax('Plan', 'copyPlanHeader', queryParams, ['id', 'name']);
};

nextdom.plan.removeHeader = function(queryParams) {
  nextdom.private.ajax('Plan', 'removePlanHeader', queryParams, ['id']);
};

nextdom.plan.getHeader = function(queryParams) {
  nextdom.private.ajax('Plan', 'getPlanHeader', queryParams, ['id', 'code']);
};

nextdom.plan.allHeader = function(queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.plan.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (isset(nextdom.plan.cache.all)) {
      params.success(nextdom.plan.cache.all);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan', 'allHeader');
    nextdom.private.ajaxCall(ajaxParams);
  }
};
