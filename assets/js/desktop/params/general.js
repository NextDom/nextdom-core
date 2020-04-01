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

// Page init
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    nextdom.config.load({
        configuration: $('#general').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#general').setValues(data, '.configKey');
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
        }
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#general').delegate('.configKey', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadInformations();
    });

    // Save button
    $("#bt_saveGeneral").on('click', function (event) {
        nextdom.config.save({
            configuration: $('#general').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#general').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#general').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // Time Synchronisation button
    $('#bt_forceSyncHour').on('click', function () {
        nextdom.forceSyncHour({
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                notify('Info', '{{Commande réalisée avec succès}}', 'success');
            }
        });
    });

    // Last connection date reset
    $('#bt_resetHour').on('click',function(){
        $.ajax({
            type: 'POST',
            url: 'src/ajax.php',
            data: {
                target: 'NextDom',
                action: 'resetHour'
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                if (data.state != 'ok') {
                    notify('Erreur', data.result, 'error');
                    return;
                }
                location.reload();
            }
        });
    });

    // Reset installation key button
    $('#bt_resetHwKey').on('click',function(){
        $.ajax({
            type: "POST",
            url: 'src/ajax.php',
            data: {
                target: 'NextDom',
                action: 'resetHour'
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                if (data.state != 'ok') {
                    notify('Erreur', data.result, 'error');
                    return;
                }
                location.reload();
            }
        });
    });

    // Refresh hardware type button
    $('#bt_refreshHardwareType').on('click',function(){
        nextdom.config.save({
            configuration: {hardware_name : ''},
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                location.reload();
            }
        });
    });
}
