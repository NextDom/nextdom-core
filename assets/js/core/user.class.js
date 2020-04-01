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

nextdom.user = function() {
};

nextdom.user.connectCheck = 0;

nextdom.user.all = function(queryParams) {
  nextdom.private.ajax('User', 'all', queryParams);
};

nextdom.user.remove = function(queryParams) {
  nextdom.private.ajax('User', 'remove', queryParams, ['id']);
};

nextdom.user.save = function(queryParams) {
  nextdom.private.ajax('User', 'save', queryParams, ['users'], true);
};

nextdom.user.saveProfils = function(queryParams) {
  nextdom.private.ajax('User', 'saveProfils', queryParams, ['profils'], true);
};

nextdom.user.get = function(queryParams) {
  nextdom.private.ajax('User', 'get', queryParams, false, true);
};

nextdom.user.isConnect = function(queryParams) {
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
      var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, queryParams || {});
      var ajaxParams = nextdom.private.getParamsAJAX(params, 'User', 'isConnect');
      ajaxParams.global = false;
      nextdom.private.ajaxCall(ajaxParams);
    }
  } else {
    if ('function' == typeof (queryParams.success)) {
      queryParams.success(true);
    }
  }
};

nextdom.user.validateTwoFactorCode = function(queryParams) {
  var paramsRequired = ['code'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'User', 'validateTwoFactorCode');
    ajaxParams.data['code'] = queryParams.code;
    ajaxParams.data['enableTwoFactorAuthentification'] = queryParams.enableTwoFactorAuthentification || 0;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.user.removeTwoFactorCode = function(queryParams) {
  nextdom.private.ajax('User', 'removeTwoFactorCode', queryParams, ['id']);
};

nextdom.user.useTwoFactorAuthentification = function(queryParams) {
  nextdom.private.ajax('User', 'useTwoFactorAuthentification', queryParams, ['login'], false, false);
};

nextdom.user.login = function(queryParams) {
  var paramsRequired = ['username', 'password'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'User', 'login');
    ajaxParams.data['username'] = queryParams.username;
    ajaxParams.data['password'] = queryParams.password;
    ajaxParams.data['twoFactorCode'] = queryParams.twoFactorCode || '';
    ajaxParams.data['storeConnection'] = queryParams.storeConnection || 0;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.user.refresh = function(queryParams) {
  nextdom.private.ajax('User', 'refresh', queryParams);
};

nextdom.user.removeBanIp = function(queryParams) {
  nextdom.private.ajax('User', 'removeBanIp', queryParams);
};

nextdom.user.removeRegisterDevice = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'User', 'removeRegisterDevice');
  ajaxParams.data['key'] = queryParams.key;
  ajaxParams.data['user_id'] = queryParams.user_id || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.user.deleteSession = function(queryParams) {
  nextdom.private.ajax('User', 'deleteSession', queryParams, ['id']);
};

nextdom.user.supportAccess = function(queryParams) {
  nextdom.private.ajax('User', 'supportAccess', queryParams, ['enable']);
};