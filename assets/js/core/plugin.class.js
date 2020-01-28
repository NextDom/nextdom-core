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

nextdom.plugin = function() {
};

nextdom.plugin.cache = Array();

nextdom.plugin.all = function(queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.plugin.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    if (isset(nextdom.plugin.cache.all) && 'function' == typeof (queryParams.success)) {
      queryParams.success(nextdom.plugin.cache.all);
      return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plugin', 'all');
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plugin.toggle = function(queryParams) {
  nextdom.private.ajax('Plugin', 'toggle', queryParams, ['id', 'state']);
};

nextdom.plugin.get = function(queryParams) {
  nextdom.private.ajax('Plugin', 'getConf', queryParams, ['id']);
};

nextdom.plugin.getDependancyInfo = function(queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    global: false,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plugin', 'getDependancyInfo');
    ajaxParams.data['id'] = queryParams.id;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plugin.dependancyInstall = function(queryParams) {
  nextdom.private.ajax('Plugin', 'dependancyInstall', queryParams, ['id']);
};

nextdom.plugin.getDeamonInfo = function(queryParams) {
  nextdom.private.ajax('Plugin', 'getDeamonInfo', queryParams, ['id'], false,false);
};

nextdom.plugin.deamonStart = function(queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Plugin', 'deamonStart');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['debug'] = queryParams.debug || 0;
    ajaxParams.data['forceRestart'] = queryParams.forceRestart || 0;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.plugin.deamonStop = function(queryParams) {
  nextdom.private.ajax('Plugin', 'deamonStop', queryParams, ['id']);
};

nextdom.plugin.deamonChangeAutoMode = function(queryParams) {
  nextdom.private.ajax('Plugin', 'deamonChangeAutoMode', queryParams, ['id', 'mode']);
};