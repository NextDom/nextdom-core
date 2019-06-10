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

function nextdom() {
}

nextdom.cache = [];
nextdom.display = {};
nextdom.connect = 0;

if (!isset(nextdom.cache.getConfiguration)) {
  nextdom.cache.getConfiguration = null;
}

nextdom.changes = function(){
  var paramsRequired = [];
  var paramsSpecifics = {
    global: false,
    success: function(data) {
      if(nextdom.connect > 0){
        nextdom.connect = 0;
      }
      nextdom.datetime = data.datetime;
      var cmd_update = [];
      var eqLogic_update = [];
      var object_summary_update = [];
      for(var i in data.result){
        if(data.result[i].name == 'cmd::update'){
          cmd_update.push(data.result[i].option);
          continue;
        }
        if(data.result[i].name == 'eqLogic::update'){
          eqLogic_update.push(data.result[i].option);
          continue;
        }
        if(data.result[i].name == 'jeeObject::summary::update'){
          object_summary_update.push(data.result[i].option);
          continue;
        }
        if(isset(data.result[i].option)){
          $('body').trigger(data.result[i].name,data.result[i].option);
        }else{
          $('body').trigger(data.result[i].name);
        }
      }
      if(cmd_update.length > 0){
        $('body').trigger('cmd::update',[cmd_update]);
      }
      if(eqLogic_update.length > 0){
        $('body').trigger('eqLogic::update',[eqLogic_update]);
      }
      if(object_summary_update.length > 0){
        $('body').trigger('jeeObject::summary::update',[object_summary_update]);
      }

      setTimeout(nextdom.changes, 1);
    },
    error: function(_error){
      if(typeof(user_id) != "undefined" && nextdom.connect == 100){
        notify('{{Erreur de connexion}}','{{Erreur lors de la connexion à NextDom}} : '+_error.message);
        window.location.reload();
      }
      nextdom.connect++;
      setTimeout(nextdom.changes, 1);
    }
  };
  try {
    nextdom.private.checkParamsRequired(paramsRequired);
  } catch (e) {
    (paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics);
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/event.ajax.php';
  paramsAJAX.data = {
    action: 'changes',
    datetime:nextdom.datetime,
  };
  $.ajax(paramsAJAX);
}


nextdom.init = function () {
  nextdom.datetime = serverDatetime;
  nextdom.display.version = 'desktop';
  if ($.mobile) {
    nextdom.display.version = 'mobile';
  }
  Highcharts.setOptions({
    lang: {
      months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
      'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      shortMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
      'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
    }
  });
  $('body').on('cmd::update', function (_event,_options) {
    nextdom.cmd.refreshValue(_options);
  });

  $('body').on('scenario::update', function (_event,_options) {
    nextdom.scenario.refreshValue(_options);
  });
  $('body').on('eqLogic::update', function (_event,_options) {
    nextdom.eqLogic.refreshValue(_options);
  });
  $('body').on('jeeObject::summary::update', function (_event,_options) {
    nextdom.object.summaryUpdate(_options);
  });

  $('body').on('ui::update', function (_event,_options) {
    if(isset(_options.page) && _options.page != ''){
      if(!$.mobile && getUrlVars('p') != _options.page){
        return;
      }
      if($.mobile && isset(CURRENT_PAGE) && CURRENT_PAGE != _options.page){
        return;
      }
    }
    if(!isset(_options.container) || _options.container == ''){
      _options.container = 'body';
    }
    $(_options.container).setValues(_options.data, _options.type);
    console.log(_options);
  });

    $('body').on('nextdom::gotoplan', function (_event,_plan_id) {
    if(getUrlVars('p') == 'plan' && 'function' == typeof (displayPlan)){
      if (_plan_id != $('#sel_planHeader').attr('data-link_id')) {
        planHeader_id = _plan_id;
        displayPlan();
      }
    }
  });

  $('body').on('nextdom::alert', function (_event,_options) {
    if (!isset(_options.message) || $.trim(_options.message) == '') {
      if(isset(_options.page) && _options.page != ''){
        if(getUrlVars('p') == _options.page || ($.mobile && isset(CURRENT_PAGE) && CURRENT_PAGE == _options.page)){
          $.hideAlert();
        }
      }else{
        $.hideAlert();
      }
    } else {
      if(isset(_options.page) && _options.page != ''){
        if(getUrlVars('p') == _options.page || ($.mobile && isset(CURRENT_PAGE) && CURRENT_PAGE == _options.page)){
            notify("Core",_options.message,_options.level);
        }
      }else{
          notify("Core",_options.message,_options.level);
      }
    }

  });
  $('body').on('nextdom::alertPopup', function (_event,_message) {
    alert(_message);
  });
  $('body').on('message::refreshMessageNumber', function (_event,_options) {
    refreshMessageNumber();
  });
  $('body').on('update::refreshUpdateNumber', function (_event,_options) {
    refreshUpdateNumber();
  });
  $('body').on('notify', function (_event,_options) {
    notify(_options.title, _options.message, _options.theme);
  });
  if (typeof user_id !== 'undefined') {
    nextdom.changes();
  }
}

nextdom.getConfiguration = function (_params) {
  var paramsRequired = ['key'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cache.getConfiguration = data.result;
      var keys = _params.key.split(':');
      data.result = nextdom.cache.getConfiguration;
      for(var i in keys){
        if (data.result[keys[i]]) {
          data.result = data.result[keys[i]];
        }
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
  if (nextdom.cache.getConfiguration != null) {
    var keys = _params.key.split(':');
    var result = nextdom.cache.getConfiguration;
    for(var i in keys){
      if (result[keys[i]]) {
        result = result[keys[i]];
      }
    }
    _params.success(result);
    return;
  }
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'getConfiguration',
    key: ''
  };
  $.ajax(paramsAJAX);
};

nextdom.haltSystem = function (_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'haltSystem',
  };
  $.ajax(paramsAJAX);
};

nextdom.ssh = function (_params) {
  if($.isPlainObject(_params)){
    command = _params.command;
  }else{
    command = _params;
    _params = {};
  }
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'ssh',
    command : command
  };
  $.ajax(paramsAJAX);
  return 'Execute command : '+command;
};

nextdom.db = function (_params) {
  if($.isPlainObject(_params)){
    command = _params.command;
  }else{
    command = _params;
    _params = {};
  }
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'db',
    command : command
  };
  $.ajax(paramsAJAX);
  return 'Execute command : '+command;
};


nextdom.rebootSystem = function (_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'rebootSystem',
  };
  $.ajax(paramsAJAX);
};

nextdom.health = function (_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'health',
  };
  $.ajax(paramsAJAX);
};

nextdom.saveCustom = function (_params) {
  var paramsRequired = ['type', 'content'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'saveCustom',
    type: _params.type,
    version: _params.version,
    content: _params.content,
  };
  $.ajax(paramsAJAX);
};

nextdom.forceSyncHour = function (_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'forceSyncHour',
  };
  $.ajax(paramsAJAX);
};

nextdom.getCronSelectModal = function(_options,_callback) {
  if ($("#mod_insertCronValue").length == 0) {
    $('body').append('<div id="mod_insertCronValue" title="{{Assistant cron}}" ></div>');
    $("#mod_insertCronValue").dialog({
      closeText: '',
      autoOpen: false,
      modal: true,
      height: 250,
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
    "{{Annuler}}": function() {
      $(this).dialog("close");
    },
    "{{Valider}}": function() {
      var retour = {};
      retour.cron = {};
      retour.value = mod_insertCron.getValue();
      if ($.trim(retour) != '' && 'function' == typeof(_callback)) {
        _callback(retour);
      }
      $(this).dialog('close');
    }
  });
  $('#mod_insertCronValue').dialog('open');
};

nextdom.getSelectActionModal = function(_options, _callback){
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
    "Annuler": function() {
      $(this).dialog("close");
    },
    "Valider": function() {
      var retour = {};
      retour.action = {};
      retour.human = mod_insertAction.getValue();
      if ($.trim(retour) != '' && 'function' == typeof(_callback)) {
        _callback(retour);
      }
      $(this).dialog('close');
    }
  });
  $('#mod_insertActionValue').dialog('open');
}

nextdom.getGraphData = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'getGraphData',
    filter_type: params.filter_type || null,
    filter_id: params.filter_id || null,
  };
  $.ajax(paramsAJAX);
};


nextdom.getDocumentationUrl = function (_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'getDocumentationUrl',
    plugin: params.plugin || null,
    page: params.page || null,
  };
  $.ajax(paramsAJAX);
};


nextdom.addWarnme = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'addWarnme',
    cmd_id: params.cmd_id,
    test: params.test,
  };
  $.ajax(paramsAJAX);
};


nextdom.getTimelineEvents = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'getTimelineEvents'
  };
  $.ajax(paramsAJAX);
};

nextdom.removeTimelineEvents = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'removeTimelineEvents'
  };
  $.ajax(paramsAJAX);
};


nextdom.getFileFolder = function(_params) {
  var paramsRequired = ['type','path'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'getFileFolder',
    type : _params.type,
    path : _params.path,
  };
  $.ajax(paramsAJAX);
};

nextdom.getFileContent = function(_params) {
  var paramsRequired = ['path'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'getFileContent',
    path : _params.path,
  };
  $.ajax(paramsAJAX);
};

nextdom.setFileContent = function(_params) {
  var paramsRequired = ['path','content'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'setFileContent',
    path : _params.path,
    content : _params.content,
  };
  $.ajax(paramsAJAX);
};


nextdom.deleteFile = function(_params) {
  var paramsRequired = ['path'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'deleteFile',
    path : _params.path,
  };
  $.ajax(paramsAJAX);
};

nextdom.createFile = function(_params) {
  var paramsRequired = ['path','name'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'createFile',
    path : _params.path,
    name : _params.name,
  };
  $.ajax(paramsAJAX);
};


nextdom.emptyRemoveHistory = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'emptyRemoveHistory',
  };
  $.ajax(paramsAJAX);
};

nextdom.cleanFileSystemRight = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'cleanFileSystemRight'
  };
  $.ajax(paramsAJAX);
};

nextdom.consistency = function(_params) {
  var paramsRequired = [];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/nextdom.ajax.php';
  paramsAJAX.data = {
    action: 'consistency'
  };
  $.ajax(paramsAJAX);
};