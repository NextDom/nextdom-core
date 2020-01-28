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

var nextdom = function() {};

nextdom.cache = [];
nextdom.display = {};
nextdom.connect = 0;
nextdom.initialized = false;

if (!isset(nextdom.cache.getConfiguration)) {
  nextdom.cache.getConfiguration = null;
}

nextdom.changes = function() {
  var paramsRequired = [];
  var paramsSpecifics = {
    global: false,
    success: function (data) {
      if (nextdom.connect > 0) {
        nextdom.connect = 0;
      }
      nextdom.datetime = data.datetime;
      var cmd_update = [];
      var scenario_update = [];
      var eqLogic_update = [];
      var object_summary_update = [];
      for (var i in data.result) {
        if (data.result[i].name === 'cmd::update') {
          cmd_update.push(data.result[i].option);
          continue;
        }
        if (data.result[i].name === 'jeeObject::summary::update') {
          scenario_update.push(data.result[i].option);
          continue;
        }
        if (data.result[i].name === 'eqLogic::update') {
          eqLogic_update.push(data.result[i].option);
          continue;
        }
        if (data.result[i].name === 'jeeObject::summary::update') {
          object_summary_update.push(data.result[i].option);
          continue;
        }
        if (isset(data.result[i].option)) {
          $('body').trigger(data.result[i].name, data.result[i].option);
        } else {
          $('body').trigger(data.result[i].name);
        }
      }
      if (cmd_update.length > 0) {
        $('body').trigger('cmd::update', [cmd_update]);
      }
      if (scenario_update.length > 0) {
        $('body').trigger('scenario::update', [scenario_update]);
      }
      if (eqLogic_update.length > 0) {
        $('body').trigger('eqLogic::update', [eqLogic_update]);
      }
      if (object_summary_update.length > 0) {
        $('body').trigger('jeeObject::summary::update', [object_summary_update]);
      }

      setTimeout(nextdom.changes, 1);
    },
    error: function (_error) {
      if (typeof (user_id) != "undefined" && nextdom.connect == 100) {
        notify('{{Erreur de connexion}}', '{{Erreur lors de la connexion à NextDom}} : ' + _error.message);
        window.location.reload();
      }
      nextdom.connect++;
      setTimeout(nextdom.changes, 1);
    }
  };
  if (nextdom.private.isValidQuery({}, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics);
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'Event', 'changes');
    ajaxParams.data['datetime'] = nextdom.datetime;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

/**
 * Initialize update events and start event loop
 */
nextdom.init = function() {
  var bodyContainer = $('body');
  nextdom.datetime = serverDatetime;
  nextdom.display.version = 'desktop';
  Highcharts.setOptions({
    lang: {
      months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      shortMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
    }
  });
  bodyContainer.on('cmd::update', function (_event, _options) {
    nextdom.cmd.refreshValue(_options);
  });
  bodyContainer.on('scenario::update', function (_event, _options) {
    nextdom.scenario.refreshValue(_options);
  });
  bodyContainer.on('eqLogic::update', function (_event, _options) {
    nextdom.eqLogic.refreshValue(_options);
  });
  bodyContainer.on('jeeObject::summary::update', function (_event, _options) {
    nextdom.object.summaryUpdate(_options);
  });

  bodyContainer.on('ui::update', function (_event, _options) {
    if (isset(_options.page) && _options.page != '') {
      if (!$.mobile && getUrlVars('p') != _options.page) {
        return;
      }
      if ($.mobile && isset(CURRENT_PAGE) && CURRENT_PAGE != _options.page) {
        return;
      }
    }
    if (!isset(_options.container) || _options.container == '') {
      _options.container = 'body';
    }
    $(_options.container).setValues(_options.data, _options.type);
  });

  bodyContainer.on('nextdom::gotoplan', function (_event, _plan_id) {
    if (getUrlVars('p') == 'plan' && 'function' == typeof (displayPlan)) {
      if (_plan_id != $('#sel_planHeader').attr('data-link_id')) {
        planHeader_id = _plan_id;
        displayPlan();
      }
    }
  });

  bodyContainer.on('nextdom::alert', function (_event, _options) {
    if (!isset(_options.message) || $.trim(_options.message) == '') {
      if (isset(_options.page) && _options.page != '') {
        if (getUrlVars('p') == _options.page || ($.mobile && isset(CURRENT_PAGE) && CURRENT_PAGE == _options.page)) {
          $.hideAlert();
        }
      } else {
        $.hideAlert();
      }
    } else {
      if (isset(_options.page) && _options.page != '') {
        if (getUrlVars('p') == _options.page || ($.mobile && isset(CURRENT_PAGE) && CURRENT_PAGE == _options.page)) {
          notify('Core', _options.message, _options.level);
        }
      } else {
        notify('Core', _options.message, _options.level);
      }
    }

  });
  bodyContainer.on('nextdom::alertPopup', function (_event, _message) {
    alert(_message);
  });
  bodyContainer.on('message::refreshMessageNumber', function (_event, _options) {
    refreshMessageNumber();
  });
  bodyContainer.on('update::refreshUpdateNumber', function (_event, _options) {
    refreshUpdateNumber();
  });
  bodyContainer.on('notify', function (_event, _options) {
    notify(_options.title, _options.message, _options.theme);
  });
  if (typeof(user_id) !== 'undefined' && !nextdom.initialized) {
    nextdom.initialized = true
    nextdom.changes();
  }
};

nextdom.getConfiguration = function(queryParams) {
  var paramsRequired = ['key'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cache.getConfiguration = data.result;
      var keys = queryParams.key.split(':');
      data.result = nextdom.cache.getConfiguration;
      for (var i in keys) {
        if (data.result[keys[i]]) {
          data.result = data.result[keys[i]];
        }
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
    if (nextdom.cache.getConfiguration != null) {
      var keys = queryParams.key.split(':');
      var result = nextdom.cache.getConfiguration;
      for (var i in keys) {
        if (result[keys[i]]) {
          result = result[keys[i]];
        }
      }
      queryParams.success(result);
      return;
    }
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDom', 'getConfiguration');
    ajaxParams.data['key'] = '';
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.haltSystem = function(queryParams) {
  nextdom.private.ajax('NextDom', 'haltSystem', queryParams);
};

nextdom.ssh = function(queryParams) {
  if ($.isPlainObject(queryParams)) {
    command = queryParams.command;
  } else {
    command = queryParams;
    queryParams = {};
  }
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDom', 'ssh');
  ajaxParams.data['command'] = command;
  nextdom.private.ajaxCall(ajaxParams);
  return 'Execute command : ' + command;
};

nextdom.db = function(queryParams) {
  if ($.isPlainObject(queryParams)) {
    command = queryParams.command;
  } else {
    command = queryParams;
    queryParams = {};
  }
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDom', 'db');
  ajaxParams.data['command'] = command;
  nextdom.private.ajaxCall(ajaxParams);
  return 'Execute command : ' + command;
};

nextdom.rebootSystem = function(queryParams) {
  nextdom.private.ajax('NextDom', 'rebootSystem', queryParams);
};

nextdom.health = function(queryParams) {
  nextdom.private.ajax('NextDom', 'health', queryParams);
};

nextdom.forceSyncHour = function(queryParams) {
  nextdom.private.ajax('NextDom', 'forceSyncHour', queryParams);
};

nextdom.getCronSelectModal = function(_options, _callback) {
  if ($("#mod_insertCronValue").length == 0) {
    $('body').append('<div id="mod_insertCronValue" title="{{Assistant cron}}" ></div>');
    $("#mod_insertCronValue").dialog({
      closeText: '',
      autoOpen: false,
      modal: true,
      height: 280,
      width: 800
    });
    jQuery.ajaxSetup({
      async: false
    });
    $('#mod_insertCronValue').load('index.php?v=d&modal=cron.human.insert');
    jQuery.ajaxSetup({
      async: true
    });
  }
  $("#mod_insertCronValue").dialog('option', 'buttons', {
    "{{Annuler}}": function () {
      $(this).dialog("close");
    },
    "{{Valider}}": function () {
      var retour = {};
      retour.cron = {};
      retour.value = mod_insertCron.getValue();
      if ($.trim(retour) != '' && 'function' == typeof (_callback)) {
        _callback(retour);
      }
      $(this).dialog('close');
    }
  });
  $('#mod_insertCronValue').dialog('open');
};

nextdom.getSelectActionModal = function(_options, _callback) {
  if (!isset(_options)) {
    _options = {};
  }
  if ($("#mod_insertActionValue").length == 0) {
    $('body').append('<div id="mod_insertActionValue" title="{{Sélectionner la commande}}" ></div>');
    $("#mod_insertActionValue").dialog({
      closeText: '',
      autoOpen: false,
      modal: true,
      height: 280,
      width: 800
    });
    jQuery.ajaxSetup({
      async: false
    });
    $('#mod_insertActionValue').load('index.php?v=d&modal=action.insert');
    jQuery.ajaxSetup({
      async: true
    });
  }
  mod_insertAction.setOptions(_options);
  $("#mod_insertActionValue").dialog('option', 'buttons', {
    "Annuler": function () {
      $(this).dialog("close");
    },
    "Valider": function () {
      var retour = {};
      retour.action = {};
      retour.human = mod_insertAction.getValue();
      if ($.trim(retour) != '' && 'function' == typeof (_callback)) {
        _callback(retour);
      }
      $(this).dialog('close');
    }
  });
  $('#mod_insertActionValue').dialog('open');
};

nextdom.getGraphData = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDom', 'getGraphData');
  ajaxParams.data['filter_type'] = params.filter_type || null;
  ajaxParams.data['filter_id'] = params.filter_id || null;
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.getDocumentationUrl = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDom', 'getDocumentationUrl');
  ajaxParams.data['plugin'] = params.plugin || null;
  ajaxParams.data['page'] = params.page || null;
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.addWarnme = function(queryParams) {
  nextdom.private.ajax('NextDom', 'addWarnme', queryParams, ['cmd_id', 'test']);
};

nextdom.getTimelineEvents = function(queryParams) {
  nextdom.private.ajax('NextDom', 'getTimelineEvents', queryParams);
};

nextdom.removeTimelineEvents = function(queryParams) {
  nextdom.private.ajax('NextDom', 'removeTimelineEvents', queryParams);
};

nextdom.getFileFolder = function(queryParams) {
  nextdom.private.ajax('NextDom', 'getFileFolder', queryParams, ['type', 'path']);
};

nextdom.getFileContent = function(queryParams) {
  nextdom.private.ajax('NextDom', 'getFileContent', queryParams, ['path']);
};

nextdom.setFileContent = function(queryParams) {
  nextdom.private.ajax('NextDom', 'setFileContent', queryParams, ['path', 'content']);
};

nextdom.deleteFile = function(queryParams) {
  nextdom.private.ajax('NextDom', 'deleteFile', queryParams, ['path']);
};

nextdom.createFile = function(queryParams) {
  nextdom.private.ajax('NextDom', 'createFile', queryParams, ['path', 'name']);
};

nextdom.emptyRemoveHistory = function(queryParams) {
  nextdom.private.ajax('NextDom', 'emptyRemoveHistory', queryParams);
};

nextdom.cleanFileSystemRight = function(queryParams) {
  nextdom.private.ajax('NextDom', 'cleanFileSystemRight', queryParams);
};

nextdom.consistency = function(queryParams) {
  nextdom.private.ajax('NextDom', 'consistency', queryParams);
};
