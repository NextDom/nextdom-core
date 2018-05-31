
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


 nextdom.scenario = function () {
 };

 nextdom.scenario.cache = Array();

 if (!isset(nextdom.scenario.cache.html)) {
    nextdom.scenario.cache.html = Array();
}
if (!isset(nextdom.scenario.update)) {
    nextdom.scenario.update = Array();
}

nextdom.scenario.all = function (_params) {
    var paramsRequired = [];
    var paramsSpecifics = {
        pre_success: function (data) {
            nextdom.scenario.cache.all = data.result;
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
    if (isset(nextdom.scenario.cache.all) && nextdom.scenario.cache.all != null && init(_params.nocache,false) == false) {
        params.success(nextdom.scenario.cache.all);
        return;
    }
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'all',
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.saveAll = function (_params) {
    var paramsRequired = ['scenarios'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'saveAll',
        scenarios: json_encode(_params.scenarios),
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.toHtml = function (_params) {
    var paramsRequired = ['id', 'version'];
    var paramsSpecifics = {
        pre_success: function (data) {
            if (_params.id == 'all' || $.isArray(_params.id)) {
                for (var i in data.result) {
                    nextdom.scenario.cache.html[i] = data.result[i];
                }
            } else {
                nextdom.scenario.cache.html[_params.id] = data.result;
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
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'toHtml',
        id: ($.isArray(_params.id)) ? json_encode(_params.id) : _params.id,
        version: _params.version
    };
    $.ajax(paramsAJAX);
}


nextdom.scenario.changeState = function (_params) {
    var paramsRequired = ['id', 'state'];
    var paramsSpecifics = {global: false};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'changeState',
        id: _params.id,
        state: _params.state
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.getTemplate = function (_params) {
    var paramsRequired = [];
    var paramsSpecifics = {global: false};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'getTemplate',
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.convertToTemplate = function (_params) {
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
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'convertToTemplate',
        id: _params.id,
        template: _params.template || '',
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.removeTemplate = function (_params) {
    var paramsRequired = ['template'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'removeTemplate',
        template: _params.template,
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.loadTemplateDiff = function (_params) {
    var paramsRequired = ['template', 'id'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'loadTemplateDiff',
        template: _params.template,
        id: _params.id,
    };
    $.ajax(paramsAJAX);
}


nextdom.scenario.applyTemplate = function (_params) {
    var paramsRequired = ['template', 'id', 'convert'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'applyTemplate',
        template: _params.template,
        id: _params.id,
        convert: _params.convert,
    };
    $.ajax(paramsAJAX);
}

nextdom.scenario.refreshValue = function (_params) {
    if (!isset(_params.global) || !_params.global) {
        if (isset(nextdom.scenario.update) && isset(nextdom.scenario.update[_params.scenario_id])) {
            nextdom.scenario.update[_params.scenario_id](_params);
            return;
        }
    }
    if ($('.scenario[data-scenario_id=' + _params.scenario_id + ']').html() == undefined) {
        return;
    }
    var version = $('.scenario[data-scenario_id=' + _params.scenario_id + ']').attr('data-version');
    var paramsRequired = ['id'];
    var paramsSpecifics = {
        global: false,
        success: function (result) {
            $('.scenario[data-scenario_id=' + params.scenario_id + ']').empty().html($(result).children());
            if ($.mobile) {
                $('.scenario[data-scenario_id=' + params.scenario_id + ']').trigger("create");
                setTileSize('.scenario');
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
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'toHtml',
        id: _params.scenario_id,
        version: _params.version || version
    };
    $.ajax(paramsAJAX);
    
};


nextdom.scenario.copy = function (_params) {
    var paramsRequired = ['id', 'name'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'copy',
        id: _params.id,
        name: _params.name
    };
    $.ajax(paramsAJAX);
};


nextdom.scenario.get = function (_params) {
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
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'get',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};

nextdom.scenario.save = function (_params) {
    var paramsRequired = ['scenario'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'save',
        scenario: json_encode(_params.scenario)
    };
    $.ajax(paramsAJAX);
};

nextdom.scenario.remove = function (_params) {
    var paramsRequired = ['id'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'remove',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};

nextdom.scenario.emptyLog = function (_params) {
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
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'emptyLog',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};

nextdom.scenario.getSelectModal = function (_options, callback) {
    if (!isset(_options)) {
        _options = {};
    }
    if ($("#mod_insertScenarioValue").length != 0) {
        $("#mod_insertScenarioValue").remove();
    }
    $('body').append('<div id="mod_insertScenarioValue" title="{{Sélectionner le scénario}}" ></div>');
    $("#mod_insertScenarioValue").dialog({
        closeText: '',
        autoOpen: false,
        modal: true,
        height: 250,
        width: 800
    });
    jQuery.ajaxSetup({async: false});
    $('#mod_insertScenarioValue').load('index.php?v=d&modal=scenario.human.insert');
    jQuery.ajaxSetup({async: true});
    
    mod_insertScenario.setOptions(_options);
    $("#mod_insertScenarioValue").dialog('option', 'buttons', {
        "Annuler": function () {
            $(this).dialog("close");
        },
        "Valider": function () {
            var retour = {};
            retour.human = mod_insertScenario.getValue();
            retour.id = mod_insertScenario.getId();
            if ($.trim(retour) != '') {
                callback(retour);
            }
            $(this).dialog('close');
        }
    });
    $('#mod_insertScenarioValue').dialog('open');
};

nextdom.scenario.testExpression = function (_params) {
    var paramsRequired = ['expression'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/scenario.ajax.php';
    paramsAJAX.data = {
        action: 'testExpression',
        expression: _params.expression
    };
    $.ajax(paramsAJAX);
};
