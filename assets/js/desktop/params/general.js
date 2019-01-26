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

jwerty.key('ctrl+s/⌘+s', function (e) {
    e.preventDefault();
    $("#bt_savegeneral").click();
});

 $("#bt_savegeneral").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#general').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#general').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#general').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#general').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#general').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#general').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('#bt_forceSyncHour').on('click', function () {
    $.hideAlert();
    nextdom.forceSyncHour({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            notify("Info", '{{Commande réalisée avec succès}}', 'success');
        }
    });
});

$("#bt_clearNextDomLastDate").on('click', function (event) {
    $.hideAlert();
    clearNextDomDate();
});

function clearNextDomDate() {
    $.ajax({
        type: "POST",
        url: "core/ajax/nextdom.ajax.php",
        data: {
            action: "clearDate"
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify("Erreur", data.result, 'error');
                return;
            }
            $('#in_nextdomLastDate').value('');
        }
    });
}

$('#bt_resetHour').on('click',function(){
 $.ajax({
    type: "POST",
    url: "core/ajax/nextdom.ajax.php",
    data: {
        action: "resetHour"
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {
        if (data.state != 'ok') {
            notify("Erreur", data.result, 'error');
            return;
        }
         location.reload();
    }
});
});

$('#bt_resetHwKey').on('click',function(){
 $.ajax({
    type: "POST",
    url: "core/ajax/nextdom.ajax.php",
    data: {
        action: "resetHwKey"
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {
        if (data.state != 'ok') {
            notify("Erreur", data.result, 'error');
            return;
        }
         location.reload();
    }
});
});

$('#bt_resetHardwareType').on('click',function(){
    nextdom.config.save({
        configuration: {hardware_name : ''},
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
                     location.reload();
        }
    });
});
