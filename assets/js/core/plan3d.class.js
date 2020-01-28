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

nextdom.plan3d = function() {
};

nextdom.plan3d.cache = Array();

nextdom.plan3d.remove = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan3d', 'remove');
  ajaxParams.data['id'] = queryParams.id || '';
  ajaxParams.data['link_type'] = queryParams.link_type || '';
  ajaxParams.data['link_id'] = queryParams.link_id || '';
  ajaxParams.data['plan3dHeader_id'] = queryParams.plan3dHeader_id || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.plan3d.save = function(queryParams) {
  var paramsRequired = ['plan3ds'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan3d', 'save');
    ajaxParams.data['plan3ds'] = json_encode(queryParams.plan3ds);
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan3d.byId = function(queryParams) {
  nextdom.private.ajax('Plan3d', 'get', queryParams, ['id']);
};

nextdom.plan3d.byName = function(queryParams) {
  var paramsRequired = ['name', 'plan3dHeader_id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan3d', 'byName');
    ajaxParams.data['name'] = queryParams.name;
    ajaxParams.data['plan3dHeader_id'] = queryParams.plan3dHeader_id;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan3d.byplan3dHeader = function(queryParams) {
  nextdom.private.ajax('Plan3d', 'plan3dHeader', queryParams, ['plan3dHeader_id']);
};

nextdom.plan3d.saveHeader = function(queryParams) {
  var paramsRequired = ['plan3dHeader'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan3d', 'saveplan3dHeader');
    ajaxParams.data['plan3dHeader'] = json_encode(queryParams.plan3dHeader);
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plan3d.removeHeader = function(queryParams) {
  nextdom.private.ajax('Plan3d', 'removeplan3dHeader', queryParams, ['id']);
};

nextdom.plan3d.getHeader = function(queryParams) {
  nextdom.private.ajax('Plan3d', 'getplan3dHeader', queryParams, ['id', 'code']);
};

nextdom.plan3d.allHeader = function(queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.plan3d.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (isset(nextdom.plan3d.cache.all)) {
      params.success(nextdom.plan3d.cache.all);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plan3d', 'allHeader');
    nextdom.private.ajaxCall(ajaxParams);
  }
};
