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
nextdom.cmd.execute = function (_params) {
  var notify = _params.notify || true;
  if (notify) {
    var eqLogic = $('.cmd[data-cmd_id=' + _params.id + ']').closest('.eqLogic');
    eqLogic.find('.statusCmd').empty().append('<i class="fa fa-spinner fa-spin"></i>');
  }
  if (_params.value != 'undefined' && (is_array(_params.value) || is_object(_params.value))) {
    _params.value = json_encode(_params.value);
  }
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    global: false,
    pre_success: function (data) {
      if (data.state != 'ok') {
        if (data.code == -32005) {
          if ($.mobile) {
            var result = prompt("{{Veuillez indiquer le code ?}}", "")
            if (result != null) {
              _params.codeAccess = result;
              nextdom.cmd.execute(_params);
            } else {
              nextdom.cmd.refreshValue({id: _params.id});
              if ('function' != typeof(_params.error)) {
                $('#div_alert').showAlert({
                  message: data.result,
                  level: 'danger'
                });
              }
              if (notify) {
                eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
                setTimeout(function () {
                  eqLogic.find('.statusCmd').empty();
                }, 3000);
              }
              return data;
            }
          } else {
            bootbox.prompt("{{Veuillez indiquer le code ?}}", function (result) {
              if (result != null) {
                _params.codeAccess = result;
                nextdom.cmd.execute(_params);
              } else {
                nextdom.cmd.refreshValue({id: _params.id});
                if ('function' != typeof(_params.error)) {
                  $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                  });
                }
                if (notify) {
                  eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
                  setTimeout(function () {
                    eqLogic.find('.statusCmd').empty();
                  }, 3000);
                }
                return data;
              }

            });
          }
        } else if (data.code == -32006) {
          if ($.mobile) {
            var result = confirm("{{Etes-vous sûr de vouloir faire cette action ?}}")
            if (result) {
              _params.confirmAction = 1;
              nextdom.cmd.execute(_params);
            } else {
              nextdom.cmd.refreshValue({id: _params.id});
              if ('function' != typeof(_params.error)) {
                $('#div_alert').showAlert({
                  message: data.result,
                  level: 'danger'
                });
              }
              if (notify) {
                eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
                setTimeout(function () {
                  eqLogic.find('.statusCmd').empty();
                }, 3000);
              }
              return data;
            }
          } else {
            bootbox.confirm("{{Etes-vous sûr de vouloir faire cette action ?}}", function (result) {
              if (result) {
                _params.confirmAction = 1;
                nextdom.cmd.execute(_params);
              } else {
                nextdom.cmd.refreshValue({id: _params.id});
                if ('function' != typeof(_params.error)) {
                  $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                  });
                }
                if (notify) {
                  eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
                  setTimeout(function () {
                    eqLogic.find('.statusCmd').empty();
                  }, 3000);
                }
                return data;
              }

            });
          }
        } else {
          if ('function' != typeof(_params.error)) {
            $('#div_alert').showAlert({
              message: data.result,
              level: 'danger'
            });
          }
          if (notify) {
            eqLogic.find('.statusCmd').empty().append('<i class="fa fa-times"></i>');
            setTimeout(function () {
              eqLogic.find('.statusCmd').empty();
            }, 3000);
          }
          return data;
        }
      }
      if (notify) {
        eqLogic.find('.statusCmd').empty().append('<i class="fa fa-rss"></i>');
        setTimeout(function () {
          eqLogic.find('.statusCmd').empty();
        }, 3000);
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
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  var cache = 1;
  if (_params.cache !== undefined) {
    cache = _params.cache;
  }
  paramsAJAX.data = {
    action: 'execCmd',
    id: _params.id,
    codeAccess: _params.codeAccess || '',
    confirmAction: _params.confirmAction || '',
    cache: cache,
    value: _params.value || '',
  };
  if (window.location.href.indexOf('p=dashboard') >= 0 || window.location.href.indexOf('p=plan') >= 0 || window.location.href.indexOf('p=view') >= 0 || $.mobile) {
    paramsAJAX.data.utid = utid;
  }
  $.ajax(paramsAJAX);
};

nextdom.cmd.test = function (_params) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    global: false,
    success: function (result) {
      switch (result.type) {
        case 'info':
          nextdom.cmd.execute({
            id: _params.id,
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
                id: _params.id,
                cache: 0,
                error: function (error) {
                  $('#div_alert').showAlert({
                    message: error.message,
                    level: 'danger'
                  });
                },
                success: function () {
                  $('#div_alert').showAlert({
                    message: '{{Action exécutée avec succès}}',
                    level: 'success'
                  });
                }
              });
              break;
            case 'slider':
              var slider = 50;
              if (isset(result.configuration) && isset(result.configuration.maxValue) && isset(result.configuration.minValue)) {
                slider = (result.configuration.maxValue - result.configuration.minValue) / 2;
              }
              nextdom.cmd.execute({
                id: _params.id,
                value: {
                  slider: slider
                },
                cache: 0,
                error: function (error) {
                  $('#div_alert').showAlert({
                    message: error.message,
                    level: 'danger'
                  });
                },
                success: function () {
                  $('#div_alert').showAlert({
                    message: '{{Action exécutée avec succès}}',
                    level: 'success'
                  });
                }
              });
              break;
            case 'color':
              nextdom.cmd.execute({
                id: _params.id,
                value: {
                  color: '#fff000'
                },
                cache: 0,
                error: function (error) {
                  $('#div_alert').showAlert({
                    message: error.message,
                    level: 'danger'
                  });
                },
                success: function () {
                  $('#div_alert').showAlert({
                    message: '{{Action exécutée avec succès}}',
                    level: 'success'
                  });
                }
              });
              break;
            case 'select':
              nextdom.cmd.execute({
                id: _params.id,
                value: {
                  select: result.configuration.listValue.split(';')[0].split('|')[0]
                },
                cache: 0,
                error: function (error) {
                  $('#div_alert').showAlert({
                    message: error.message,
                    level: 'danger'
                  });
                },
                success: function () {
                  $('#div_alert').showAlert({
                    message: '{{Action exécutée avec succès}}',
                    level: 'success'
                  });
                }
              });
              break;
            case 'message':
              nextdom.cmd.execute({
                id: _params.id,
                value: {
                  title: '{{[NextDom] Message de test}}',
                  message: '{{Ceci est un test de message pour la commande}} ' + result.name
                },
                cache: 0,
                error: function (error) {
                  $('#div_alert').showAlert({
                    message: error.message,
                    level: 'danger'
                  });
                },
                success: function () {
                  $('#div_alert').showAlert({
                    message: '{{Action exécutée avec succès}}',
                    level: 'success'
                  });
                }
              });
              break;
          }
          break;
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
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'getCmd',
    id: _params.id,
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.refreshByEqLogic = function (_params) {
  var cmds = $('.cmd[data-eqLogic_id=' + _params.eqLogic_id + ']');
  if (cmds.length > 0) {
    $(cmds).each(function () {
      if ($(this).closest('.eqLogic[data-eqLogic_id=' + _params.eqLogic_id + ']').html() != undefined) {
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

nextdom.cmd.refreshValue = function (_params) {
  for (var i in _params) {
    var cmd = $('.cmd[data-cmd_id=' + _params[i].cmd_id + ']');
    if (cmd.html() == undefined || cmd.hasClass('noRefresh')) {
      continue;
    }
    if (!isset(nextdom.cmd.update) || !isset(nextdom.cmd.update[_params[i].cmd_id])) {
      continue;
    }
    nextdom.cmd.update[_params[i].cmd_id](_params[i]);

  }
  for (var i in _params) {
    try {
      for (var j in nextdom.history.chart) {
        if (isset(nextdom.history.chart[j].chart) && isset(nextdom.history.chart[j].chart.series)) {
          $(nextdom.history.chart['div_graph'].chart.series).each(function (k, serie) {
            try {
              if (serie.options.id == _params[i].cmd_id) {
                serie.addPoint([Date.parse(_params[i].collectDate + ' UTC') - 1, _params[i].value], true, true);
              }
            } catch (error) {
            }
          });
        }
      }
    } catch (e) {
    }
  }
};

nextdom.cmd.toHtml = function (_params) {
  var paramsRequired = ['id', 'version'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'toHtml',
    id: _params.id,
    version: _params.version
  };
  $.ajax(paramsAJAX);
}

nextdom.cmd.replaceCmd = function (_params) {
  var paramsRequired = ['source_id', 'target_id'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'replaceCmd',
    source_id: _params.source_id,
    target_id: _params.target_id
  };
  $.ajax(paramsAJAX);
}

nextdom.cmd.save = function (_params) {
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
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'save',
    cmd: json_encode(_params.cmd)
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.multiSave = function (_params) {
  var paramsRequired = ['cmds'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cmd.cache.byId = [];
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
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'multiSave',
    cmd: json_encode(_params.cmds)
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.byId = function (_params) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cmd.cache.byId[data.result.id] = data.result;
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
  if (isset(nextdom.cmd.cache.byId[params.id]) && init(params.noCache, false) == false) {
    params.success(nextdom.cmd.cache.byId[params.id]);
    return;
  }
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'byId',
    id: _params.id
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.byHumanName = function (_params) {
  var paramsRequired = ['humanName'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.cmd.cache.byHumanName[data.result.humanName] = data.result;
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
  if (isset(nextdom.cmd.cache.byHumanName[params.humanName]) && init(params.noCache, false) == false) {
    params.success(nextdom.cmd.cache.byHumanName[params.humanName]);
    return;
  }
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'byHumanName',
    humanName: _params.humanName
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.usedBy = function (_params) {
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
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'usedBy',
    id: _params.id
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.changeType = function (_cmd, _subType) {
  var selSubType = '<select style="width : 120px;margin-top : 5px;" class="cmdAttr form-control input-sm" data-l1key="subType">';
  var type = _cmd.find('.cmdAttr[data-l1key=type]').value();
  nextdom.getConfiguration({
    key: 'cmd:type:' + type + ':subtype',
    default: 0,
    async: false,
    error: function (error) {
      _params.error(error);
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
      _params.error(error);
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
      if ($.trim(retour) != '' && 'function' == typeof(_callback)) {
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
    type: "POST",
    url: "core/ajax/scenario.ajax.php",
    data: {
      action: 'actionToHtml',
      version: 'scenario',
      expression: _expression,
      option: json_encode(_options)
    },
    dataType: 'json',
    async: ('function' == typeof(_callback)),
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({
          message: data.result,
          level: 'danger'
        });
        return;
      }
      if (data.result.html != '') {
        html += data.result.html;
      }
      if ('function' == typeof(_callback)) {
        _callback(html);
        return;
      }
    }
  });
  return html;
};

nextdom.cmd.displayActionsOption = function (_params) {
  var paramsRequired = ['params'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.async = _params.async || true;
  paramsAJAX.url = 'core/ajax/scenario.ajax.php';
  paramsAJAX.data = {
    action: 'actionToHtml',
    params: json_encode(_params.params)
  };
  $.ajax(paramsAJAX);
};

nextdom.cmd.normalizeName = function(_tagname) {
  cmdName = _tagname.toLowerCase().trim();
  var cmdTests = [];
  var cmdType = null;
  var cmdList = {
    'on':'on',
    'off':'off',
    'monter':'on',
    'descendre':'off',
    'ouvrir':'on',
    'ouvert':'on',
    'fermer':'off',
    'activer':'on',
    'desactiver':'off',
    'désactiver':'off',
    'lock':'on',
    'unlock':'off',
    'marche':'on',
    'arret':'off',
    'arrêt':'off',
    'stop':'off',
    'go':'on'
  };
  var cmdTestsList = [' ', '-', '_'];
  for(var i in cmdTestsList){
    cmdTests = cmdTests.concat(cmdName.split(cmdTestsList[i]))
  }
  for(var j in cmdTests){
    if(cmdList[cmdTests[j]]){
      return cmdList[cmdTests[j]];
    }
  }
  return _tagname;
};


nextdom.cmd.setOrder = function (_params) {
  var paramsRequired = ['cmds'];
  var paramsSpecifics = {};
  try {
    nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
  } catch (e) {
    (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
    return;
  }
  var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
  var paramsAJAX = nextdom.private.getParamsAJAX(params);
  paramsAJAX.url = 'core/ajax/cmd.ajax.php';
  paramsAJAX.data = {
    action: 'setOrder',
    cmds: json_encode(_params.cmds)
  };
  $.ajax(paramsAJAX);
};


nextdom.cmd.displayDuration = function (_date, _el) {
  var arrDate = _date.split(/-|\s|:/);
  var timeInMillis = new Date(arrDate[0], arrDate[1] - 1, arrDate[2], arrDate[3], arrDate[4], arrDate[5]).getTime();
  _el.attr('data-time', timeInMillis);
  if (_el.attr('data-interval') != undefined) {
    clearInterval(_el.attr('data-interval'));
  }
  if (_el.attr('data-time') < (Date.now() + clientServerDiffDatetime)) {
    var d = ((Date.now() + clientServerDiffDatetime) - _el.attr('data-time')) / 1000;
    var j = Math.floor(d / 86400);
    var h = Math.floor(d % 86400 / 3600);
    var m = Math.floor(d % 3600 / 60);
    _el.empty().append(((j > 0 ? j + " j " : "") + (h > 0 ? h + " h " : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + " min" : "0 min")));
    var myinterval = setInterval(function () {
      var d = ((Date.now() + clientServerDiffDatetime) - _el.attr('data-time')) / 1000;
      var j = Math.floor(d / 86400);
      var h = Math.floor(d % 86400 / 3600);
      var m = Math.floor(d % 3600 / 60);
      _el.empty().append(((j > 0 ? j + " j " : "") + (h > 0 ? h + " h " : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + " min" : "0 min")));
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
        _el.empty().append(((j > 0 ? j + " j " : "") + (h > 0 ? h + " h " : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + " min" : "0 min")));
      } else {
        _el.empty().append("0 min");
      }
    }, 60000);
    _el.attr('data-interval', myinterval);
  }
};
