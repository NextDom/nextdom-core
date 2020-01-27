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
var submitTwoFactorButton = $('#submitTwoFactor');
var divLogin = $('#login-part1');
var divTwoFactor = $('#login-part2');
var installMobile = $('.btn-install-mobile');
var installMobilePre = $('.mobile-pre');
/**
 * Init events of the page
 */
function initEvents() {
    // Login input value change or focus out
    loginInput.on('focusout keyup paste', function (userEvent) {
        inputEvent(userEvent);
    });

    // Password input value change
    passwordInput.on('focusout keyup', function (userEvent) {
        inputEvent(userEvent);
    });

    // Two factor input value change
    twoFactorInput.on('focusout keyup', function (userEvent) {
        inputEvent(userEvent);
    });

    // Connexion buttons
    submitButton.on('click', function () {
        testIfUserUseTwoFactorAuth();
    });
    submitTwoFactorButton.on('click', function () {
        checkLogin();
    });

    // Password input ENTER key click
    passwordInput.keypress(function (e) {
        if (e.which === ENTER_KEY) {
            testIfUserUseTwoFactorAuth();
        }
    });

    // Two factor input ENTER key click
    twoFactorInput.keypress(function (e) {
        if (e.which === ENTER_KEY) {
            checkLogin();
        }
    });

    // Mobile install line copy
    if (installMobile !== undefined) {
        installMobile.click(function() {
            copyInstallCode();
        });
        installMobilePre.click(function() {
            copyInstallCode();
        });
    }
}

/**
 * Copy to clipboard the install line code
 *
 */
function copyInstallCode() {
    if (document.selection) {
       var selectedRange = document.body.createTextRange();
       selectedRange.moveToElementText(installMobilePre[0]);
       selectedRange.select();
    }
    else if (window.getSelection) {
       var selectedRange = document.createRange();
       selectedRange.selectNodeContents(installMobilePre[0]);
       window.getSelection().removeAllRanges();
       window.getSelection().addRange(selectedRange);
    }
    document.execCommand('copy');
    notify('Info', '{{Code d\'installation copié !}}', 'success');
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
        divLogin.hide();
        divTwoFactor.show();
    } else {
        divLogin.show();
        divTwoFactor.hide();
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
            if (useTwoFactor === 1) {
                updateTwoFactorVisibility();
            } else {
                checkLogin();
            }
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
        inputField.addClass("has-error");
        container.addClass('animationShake');
    }
    else {
        inputField.removeClass("has-error");
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
    submitButton.addClass('disabled');
    submitButton.find('.fa-refresh').show();
    submitButton.find('.fa-unlock').hide();
    submitTwoFactorButton.addClass('disabled');
    submitTwoFactorButton.find('.fa-refresh').show();
    submitTwoFactorButton.find('.fa-lock-open').hide();
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
            useTwoFactor = 0;
            passwordInput.val('');
            twoFactorInput.val('');
            updateTwoFactorVisibility();
            submitButton.removeClass('disabled');
            submitButton.find('.fa-refresh').hide();
            submitButton.find('.fa-unlock').show();
            submitTwoFactorButton.removeClass('disabled');
            submitTwoFactorButton.find('.fa-refresh').hide();
            submitTwoFactorButton.find('.fa-lock-open').show();
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
