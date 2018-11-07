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

$('#in_login_username').on('focusout change keypress',function(){
    nextdom.user.useTwoFactorAuthentification({
        login: $('#in_login_username').value(),
        error: function (error) {
           notify('core',error.message, 'danger');
        },
        success: function (data) {
            if(data == 1){
                $('#div_twoFactorCode').show();
            }else{
                $('#div_twoFactorCode').hide();
            }
        }
    });
});

$('#bt_login_validate').on('click', function() {
    tryLogin();
});

$('#in_login_password').keypress(function(e) {
    if(e.which == 13) {
        tryLogin();
    }
});

$('#in_twoFactorCode').keypress(function(e) {
    if(e.which == 13) {
        tryLogin();
    }
});

function tryLogin() {
    nextdom.user.login({
        username: $('#in_login_username').val(),
        password: $('#in_login_password').val(),
        twoFactorCode: $('#in_twoFactorCode').val(),
        storeConnection: $('#cb_storeConnection').value(),
        error: function (error) {
            $('.login-box').addClass('animationShake');
            notify('Core',error.message,'error');
        },
        success: function (data) {
            $('.login-box').addClass('animationZoomOut');
            window.location.href = 'index.php?v=d';
        }
    });
}
