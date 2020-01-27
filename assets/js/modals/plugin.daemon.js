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

// Page init
var timeout_refreshDeamonInfo = null;
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    refreshDeamonInfo();
}

/**
 * Init events on the profils page
 */
function initEvents() {
    $('.bt_startDeamon').on('click', function () {
        clearTimeout(timeout_refreshDeamonInfo);
        savePluginConfig({
            relaunchDeamon: false,
            success: function () {
                nextdom.plugin.deamonStart({
                    id: plugin_id,
                    forceRestart: 1,
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                        refreshDeamonInfo();
                        timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
                    },
                    success: function () {
                        refreshDeamonInfo();
                        timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
                    }
                });
            }
        });
    });

    $('.bt_stopDeamon').on('click', function () {
        clearTimeout(timeout_refreshDeamonInfo);
        nextdom.plugin.deamonStop({
            id: plugin_id,
            error: function (error) {
                notify('Erreur', error.message, 'error');
                refreshDeamonInfo();
                timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
            },
            success: function () {
                refreshDeamonInfo();
                timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
            }
        });
    });

    $('.bt_changeAutoMode').on('click', function () {
        clearTimeout(timeout_refreshDeamonInfo);
        var mode = $(this).attr('data-mode');
        nextdom.plugin.deamonChangeAutoMode({
            id: plugin_id,
            mode: mode,
            error: function (error) {
                notify('Erreur', error.message, 'error');
                refreshDeamonInfo();
                timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
            },
            success: function () {
                refreshDeamonInfo();
                timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
            }
        });
    });
}

/**
 * Refesh the demon informations
 */
function refreshDeamonInfo() {
    var in_progress = true;
    var nok = false;
    nextdom.plugin.getDeamonInfo({
        id: plugin_id,
        success: function (data) {
            switch (data.state) {
                case 'ok':
                    if (data.auto == 1) {
                        $('.bt_stopDeamon').show();
                    }
                    $('.deamonState').empty().append('<span class="label label-success label-sticker-sm">{{ OK }}</span>');
                    break;
                case 'nok':
                    if (data.auto == 1) {
                        nok = true;
                    }
                    $('.bt_stopDeamon').hide();
                    $('.deamonState').empty().append('<span class="label label-danger label-sticker-sm">{{ NOK }}</span>');
                    break;
                default:
                    $('.deamonState').empty().append('<span class="label label-warning label-sticker-sm">' + data.state + '</span>');
            }
            switch (data.launchable) {
                case 'ok':
                    $('.bt_startDeamon').show();
                    if (data.auto == 1 && data.state == 'ok') {
                        $('.bt_stopDeamon').show();
                    }
                    $('.deamonLaunchable').empty().append('<span class="label label-success label-sticker-sm">{{ OK }}</span>');
                    break;
                case 'nok':
                    if (data.auto == 1) {
                        nok = true;
                    }
                    $('.bt_startDeamon').hide();
                    $('.bt_stopDeamon').hide();
                    $('.deamonLaunchable').empty().append('<span class="label label-danger label-sticker-sm">{{ NOK }}</span><i class="spacing-right"></i><span class="label label-primary label-sticker-sm" style="white-space: unset;">' + data.launchable_message +'</span>');
                    break;
                default:
                    $('.deamonLaunchable').empty().append('<span class="label label-warning label-sticker-sm">' + data.state + '</span>');
            }
            $('.td_lastLaunchDeamon').empty().append(data.last_launch);
            if (data.auto == 1) {
                $('.bt_stopDeamon').hide();
                $('.bt_changeAutoMode').removeClass('btn-success').addClass('btn-danger');
                $('.bt_changeAutoMode').attr('data-mode', 0);
                $('.bt_changeAutoMode').html('<i class="fas fa-times"></i>{{ Désactiver }}');
            } else {
                if (data.launchable == 'ok' && data.state == 'ok') {
                    $('.bt_stopDeamon').show();
                }
                $('.bt_changeAutoMode').removeClass('btn-danger').addClass('btn-success');
                $('.bt_changeAutoMode').attr('data-mode', 1);
                $('.bt_changeAutoMode').html('<i class="fas fa-magic"></i>{{ Activer }}');
            }
            if (!nok) {
                $("#div_plugin_deamon").closest('.box').removeClass('box-danger').addClass('box-success');
            } else {
                $("#div_plugin_deamon").closest('.box').removeClass('box-success').addClass('box-danger');
            }

            if ($("#div_plugin_deamon").is(':visible')) {
                timeout_refreshDeamonInfo = setTimeout(refreshDeamonInfo, 5000);
            }
        }
    });
}
