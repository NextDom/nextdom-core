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

nextdom.object = function() {
};

nextdom.object.cache = Array();

if (!isset(nextdom.object.cache.getEqLogic)) {
  nextdom.object.cache.getEqLogic = Array();
}

if (!isset(nextdom.object.cache.byId)) {
  nextdom.object.cache.byId = Array();
}

nextdom.object.getEqLogic = function(queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.object.cache.getEqLogic[queryParams.id] = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (isset(nextdom.object.cache.getEqLogic[params.id])) {
      params.success(nextdom.object.cache.getEqLogic[params.id]);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'EqLogic', 'listByObject');
    ajaxParams.data['object_id'] = queryParams.id;
    ajaxParams.data['onlyEnable'] = queryParams.onlyEnable || 0;
    ajaxParams.data['orderByName'] = queryParams.orderByName || 0;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.all = function(queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (!isset(queryParams.onlyHasEqLogic)) {
        nextdom.object.cache.all = data.result;
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (isset(nextdom.object.cache.all) && !isset(queryParams.onlyHasEqLogic)) {
      params.success(nextdom.object.cache.all);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Object', 'all');
    ajaxParams.data['onlyHasEqLogic'] = queryParams.onlyHasEqLogic || 0;
    ajaxParams.data['searchOnchild'] = queryParams.searchOnchild || 0;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.toHtml = function(queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Object', 'toHtml');
    ajaxParams.data['id'] = ($.isArray(queryParams.id)) ? json_encode(queryParams.id) : queryParams.id;
    ajaxParams.data['version'] = queryParams.version || 'dashboard';
    ajaxParams.data['category'] = queryParams.category || 'all';
    ajaxParams.data['summary'] = queryParams.summary || '';
    ajaxParams.data['tag'] = queryParams.tag || 'all';
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.remove = function(queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.object.cache.all)) {
        delete nextdom.object.cache.all;
      }
      if (isset(nextdom.object.cache.getEqLogic[queryParams.id])) {
        delete nextdom.object.cache.getEqLogic[queryParams.id];
      }
      if (isset(nextdom.object.cache.byId[queryParams.id])) {
        delete nextdom.object.cache.byId[queryParams.id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Object', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.save = function(queryParams) {
  var paramsRequired = ['object'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.object.cache.all)) {
        delete nextdom.object.cache.all;
      }
      if (isset(nextdom.object.cache.getEqLogic[data.result.id])) {
        delete nextdom.object.cache.getEqLogic[data.result.id];
      }
      if (isset(nextdom.object.cache.byId[data.result.id])) {
        delete nextdom.object.cache.byId[data.result.id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Object', 'save');
    ajaxParams.data['object'] = json_encode(queryParams.object);
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.byId = function(queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.object.cache.byId[data.result.id] = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (isset(nextdom.object.cache.byId[params.id]) && init(queryParams.cache, true) == true) {
      params.success(nextdom.object.cache.byId[params.id]);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Object', 'byId');
    ajaxParams.data['id'] = queryParams.id;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.setOrder = function(queryParams) {
  nextdom.private.ajax('Object', 'setOrder', queryParams, ['objects'], true);
};

nextdom.object.summaryUpdate = function(queryParams) {
  var objects = {};
  var sends = {};
  for (var i in queryParams) {
    var object = $('.objectSummary' + queryParams[i].object_id);
    if (object.html() === undefined || object.attr('data-version') === undefined) {
      continue;
    }
    if (isset(queryParams[i]['keys'])) {
      var updated = false;
      for (var j in queryParams[i]['keys']) {
        var keySpan = object.find('.objectSummary' + j);
        if (keySpan.html() !== undefined) {
          updated = true;
          if (keySpan.closest('.objectSummaryParent').attr('data-displayZeroValue') == 0 && queryParams[i]['keys'][j]['value'] === 0) {
            keySpan.closest('.objectSummaryParent').hide();
            continue;
          }
          if (queryParams[i]['keys'][j]['value'] === null) {
            continue;
          }
          keySpan.closest('.objectSummaryParent').show();
          keySpan.empty().append(queryParams[i]['keys'][j]['value']);
        }
      }
      if (updated) {
        continue;
      }
    }
    objects[queryParams[i].object_id] = {object: object, version: object.attr('data-version')};
    sends[queryParams[i].object_id] = {version: object.attr('data-version')};
  }
  if (Object.keys(objects).length == 0) {
    return;
  }
  var paramsRequired = [];
  var paramsSpecifics = {
    global: false,
    success: function (result) {
      for (var i in result) {
        objects[i].object.replaceWith($(result[i].html));
        if ($('.objectSummary' + i).closest('.objectSummaryHide') != []) {
          if ($(result[i].html).html() == '') {
            $('.objectSummary' + i).closest('.objectSummaryHide').hide();
          } else {
            $('.objectSummary' + i).closest('.objectSummaryHide').show();
          }
        }
      }
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Object', 'getSummaryHtml');
    ajaxParams.data['ids'] = queryParams.ids;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.object.getImgPath = function(queryParams) {
  nextdom.object.byId({
    id: queryParams.id,
    global: false,
    async: false,
    error: function (data) {
    },
    success: function (data) {
      if (!isset(data.img)) {
        return '';
      }
      queryParams.success(data.img);
    }
  });
};

nextdom.object.removeImage = function(queryParams) {
  nextdom.private.ajax('Object', 'removeImage', queryParams, ['id']);
};