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
nextdom.cmd = function () {
};
nextdom.cmd.cache = Array();
if (!isset(nextdom.cmd.cache.byId)) {
  nextdom.cmd.cache.byId = Array();
}
if (!isset(nextdom.cmd.cache.byHumanName)) {
  nextdom.cmd.cache.byHumanName = Array();
}
if (!isset(nextdom.cmd.update)) {
  nextdom.cmd.update = Array();
}
nextdom.cmd.execute = function (queryParams) {
  var notifyMe = queryParams.notify || true;
  if (notifyMe) {
    var eqLogic = $('.cmd[data-cmd_id=' + queryParams.id + ']').closest('.eqLogic');
    eqLogic.find('.statusCmd').empty().append('<i class="fa fa-spinner fa-spin"></i>');
  }
  if (queryParams.value != 'undefined' && (is_array(queryParams.value) || is_object(queryParams.value))) {
    queryParams.value = json_encode(queryParams.value);
  }
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    global: false,
    pre_success: function (data) {
      if (data.state != 'ok') {
        if (data.code == -32005) {
          bootbox.prompt("{{Veuillez indiquer le code ?}}", function (result) {
            if (result != null) {
              queryParams.codeAccess = result;
              nextdom.cmd.execute(queryParams);
            } else {
              nextdom.cmd.refreshValue({id: queryParams.id});
              if ('function' != typeof (queryParams.error)) {
                notify('Core', data.result, "error");
              }
              if (notifyMe) {
                eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
                setTimeout(function () {
                  eqLogic.find('.statusCmd').empty();
                }, 3000);
              }
              return data;
            }
          });
        } else if (data.code == -32006) {
          bootbox.confirm("{{Etes-vous sûr de vouloir faire cette action ?}}", function (result) {
            if (result) {
              queryParams.confirmAction = 1;
              nextdom.cmd.execute(queryParams);
            } else {
              nextdom.cmd.refreshValue({id: queryParams.id});
              if ('function' != typeof (queryParams.error)) {
                notify('Core', data.result, "error");
              }
              if (notifyMe) {
                eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
                setTimeout(function () {
                  eqLogic.find('.statusCmd').empty();
                }, 3000);
              }
              return data;
            }
          });
        } else {
          if ('function' != typeof (queryParams.error)) {
            notify('Core', data.result, "error");
          }
          if (notifyMe) {
            eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
            setTimeout(function () {
              eqLogic.find('.statusCmd').empty();
            }, 3000);
          }
          return data;
        }
      }
      if (notifyMe) {
        eqLogic.find('.statusCmd').empty().append('<i class="fa fa-rss"></i>');
        setTimeout(function () {
          eqLogic.find('.statusCmd').empty();
        }, 3000);
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'execCmd');
    var cache = 1;
    if (queryParams.cache !== undefined) {
      cache = queryParams.cache;
    }
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['codeAccess'] = queryParams.codeAccess || '';
    ajaxParams.data['confirmAction'] = queryParams.confirmAction || '';
    ajaxParams.data['cache'] = cache;
    ajaxParams.data['value'] = queryParams.value || '';
    if (window.location.href.indexOf('p=dashboard') >= 0 || window.location.href.indexOf('p=plan') >= 0 || window.location.href.indexOf('p=view') >= 0 || $.mobile) {
      ajaxParams.data['utid'] = utid;
    }
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.test = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    global: false,
    success: function (result) {
      switch (result.type) {
        case 'info':
          nextdom.cmd.execute({
            id: queryParams.id,
            cache: 0,
            notify: false,
            success: function (result) {
              bootbox.confirm('{{Résultat de la commande : }}' + result, function () {
              });
            }
          });
          break;
        case 'action':
          switch (result.subType) {
            case 'other':
              nextdom.cmd.execute({
                id: queryParams.id,
                cache: 0,
                error: function (error) {
                  notify('Core', error.message, "error");
                },
                success: function () {
                  notify('Core', "{{Action exécutée avec succès}}", "success");
                }
              });
              break;
            case 'slider':
              var slider = 50;
              if (isset(result.configuration) && isset(result.configuration.maxValue) && isset(result.configuration.minValue)) {
                slider = (result.configuration.maxValue - result.configuration.minValue) / 2;
              }
              nextdom.cmd.execute({
                id: queryParams.id,
                value: {
                  slider: slider
                },
                cache: 0,
                error: function (error) {
                  notify('Core', error.message, "error");
                },
                success: function () {
                  notify('Core', '{{Action exécutée avec succès}}', "success");
                }
              });
              break;
            case 'color':
              nextdom.cmd.execute({
                id: queryParams.id,
                value: {
                  color: '#fff000'
                },
                cache: 0,
                error: function (error) {
                  notify('Core', error.message, "error");
                },
                success: function () {
                  notify('Core', '{{Action exécutée avec succès}}', "success");
                }
              });
              break;
            case 'select':
              nextdom.cmd.execute({
                id: queryParams.id,
                value: {
                  select: result.configuration.listValue.split(';')[0].split('|')[0]
                },
                cache: 0,
                error: function (error) {
                  notify('Core', error.message, "error");
                },
                success: function () {
                  notify('Core', '{{Action exécutée avec succès}}', "success");
                }
              });
              break;
            case 'message':
              nextdom.cmd.execute({
                id: queryParams.id,
                value: {
                  title: '{{[NextDom] Message de test}}',
                  message: '{{Ceci est un test de message pour la commande}} ' + result.name
                },
                cache: 0,
                error: function (error) {
                  notify('Core', error.message, "error");
                },
                success: function () {
                  notify('Core', '{{Action exécutée avec succès}}', "success");
                }
              });
              break;
          }
          break;
      }
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'getCmd');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.refreshByEqLogic = function (queryParams) {
  var cmds = $('.cmd[data-eqLogic_id=' + queryParams.eqLogic_id + ']');
  if (cmds.length > 0) {
    $(cmds).each(function () {
      if ($(this).closest('.eqLogic[data-eqLogic_id=' + queryParams.eqLogic_id + ']').html() !== undefined) {
        return true;
      }
      nextdom.cmd.toHtml({
        global: false,
        id: $(this).attr('data-cmd_id'),
        version: $(this).attr('data-version'),
        success: function (data) {
          $('.cmd[data-cmd_id=' + data.id + ']').replaceWith(data.html);
        }
      })
    });
  }
}

nextdom.cmd.refreshValue = function (queryParams) {
  for (var i in queryParams) {
    var cmd = $('.cmd[data-cmd_id=' + queryParams[i].cmd_id + ']');
    if (cmd.html() === undefined || cmd.hasClass('noRefresh')) {
      continue;
    }
    if (!isset(nextdom.cmd.update) || !isset(nextdom.cmd.update[queryParams[i].cmd_id])) {
      continue;
    }
    nextdom.cmd.update[queryParams[i].cmd_id](queryParams[i]);
  }
};

nextdom.cmd.toHtml = function (queryParams) {
  var paramsRequired = ['id', 'version'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'toHtml');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['version'] = queryParams.version;
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.replaceCmd = function (queryParams) {
  var paramsRequired = ['source_id', 'target_id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'replaceCmd');
    ajaxParams.data['source_id'] = queryParams.source_id;
    ajaxParams.data['target_id'] = queryParams.target_id;
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.save = function (queryParams) {
  var paramsRequired = ['cmd'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.cmd.cache.byId[data.result.id])) {
        delete nextdom.cmd.cache.byId[data.result.id];
      }
      if (isset(nextdom.eqLogic.cache.byId[data.result.eqLogic_id])) {
        delete nextdom.eqLogic.cache.byId[data.result.eqLogic_id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'save');
    ajaxParams.data['cmd'] = json_encode(queryParams.cmd);
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.multiSave = function (queryParams) {
  var paramsRequired = ['cmds'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cmd.cache.byId = [];
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'multiSave');
    ajaxParams.data['cmd'] = json_encode(queryParams.cmds);
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.byId = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cmd.cache.byId[data.result.id] = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    if (isset(nextdom.cmd.cache.byId[params.id]) && init(params.noCache, false) == false) {
      params.success(nextdom.cmd.cache.byId[params.id]);
      return;
    }
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'byId');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.byHumanName = function (queryParams) {
  var paramsRequired = ['humanName'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cmd.cache.byHumanName[data.result.humanName] = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    if (isset(nextdom.cmd.cache.byHumanName[params.humanName]) && init(params.noCache, false) == false) {
      params.success(nextdom.cmd.cache.byHumanName[params.humanName]);
      return;
    }
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'byHumanName');
    ajaxParams.data['humanName'] = queryParams.humanName;
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.usedBy = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'usedBy');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.changeType = function (_cmd, _subType) {
  var selSubType = '<select style="width : 120px;margin-top : 5px;" class="cmdAttr form-control input-sm" data-l1key="subType">';
  var type = _cmd.find('.cmdAttr[data-l1key=type]').value();
  nextdom.getConfiguration({
    key: 'cmd:type:' + type + ':subtype',
    default: 0,
    async: false,
    error: function (error) {
      queryParams.error(error);
    },
    success: function (subType) {
      for (var i in subType) {
        selSubType += '<option value="' + i + '">' + subType[i].name + '</option>';
      }
      selSubType += '</select>';
      _cmd.find('.subType').empty();
      _cmd.find('.subType').append(selSubType);
      if (isset(_subType)) {
        _cmd.find('.cmdAttr[data-l1key=subType]').value(_subType);
        modifyWithoutSave = false;
      }
      nextdom.cmd.changeSubType(_cmd);
    }
  });
};

nextdom.cmd.changeSubType = function (_cmd) {
  nextdom.getConfiguration({
    key: 'cmd:type:' + _cmd.find('.cmdAttr[data-l1key=type]').value() + ':subtype:' + _cmd.find('.cmdAttr[data-l1key=subType]').value(),
    default: 0,
    async: false,
    error: function (error) {
      queryParams.error(error);
    },
    success: function (subtype) {
      for (var i in subtype) {
        if (isset(subtype[i].visible)) {
          var el = _cmd.find('.cmdAttr[data-l1key=' + i + ']');
          if (el.attr('type') == 'checkbox' && el.parent().is('span')) {
            el = el.parent();
          }
          if (subtype[i].visible) {
            if (el.hasClass('bootstrapSwitch')) {
              el.parent().parent().show();
              el.parent().parent().removeClass('hide');
            }
            if (el.attr('type') == 'checkbox') {
              el.parent().show();
              el.parent().removeClass('hide');
            }
            el.show();
            el.removeClass('hide');
          } else {
            if (el.hasClass('bootstrapSwitch')) {
              el.parent().parent().hide();
              el.parent().parent().addClass('hide');
            }
            if (el.attr('type') == 'checkbox') {
              el.parent().hide();
              el.parent().addClass('hide');
            }
            el.hide();
            el.addClass('hide');
          }
          if (isset(subtype[i].parentVisible)) {
            if (subtype[i].parentVisible) {
              el.parent().show();
              el.parent().removeClass('hide');
            } else {
              el.parent().hide();
              el.parent().addClass('hide');
            }
          }
        } else {
          for (var j in subtype[i]) {
            var el = _cmd.find('.cmdAttr[data-l1key=' + i + '][data-l2key=' + j + ']');
            if (el.attr('type') == 'checkbox' && el.parent().is('span')) {
              el = el.parent();
            }

            if (isset(subtype[i][j].visible)) {
              if (subtype[i][j].visible) {
                if (el.hasClass('bootstrapSwitch')) {
                  el.parent().parent().parent().show();
                  el.parent().parent().parent().removeClass('hide');
                }
                if (el.attr('type') == 'checkbox') {
                  el.parent().show();
                  el.parent().removeClass('hide');
                }
                el.show();
                el.removeClass('hide');
              } else {
                if (el.hasClass('bootstrapSwitch')) {
                  el.parent().parent().parent().hide();
                  el.parent().parent().parent().addClass('hide');
                }
                if (el.attr('type') == 'checkbox') {
                  el.parent().hide();
                  el.parent().addClass('hide');
                }
                el.hide();
                el.addClass('hide');
              }
            }
            if (isset(subtype[i][j].parentVisible)) {
              if (subtype[i][j].parentVisible) {
                el.parent().show();
                el.parent().removeClass('hide');
              } else {
                el.parent().hide();
                el.parent().addClass('hide');
              }
            }
          }
        }
      }

      if (_cmd.find('.cmdAttr[data-l1key=type]').value() == 'action') {
        _cmd.find('.cmdAttr[data-l1key=value]').show();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').show();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdToValue]').show();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=returnStateValue]').hide();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=returnStateTime]').hide();
      } else {
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=returnStateValue]').show();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=returnStateTime]').show();
        _cmd.find('.cmdAttr[data-l1key=value]').hide();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').hide();
        _cmd.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdToValue]').hide();
      }
      modifyWithoutSave = false;
    }
  });
};

nextdom.cmd.availableType = function () {
  var selType = '<select style="width : 120px; margin-bottom : 3px;" class="cmdAttr form-control input-sm" data-l1key="type">';
  selType += '<option value="info">{{Info}}</option>';
  selType += '<option value="action">{{Action}}</option>';
  selType += '</select>';
  return selType;
};

nextdom.cmd.getSelectModal = function (_options, _callback) {
  if (!isset(_options)) {
    _options = {};
  }
  if ($("#mod_insertCmdValue").length == 0) {
    $('body').append('<div id="mod_insertCmdValue" title="{{Sélectionner la commande}}" ></div>');
    $("#mod_insertCmdValue").dialog({
      closeText: '',
      autoOpen: false,
      modal: true,
      height: 250,
      width: 800
    });
    jQuery.ajaxSetup({
      async: false
    });
    $('#mod_insertCmdValue').load('index.php?v=d&modal=cmd.human.insert');
    jQuery.ajaxSetup({
      async: true
    });
  }
  mod_insertCmd.setOptions(_options);
  $("#mod_insertCmdValue").dialog('option', 'buttons', {
    "Annuler": function () {
      $(this).dialog("close");
    },
    "Valider": function () {
      var retour = {};
      retour.cmd = {};
      retour.human = mod_insertCmd.getValue();
      retour.cmd.id = mod_insertCmd.getCmdId();
      retour.cmd.type = mod_insertCmd.getType();
      retour.cmd.subType = mod_insertCmd.getSubType();
      if ($.trim(retour) != '' && 'function' == typeof (_callback)) {
        _callback(retour);
      }
      $(this).dialog('close');
    }
  });
  $('#mod_insertCmdValue').dialog('open');
};

nextdom.cmd.displayActionOption = function (_expression, _options, _callback) {
  var html = '';
  $.ajax({
    type: 'POST',
    url: "src/ajax.php",
    data: {
      target: 'Scenario',
      action: 'actionToHtml',
      version: 'scenario',
      expression: _expression,
      option: json_encode(_options)
    },
    dataType: 'json',
    async: ('function' == typeof (_callback)),
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        notify('Core', data.result, "error");
        return;
      }
      if (data.result.html != '') {
        html += data.result.html;
      }
      if ('function' == typeof (_callback)) {
        _callback(html);
        return;
      }
    }
  });
  return html;
};

nextdom.cmd.displayActionsOption = function (queryParams) {
  var paramsRequired = ['params'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Scenario', 'actionToHtml');
    ajaxParams.async = queryParams.async || true;
    ajaxParams.data['params'] = json_encode(queryParams.params);
    $.ajax(ajaxParams);
  }
};

nextdom.cmd.normalizeName = function (_tagname) {
  cmdName = _tagname.toLowerCase().trim();
  var cmdTests = [];
  var cmdType = null;
  var cmdList = {
    'on': 'on',
    'off': 'off',
    'monter': 'on',
    'descendre': 'off',
    'ouvrir': 'on',
    'ouvert': 'on',
    'fermer': 'off',
    'activer': 'on',
    'desactiver': 'off',
    'désactiver': 'off',
    'lock': 'on',
    'unlock': 'off',
    'marche': 'on',
    'arret': 'off',
    'arrêt': 'off',
    'stop': 'off',
    'go': 'on'
  };
  var cmdTestsList = [' ', '-', '_'];
  for (var i in cmdTestsList) {
    cmdTests = cmdTests.concat(cmdName.split(cmdTestsList[i]))
  }
  for (var j in cmdTests) {
    if (cmdList[cmdTests[j]]) {
      return cmdList[cmdTests[j]];
    }
  }
  return _tagname;
};


nextdom.cmd.setOrder = function (queryParams) {
  var paramsRequired = ['cmds'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'setOrder');
    ajaxParams.data['cmds'] = json_encode(queryParams.cmds);
    $.ajax(ajaxParams);
  }
};


nextdom.cmd.displayDuration = function (_date, _el) {
  var arrDate = _date.split(/-|\s|:/);
  var timeInMillis = new Date(arrDate[0], arrDate[1] - 1, arrDate[2], arrDate[3], arrDate[4], arrDate[5]).getTime();
  _el.attr('data-time', timeInMillis);
  if (_el.attr('data-interval') !== undefined) {
    clearInterval(_el.attr('data-interval'));
  }
  if (_el.attr('data-time') < (Date.now() + clientServerDiffDatetime)) {
    var d = ((Date.now() + clientServerDiffDatetime) - _el.attr('data-time')) / 1000;
    var j = Math.floor(d / 86400);
    var h = Math.floor(d % 86400 / 3600);
    var m = Math.floor(d % 3600 / 60);
    _el.empty().append(((j > 0 ? j + " j " : "") + (h > 0 ? h + " h " : "") + (m > 0 ? (h > 0 && m < 10 ? '0' : "") + m + " min" : "0 min")));
    var myinterval = setInterval(function () {
      var d = ((Date.now() + clientServerDiffDatetime) - _el.attr('data-time')) / 1000;
      var j = Math.floor(d / 86400);
      var h = Math.floor(d % 86400 / 3600);
      var m = Math.floor(d % 3600 / 60);
      _el.empty().append(((j > 0 ? j + " j " : "") + (h > 0 ? h + " h " : "") + (m > 0 ? (h > 0 && m < 10 ? '0' : "") + m + " min" : "0 min")));
    }, 60000);
    _el.attr('data-interval', myinterval);
  } else {
    _el.empty().append("0 min");
    var myinterval = setInterval(function () {
      if (_el.attr('data-time') < (Date.now() + clientServerDiffDatetime)) {
        var d = ((Date.now() + clientServerDiffDatetime) - _el.attr('data-time')) / 1000;
        var j = Math.floor(d / 86400);
        var h = Math.floor(d % 86400 / 3600);
        var m = Math.floor(d % 3600 / 60);
        _el.empty().append(((j > 0 ? j + " j " : "") + (h > 0 ? h + " h " : "") + (m > 0 ? (h > 0 && m < 10 ? '0' : "") + m + " min" : "0 min")));
      } else {
        _el.empty().append("0 min");
      }
    }, 60000);
    _el.attr('data-interval', myinterval);
  }
};
