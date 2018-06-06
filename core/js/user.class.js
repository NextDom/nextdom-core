
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

 nextdom.user.all = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'all',
    };
    $.ajax(paramsAJAX);
}

nextdom.user.remove = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'remove',
        id: _params.id
    };
    $.ajax(paramsAJAX);
}

nextdom.user.save = function(_params) {
    var paramsRequired = ['users'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'save',
        users: json_encode(_params.users)
    };
    $.ajax(paramsAJAX);
}

nextdom.user.saveProfils = function(_params) {
    var paramsRequired = ['profils'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'saveProfils',
        profils: json_encode(_params.profils)
    };
    $.ajax(paramsAJAX);
}

nextdom.user.get = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'get',
        profils: json_encode(_params.profils)
    };
    $.ajax(paramsAJAX);
};

nextdom.user.isConnect = function(_params) {
    if (Math.round(+new Date() / 1000) > (nextdom.user.connectCheck + 300)) {
        var paramsRequired = [];
        var paramsSpecifics = {
            pre_success: function(data) {
                if (data.state != 'ok') {
                    return {state: 'ok', result: false};
                } else {
                    nextdom.user.connectCheck = Math.round(+new Date() / 1000);
                    return {state: 'ok', result: true};
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
        paramsAJAX.url = 'core/ajax/user.ajax.php';
        paramsAJAX.global = false;
        paramsAJAX.data = {
            action: 'isConnect',
        };
        $.ajax(paramsAJAX);
    } else {
        if ('function' == typeof (_params.success)) {
            _params.success(true);
        }
    }
}

nextdom.user.validateTwoFactorCode = function(_params) {
    var paramsRequired = ['code'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'validateTwoFactorCode',
        code: _params.code,
        enableTwoFactorAuthentification : _params.enableTwoFactorAuthentification || 0
    };
    $.ajax(paramsAJAX);
};

nextdom.user.useTwoFactorAuthentification = function(_params) {
    var paramsRequired = ['login'];
    var paramsSpecifics = {
        global: false,
    }
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'useTwoFactorAuthentification',
        login: _params.login
    };
    $.ajax(paramsAJAX);
};

nextdom.user.login = function(_params) {
    var paramsRequired = ['username','password'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'login',
        username: _params.username,
        password: _params.password,
        twoFactorCode: _params.twoFactorCode || '',
        storeConnection: _params.storeConnection || 0,
    };
    $.ajax(paramsAJAX);
};


nextdom.user.refresh = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'refresh',
    };
    $.ajax(paramsAJAX);
};


nextdom.user.removeBanIp = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'removeBanIp',
    };
    $.ajax(paramsAJAX);
};

nextdom.user.removeRegisterDevice = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'removeRegisterDevice',
        key: _params.key,
        user_id : _params.user_id || ''
    };
    $.ajax(paramsAJAX);
};

nextdom.user.deleteSession = function(_params) {
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
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'deleteSession',
        id: _params.id
    };
    $.ajax(paramsAJAX);
};

nextdom.user.supportAccess = function(_params) {
    var paramsRequired = ['enable'];
    var paramsSpecifics = {};
    try {
        nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
    } catch (e) {
        (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
        return;
    }
    var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
    var paramsAJAX = nextdom.private.getParamsAJAX(params);
    paramsAJAX.url = 'core/ajax/user.ajax.php';
    paramsAJAX.data = {
        action: 'supportAccess',
        enable: _params.enable
    };
    $.ajax(paramsAJAX);
};