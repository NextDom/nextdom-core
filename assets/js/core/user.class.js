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


nextdom.user = function () {
};
nextdom.user.connectCheck = 0;

nextdom.user.all = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'all');
  $.ajax(ajaxParams);
};

nextdom.user.remove = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.user.save = function (queryParams) {
  var paramsRequired = ['users'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'save');
    ajaxParams.data['users'] = json_encode(queryParams.users);
    $.ajax(ajaxParams);
  }
};

nextdom.user.saveProfils = function (queryParams) {
  var paramsRequired = ['profils'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'saveProfils');
    ajaxParams.data['profils'] = json_encode(queryParams.profils);
    $.ajax(ajaxParams);
  }
};

nextdom.user.get = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'get');
  ajaxParams.data['profils'] = json_encode(queryParams.profils);
  $.ajax(ajaxParams);
};

nextdom.user.isConnect = function (queryParams) {
  if (Math.round(+new Date() / 1000) > (nextdom.user.connectCheck + 300)) {
    var paramsRequired = [];
    var paramsSpecifics = {
      pre_success: function (data) {
        if (data.state != 'ok') {
          return {state: 'ok', result: false};
        } else {
          nextdom.user.connectCheck = Math.round(+new Date() / 1000);
          return {state: 'ok', result: true};
        }
      }
    };
    if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
      var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
      var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'isConnect');
      ajaxParams.global = false;
      $.ajax(ajaxParams);
    }
  } else {
    if ('function' == typeof (queryParams.success)) {
      queryParams.success(true);
    }
  }
};

nextdom.user.validateTwoFactorCode = function (queryParams) {
  var paramsRequired = ['code'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'validateTwoFactorCode');
    ajaxParams.data['code'] = queryParams.code;
    ajaxParams.data['enableTwoFactorAuthentification'] = queryParams.enableTwoFactorAuthentification || 0;
    $.ajax(ajaxParams);
  }
};

nextdom.user.removeTwoFactorCode = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'removeTwoFactorCode');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.user.useTwoFactorAuthentification = function (queryParams) {
  var paramsRequired = ['login'];
  var paramsSpecifics = {
    global: false,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'useTwoFactorAuthentification');
    ajaxParams.data['login'] = queryParams.login;
    $.ajax(ajaxParams);
  }
};

nextdom.user.login = function (queryParams) {
  var paramsRequired = ['username', 'password'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'login');
    ajaxParams.data['username'] = queryParams.username;
    ajaxParams.data['password'] = queryParams.password;
    ajaxParams.data['twoFactorCode'] = queryParams.twoFactorCode || '';
    ajaxParams.data['storeConnection'] = queryParams.storeConnection || 0;
    $.ajax(ajaxParams);
  }
};


nextdom.user.refresh = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'refresh');
  $.ajax(ajaxParams);
};


nextdom.user.removeBanIp = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'removeBanIp');
  $.ajax(ajaxParams);
};

nextdom.user.removeRegisterDevice = function (queryParams) {
  var params = $.extend({}, nextdom.private.defaultqueryParams, {}, queryParams || {});
  var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'removeRegisterDevice');
  ajaxParams.data['key'] = queryParams.key;
  ajaxParams.data['user_id'] = queryParams.user_id || '';
  $.ajax(ajaxParams);
};

nextdom.user.deleteSession = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'deleteSession');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.user.supportAccess = function (queryParams) {
  var paramsRequired = ['enable'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'User', 'supportAccess');
    ajaxParams.data['enable'] = queryParams.enable;
    $.ajax(ajaxParams);
  }
};