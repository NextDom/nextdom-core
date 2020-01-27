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
printConvertColor();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    nextdom.config.load({
        configuration: $('#interact_config').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#interact_config').setValues(data, '.configKey');
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
    $('#interact_config').delegate('.configKey', 'change', function () {
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
    $('#bt_saveinteract_config').on('click', function (event) {
        saveConvertColor();
        nextdom.config.save({
            configuration: $('#interact_config').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#interact_config').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#interact_config').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // New color add
    $('#bt_addColorConvert').on('click', function () {
        addConvertColor('?','#FFFFFF');
        $('.colorpick').colorpicker();
    });

    /*CMD color*/
    $('.bt_selectWarnMeCmd').on('click', function () {
        nextdom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
            $('.configKey[data-l1key="interact::warnme::defaultreturncmd"]').value(result.human);
        });
    });
}

/**
 * Display all colors
 */
function printConvertColor() {
    $.ajax({
        type: "POST",
        url: "src/ajax.php",
        data: {
            target: 'Config',
            action: 'getKey',
            key: 'convertColor'
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
            $('#table_convertColor tbody').empty();
            for (var color in data.result) {
                addConvertColor(color, data.result[color]);
            }
            $('.colorpick').colorpicker();
            modifyWithoutSave = false;
        }
    });
}

/**
 * Add a color
 *
 * @param _color Color name
 * @param _html Color value
 */
function addConvertColor(_color, _html) {
    var tr = '<tr>';
    tr += '<td>';
    tr += '<input class="color form-control input-sm" value="' + init(_color) + '"/>';
    tr += '</td>';
    tr += '<td>';
    tr += '<div class="input-group">';
    tr += '<div class="colorpicker-component colorpick">';
    tr += '<input type="text" class="html form-control input-sm" value="' + init(_html) + '" />';
    tr += '<span class="input-group-addon"><i></i></span>';
    tr += '</div>';
    tr += '</div>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_convertColor tbody').append(tr);
    modifyWithoutSave = true;
}

/**
 * Save colors
 */
function saveConvertColor() {
    var value = {};
    var colors = {};
    $('#table_convertColor tbody tr').each(function () {
        colors[$(this).find('.color').value()] = $(this).find('.html').value();
    });
    value.convertColor = colors;
    $.ajax({
        type: "POST",
        url: "src/ajax.php",
        data: {
            target: 'Config',
            action: 'addKey',
            value: json_encode(value)
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
            modifyWithoutSave = false;
        }
    });
}
