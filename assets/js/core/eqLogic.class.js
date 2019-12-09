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


nextdom.eqLogic = function () {
};

nextdom.eqLogic.cache = Array();
nextdom.eqLogic.displayObjectName = false;

nextdom.eqLogic.changeDisplayObjectName = function (_display) {

};

if (!isset(nextdom.eqLogic.cache.getCmd)) {
  nextdom.eqLogic.cache.getCmd = Array();
}

if (!isset(nextdom.eqLogic.cache.byId)) {
  nextdom.eqLogic.cache.byId = Array();
}

nextdom.eqLogic.save = function (queryParams) {
  var paramsRequired = ['type', 'eqLogics'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.eqLogic.cache.byId[data.result.id])) {
        delete nextdom.eqLogic.cache.byId[data.result.id];
      }
      if (isset(nextdom.object.cache.all)) {
        delete nextdom.object.cache.all;
      }
      if (isset(nextdom.object.cache.getEqLogic[data.result.object_id])) {
        delete nextdom.object.cache.getEqLogic[data.result.object_id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'save');
    ajaxParams.async = queryParams.async || true;
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['eqLogic'] = json_encode(queryParams.eqLogics);
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.simpleSave = function (queryParams) {
  var paramsRequired = ['eqLogic'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'simpleSave');
    ajaxParams.data['eqLogic'] = json_encode(queryParams.eqLogic);
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.remove = function (queryParams) {
  var paramsRequired = ['id', 'type'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.eqLogic.cache.byId[queryParams.eqLogic_Id])) {
        delete nextdom.eqLogic.cache.byId[queryParams.eqLogic_Id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'remove');
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.copy = function (queryParams) {
  var paramsRequired = ['id', 'name'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.eqLogic.cache.byId[queryParams.eqLogic_Id])) {
        delete nextdom.eqLogic.cache.byId[queryParams.eqLogic_Id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'copy');
    ajaxParams.data['name'] = queryParams.name;
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.print = function (queryParams) {
  var paramsRequired = ['id', 'type'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.eqLogic.cache.getCmd[queryParams.id] = data.result.cmd;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'get');
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['status'] = queryParams.status || 0;
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.toHtml = function (queryParams) {
  var paramsRequired = ['id', 'version'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'toHtml');
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['version'] = queryParams.version;
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.getCmd = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (data) {
      nextdom.eqLogic.cache.getCmd[queryParams.id] = data.result;
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    if (isset(nextdom.eqLogic.cache.getCmd[queryParams.id]) && 'function' == typeof (queryParams.success) && init(queryParams.noCache, false) == false) {
      queryParams.success(nextdom.eqLogic.cache.getCmd[queryParams.id]);
      return;
    }
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'byEqLogic');
    ajaxParams.data['eqLogic_id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};


nextdom.eqLogic.byId = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {
    pre_success: function (result) {
      nextdom.eqLogic.cache.byId[queryParams.id] = result;
      return result;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    if (init(queryParams.noCache, false) == false && isset(nextdom.eqLogic.cache.byId[queryParams.id]) && 'function' == typeof (queryParams.success)) {
      queryParams.success(nextdom.eqLogic.cache.byId[queryParams.eqLogic_id]);
      return;
    }
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'byId');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.builSelectCmd = function (queryParams) {
  if (!isset(queryParams.filter)) {
    queryParams.filter = {};
  }
  nextdom.eqLogic.getCmd({
    id: queryParams.id,
    async: false,
    success: function (cmds) {
      var result = '';
      for (var i in cmds) {
        if ((init(queryParams.filter.type, 'all') == 'all' || cmds[i].type == queryParams.filter.type) &&
          (init(queryParams.filter.subType, 'all') == 'all' || cmds[i].subType == queryParams.filter.subType) &&
          (init(queryParams.filter.isHistorized, 'all') == 'all' || cmds[i].isHistorized == queryParams.filter.isHistorized)
        ) {
          result += '<option value="' + cmds[i].id + '" data-type="' + cmds[i].type + '"  data-subType="' + cmds[i].subType + '" >' + cmds[i].name + '</option>';
        }
      }
      if ('function' == typeof (queryParams.success)) {
        queryParams.success(result);
      }
    }
  });
};

nextdom.eqLogic.getSelectModal = function (_options, callback) {
  if (!isset(_options)) {
    _options = {};
  }
  if ($("#mod_insertEqLogicValue").length == 0) {
    $('body').append('<div id="mod_insertEqLogicValue" title="{{Sélectionner un équipement}}" ></div>');

    $("#mod_insertEqLogicValue").dialog({
      closeText: '',
      autoOpen: false,
      modal: true,
      height: 250,
      width: 800
    });
    jQuery.ajaxSetup({async: false});
    $('#mod_insertEqLogicValue').load('index.php?v=d&modal=eqLogic.human.insert');
    jQuery.ajaxSetup({async: true});
  }
  mod_insertEqLogic.setOptions(_options);
  $("#mod_insertEqLogicValue").dialog('option', 'buttons', {
    "Annuler": function () {
      $(this).dialog("close");
    },
    "Valider": function () {
      var retour = {};
      retour.human = mod_insertEqLogic.getValue();
      retour.id = mod_insertEqLogic.getId();
      if ($.trim(retour) != '') {
        callback(retour);
      }
      $(this).dialog('close');
    }
  });
  $('#mod_insertEqLogicValue').dialog('open');
};

nextdom.eqLogic.refreshValue = function (queryParams) {
  var paramsRequired = [];
  var eqLogics = {};
  var sends = {};
  for (var i in queryParams) {
    nextdom.cmd.refreshByEqLogic({eqLogic_id: queryParams[i].eqLogic_id});
    var eqLogic = $('.eqLogic[data-eqLogic_id=' + queryParams[i].eqLogic_id + ']');
    if (eqLogic.html() === undefined || eqLogic.attr('data-version') === undefined) {
      continue;
    }
    eqLogics[queryParams[i].eqLogic_id] = {eqLogic: eqLogic, version: eqLogic.attr('data-version')};
    sends[queryParams[i].eqLogic_id] = {version: eqLogic.attr('data-version')};
  }
  if (Object.keys(eqLogics).length == 0) {
    return;
  }
  var paramsSpecifics = {
    global: false,
    success: function (result) {
      for (var i in result) {
        var gridstack = false;
        var html = $(result[i].html);
        var eqLogic = eqLogics[i].eqLogic;
        var visible = eqLogic.is(":visible");
        var uid = html.attr('data-eqLogic_uid');
        if (uid != 'undefined') {
          eqLogic.attr('data-eqLogic_uid', uid);
        }
        eqLogic.empty().html(html.children());
        eqLogic.attr("class", html.attr("class"));
        var top = eqLogic.css('top');
        var left = eqLogic.css('left');
        var width = eqLogic.css('width');
        var height = eqLogic.css('height');
        var margin = eqLogic.css('margin');
        var padding = eqLogic.css('padding');
        var position = eqLogic.css('position');
        var transform_origin = eqLogic.css('transform-origin');
        var transform = eqLogic.css('transform');
        var zindex = eqLogic.css('z-index');
        eqLogic.attr("style", html.attr("style"));
        eqLogic.css('top', top);
        eqLogic.css('left', left);
        eqLogic.css('width', width);
        eqLogic.css('height', height);
        eqLogic.css('margin', margin);
        eqLogic.css('padding', padding);
        eqLogic.css('position', position);
        eqLogic.css('transform-origin', transform_origin);
        eqLogic.css('transform', transform);
        eqLogic.css('z-index', zindex);
        if (!visible) {
          eqLogic.hide();
        }
        eqLogic.trigger('change');
        if ($.mobile) {
          $('.eqLogic[data-eqLogic_id=' + i + ']').trigger("create");
          setTileSize('.eqLogic');
        } else {
          if (typeof editWidgetMode == 'function') {
            editWidgetMode();
          }
        }
      }
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'toHtml');
    ajaxParams.data['ids'] = json_encode(sends);
    $.ajax(ajaxParams);
  }
};


nextdom.eqLogic.setOrder = function (queryParams) {
  var paramsRequired = ['eqLogics'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'setOrder');
    ajaxParams.data['eqLogics'] = json_encode(queryParams.eqLogics);
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.removes = function (queryParams) {
  var paramsRequired = ['eqLogics'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'removes');
    ajaxParams.data['eqLogics'] = json_encode(queryParams.eqLogics);
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.setIsVisibles = function (queryParams) {
  var paramsRequired = ['eqLogics', 'isVisible'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'setIsVisibles');
    ajaxParams.data['eqLogics'] = json_encode(queryParams.eqLogics);
    ajaxParams.data['isVisible'] = queryParams.isVisible;
    $.ajax(ajaxParams);
  }
};

nextdom.eqLogic.setIsEnables = function (queryParams) {
  var paramsRequired = ['eqLogics', 'isEnable'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'setIsEnables');
    ajaxParams.data['eqLogics'] = json_encode(queryParams.eqLogics);
    ajaxParams.data['isEnable'] = queryParams.isEnable;
    $.ajax(ajaxParams);
  }
};


nextdom.eqLogic.htmlAlert = function (queryParams) {
  var paramsRequired = ['version'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'htmlAlert');
    ajaxParams.data['version'] = queryParams.version;
    $.ajax(ajaxParams);
  }
};


nextdom.eqLogic.htmlBattery = function (queryParams) {
  var paramsRequired = ['version'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'EqLogic', 'htmlBattery');
    ajaxParams.data['version'] = queryParams.version;
    $.ajax(ajaxParams);
  }
};
