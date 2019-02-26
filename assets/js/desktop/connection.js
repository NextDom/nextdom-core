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
var isTwoFactor = 0;

$('#in_login_username').on('focusout focusin change keypress',function(){
    checkTwoFactor();
});

$('#in_login_password').on('focusout focusin change keypress',function(){
    checkTwoFactor();
});

$('#in_twoFactorCode').on('focusout focusin change keypress',function(){
    checkTwoFactor();
});

$('#bt_login_validate').on('click', function() {
    checkTwoFactorLogin();
});

$('#in_login_password').keypress(function(e) {
    if(e.which == 13) {
        checkTwoFactorLogin();
    }
});

$('#in_twoFactorCode').keypress(function(e) {
    if(e.which == 13) {
        checkTwoFactorLogin();
    }
});

function checkTwoFactor() {
    $('#div_login_username').removeClass("has-error");
    $('#div_login_password').removeClass("has-error");
    $('#div_twoFactorCode').removeClass("has-error");
    $('#div_login_username').removeClass('animationShake');
    $('#div_login_password').removeClass('animationShake');
    $('#div_twoFactorCode').removeClass('animationShake');
    nextdom.user.useTwoFactorAuthentification({
        login: $('#in_login_username').value(),
        error: function (error) {
           notify('core',error.message, 'danger');
           $('#in_login_password').empty();
           $('#in_twoFactorCode').empty();
        },
        success: function (data) {
            isTwoFactor = data;
        }
    });
    if(isTwoFactor == 1){
        $('#div_twoFactorCode').show();
    }else{
        $('#div_twoFactorCode').hide();
    }
}

function checkTwoFactorLogin() {
    checkTwoFactor();
    if (document.getElementById("div_twoFactorCode").style.display === "none"){
        if(isTwoFactor == 1){
            $('#div_twoFactorCode').show();
        }else{
            $('#div_twoFactorCode').hide();
            tryLogin();
        }
    }else{
        if ($('#in_twoFactorCode').val() === ""){
            $('#div_twoFactorCode').addClass("has-error");
            $('#div_twoFactorCode').addClass('animationShake');
        }else{
            $('#div_twoFactorCode').hide();
            tryLogin();
        }
    }
}

function tryLogin() {
    $('.login-box').removeClass('animationZoomIn');
    nextdom.user.login({
        username: $('#in_login_username').val(),
        password: $('#in_login_password').val(),
        twoFactorCode: $('#in_twoFactorCode').val(),
        storeConnection: $('#cb_storeConnection').value(),
        error: function (error) {
            $('#div_login_username').addClass('animationShake');
            $('#div_login_password').addClass('animationShake');
            $('#div_twoFactorCode').addClass('animationShake');
            $('#div_login_username').addClass("has-error");
            $('#div_login_password').addClass("has-error");
            $('#div_twoFactorCode').addClass("has-error");
            notify('Core',error.message,'error');
            $('#in_login_password').empty();
            $('#in_twoFactorCode').empty();
        },
        success: function (data) {
            $('.login-box').addClass('animationZoomOut');
            window.location.href = 'index.php?v=d';
        }
    });
}
