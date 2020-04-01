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
*/

// Page init
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    $('#div_displayEqLogicConfigure').setValues(eqLogicInfo, '.eqLogicAttr');
    $('.label-config').each(function () {
        if ($(this).html() == "") {
            $(this).html("/");
        }
    });

    if ($('.eqLogicAttr[data-l1key="display"][data-l2key="layout::dashboard"]').value() == 'table') {
      $("#widget_layout_table").show();
    }

    $('#tableCmdLayoutConfiguration tbody td .cmdLayoutContainer').sortable({
        connectWith: '#tableCmdLayoutConfiguration tbody td .cmdLayoutContainer',
        items: ".cmdLayout"
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    $('.sel_layout').on('change', function () {
        var type = $(this).attr('data-type');
        $('#widget_layout_table').hide();
        $('#widget_layout_' + $(this).value()).show();
    });

    $('.background-color-default').off('change').on('change', function () {
        if ($(this).value() == 1) {
            $(this).closest('td').find('.span_configureBackgroundColor').hide();
        } else {
            $(this).closest('td').find('.span_configureBackgroundColor').show();
        }
    });

    $('.background-color-transparent').off('change').on('change', function () {
        var td = $(this).closest('td');
        if ($(this).value() == 1) {
            td.find('.background-color').hide();
        } else {
            td.find('.background-color').show();
        }
    });

    $('.color-default').off('change').on('change', function () {
        var td = $(this).closest('td');
        if ($(this).value() == 1) {
            td.find('.color').hide();
        } else {
            td.find('.color').show();
        }
    });

    $('.border-default').off('change').on('change', function () {
        var td = $(this).closest('td');
        if ($(this).value() == 1) {
            td.find('.border').hide();
        } else {
            td.find('.border').show();
        }
    });

    $('.border-radius-default').off('change').on('change', function () {
        var td = $(this).closest('td');
        if ($(this).value() == 1) {
            td.find('.border-radius').hide();
        } else {
            td.find('.border-radius').show();
        }
    });

    $('.advanceWidgetParameterDefault').off('change').on('change', function () {
        if ($(this).value() == 1) {
            $(this).closest('td').find('.advanceWidgetParameter').hide();
        } else {
            $(this).closest('td').find('.advanceWidgetParameter').show();
        }
    });

    $('.advanceWidgetParameterColorTransparent').off('change').on('change', function () {
        if ($(this).value() == 1) {
            $(this).closest('td').find('.advanceWidgetParameterColor').hide();
        } else {
            $(this).closest('td').find('.advanceWidgetParameterColor').show();
        }
    });

    $('#bt_eqLogicConfigureGraph').on('click', function () {
        loadModal('modal2', '{{ Graphique des liens }}', 'graph.link&filter_type=eqLogic&filter_id=' + eqLogicInfo.id);
    });

    $('#table_widgetParameters').on('click', '.removeWidgetParameter', function () {
        $(this).closest('tr').remove();
    });

    $('#bt_EqLogicConfigurationTabComment').on('click', function () {
        setTimeout(function () {
            $('.eqLogicAttr[data-l1key=comment]').trigger('change');
        }, 10);
    });

    $('#bt_eqLogicConfigureRawObject').off('click').on('click', function () {
        loadModal('modal2', '{{ Informations brutes }}', 'object.display&class=eqLogic&id=' + eqLogicInfo.id);
    });

    $('#bt_addWidgetParameters').off().on('click', function () {
        var tr = '<tr>';
        tr += '<td>';
        tr += '<input class="form-control key" />';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="form-control value" />';
        tr += '</td>';
        tr += '<td class="text-center">';
        tr += '<a class="btn btn-danger btn-sm removeWidgetParameter"><i class="fas fa-trash no-spacing"></i></a>';
        tr += '</td>';
        tr += '</tr>';
        $('#table_widgetParameters tbody').append(tr);
    });

    $('.bt_displayWidget').off('click').on('click', function () {
        var eqLogic = $('#div_displayEqLogicConfigure').getValues('.eqLogicAttr')[0];
        loadModal('modal2', '{{ Widget }}', 'eqLogic.displayWidget&eqLogic_id=' + eqLogic.id + '&version=' + $(this).attr('data-version'));
    });

    $('#bt_eqLogicConfigureSave').on('click', function () {
        var eqLogic = $('#div_displayEqLogicConfigure').getValues('.eqLogicAttr')[0];
        if (!isset(eqLogic.display)) {
            eqLogic.display = {};
        }
        if (!isset(eqLogic.display.parameters)) {
            eqLogic.display.parameters = {};
        }
        $('#table_widgetParameters tbody tr').each(function () {
            eqLogic.display.parameters[$(this).find('.key').value()] = $(this).find('.value').value();
        });
        nextdom.eqLogic.save({
            eqLogics: [eqLogic],
            type: eqLogic.eqType_name,
            error: function (error) {
                notify("EqLogic", error.message, 'error');
            },
            success: function () {
                cmds = [];
                order = 1;
                $('#tableCmdLayoutConfiguration tbody td').find('.cmdLayout').each(function () {
                    cmd = {};
                    cmd.id = $(this).attr('data-cmd_id');
                    cmd.line = $(this).closest('td').attr('data-line');
                    cmd.column = $(this).closest('td').attr('data-column');
                    cmd.order = order;
                    cmds.push(cmd);
                    order++;
                });
                nextdom.cmd.setOrder({
                    version: 'dashboard',
                    cmds: cmds,
                    error: function (error) {
                        notify("EqLogic", error.message, 'error');
                    },
                    success: function () {
                        notify("EqLogic", '{{ Enregistrement réussi }}', 'success');
                    }
                });
                $('#md_modal').load('index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + $('.li_eqLogic.active').attr('data-eqLogic_id')).dialog('open');
            }
        });
    });

    $('#bt_eqLogicConfigureRemove').on('click', function () {
        bootbox.confirm('{{ Etes-vous sûr de vouloir supprimer cet équipement ? }}', function (result) {
            if (result) {
                var eqLogic = $('#div_displayEqLogicConfigure').getValues('.eqLogicAttr')[0];
                nextdom.eqLogic.remove({
                    id: eqLogic.id,
                    type: eqLogic.eqType_name,
                    error: function (error) {
                        notify("EqLogic", error.message, 'error');
                    },
                    success: function (data) {
                        notify("EqLogic", '{{ Suppression réalisée avec succès }}', 'success');
                    }
                });
            }
        });
    });

    $('.bt_advanceCmdConfigurationOnEqLogicConfiguration').off('click').on('click', function () {
        loadModal('modal2', '{{ Configuration de la commande }}', 'cmd.configure&cmd_id=' + $(this).attr('data-id'));
    });

    $('.advanceCmdConfigurationCmdConfigure').off('dblclick').on('dblclick', function () {
        loadModal('modal2', '{{ Configuration de la commande }}', 'cmd.configure&cmd_id=' + $(this).attr('data-id'));
    });

    $('#bt_resetbattery').on('click', function () {
        bootbox.confirm('{{ Avez vous changé les piles ? Cette action mettra la date de changement de piles à aujourd\'hui }}', function (result) {
            if (result) {
                var eqLogic = {};
                eqLogic['id'] = eqLogicInfo.id;
                eqLogic['configuration'] = {};
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1;
                var hh = today.getHours();
                var MM = today.getMinutes();
                var ss = today.getSeconds();
                var yyyy = today.getFullYear();
                eqLogic['configuration']['batterytime'] = yyyy + '-' + mm + '-' + dd + ' ' + hh + ':' + MM + ':' + ss;
                jeedom.eqLogic.simpleSave({
                    eqLogic: eqLogic,
                    error: function (error) {
                        $('#md_displayEqLogicConfigure').showAlert({message: error.message, level: 'danger'});
                    },
                    success: function (data) {
                        $('#md_displayEqLogicConfigure').showAlert({message: '{{ Changement de pile(s) pris en compte }}', level: 'success'});
                        $('.eqLogicAttr[data-l1key=configuration][data-l2key=batterytime]').value(yyyy+'-'+mm+'-'+dd+' '+hh+':'+MM+':'+ss);
                    }
                });
            }
        });
    });

    // Close button
    $('#bt_eqLogicConfigureClose').on('click', function () {
        $('#md_modal').dialog('close');
    });
}
