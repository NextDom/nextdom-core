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

/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*
* @Support <https://www.nextdom.org>
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

var useTwoFactor = 0;
var ENTER_KEY = 13;
var loginInput = $('#login');
var passwordInput = $('#password');
var twoFactorInput = $('#twofactor');
var submitButton = $('#submit');

/**
 * Init events of the page
 */
function initEvents() {
    loginInput.on('focusout keyup paste', function (userEvent) {
        testIfUserUseTwoFactorAuth();
        inputEvent(userEvent);
    });

    passwordInput.on('focusout keyup', function (userEvent) {
        inputEvent(userEvent);
    });

    twoFactorInput.on('focusout keyup', function (userEvent) {
        inputEvent(userEvent);
    });

    submitButton.on('click', function () {
        checkLogin();
    });

    passwordInput.keypress(function (e) {
        if (e.which === ENTER_KEY) {
            checkLogin();
        }
    });

    twoFactorInput.keypress(function (e) {
        if (e.which === ENTER_KEY) {
            checkLogin();
        }
    });
}

/**
 * Called on user event on input
 *
 * @param userEvent User event informations
 */
function inputEvent(userEvent) {
    if (userEvent.type === 'keyup' || userEvent.type === 'paste') {
        clearErrors();
    }
}

/**
 * Show or hide two factor visibility (depends of useTwoFactor)
 */
function updateTwoFactorVisibility() {
    if (useTwoFactor === 1) {
        twoFactorInput.parent().show();
    } else {
        twoFactorInput.parent().hide();
    }
}

/**
 * Ask NextDom to know if user uses two factor authentication
 */
function testIfUserUseTwoFactorAuth() {
    nextdom.user.useTwoFactorAuthentification({
        login: loginInput.value(),
        error: function (error) {
            notify('core', error.message, 'danger');
            passwordInput.empty();
            twoFactorInput.empty();
        },
        success: function (useTwoFactorAnswer) {
            useTwoFactor = parseInt(useTwoFactorAnswer);
            updateTwoFactorVisibility();
        }
    });
}

/**
 * Clear errors on the form
 */
function clearErrors() {
    setErrorOnInput(loginInput, false);
    setErrorOnInput(passwordInput, false);
    setErrorOnInput(twoFactorInput, false);
}

/**
 * Set error on form
 *
 * @param inputField Input where the error must be set
 * @param state True for set error on
 */
function setErrorOnInput(inputField, state) {
    var container = inputField.parent();
    if (state) {
        container.addClass("has-error");
        container.addClass('animationShake');
    }
    else {
        container.removeClass("has-error");
        container.removeClass('animationShake');
    }
}

/**
 * Check user login
 */
function checkLogin() {
    if (loginInput.val() === '') {
        setErrorOnInput(loginInput, true);
    }
    if (passwordInput.val() === '') {
        setErrorOnInput(twoFactorInput, true);
    }
    if (useTwoFactor && twoFactorInput.val() === '') {
        setErrorOnInput(twoFactorInput, true);
    }
    $('.login-box').removeClass('animationZoomIn');
    nextdom.user.login({
        username: loginInput.val(),
        password: passwordInput.val(),
        twoFactorCode: twoFactorInput.val(),
        storeConnection: $('#storeConnection').value(),
        error: function (error) {
            setErrorOnInput(loginInput, true);
            setErrorOnInput(passwordInput, true);
            setErrorOnInput(twoFactorInput, true);
            notify('Core', error.message, 'error');
            twoFactorInput.val('');
        },
        success: function (data) {
            $('.login-box').addClass('animationZoomOut');
            window.location.href = 'index.php?v=d';
        }
    });
}

/**
 * Entry point
 */
initEvents();
loginInput.focus();