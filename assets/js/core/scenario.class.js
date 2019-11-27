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

nextdom.scenario.all = function (queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.scenario.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    if (isset(nextdom.scenario.cache.all) && nextdom.scenario.cache.all != null && init(queryParams.nocache, false) == false) {
      params.success(nextdom.scenario.cache.all);
      return;
    }
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'all');
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.saveAll = function (queryParams) {
  var paramsRequired = ['scenarios'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    delete nextdom.scenario.cache.all;
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'saveAll');
    ajaxParams.data['scenarios'] = json_encode(queryParams.scenarios);
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.toHtml = function (queryParams) {
  var paramsRequired = ['id', 'version'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (queryParams.id == 'all' || $.isArray(queryParams.id)) {
        for (var i in data.result) {
          nextdom.scenario.cache.html[i] = data.result[i];
        }
      } else {
        nextdom.scenario.cache.html[queryParams.id] = data.result;
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'toHtml');
    ajaxParams.data['id'] = ($.isArray(queryParams.id)) ? json_encode(queryParams.id) : queryParams.id;
    ajaxParams.data['version'] = queryParams.version;
    $.ajax(ajaxParams);
  }
};


nextdom.scenario.changeState = function (queryParams) {
  var paramsRequired = ['id', 'state'];
  var paramsSpecifics = {global: false};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'changeState');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['state'] = queryParams.state;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.getTemplate = function (queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {global: false};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'getTemplate');
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.convertToTemplate = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'convertToTemplate');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['template'] = queryParams.template || '';
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.removeTemplate = function (queryParams) {
  var paramsRequired = ['template'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'removeTemplate');
    ajaxParams.data['template'] = queryParams.template;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.loadTemplateDiff = function (queryParams) {
  var paramsRequired = ['template', 'id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'loadTemplateDiff');
    ajaxParams.data['action'] = 'loadTemplateDiff';
    ajaxParams.data['template'] = queryParams.template;
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.applyTemplate = function (queryParams) {
  var paramsRequired = ['template', 'id', 'convert'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'applyTemplate');
    ajaxParams.data['template'] = queryParams.template;
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['convert'] = queryParams.convert;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.refreshValue = function (queryParams) {
  if (!isset(queryParams.global) || !queryParams.global) {
    if (isset(nextdom.scenario.update) && isset(nextdom.scenario.update[queryParams.scenario_id])) {
      nextdom.scenario.update[queryParams.scenario_id](queryParams);
      return;
    }
  }
  if ($('.scenario[data-scenario_id=' + queryParams.scenario_id + ']').html() === undefined) {
    return;
  }
  var version = 'dashboard';
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    global: false,
    success: function (result) {
      $('.scenario[data-scenario_id=' + params.scenario_id + ']').empty().html($(result).children());
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'toHtml');
    ajaxParams.data['id'] = queryParams.scenario_id;
    ajaxParams.data['version'] = queryParams.version || version;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.copy = function (queryParams) {
  var paramsRequired = ['id', 'name'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'copy');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['name'] = queryParams.name;
    $.ajax(ajaxParams);
  }
};


nextdom.scenario.get = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'get');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.save = function (queryParams) {
  var paramsRequired = ['scenario'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'save');
    ajaxParams.data['scenario'] = json_encode(queryParams.scenario);
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.remove = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    delete nextdom.scenario.cache.all
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.emptyLog = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'emptyLog');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
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

nextdom.scenario.testExpression = function (queryParams) {
  var paramsRequired = ['expression'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'testExpression');
    ajaxParams.data['expression'] = queryParams.expression;
    $.ajax(ajaxParams);
  }
};

nextdom.scenario.setOrder = function (queryParams) {
  var paramsRequired = ['scenarios'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'setOrder');
    ajaxParams.data['scenarios'] = json_encode(queryParams.scenarios);
    $.ajax(ajaxParams);
  }
};
