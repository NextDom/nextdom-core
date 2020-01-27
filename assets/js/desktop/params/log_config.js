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
        configuration: $('#log_config').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#log_config').setValues(data, '.configKey');
            loadActionOnMessage();
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
    $('#log_config').delegate('.configKey', 'change', function () {
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
    $("#bt_savelog_config").on('click', function (event) {
        var config = $('#log_config').getValues('.configKey')[0];
        config.actionOnMessage = json_encode($('#div_actionOnMessage .actionOnMessage').getValues('.expressionAttr'));
        nextdom.config.save({
            configuration: config,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#log_config').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#log_config').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // Log engine change
    $('#log_config').delegate('.configKey[data-l1key="log::engine"]', 'change', function () {
        $('.logEngine').hide();
        $('.logEngine.'+$(this).value()).show();
        modifyWithoutSave = true;
        $(".bt_cancelModifs").show();
    });

    // Add action on message
    $('#bt_addActionOnMessage').on('click',function(){
        actionOptions = [];
        addActionOnMessage();
    });

    // Remove action on message
    $("body").delegate('.bt_removeAction', 'click', function () {
        $(this).closest('.actionOnMessage').remove();
    });

    // Display option depend of choose action
    $('body').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
        var expression = $(this).closest('.actionOnMessage').getValues('.expressionAttr');
        var el = $(this);
        nextdom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
            el.closest('.actionOnMessage').find('.actionOptions').html(html);
            initTextAreaAutosize();
        })
    });

    // Timeline clear button
    $('#bt_removeTimelineEvent').on('click',function(){
        nextdom.removeTimelineEvents({
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                notify('Info', '{{Evènement de la timeline supprimé avec succès}}', 'success');
            }
        });
    });

    // ALert cmd
    $('.bt_selectAlertCmd').on('click', function () {
        var type=$(this).attr('data-type');
        nextdom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
            $('.configKey[data-l1key="alert::'+type+'Cmd"]').atCaret('insert', result.human);
        });
    });

    // Action buttons
    $("body").delegate(".listAction", 'click', function () {
        var el = $(this).closest('.actionOnMessage').find('.expressionAttr[data-l1key=cmd]');
        nextdom.getSelectActionModal({}, function (result) {
          el.value(result.human);
          nextdom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.actionOnMessage').find('.actionOptions').html(html);
            initTextAreaAutosize();
        });
      });
    });
    $("body").delegate(".listCmdAction", 'click', function () {
        var el = $(this).closest('.actionOnMessage').find('.expressionAttr[data-l1key=cmd]');
        nextdom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
            el.value(result.human);
            nextdom.cmd.displayActionOption(el.value(), '', function (html) {
                el.closest('.actionOnMessage').find('.actionOptions').html(html);
                initTextAreaAutosize();
            });
        });
    });
}

/**
 * Display actions on message
 */
function loadActionOnMessage(){
    $('#div_actionOnMessage').empty();
    nextdom.config.load({
        configuration: 'actionOnMessage',
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            if(data == ''){
                return;
            }
            actionOptions = [];
            for (var i in data) {
                addActionOnMessage(data[i]);
            }
            nextdom.cmd.displayActionsOption({
                params : actionOptions,
                async : false,
                error: function (error) {
                    notify('Erreur', error.message, 'error');
                },
                success : function(data){
                    for(var i in data){
                        $('#'+data[i].id).append(data[i].html.html);
                    }
                    initTextAreaAutosize();
                }
            });
        }
    });
}

/**
 * Add an action on message
 *
 * @param _action action object with options
 */
function addActionOnMessage(_action) {
    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }
    var div = '<div class="actionOnMessage">';
    div += '<div class="form-group">';
    div += '<label class="control-label">Action</label>';
    div += '<div>';
    div += '<label class="checkbox-inline">';
    div += '<input type="checkbox" class="expressionAttr" id="MessageActiv" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
    div += '{{Activer}}</label>';
    div += '<label class="checkbox-inline">';
    div += '<input type="checkbox" class="expressionAttr" id="MessagePara" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'éxecute en parrallele des autres actions}}" />';
    div += '{{En parallèle}}</label>';
    div += '</div>';
    div += '</div>';
    div += '<div class="form-group">';
    div += '<div class="input-group">';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-danger bt_removeAction btn-sm"><i class="fas fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default btn-sm listAction" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>';
    div += '<a class="btn btn-default btn-sm listCmdAction"><i class="fas fa-list-alt"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';
    div += '<div class="form-group">';
    var actionOption_id = uniqId();
    div += '<div class="actionOptions" id="'+actionOption_id+'">';
    div += '</div>';
    div += '</div>';
    $('#div_actionOnMessage').append(div);
    $('#div_actionOnMessage .actionOnMessage:last').setValues(_action, '.expressionAttr');
    actionOptions.push({
        expression : init(_action.cmd, ''),
        options : _action.options,
        id : actionOption_id
    });
}
