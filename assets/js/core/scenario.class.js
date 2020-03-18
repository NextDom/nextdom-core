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

nextdom.scenario = function() {
};

nextdom.scenario.cache = Array();

if (!isset(nextdom.scenario.cache.html)) {
  nextdom.scenario.cache.html = Array();
}
if (!isset(nextdom.scenario.update)) {
  nextdom.scenario.update = Array();
}

nextdom.scenario.all = function(queryParams) {
  var paramsRequired = [];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.scenario.cache.all = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (isset(nextdom.scenario.cache.all) && nextdom.scenario.cache.all != null && init(queryParams.nocache, false) == false) {
      params.success(nextdom.scenario.cache.all);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Scenario', 'all');
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.scenario.saveAll = function(queryParams) {
  nextdom.private.ajax('Scenario', 'saveAll', queryParams, ['scenarios'], true);
};

nextdom.scenario.toHtml = function(queryParams) {
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
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Scenario', 'toHtml');
    ajaxParams.data['id'] = ($.isArray(queryParams.id)) ? json_encode(queryParams.id) : queryParams.id;
    ajaxParams.data['version'] = queryParams.version;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.scenario.changeState = function(queryParams) {
  nextdom.private.ajax('Scenario', 'changeState', queryParams, ['id', 'state'], false, false);
};

nextdom.scenario.getTemplate = function(queryParams) {
  nextdom.private.ajax('Scenario', 'getTemplate', queryParams, false, false, false);
};

nextdom.scenario.convertToTemplate = function(queryParams) {
  var paramsRequired = ['id'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Scenario', 'convertToTemplate');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['template'] = queryParams.template || '';
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.scenario.removeTemplate = function(queryParams) {
  nextdom.private.ajax('Scenario', 'removeTemplate', queryParams, ['template']);
};

nextdom.scenario.loadTemplateDiff = function(queryParams) {
  nextdom.private.ajax('Scenario', 'loadTemplateDiff', queryParams, ['template', 'id']);
};

nextdom.scenario.applyTemplate = function(queryParams) {
  nextdom.private.ajax('Scenario', 'applyTemplate', queryParams, ['template', 'id', 'convert', 'newValues']);
};

nextdom.scenario.refreshValue = function(queryParams) {
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
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Scenario', 'toHtml');
    ajaxParams.data['id'] = queryParams.scenario_id;
    ajaxParams.data['version'] = queryParams.version || version;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.scenario.copy = function(queryParams) {
  nextdom.private.ajax('Scenario', 'copy', queryParams, ['id', 'name']);
};

nextdom.scenario.get = function(queryParams) {
  nextdom.private.ajax('Scenario', 'get', queryParams, ['id']);
};

nextdom.scenario.save = function(queryParams) {
  var paramsRequired = ['scenario'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    delete nextdom.scenario.cache.all;
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Scenario', 'save');
    ajaxParams.data['scenario'] = json_encode(queryParams.scenario);
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.scenario.remove = function(queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    delete nextdom.scenario.cache.all;
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Scenario', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.scenario.emptyLog = function(queryParams) {
  nextdom.private.ajax('Scenario', 'emptyLog', queryParams, ['id']);
};

nextdom.scenario.getSelectModal = function(_options, callback) {
  if (!isset(_options)) {
    _options = {};
  }
  if ($("#mod_insertScenarioValue").length !== 0) {
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

nextdom.scenario.testExpression = function(queryParams) {
  nextdom.private.ajax('Scenario', 'testExpression', queryParams, ['expression']);
};

nextdom.scenario.setOrder = function(queryParams) {
  nextdom.private.ajax('Scenario', 'setOrder', queryParams, ['scenarios'], true);
};
