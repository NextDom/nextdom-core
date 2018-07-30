
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

 nextdom.view.all = function (_params) {
    var paramsRequired = [];
    var paramsSpecifics = {
        pre_success: function (data) {
            nextdom.view.cache.all = data.result;
            return data;
        }
    };
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    if (isset(nextdom.view.cache.all) && 'function' == typeof (_params.success)) {
        _params.success(nextdom.view.cache.all);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: 'all',
    };
    $.ajax(paramsAJAX);
}

nextdom.view.toHtml = function (_params) {
    if (_params.version == 'mobile') {
        _params.version = 'mview';
    }
    if (_params.version == 'dashboard') {
        _params.version = 'dview';
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
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: "get",
        id: ($.isArray(_params.id)) ? json_encode(_params.id) : _params.id,
        version: _params.version,
    };
    $.ajax(paramsAJAX);
}

nextdom.view.handleViewAjax = function (_params) {
    var result = {html: '', scenario: [], cmd: [], eqLogic: []};
    for (var i in _params.view.viewZone) {
        var viewZone = _params.view.viewZone[i];
        
        result.html += '<div class="col-xs-12 col-sm-'+init(viewZone.configuration.zoneCol,12)+'">';
        
        result.html += '<legend class="div_viewZone" style="color : #716b7a" data-zone_id="' + viewZone.id + '">' + viewZone.name + '</legend>';
        var div_id = 'div_viewZone' + viewZone.id + Date.now();
        /*         * *****************viewZone widget***************** */
        if (viewZone.type == 'widget') {
            result.html += '<div id="' + div_id + '" class="eqLogicZone" data-viewZone-id="'+viewZone.id+'">';
            for (var j in viewZone.viewData) {
                var viewData = viewZone.viewData[j];
                result.html += viewData.html;
                result[viewData.type].push(viewData.id);
            }
            result.html += '</div>';
        }else if (viewZone.type == 'graph') {
            result.html += '<div id="' + div_id + '" class="chartContainer">';
            result.html += '<script>';
            for (var j in viewZone.viewData) {
                var viewData = viewZone.viewData[j];
                var configuration = json_encode(viewData.configuration);
                result.html += 'nextdom.history.drawChart({cmd_id : ' + viewData.link_id + ',el : "' + div_id + '",dateRange : "' + viewZone.configuration.dateRange + '",option : jQuery.parseJSON("' + configuration.replace(/\"/g, "\\\"") + '")});';
            }
            result.html += '</script>';
            result.html += '</div>';
        }else if (viewZone.type == 'table') {
           result.html += viewZone.html;; 
       }
       result.html += '</div>';
   }
   return result;
}


nextdom.view.remove = function (_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: 'remove',
        id: _params.id,
    };
    $.ajax(paramsAJAX);
}


nextdom.view.save = function (_params) {
    var paramsRequired = ['id', 'view'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    console.log(_params);
    paramsAJAX.data = {
        action: 'save',
        view_id: _params.id,
        view: json_encode(_params.view),
    };
    console.log(paramsAJAX);
    $.ajax(paramsAJAX);
}

nextdom.view.get = function (_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: 'get',
        id: _params.id,
    };
    $.ajax(paramsAJAX);
}

nextdom.view.setEqLogicOrder = function (_params) {
    var paramsRequired = ['eqLogics'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: 'setEqLogicOrder',
        eqLogics: json_encode(_params.eqLogics),
    };
    $.ajax(paramsAJAX);
}

nextdom.view.setOrder = function(_params) {
    var paramsRequired = ['views'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: 'setOrder',
        views: json_encode(_params.views)
    };
    $.ajax(paramsAJAX);
};


nextdom.view.removeImage = function (_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/view.ajax.php';
    paramsAJAX.data = {
        action: 'removeImage',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};