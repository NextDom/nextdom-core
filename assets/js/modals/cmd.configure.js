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

$("#md_cmdConfigureSelectMultiple").dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    height: (jQuery(window).height() - 150),
    width: ((jQuery(window).width() - 150) < 1200) ? (jQuery(window).width() - 50) : 1200,
    position: {my: 'center', at: 'center', of: window},
    open: function () {
        $("body").css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
        $("body").css({overflow: 'inherit'});
    }
});

$('#table_widgetParametersCmd').delegate('.removeWidgetParameter', 'click', function () {
    $(this).closest('tr').remove();
});

$('#bt_addWidgetParametersCmd').off().on('click', function () {
    var tr = '<tr>';
    tr += '<td>';
    tr += '<input class="form-control key" />';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="form-control value" />';
    tr += '</td>';
    tr += '<td>';
    tr += '<a class="btn btn-danger btn-sm removeWidgetParameter pull-right"><i class="fas fa-times spacing-right"></i>{{Supprimer}}</a>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_widgetParametersCmd tbody').append(tr);
});

$('#div_displayCmdConfigure').setValues(cmdInfo, '.cmdAttr');
$('#bt_cmdConfigureRawObject').off('click').on('click', function () {
    $('#md_modal2').dialog({title: "{{Informations}}"});
    $("#md_modal2").load('index.php?v=d&modal=object.display&class=cmd&id=' + cmdInfo.id).dialog('open');
});
$('#bt_cmdConfigureGraph').on('click', function () {
    $('#md_modal2').dialog({title: "{{Graphique des liens}}"});
    $("#md_modal2").load('index.php?v=d&modal=graph.link&filter_type=cmd&filter_id=' + cmdInfo.id).dialog('open');
});

$('#bt_cmdConfigureCopyHistory').off('click').on('click', function () {
    nextdom.cmd.getSelectModal({cmd: {type: 'info', subType: cmdInfo.subType}}, function (result) {
        var target_id = result.cmd.id
        var name = result.human
        bootbox.confirm('{{Etes-vous sûr de vouloir copier l\'historique de}} <strong>' + cmdInfo.name + '</strong> {{vers}} <strong>' + name + '</strong> ? {{Il est conseillé de vider l\'historique de la commande}} : <strong>' + name + '</strong> {{ avant la copie}}', function (result) {
            if (result) {
                nextdom.history.copyHistoryToCmd({
                    source_id: cmdInfo.id,
                    target_id: target_id,
                    error: function (error) {
                        notify('Core', error.message, 'error');
                    },
                    success: function (data) {
                        notify('Core', '{{Historique copié avec succès}}', 'success');
                    }
                });
            }
        });
    });
});

$('#bt_cmdConfigureCopyHistory').off('click').on('click', function () {
    nextdom.cmd.getSelectModal({cmd: {type: 'info', subType: cmdInfo.subType}}, function (result) {
        var target_id = result.cmd.id
        var name = result.human
        bootbox.confirm('{{Etes-vous sûr de vouloir copier l\'historique de}} <strong>' + cmdInfo.name + '</strong> {{vers}} <strong>' + name + '</strong> ? {{Il est conseillé de vider l\'historique de la commande}} : <strong>' + name + '</strong> {{ avant la copie}}', function (result) {
            if (result) {
                nextdom.history.copyHistoryToCmd({
                    source_id: cmdInfo.id,
                    target_id: target_id,
                    error: function (error) {
                        notify('Core', error.message, 'error');
                    },
                    success: function (data) {
                        notify('Core', '{{Historique copié avec succès}}', 'success');
                    }
                });
            }
        });
    });
});


$('#bt_cmdConfigureReplaceMeBy').off('click').on('click', function () {
    nextdom.cmd.getSelectModal({cmd: {type: cmdInfo.type, subType: cmdInfo.subType}}, function (result) {
        var target_id = result.cmd.id
        var name = result.human
        bootbox.confirm('{{Etes-vous sûr de vouloir remplacer}} <strong>' + cmdInfo.name + '</strong> {{par}} <strong>' + name + '</strong> ?', function (result) {
            if (result) {
                nextdom.cmd.replaceCmd({
                    source_id: cmdInfo.id,
                    target_id: target_id,
                    error: function (error) {
                        notify('Core', error.message, 'error');
                    },
                    success: function (data) {
                        notify('Core', '{{Remplacement réalisé avec succès}}', 'success');
                    }
                });
            }
        });
    });
});

$('#bt_cmdConfigureReplaceByMe').off('click').on('click', function () {
    nextdom.cmd.getSelectModal({cmd: {type: cmdInfo.type, subType: cmdInfo.subType}}, function (result) {
        var target_id = result.cmd.id
        var name = result.human
        bootbox.confirm('{{Etes-vous sûr de vouloir remplacer l\'ID}} <strong>' + name + '</strong> {{par}} <strong>' + cmdInfo.name + '</strong> ?', function (result) {
            if (result) {
                nextdom.cmd.replaceCmd({
                    source_id: target_id,
                    target_id: cmdInfo.id,
                    error: function (error) {
                        notify('Core', error.message, 'error');
                    },
                    success: function (data) {
                        notify('Core', '{{Remplacement réalisé avec succès}}', 'success');
                    }
                });
            }
        });
    });
});


$('#bt_cmdConfigureReplaceIdByMe').off('click').on('click', function () {
    var target_id = prompt("{{ID de commande à remplacer ?}}");
    if (target_id == null) {
        return;
    }
    bootbox.confirm('{{Etes-vous sûr de vouloir remplacer}} <strong>' + target_id + '</strong> {{par}} <strong>' + cmdInfo.name + '</strong> ?', function (result) {
        if (result) {
            nextdom.cmd.replaceCmd({
                source_id: target_id,
                target_id: cmdInfo.id,
                error: function (error) {
                    notify('Core', error.message, 'error');
                },
                success: function (data) {
                    notify('Core', '{{Remplacement réalisé avec succès}}', 'success');
                }
            });
        }
    });
});


if (isset(cmdInfo.configuration.actionCheckCmd) && $.isArray(cmdInfo.configuration.actionCheckCmd) && cmdInfo.configuration.actionCheckCmd.length != null) {
    for (var i in cmdInfo.configuration.actionCheckCmd) {
        addActionCmd(cmdInfo.configuration.actionCheckCmd[i], 'actionCheckCmd', '{{Action}}');
    }
}

if (isset(cmdInfo.configuration.nextdomPreExecCmd) && $.isArray(cmdInfo.configuration.nextdomPreExecCmd) && cmdInfo.configuration.nextdomPreExecCmd.length != null) {
    for (var i in cmdInfo.configuration.nextdomPreExecCmd) {
        addActionCmd(cmdInfo.configuration.nextdomPreExecCmd[i], 'actionPreExecCmd', '{{Action}}');
    }
}

if (isset(cmdInfo.configuration.nextdomPostExecCmd) && $.isArray(cmdInfo.configuration.nextdomPostExecCmd) && cmdInfo.configuration.nextdomPostExecCmd.length != null) {
    for (var i in cmdInfo.configuration.nextdomPostExecCmd) {
        addActionCmd(cmdInfo.configuration.nextdomPostExecCmd[i], 'actionPostExecCmd', '{{Action}}');
    }
}

editorCodeDview = null;
editorCodeDplan = null;
editorCodeMobile = null;
editorCodeMview = null;
editorCodeDashboard = null;

$('#bt_codeDashboard').one('click', function () {
    setTimeout(function () {
        editorCodeDashboard = CodeMirror.fromTextArea(document.getElementById("ta_codeDashboard"), {
            lineNumbers: true,
            mode: "text/javascript",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }, 1);
});

$('#bt_codeDview').one('click', function () {
    setTimeout(function () {
        editorCodeDview = CodeMirror.fromTextArea(document.getElementById("ta_codeDview"), {
            lineNumbers: true,
            mode: "text/javascript",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }, 1);
});

$('#bt_codeDplan').one('click', function () {
    setTimeout(function () {
        editorCodeDplan = CodeMirror.fromTextArea(document.getElementById("ta_codeDplan"), {
            lineNumbers: true,
            mode: "text/javascript",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }, 1);
});

$('#bt_codeMobile').one('click', function () {
    setTimeout(function () {
        editorCodeMobile = CodeMirror.fromTextArea(document.getElementById("ta_codeMobile"), {
            lineNumbers: true,
            mode: "text/javascript",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }, 1);
});

$('#bt_codeMview').one('click', function () {
    setTimeout(function () {
        editorCodeMview = CodeMirror.fromTextArea(document.getElementById("ta_codeMview"), {
            lineNumbers: true,
            mode: "text/javascript",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }, 1);
});

$('#bt_reinitHtmlCode').on('click', function () {
    $('#ta_codeDashboard').value('');
    $('#ta_codeDview').value('');
    $('#ta_codeDplan').value('');
    $('#ta_codeMobile').value('');
    $('#ta_codeMview').value('');
    if (editorCodeDashboard != null) {
        editorCodeDashboard.setValue('');
    }
    if (editorCodeDview != null) {
        editorCodeDview.setValue('');
    }
    if (editorCodeDplan != null) {
        editorCodeDplan.setValue('');
    }
    if (editorCodeMobile != null) {
        editorCodeMobile.setValue('');
    }
    if (editorCodeMview != null) {
        editorCodeMview.setValue('');
    }
    notify('Core', '{{Opération effectuée avec succès, n\'oubliez pas de sauvegarder}}', 'success');
});


$('#bt_cmdConfigureSave').on('click', function () {
    var cmd = $('#div_displayCmdConfigure').getValues('.cmdAttr')[0];
    if (!isset(cmd.display)) {
        cmd.display = {};
    }
    if (!isset(cmd.display.parameters)) {
        cmd.display.parameters = {};
    }
    $('#table_widgetParametersCmd tbody tr').each(function () {
        cmd.display.parameters[$(this).find('.key').value()] = $(this).find('.value').value();
    });
    var checkCmdParameter = $('#div_nextdomCheckCmdCmdOption').getValues('.expressionAttr')[0];
    if (isset(checkCmdParameter) && isset(checkCmdParameter.options)) {
        cmd.configuration.nextdomCheckCmdCmdActionOption = checkCmdParameter.options;
    }
    cmd.configuration.actionCheckCmd = {};
    cmd.configuration.actionCheckCmd = $('#div_actionCheckCmd .actionCheckCmd').getValues('.expressionAttr');

    cmd.configuration.nextdomPreExecCmd = $('#div_actionPreExecCmd .actionPreExecCmd').getValues('.expressionAttr');

    cmd.configuration.nextdomPostExecCmd = $('#div_actionPostExecCmd .actionPostExecCmd').getValues('.expressionAttr');

    if (editorCodeDashboard != null) {
        cmd.html.dashboard = editorCodeDashboard.getValue();
    }
    if (editorCodeDview != null) {
        cmd.html.dview = editorCodeDview.getValue();
    }
    if (editorCodeDplan != null) {
        cmd.html.dplan = editorCodeDplan.getValue();
    }
    if (editorCodeMobile != null) {
        cmd.html.mobile = editorCodeMobile.getValue();
    }
    if (editorCodeMview != null) {
        cmd.html.mview = editorCodeMview.getValue();
    }
    nextdom.cmd.save({
        cmd: cmd,
        error: function (error) {
            notify('Core', error.message, 'error');
        },
        success: function () {
            notify('Core', '{{Enregistrement réussi}}', 'success');
        }
    });
});


$("body").undelegate('.bt_removeAction', 'click').delegate('.bt_removeAction', 'click', function () {
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

$("body").undelegate(".listCmd", 'click').delegate(".listCmd", 'click', function () {
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
    nextdom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        el.value(result.human);
        nextdom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            taAutosize();
        });
    });
});

$("body").undelegate(".listAction", 'click').delegate(".listAction", 'click', function () {
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
    nextdom.getSelectActionModal({}, function (result) {
        el.value(result.human);
        nextdom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            taAutosize();
        });
    });
});

$('body').undelegate(".cmdAction.expressionAttr[data-l1key=cmd]", 'focusout').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
    var type = $(this).attr('data-type')
    var expression = $(this).closest('.' + type).getValues('.expressionAttr');
    var el = $(this);
    nextdom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
        el.closest('.' + type).find('.actionOptions').html(html);
        taAutosize();
    })
});

function addActionCmd(_action, _type, _name) {
    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }
    var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';
    div += '<div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">';
    div += '<div class="pull-right">';
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
    div += '<label class="control-label label-check" data-toggle="tooltip" title="" data-original-title="{{Décocher pour désactiver l\'action}}">{{Activer}}</label>';
    div += '<input type="checkbox" class="expressionAttr spacing-left" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'exécute en parallèle des autres actions}}" />';
    div += '<label class="control-label label-check" data-toggle="tooltip" title="" data-original-title="{{Cocher pour que la commande s\'exécute en parallèle des autres actions}}">{{Parallèle}}</label>';
    div += '</div>';
    div += '</div>';
    div += '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">';
    div += '<div class="input-group">';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default btn-sm bt_removeAction" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn  btn-default btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
    div += '<a class="btn btn-default btn-sm listCmd" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';
    div += '<div class="col-lg-6 col-md-5 col-sm-4 col-xs-6 actionOptions">';
    div += nextdom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
    div += '</div>';
    $('#div_' + _type).append(div);
    $('#div_' + _type + ' .' + _type + ':last').setValues(_action, '.expressionAttr');
    taAutosize();
}

$('#bt_cmdConfigureSaveOn').on('click', function () {
    var cmd = $('#div_displayCmdConfigure').getValues('.cmdAttr')[0];
    if (!isset(cmd.display)) {
        cmd.display = {};
    }
    if (!isset(cmd.display.parameters)) {
        cmd.display.parameters = {};
    }
    $('#table_widgetParametersCmd tbody tr').each(function () {
        cmd.display.parameters[$(this).find('.key').value()] = $(this).find('.value').value();
    });
    cmd = {display: cmd.display, template: cmd.template};
    $('#md_cmdConfigureSelectMultiple').load('index.php?v=d&modal=cmd.selectMultiple&cmd_id=' + cmdInfo.id, function () {
        initTableSorter();
        $('#bt_cmdConfigureSelectMultipleAlertToogle').off().on('click', function () {
            var state = false;
            if ($(this).attr('data-state') == 0) {
                state = true;
                $(this).attr('data-state', 1);
                $(this).find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
                $('#table_cmdConfigureSelectMultiple tbody tr .selectMultipleApplyCmd:visible').value(1);
            } else {
                state = false;
                $(this).attr('data-state', 0);
                $(this).find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');
                $('#table_cmdConfigureSelectMultiple tbody tr .selectMultipleApplyCmd:visible').value(0);
            }
        });

        $('#bt_cmdConfigureSelectMultipleAlertApply').off().on('click', function () {
            $('#table_cmdConfigureSelectMultiple tbody tr').each(function () {
                if ($(this).find('.selectMultipleApplyCmd').prop('checked')) {
                    cmd.id = $(this).attr('data-cmd_id');
                    nextdom.cmd.save({
                        cmd: cmd,
                        error: function (error) {
                            notify('Core', error.message, 'error');
                        },
                        success: function () {

                        }
                    });
                }
            });
            notify("Core", "{{Modification(s) appliquée(s) avec succès}}", "success");
        });
    }).dialog('open');
});
$('#bt_cmdConfigureChooseIcon').on('click', function () {
    var iconeGeneric = $(this).closest('.iconeGeneric');
    chooseIcon(function (_icon) {
        iconeGeneric.find('.cmdAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
    });
});

$('body').undelegate('.cmdAttr[data-l1key=display][data-l2key=icon]', 'click').delegate('.cmdAttr[data-l1key=display][data-l2key=icon]', 'click', function () {
    $(this).empty();
});

$('#bt_cmdConfigureLogRealTime').off('click').on('click', function () {
    $('#md_modal2').dialog({title: "{{Logs}}"});
    $('#md_modal2').load('index.php?v=d&modal=log.display&log=event&search=' + cmdInfoSearchString).dialog('open');
});

$('#bt_cmdConfigureTest').on('click', function () {
    nextdom.cmd.test({id: cmdInfo.id});
});
