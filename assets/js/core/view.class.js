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


nextdom.view = function () {
};

nextdom.view.cache = Array();

nextdom.view.all = function (queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.view.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    if (isset(nextdom.view.cache.all) && 'function' == typeof (queryParams.success)) {
      queryParams.success(nextdom.view.cache.all);
      return;
    }
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'all');
    ajaxParams.data['action'] = 'all';
    $.ajax(ajaxParams);
  }
};

nextdom.view.toHtml = function (queryParams) {
  if (queryParams.version == 'mobile') {
    queryParams.version = 'mview';
  }
  if (queryParams.version == 'dashboard') {
    queryParams.version = 'dview';
  }
  var paramsRequired = ['id', 'version'];
  var paramsSpecifics = {
    pre_success: function (data) {
      result = nextdom.view.handleViewAjax({view: data.result});
      result.raw = data.result;
      data.result = result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'get');
    ajaxParams.data['id'] = ($.isArray(queryParams.id)) ? json_encode(queryParams.id) : queryParams.id;
    ajaxParams.data['version'] = queryParams.version;
    ajaxParams.data['html'] = true;
    $.ajax(ajaxParams);
  }
};

nextdom.view.handleViewAjax = function (queryParams) {
  var result = {html: '', scenario: [], cmd: [], eqLogic: []};
  for (var i in queryParams.view.viewZone) {
    var viewZone = queryParams.view.viewZone[i];

    result.html += '<div class="col-xs-12 col-sm-' + init(viewZone.configuration.zoneCol, 12) + '">';

    result.html += '<legend class="div_viewZone" style="color : #716b7a" data-zone_id="' + viewZone.id + '">' + viewZone.name + '</legend>';
    var div_id = 'div_viewZone' + viewZone.id + Date.now();
    /*         * *****************viewZone widget***************** */
    if (viewZone.type == 'widget') {
      result.html += '<div id="' + div_id + '" class="eqLogicZone" data-viewZone-id="' + viewZone.id + '">';
      for (var j in viewZone.viewData) {
        var viewData = viewZone.viewData[j];
        result.html += viewData.html;
        result[viewData.type].push(viewData.id);
      }
      result.html += '</div>';
    } else if (viewZone.type == 'graph') {
      result.html += '<div id="' + div_id + '" class="chartContainer">';
      result.html += '<script>';
      for (var j in viewZone.viewData) {
        var viewData = viewZone.viewData[j];
        var configuration = json_encode(viewData.configuration);
        result.html += 'nextdom.history.drawChart({noError:true,cmd_id : ' + viewData.link_id + ',el : "' + div_id + '",dateRange : "' + viewZone.configuration.dateRange + '",option : jQuery.parseJSON("' + configuration.replace(/\"/g, "\\\"") + '")});';
      }
      result.html += '</script>';
      result.html += '</div>';
    } else if (viewZone.type == 'table') {
      result.html += viewZone.html;
      ;
    }
    result.html += '</div>';
  }
  return result;
};


nextdom.view.remove = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};


nextdom.view.save = function (queryParams) {
  var paramsRequired = ['id', 'view'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'save');
    ajaxParams.data['view_id'] = queryParams.id;
    ajaxParams.data['view'] = json_encode(queryParams.view);
    $.ajax(ajaxParams);
  }
};

nextdom.view.get = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'get');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.view.setEqLogicOrder = function (queryParams) {
  var paramsRequired = ['eqLogics'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'setEqLogicOrder');
    ajaxParams.data['eqLogics'] = json_encode(queryParams.eqLogics);
    $.ajax(ajaxParams);
  }
};

nextdom.view.setOrder = function (queryParams) {
  var paramsRequired = ['views'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'setOrder');
    ajaxParams.data['views'] = json_encode(queryParams.views);
    $.ajax(ajaxParams);
  }
};


nextdom.view.removeImage = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'View', 'removeImage');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};