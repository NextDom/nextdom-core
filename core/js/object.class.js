
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

nextdom.object.getEqLogic = function(_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {
        pre_success: function(data) {
            nextdom.object.cache.getEqLogic[_params.id] = data.result;
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
    if (isset(nextdom.object.cache.getEqLogic[params.id])) {
        params.success(nextdom.object.cache.getEqLogic[params.id]);
        return;
    }
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/eqLogic.ajax.php';
    paramsAJAX.data = {
        action: "listByObject",
        object_id: _params.id,
        onlyEnable: _params.onlyEnable || 0,
        orderByName : _params.orderByName || 0
    };
    $.ajax(paramsAJAX);
};

nextdom.object.all = function(_params) {
    var paramsRequired = [];
    var paramsSpecifics = {
        pre_success: function(data) {
            if(!isset(_params.onlyHasEqLogic)){
                nextdom.object.cache.all = data.result;
            }
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
    if (isset(nextdom.object.cache.all) && !isset(_params.onlyHasEqLogic)) {
        params.success(nextdom.object.cache.all);
        return;
    }
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'all',
        onlyHasEqLogic : _params.onlyHasEqLogic || '',
        searchOnchild : _params.searchOnchild || '1'
    };
    $.ajax(paramsAJAX);
};

nextdom.object.toHtml = function(_params) {
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
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'toHtml',
        id: ($.isArray(_params.id)) ? json_encode(_params.id) : _params.id,
        version: _params.version || 'dashboard',
        category :  _params.category || 'all',
        summary :  _params.summary || '',
        tag :  _params.tag || 'all',
    };
    $.ajax(paramsAJAX);
};

nextdom.object.remove = function(_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {
        pre_success: function(data) {
            if (isset(nextdom.object.cache.all)) {
                delete nextdom.object.cache.all;
            }
            if (isset(nextdom.object.cache.getEqLogic[_params.id])) {
                delete nextdom.object.cache.getEqLogic[_params.id];
            }
            if(isset(nextdom.object.cache.byId[_params.id])){
                delete nextdom.object.cache.byId[_params.id];
            }
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
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'remove',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};

nextdom.object.save = function(_params) {
    var paramsRequired = ['object'];
    var paramsSpecifics = {
        pre_success: function(data) {
            if (isset(nextdom.object.cache.all)) {
                delete nextdom.object.cache.all;
            }
            if (isset(nextdom.object.cache.getEqLogic[data.result.id])) {
                delete nextdom.object.cache.getEqLogic[data.result.id];
            }
            if(isset(nextdom.object.cache.byId[data.result.id])){
                delete nextdom.object.cache.byId[data.result.id];
            }
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
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'save',
        object: json_encode(_params.object),
    };
    $.ajax(paramsAJAX);
};


nextdom.object.byId = function(_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {
        pre_success: function(data) {
            nextdom.object.cache.byId[data.result.id] = data.result;
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
    if (isset(nextdom.object.cache.byId[params.id]) && init(_params.cache,true) == true) {
        params.success(nextdom.object.cache.byId[params.id]);
        return;
    }
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'byId',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};

nextdom.object.setOrder = function(_params) {
    var paramsRequired = ['objects'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'setOrder',
        objects: json_encode(_params.objects)
    };
    $.ajax(paramsAJAX);
};


nextdom.object.summaryUpdate = function(_params) {
    var objects = {};
    var sends = {};
    for(var i in _params){
        var object = $('.objectSummary' + _params[i].object_id);
        if (object.html() == undefined || object.attr('data-version') == undefined) {
            continue;
        }
        if(isset(_params[i]['keys'])){
            var updated = false;
            for(var j in _params[i]['keys']){
                var keySpan = object.find('.objectSummary'+j);
                if(keySpan.html() != undefined){
                    updated = true;
                    if(keySpan.closest('.objectSummaryParent').attr('data-displayZeroValue') == 0 && _params[i]['keys'][j]['value'] === 0){
                        keySpan.closest('.objectSummaryParent').hide();
                        continue;
                    }
                    if(_params[i]['keys'][j]['value'] === null){
                        continue;
                    }
                    keySpan.closest('.objectSummaryParent').show();
                    keySpan.empty().append(_params[i]['keys'][j]['value']);
                }
            }
            if(updated){
                continue;
            }
        }
        objects[_params[i].object_id] = {object : object, version : object.attr('data-version')};
        sends[_params[i].object_id] = {version : object.attr('data-version')};
    }
    if (Object.keys(objects).length == 0){
        return;
    }
    var paramsRequired = [];
    var paramsSpecifics = {
        global: false,
        success: function (result) {
            for(var i in result){
                objects[i].object.replaceWith($(result[i].html));
                if($('.objectSummary' + i).closest('.objectSummaryHide') != []){
                    if($(result[i].html).html() == ''){
                        $('.objectSummary' + i).closest('.objectSummaryHide').hide();
                    }else{
                        $('.objectSummary' + i).closest('.objectSummaryHide').show();
                    }
                }
            }
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
    paramsAJAX.url = 'core/ajax/object.ajax.php';
    paramsAJAX.data = {
        action: 'getSummaryHtml',
        ids: json_encode(sends),
    };
    $.ajax(paramsAJAX);
};