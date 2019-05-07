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
* @Email <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

var tab = null;
var modifyWithoutSave = false;
var editor = [];
var GENERAL_TAB = 'generaltab';

/**
 * List of colors for scenario elements
 * @type {string[]}
 */
var listColor = ['#16a085', '#27ae60', '#2980b9', '#745cb0', '#f39c12', '#d35400', '#c0392b', '#2c3e50', '#7f8c8d'];
var listColorStrong = ['#12846D', '#229351', '#246F9E', '#634F96', '#D88811', '#B74600', '#A53026', '#1D2935', '#687272'];
var colorIndex = 0;

/* Space before is normal */
var autoCompleteCondition = [
    " rand(MIN,MAX)",
    " #heure#",
    " #jour#",
    " #mois#",
    " #annee#",
    " #date#",
    " #time#",
    " #timestamp#",
    " #semaine#",
    " #sjour#",
    " #minute#",
    " #IP#",
    " #hostname#",
    " variable(mavariable,defaut)",
    " delete_variable(mavariable)",
    " tendance(commande,periode)",
    " average(commande,periode)",
    " max(commande,periode)",
    " min(commande,periode)",
    " round(valeur)",
    " trigger(commande)",
    " randomColor(debut,fin)",
    " lastScenarioExecution(scenario)",
    " stateDuration(commande)",
    " lastChangeStateDuration(commande,value)",
    " median(commande1,commande2)",
    " time(value)",
    " collectDate(cmd)",
    " valueDate(cmd)",
    " eqEnable(equipement)",
    " name(type,commande)",
    " value(commande)",
    " lastCommunication(equipment)"
];
autoCompleteAction = [
    "tag",
    "report",
    "sleep",
    "variable",
    "delete_variable",
    "scenario",
    "stop",
    "wait",
    "gotodesign",
    "log",
    "message",
    "equipement",
    "ask",
    "nextdom_poweroff",
    "scenario_return",
    "alert",
    "popup",
    "icon",
    "event",
    "remove_inat"
];

var pageContainer = $('#div_pageContainer');
var modalContainer = $('#md_modal');
var scenarioContainer = $('#div_scenarioElement');

/**
 * Event on scenario card click
 */
function loadScenario(scenarioId, tabToShow) {
    $.hideAlert();
    $('#scenarioThumbnailDisplay').hide();
    printScenario(scenarioId);
    var currentUrl = document.location.toString();
    // Mise à jour d'URL
    if (currentUrl.indexOf('id=') === -1) {
        var hashIndex = currentUrl.indexOf('#');
        var updatedUrl = '';
        if (hashIndex === -1) {
            history.pushState({}, null, currentUrl + '&id=' + scenarioId);
        }
        else {
            updatedUrl = currentUrl.substr(0, hashIndex);
            updatedUrl += '&id=' + scenarioId;
            updatedUrl += currentUrl.substr(hashIndex);
        }
        history.pushState({}, null, updatedUrl);
    }
    $('.nav-tabs a[href="#' + tabToShow + '"]').tab('show');
}

/**
 * Enable/Disable all scenarios
 */
function toggleAllScenariosState() {
    nextdom.config.save({
        configuration: {
            enableScenario: $("#bt_changeAllScenarioState").attr('data-state')
        },
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            loadPage('index.php?v=d&p=scenario');
        }
    });
}

/**
 * Add scenario (prompt for scenario name)
 */
function addScenario() {
    bootbox.prompt("Nom du scénario ?", function (result) {
        if (result !== null) {
            nextdom.scenario.save({
                scenario: {name: result},
                error: function (error) {
                    $('#div_alert').showAlert({message: error.message, level: 'danger'});
                },
                success: function (data) {
                    modifyWithoutSave = false;
                    $('#scenarioThumbnailDisplay').hide();
                    $('#bt_scenarioThumbnailDisplay').hide();
                    printScenario(data.id);
                }
            });
        }
    });
}

/**
 * Delete scenario
 */
function deleteScenario() {
    $.hideAlert();
    bootbox.confirm('{{Etes-vous sûr de vouloir supprimer le scénario}} <span style="font-weight: bold ;">' + $('.scenarioAttr[data-l1key=name]').value() + '</span> ?', function (result) {
        if (result) {
            nextdom.scenario.remove({
                id: $('.scenarioAttr[data-l1key=id]').value(),
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    modifyWithoutSave = false;
                    loadPage('index.php?v=d&p=scenario');
                    notify("Info", '{{Suppression effectuée avec succès}}', 'success');
                }
            });
        }
    });
}

/**
 * Test the scenario
 */
function testScenario() {
    $.hideAlert();
    nextdom.scenario.changeState({
        id: $('.scenarioAttr[data-l1key=id]').value(),
        state: 'start',
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            notify("Info", '{{Lancement du scénario réussi}}', 'success');
        }
    });
}

/**
 * Copy current scenario in another (prompt for name of the new scenario)
 */
function copyScenario() {
    bootbox.prompt("Nom du scénario ?", function (result) {
        if (result !== null && result !== '') {
            nextdom.scenario.copy({
                id: $('.scenarioAttr[data-l1key=id]').value(),
                name: result,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#scenarioThumbnailDisplay').hide();
                    $('#bt_scenarioThumbnailDisplay').hide();
                    printScenario(data.id);
                }
            });
        }
    });
}

/**
 * Stop current scenario
 */
function stopScenario() {
    nextdom.scenario.changeState({
        id: $('.scenarioAttr[data-l1key=id]').value(),
        state: 'stop',
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            notify("Info", '{{Arrêt du scénario réussi}}', 'success');
        }
    });
}

/**
 * Initialise sortables items
 */
function updateSortable() {
    $('.element').removeClass('sortable');
    $('#div_scenarioElement > .element').addClass('sortable');
    $('.subElement .expressions').each(function () {
        if ($(this).children('.sortable:not(.empty)').length > 0) {
            $(this).children('.sortable.empty').hide();
        } else {
            $(this).children('.sortable.empty').show();
        }
    });
}

/**
 * Initialise event on else button toggle
 */
function updateElseToggle() {
    $('.subElementElse').each(function () {
        if ($(this).parent().css('display') === 'table') $(this).parent().prev().find('.bt_addSinon:first').children('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    });
}

/**
 * Initialise code mirror on code element
 */
function setEditor() {
    $('.expressionAttr[data-l1key=type][value=code]').each(function () {
        var expression = $(this).closest('.expression');
        var code = expression.find('.expressionAttr[data-l1key=expression]');
        if (code.attr('id') === undefined && code.is(':visible')) {
            code.uniqueId();
            var id = code.attr('id');
            setTimeout(function () {
                editor[id] = CodeMirror.fromTextArea(document.getElementById(id), {
                    lineNumbers: true,
                    mode: 'text/x-php',
                    matchBrackets: true,
                    viewportMargin: Infinity
                });
            }, 1);
        }

    });
}

/**
 *
 * @param val
 * @returns {*}
 */
function splitAutocomplete(val) {
    return val.split(/ \s*/);
}

/**
 *
 * @param term
 * @returns {*}
 */
function extractLastAutocomplete(term) {
    return splitAutocomplete(term).pop();
}

/**
 * Initialise autocomplete fields
 */
function setAutocomplete() {
    $('.expression').each(function () {
        if ($(this).find('.expressionAttr[data-l1key=type]').value() === 'condition') {
            $(this).find('.expressionAttr[data-l1key=expression]').autocomplete({
                source: function (request, response) {
                    response($.ui.autocomplete.filter(
                        autoCompleteCondition, extractLastAutocomplete(request.term)));
                },
                classes: {
                    "ui-autocomplete": "autocomplete"
                },
                autoFocus: true,
                minLength: 0,
                focus: function () {
                    return false;
                },
                select: function (event, ui) {
                    var terms = splitAutocomplete(this.value);
                    terms.pop();
                    terms.push(ui.item.value.trim());
                    terms.push("");
                    this.value = terms.join(" ");
                    return false;
                }
            });
        }
        if ($(this).find('.expressionAttr[data-l1key=type]').value() === 'action') {
            $(this).find('.expressionAttr[data-l1key=expression]').autocomplete({
                source: autoCompleteAction,
                classes: {
                    "ui-autocomplete": "autocomplete"
                },
                autoFocus: true,
                minLength: 0,
                close: function (event, ui) {
                    $(this).trigger('focusout');
                }
            });
        }
    });
}

/**
 * Show the scenario
 * @param scenarioId
 */
function printScenario(scenarioId) {
    showLoadingCustom();
    nextdom.scenario.update[scenarioId] = function (_options) {
        if (_options.scenario_id = !pageContainer.getValues('.scenarioAttr')[0]['id']) {
            return;
        }
        var stopScenarioBtn = $('#bt_stopScenario');
        var scenarioState = $('#span_ongoing');
        switch (_options.state) {
            case 'error' :
                stopScenarioBtn.hide();
                scenarioState.text('{{Erreur}}');
                scenarioState.removeClass('label-info label-danger label-success').addClass('label-warning');
                break;
            case 'on' :
                stopScenarioBtn.show();
                scenarioState.text('{{Actif}}');
                scenarioState.removeClass('label-info label-danger label-warning').addClass('label-success');
                break;
            case 'in progress' :
                stopScenarioBtn.show();
                scenarioState.text('{{En cours}}');
                scenarioState.addClass('label-success');
                scenarioState.removeClass('label-success label-danger label-warning').addClass('label-info');
                break;
            case 'stop' :
            default :
                stopScenarioBtn.hide();
                scenarioState.text('{{Arrêté}}');
                scenarioState.removeClass('label-info label-success label-warning').addClass('label-danger');
        }
    };
    nextdom.scenario.get({
        id: scenarioId,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            colorIndex = 0;
            $('.scenarioAttr').value('');
            if (data.name) {
                document.title = data.name + ' - NextDom';
            }
            $('.scenarioAttr[data-l1key=object_id] option:first').attr('selected', true);
            $('.scenarioAttr[data-l1key=object_id]').val('');
            pageContainer.setValues(data, '.scenarioAttr');
            data.lastLaunch = (data.lastLaunch == null) ? '{{Jamais}}' : data.lastLaunch;
            $('#span_lastLaunch').text(data.lastLaunch);

            scenarioContainer.empty();
            $('.provokeMode').empty();
            $('.scheduleMode').empty();
            $('.scenarioAttr[data-l1key=mode]').trigger('change');
            for (var i in data.schedules) {
                $('#div_schedules').schedule.display(data.schedules[i]);
            }
            nextdom.scenario.update[scenarioId](data);
            if (data.isActive !== 1) {
                var inGoing = $('#in_going');
                inGoing.text('{{Inactif}}');
                inGoing.removeClass('label-danger');
                inGoing.removeClass('label-success');
            }
            if ($.isArray(data.trigger)) {
                for (var triggerIndex in data.trigger) {
                    if (data.trigger[triggerIndex] !== '' && data.trigger[triggerIndex] != null) {
                        addTrigger(data.trigger[triggerIndex]);
                    }
                }
            } else {
                if (data.trigger !== '' && data.trigger != null) {
                    addTrigger(data.trigger);
                }
            }
            if ($.isArray(data.schedule)) {
                for (var scheduleIndex in data.schedule) {
                    if (data.schedule[scheduleIndex] !== '' && data.schedule[scheduleIndex] != null) {
                        addSchedule(data.schedule[scheduleIndex]);
                    }
                }
            } else {
                if (data.schedule !== '' && data.schedule != null) {
                    addSchedule(data.schedule);
                }
            }

            if (data.elements.length === 0) {
                scenarioContainer.append('<div class="span_noScenarioElement"><span>{{Pour programmer votre scénario, veuillez commencer par ajouter des blocs...}}</span></div>')
            }
            actionOptions = [];
            for (var i in data.elements) {
                scenarioContainer.append(addElement(data.elements[i]));
            }
            nextdom.cmd.displayActionsOption({
                params: actionOptions,
                async: false,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    showLoadingCustom();
                    for (var i in data) {
                        $('#' + data[i].id).append(data[i].html.html);
                    }
                    hideLoadingCustom();
                    taAutosize();
                }
            });
            updateSortable();
            setInputExpressionsEvent();
            setAutocomplete();
            updateElseToggle();
            $('#div_editScenario').show();
            taAutosize();
            setTimeout(function () {
                setEditor();
            }, 100);
            modifyWithoutSave = false;
            setTimeout(function () {
                modifyWithoutSave = false;
            }, 1000);
        }
    });
}

/**
 * Save the scenario in the database
 */
function saveScenario() {
    $.hideAlert();
    var scenario = pageContainer.getValues('.scenarioAttr')[0];
    scenario.type = "expert";
    var elements = [];
    scenarioContainer.children('.element').each(function () {
        elements.push(getElement($(this)));
    });
    scenario.elements = elements;
    nextdom.scenario.save({
        scenario: scenario,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            notify("Info", '{{Sauvegarde effectuée avec succès}}', 'success');
        }
    });
    $('#bt_scenarioThumbnailDisplay').show();
}

/**
 * Add trigger start element
 *
 * @param triggerCode
 */
function addTrigger(triggerCode) {
    var triggerHtml = '<div class="form-group trigger">';
    triggerHtml += '<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label">{{Evénement}}</label>';
    triggerHtml += '<div class="col-lg-10 col-md-9 col-sm-6 col-xs-12">';
    triggerHtml += '<div class="input-group">';
    triggerHtml += '<input class="scenarioAttr input-sm form-control" data-l1key="trigger" value="' + triggerCode.replace(/"/g, '&quot;') + '" >';
    triggerHtml += '<span class="input-group-btn">';
    triggerHtml += '<a class="btn btn-default btn-sm cursor bt_selectTrigger" title="{{Choisir une commande}}"><i class="fas fa-list-alt"></i></a>';
    triggerHtml += '<a class="btn btn-default btn-sm cursor bt_selectDataStoreTrigger" title="{{Choisir une variable}}"><i class="fas fa-calculator"></i></a>';
    triggerHtml += '<a class="btn btn-default btn-sm cursor bt_removeTrigger"><i class="fas fa-minus-circle"></i></a>';
    triggerHtml += '</span>';
    triggerHtml += '</div>';
    triggerHtml += '</div>';
    triggerHtml += '</div>';
    $('.provokeMode').append(triggerHtml);
}

/**
 * Add schedule start element
 *
 * @param scheduleCode
 */
function addSchedule(scheduleCode) {
    var scheduleHtml = '<div class="form-group schedule">';
    scheduleHtml += '<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label">{{Programmation}}</label>';
    scheduleHtml += '<div class="col-lg-10 col-md-9 col-sm-6 col-xs-12">';
    scheduleHtml += '<div class="input-group">';
    scheduleHtml += '<input class="scenarioAttr input-sm form-control" data-l1key="schedule" value="' + scheduleCode.replace(/"/g, '&quot;') + '">';
    scheduleHtml += '<span class="input-group-btn">';
    scheduleHtml += '<a class="btn btn-default btn-sm cursor helpSelectCron"><i class="fas fa-question-circle"></i></a>';
    scheduleHtml += '<a class="btn btn-default btn-sm cursor bt_removeSchedule"><i class="fas fa-minus-circle"></i></a>';
    scheduleHtml += '</span>';
    scheduleHtml += '</div>';
    scheduleHtml += '</div>';
    scheduleHtml += '</div>';
    $('.scheduleMode').append(scheduleHtml);
}

/**
 * Get HTML data of a Condition expression
 * @param expressionData
 * @returns {string}
 */
function getConditionExpressionHTML(expressionData) {
    var htmlData = '';
    if (isset(expressionData.expression)) {
        expressionData.expression = expressionData.expression.replace(/"/g, '&quot;');
    }
    htmlData += '<div class="input-group input-group-sm no-border">';
    htmlData += '<textarea class="expressionAttr form-control scenario-text" data-l1key="expression" rows="1">' + init(expressionData.expression) + '</textarea>';
    htmlData += '<span class="input-group-btn">';
    htmlData += '<button type="button" class="btn btn-default cursor bt_selectCmdExpression tooltips" title="{{Rechercher une commande}}"><i class="fas fa-list-alt"></i></button>';
    htmlData += '<button type="button" class="btn btn-default cursor bt_selectScenarioExpression tooltips" title="{{Rechercher un scenario}}"><i class="fas fa-history"></i></button>';
    htmlData += '<button type="button" class="btn btn-default cursor bt_selectEqLogicExpression tooltips" title="{{Rechercher d\'un équipement}}"><i class="fas fa-cube"></i></button>';
    htmlData += '</span>';
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Element expression
 * @param expressionData
 * @returns {string}
 */
function getElementExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<div class="col-xs-12" style="padding-right: 0px; padding-left: 0px;">';
    if (isset(expressionData.element) && isset(expressionData.element.html)) {
        htmlData += expressionData.element.html;
    } else {
        var element = addElement(expressionData.element);
        if ($.trim(element) === '') {
            return '';
        }
        htmlData += element;
    }
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Action expression
 * @param expressionData
 * @returns {string}
 */
function getActionExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<div class="col-xs-1 scenario-action">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(expressionData.options) || !isset(expressionData.options.enable) || parseInt(expressionData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'action}}"/>';
    }
    if (!isset(expressionData.options) || !isset(expressionData.options.background) || parseInt(expressionData.options.background) === 0) {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'exécute en parallèle des autres actions}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" checked title="{{Cocher pour que la commande s\'exécute en parallèle des autres actions}}"/>';
    }
    var expression_txt = init(_expression.expression);
    if(typeof expression_txt !== 'string'){
        expression_txt = json_encode(expression_txt);
    }
    htmlData += '</div>';
    htmlData += '<div class="col-xs-11 scenario-sub-group"><div class="input-group input-group-sm no-border">';
    htmlData += '<span class="input-group-btn">';
    htmlData += '<button class="btn btn-default bt_removeExpression" type="button" title="{{Supprimer l\'action}}"><i class="fas fa-minus-circle"></i></button>';
    htmlData += '</span>';
    htmlData += '<input class="expressionAttr form-control" data-l1key="expression" value="' + expression_txt.replace(/"/g, '&quot;') + '" style="font-weight:bold;"/>';
    htmlData += '<span class="input-group-btn">';
    htmlData += '<button class="btn btn-default bt_selectOtherActionExpression" type="button" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></button>';
    htmlData += '<button class="btn btn-default bt_selectCmdExpression" type="button" title="{{Sélectionner la commande}}"><i class="fas fa-list-alt"></i></button>';
    htmlData += '</span>';
    htmlData += '</div></div>';
    var actionOption_id = uniqId();
    htmlData += '<div class="col-xs-11 col-xs-offset-1 expressionOptions scenario-sub-group" id="' + actionOption_id + '">';
    htmlData += '</div>';
    actionOptions.push({
        expression: init(expressionData.expression, ''),
        options: expressionData.options,
        id: actionOption_id
    });
    return htmlData;
}

/**
 * Get HTML data of a Code expression
 * @param expressionData
 * @returns {string}
 */
function getCodeExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<div>';
    htmlData += '<textarea class="expressionAttr scenario-code-text form-control" data-l1key="expression">' + init(expressionData.expression) + '</textarea>';
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Comment expression
 * @param expressionData
 * @returns {string}
 */
function getCommentExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<textarea class="expressionAttr scenario-comment-text form-control" data-l1key="expression">' + init(expressionData.expression) + '</textarea>';
    return htmlData;
}

/**
 * Add an expression in scenario (sub)element
 * @param expressionToAdd
 * @returns {string}
 */
function addExpression(expressionToAdd) {
    if (!isset(expressionToAdd) || !isset(expressionToAdd.type) || expressionToAdd.type === '') {
        return '';
    }
    var sortable = 'sortable';
    if (expressionToAdd.type === 'condition') {
        sortable = 'noSortable';
    }
    var htmlData = '<div class="expression scenario-group ' + sortable + ' col-xs-12">';
    htmlData += '<input class="expressionAttr" data-l1key="id" type="hidden" value="' + init(expressionToAdd.id) + '"/>';
    htmlData += '<input class="expressionAttr" data-l1key="scenarioSubElement_id" type="hidden" value="' + init(expressionToAdd.scenarioSubElement_id) + '"/>';
    htmlData += '<input class="expressionAttr" data-l1key="type" type="hidden" value="' + init(expressionToAdd.type) + '"/>';
    switch (expressionToAdd.type) {
        case 'condition':
            htmlData += getConditionExpressionHTML(expressionToAdd);
            break;
        case 'element' :
            htmlData += getElementExpressionHTML(expressionToAdd);
            break;
        case 'action' :
            htmlData += getActionExpressionHTML(expressionToAdd);
            break;
        case 'code' :
            htmlData += getCodeExpressionHTML(expressionToAdd);
            break;
        case 'comment' :
            htmlData += getCommentExpressionHTML(expressionToAdd);
            break;
    }
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get the first expression HTML code
 * @param subElementData
 * @returns {string}
 */
function addFirstExpressionHTML(subElementData) {
    var expression = {type: 'condition'};
    if (isset(subElementData.expressions) && isset(subElementData.expressions[0])) {
        expression = subElementData.expressions[0];
    }
    return addExpression(expression);
}

/**
 * Get all expression HTML code
 * @param subElementData
 * @returns {string}
 */
function addAllExpressionsHTML(subElementData) {
    var expressionsData = '';
    if (isset(subElementData.expressions)) {
        for (var expressionIndex in subElementData.expressions) {
            expressionsData += addExpression(subElementData.expressions[expressionIndex]);
        }
    }
    return expressionsData;
}

/**
 * Get HTML data of the If block
 *
 * @param subElementData
 * @returns {string}
 */
function getIfSubElementHTML(subElementData) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-si">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    var checked = '';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        checked = ' checked="checked"';
    }
    htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="Décocher pour désactiver l\'élément" ' + checked + '/>';
    htmlData += '<span class="scenario-title">{{SI}}</span>';
    if (!isset(subElementData.options) || !isset(subElementData.options.allowRepeatCondition) || parseInt(subElementData.options.allowRepeatCondition) === 0) {
        htmlData += '<a class="btn btn-default btn-sm cursor subElementAttr tooltips scenario-btn-repeat" title="{{Autoriser ou non la répétition des actions si l\'évaluation de la condition est la même que la précédente}}" data-l1key="options" data-l2key="allowRepeatCondition" value="0"><i class="fas fa-refresh"></i></a>';
    } else {
        htmlData += '<a class="btn btn-default btn-sm cursor subElementAttr tooltips scenario-btn-repeat" title="{{Autoriser ou non la répétition des actions si l\'évaluation de la condition est la même que la précédente}}" data-l1key="options" data-l2key="allowRepeatCondition" value="1"><i class="fas fa-ban text-danger"></i></a>';
    }
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an then block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getThenSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '  <div class="scenario-alors">';
    htmlData += '     <button class="btn btn-xs btn-default bt_addSinon scenario-expand" type="button" id="addSinon" data-toggle="dropdown" title="{{Afficher/masquer le bloc Sinon}}" aria-haspopup="true" aria-expanded="true">';
    htmlData += '       <i class="fas fa-chevron-right"></i>';
    htmlData += '     </button>';
    htmlData += '     <span class="scenario-title">{{ALORS}}</span>';
    htmlData += '     <div class="dropdown cursor">';
    htmlData += '       <button class="btn btn-xs btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '         <i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '       </button>';
    htmlData += '       <ul class="dropdown-menu">';
    htmlData += '         <li><a class="bt_addScenarioElement fromSubElement tootlips" title="{{Permet d\'ajouter des éléments fonctionnels essentiels pour créer vos scénarios (Ex: SI/ALORS….)}}">{{Bloc}}</a></li>';
    htmlData += '         <li><a class="bt_addAction">{{Action}}</a></li>';
    htmlData += '       </ul>';
    htmlData += '     </div>';
    htmlData += '   </div>';
    htmlData += '  <div class="expressions scenario-si-bloc" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += '     <div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Else block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getElseSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr subElementElse" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-sinon">';
    htmlData += '<span class="scenario-title">{{SINON}}</span>';
    htmlData += '<div class="dropdown cursor">';
    htmlData += '<button class="btn btn-xs btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '<i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '</button>';
    htmlData += '<ul class="dropdown-menu">';
    htmlData += '<li><a class="bt_addScenarioElement fromSubElement tootlips" title="{{Permet d\'ajouter des éléments fonctionnels essentiels pour créer vos scénarios (ex. : SI/ALORS….)}}">{{Bloc}}</a></li>';
    htmlData += '<li><a class="bt_addAction">{{Action}}</a></li>';
    htmlData += '</ul>';
    htmlData += '</div>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-si-bloc" style="background-color: ' + listColor[elementColorIndex] + '; border-top :1px solid ' + listColorStrong[elementColorIndex] + '">';
    htmlData += '<div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of a For block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getForSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-for">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}"/>';
    }
    htmlData += '<span class="scenario-title">{{DE 1 A}}</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an In block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getInSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-in">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    }
    htmlData += '<span class="scenario-title">{{DANS}}</span>';
    htmlData += '<span class="scenario-unity">(en min)</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an At block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getAtSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-at">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}"/>';
    }
    htmlData += '<span class="scenario-title">{{A}}</span>';
    htmlData += '<span class="scenario-unity-line">{{(Hmm)}}</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of a Do block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getDoSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-faire">';
    htmlData += '<span class="scenario-title">{{FAIRE}}</span>';
    htmlData += '<div class="dropdown cursor">';
    htmlData += '<button class="btn btn-xs btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '<i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '</button>';
    htmlData += '<ul class="dropdown-menu">';
    htmlData += '<li><a class="bt_addScenarioElement fromSubElement tootlips" title="{{Permet d\'ajouter des éléments fonctionnels essentiels pour créer vos scénarios (ex. : SI/ALORS….)}}">{{Bloc}}</a></li>';
    htmlData += '<li><a class="bt_addAction">{{Action}}</a></li>';
    htmlData += '</ul>';
    htmlData += '</div>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += '<div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of a Code block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getCodeSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-code">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}"/>';
    }
    htmlData += '<span class="scenario-title">{{CODE}}</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += addFirstExpressionHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of a Comment block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getCommentSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="comment"/>';
    htmlData += '<div class="scenario-comment">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += addFirstExpressionHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an Action block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getActionSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-action-bloc">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    }
    htmlData += '<span class="scenario-title">{{ACTION}}</span>';
    htmlData += '<div class="dropdown cursor">';
    htmlData += '<button class="btn btn-xs btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '<i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '</button>';
    htmlData += '<ul class="dropdown-menu">';
    htmlData += '<li><a class="bt_addScenarioElement fromSubElement tootlips" title="{{Permet d\'ajouter des éléments fonctionnels essentiels pour créer vos scénarios (Ex: SI/ALORS….)}}">{{Bloc}}</a></li>';
    htmlData += '<li><a class="bt_addAction">{{Action}}</a></li>';
    htmlData += '</ul>';
    htmlData += '</div>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-si-bloc" style="display:table-cell; background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += '<div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Add an subelement to the scenario
 *
 * @param subElementToAdd
 * @param elementColorIndex
 * @returns {string}
 */
function addSubElement(subElementToAdd, elementColorIndex) {
    if (!isset(subElementToAdd.type) || subElementToAdd.type === '') {
        return '';
    }
    if (!isset(subElementToAdd.options)) {
        subElementToAdd.options = {};
    }
    var noSortable = '';
    if (subElementToAdd.type === 'if' || subElementToAdd.type === 'for' || subElementToAdd.type === 'code') {
        noSortable = 'noSortable';
    }
    var displayElse = 'table';
    if (subElementToAdd.type === 'else') {
        if (!isset(subElementToAdd.expressions) || subElementToAdd.expressions.length === 0) {
            displayElse = 'none';
        }
    }
    var subElementHTML = '<div class="subElement scenario-group ' + noSortable + '" style="display:' + displayElse + '">';
    subElementHTML += '<input class="subElementAttr" data-l1key="id" type="hidden" value="' + init(subElementToAdd.id) + '"/>';
    subElementHTML += '<input class="subElementAttr" data-l1key="scenarioElement_id" type="hidden" value="' + init(subElementToAdd.scenarioElement_id) + '"/>';
    subElementHTML += '<input class="subElementAttr" data-l1key="type" type="hidden" value="' + init(subElementToAdd.type) + '"/>';
    switch (subElementToAdd.type) {
        case 'if' :
            subElementHTML += getIfSubElementHTML(subElementToAdd);
            break;
        case 'then' :
            subElementHTML += getThenSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'else' :
            subElementHTML += getElseSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'for' :
            subElementHTML += getForSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'in' :
            subElementHTML += getInSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'at' :
            subElementHTML += getAtSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'do' :
            subElementHTML += getDoSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'code' :
            subElementHTML += getCodeSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'comment' :
            subElementHTML += getCommentSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'action' :
            subElementHTML += getActionSubElementHTML(subElementToAdd, elementColorIndex);
            break;
    }
    subElementHTML += '</div>';
    return subElementHTML;
}

/**
 * Get next color in the list
 *
 * @returns {number} Next color index in the list
 */
function getNextColorIndex() {
    colorIndex++;
    if (colorIndex > 4) {
        colorIndex = 0;
    }
    return colorIndex;
}

/**
 * Add an element to the scenario
 *
 * @param elementToAdd
 * @returns {string}
 */
function addElement(elementToAdd) {
    if (!isset(elementToAdd)) {
        return '';
    }
    if (!isset(elementToAdd.type) || elementToAdd.type === '') {
        return '';
    }

    var elementColorIndex = getNextColorIndex();
    var lightColor = listColor[elementColorIndex];
    var strongColor = listColorStrong[elementColorIndex];

    var elementHTML = '<div class="element" style="background-color:' + strongColor + ';border-color:' + lightColor + '">';
    elementHTML += '<input class="elementAttr" data-l1key="id" type="hidden" value="' + init(elementToAdd.id) + '"/>';
    elementHTML += '<input class="elementAttr" data-l1key="type" type="hidden" value="' + init(elementToAdd.type) + '"/>';
    if (isset(elementToAdd.subElements)) {
        for (var subElementIndex in elementToAdd.subElements) {
            elementHTML += addSubElement(elementToAdd.subElements[subElementIndex], elementColorIndex);
        }
    }
    else {
        switch (elementToAdd.type) {
            case 'if':
                elementHTML += addSubElement({type: 'if'}, elementColorIndex);
                elementHTML += addSubElement({type: 'then'}, elementColorIndex);
                elementHTML += addSubElement({type: 'else'}, elementColorIndex);
                break;
            case 'for':
                elementHTML += addSubElement({type: 'for'}, elementColorIndex);
                elementHTML += addSubElement({type: 'do'}, elementColorIndex);
                break;
            case 'in' :
                elementHTML += addSubElement({type: 'in'}, elementColorIndex);
                elementHTML += addSubElement({type: 'do'}, elementColorIndex);
                break;
            case 'at' :
                elementHTML += addSubElement({type: 'at'}, elementColorIndex);
                elementHTML += addSubElement({type: 'do'}, elementColorIndex);
                break;
            case 'code' :
                elementHTML += addSubElement({type: 'code'}, elementColorIndex);
                break;
            case 'comment' :
                elementHTML += addSubElement({type: 'comment'}, elementColorIndex);
                break;
            case 'action' :
                elementHTML += addSubElement({type: 'action'}, elementColorIndex);
                break;
        }
    }
    elementHTML += '</div>';
    return elementHTML;
}

/**
 * Get element data (and subelements)
 * @param rootElement
 * @returns {*}
 */
function getElement(rootElement) {
    var element = rootElement.getValues('.elementAttr', 1);
    if (element.length === 0) {
        return;
    }
    element = element[0];
    element.subElements = [];

    rootElement.findAtDepth('.subElement', 2).each(function () {
        var subElement = $(this).getValues('.subElementAttr', 2);
        subElement = subElement[0];
        subElement.expressions = [];
        var expression_dom = $(this).children('.expressions');
        if (expression_dom.length === 0) {
            expression_dom = $(this).children('legend').findAtDepth('.expressions', 2);
        }
        expression_dom.children('.expression').each(function () {
            var expression = $(this).getValues('.expressionAttr', 3);
            expression = expression[0];
            if (expression.type === 'element') {
                expression.element = getElement($(this).findAtDepth('.element', 2));
            }
            if (subElement.type === 'code') {
                var id = $(this).find('.expressionAttr[data-l1key=expression]').attr('id');
                if (id !== undefined && isset(editor[id])) {
                    expression.expression = editor[id].getValue();
                }
            }
            subElement.expressions.push(expression);

        });
        element.subElements.push(subElement);
    });
    return element;
}

/**
 * Set the event of the expression input
 */
function setInputExpressionsEvent() {
    var inputExpressions = $('.expressionAttr[data-l1key=expression]');
    inputExpressions.off('keyup').on('keyup', function () {
        checkExpressionInput($(this));
    });
    inputExpressions.each(function () {
        checkExpressionInput($(this));
    });
}

/**
 * Check an input that contains expression and decorate on error
 *
 * @param inputElement JQuery object of the input to check
 */
function checkExpressionInput(inputElement) {
    if (!checkExpressionValidity(inputElement.val())) {
        inputElement.addClass('expression-error');
    }
    else {
        if (inputElement.hasClass('expression-error')) {
            inputElement.removeClass('expression-error');
        }
    }
}

/**
 * Check if the string is a valid NextDom expression
 *
 * @param stringToCheck String to check
 *
 * @returns {boolean} True if the string is valid
 */
function checkExpressionValidity(stringToCheck) {
    var validityCheckRegex = /((\w+|-?(\d+\.\d+|\.?\d+)|".*?"|'.*?'|#.*?#|\(|,|\)|!)[ ]*([!*+&|\-\/>=<]+|and|or|ou|et)*[ ]*)*/;
    var prohibedFirstsCharacters = ['*', '+', '&', '|', '-', '/', '>', '=', '<'];
    var prohibedLastsCharacters = ['!', '*', '+', '&', '|', '-', '/', '>', '=', '<'];
    var result = false;

    stringToCheck = stringToCheck.trim();
    if (validityCheckRegex.exec(stringToCheck)[0] === stringToCheck) {
        result = true;
        if (stringToCheck.length > 0) {
            if (prohibedFirstsCharacters.indexOf(stringToCheck[0]) !== -1) {
                result = false;
            }
            if (prohibedLastsCharacters.indexOf(stringToCheck[stringToCheck.length - 1]) !== -1) {
                result = false;
            }
        }
        var parenthesisStack = [];
        for (var i = 0; i < stringToCheck.length; ++i) {
            if (stringToCheck[i] === '(') {
                parenthesisStack.push('(');
            }
            else if (stringToCheck[i] === ')') {
                if (parenthesisStack.length === 0) {
                    result = false;
                    break;
                }
                if (parenthesisStack[parenthesisStack.length - 1] !== '(') {
                    result = false;
                    break;
                }
                parenthesisStack.pop();
            }
        }
        if (parenthesisStack.length > 0) {
            result = false;
        }
    }

    return result;
}

/**
 * Get HTML for numeric expression choice
 * @returns {string}
 */
function getNumericExpressionHTML(humanResult) {
    return '<div class="row"> ' +
        '<div class="col-md-12"> ' +
        '<form class="form-horizontal" onsubmit="return false;"> ' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >' + humanResult + ' {{est}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="operator">' +
        '<option value="==">{{égal}}</option>' +
        '<option value=">">{{supérieur}}</option>' +
        '<option value="<">{{inférieur}}</option>' +
        '<option value="!=">{{différent}}</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-xs-4">' +
        '<input type="number" class="conditionAttr form-control" data-l1key="operande" />' +
        '</div>' +
        '</div>' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="next">' +
        '<option value="">rien</option>' +
        '<option value="ET">{{et}}</option>' +
        '<option value="OU">{{ou}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div> </div>' +
        '</form> </div> </div>';
}

/**
 * Get HTML for string expression choice
 * @returns {string}
 */
function getStringExpressionHTML(humanResult) {
    return '<div class="row"> ' +
        '<div class="col-md-12"> ' +
        '<form class="form-horizontal" onsubmit="return false;"> ' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >' + humanResult + ' {{est}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="operator">' +
        '<option value="==">{{égale}}</option>' +
        '<option value="matches">{{contient}}</option>' +
        '<option value="!=">{{différent}}</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-xs-4">' +
        '<input class="conditionAttr form-control" data-l1key="operande" />' +
        '</div>' +
        '</div>' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="next">' +
        '<option value="">{{rien}}</option>' +
        '<option value="ET">{{et}}</option>' +
        '<option value="OU">{{ou}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div> </div>' +
        '</form> </div> </div>';
}

/**
 * Get HTML for binary expression choice
 * @returns {string}
 */
function getBinaryExpressionHTML(humanResult) {
    return '<div class="row"> ' +
        '<div class="col-md-12"> ' +
        '<form class="form-horizontal" onsubmit="return false;"> ' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >' + humanResult + ' {{est}}</label>' +
        '<div class="col-xs-7">' +
        '<input class="conditionAttr" data-l1key="operator" value="==" type="hidden" />' +
        '<select class="conditionAttr form-control" data-l1key="operande">' +
        '<option value="1">{{Ouvert}}</option>' +
        '<option value="0">{{Fermé}}</option>' +
        '<option value="1">{{Allumé}}</option>' +
        '<option value="0">{{Eteint}}</option>' +
        '<option value="1">{{Déclenché}}</option>' +
        '<option value="0">{{Au repos}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="next">' +
        '<option value="">{{rien}}</option>' +
        '<option value="ET">{{et}}</option>' +
        '<option value="OU">{{ou}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div></div>' +
        '</form></div></div>';
}

/**
 * Show modal for command selection
 * @param elementData
 * @param expressionElement
 */
function selectCmdExpression(elementData, expressionElement) {
    var type = 'info';
    if (expressionElement.find('.expressionAttr[data-l1key=type]').value() === 'action') {
        type = 'action';
    }
    nextdom.cmd.getSelectModal({cmd: {type: type}}, function (result) {
        if (expressionElement.find('.expressionAttr[data-l1key=type]').value() === 'action') {
            expressionElement.find('.expressionAttr[data-l1key=expression]').value(result.human);
            nextdom.cmd.displayActionOption(expressionElement.find('.expressionAttr[data-l1key=expression]').value(), '', function (html) {
                expressionElement.find('.expressionOptions').html(html);
                taAutosize();
            });
        }
        if (expressionElement.find('.expressionAttr[data-l1key=type]').value() === 'condition') {
            var message = '';
            switch (result.cmd.subType) {
                case 'numeric':
                    message = getNumericExpressionHTML(result.human);
                    break;
                case 'string':
                    message = getStringExpressionHTML(result.human);
                    break;
                case 'binary':
                    message = getBinaryExpressionHTML(result.human);
                    break;
                default:
                    message = 'Aucun choix possible';
                    break;
            }
            bootbox.dialog({
                title: "{{Ajout d'un nouveau scénario}}",
                message: message,
                buttons: {
                    "Ne rien mettre": {
                        className: "btn-default",
                        callback: function () {
                            expressionElement.find('.expressionAttr[data-l1key=expression]').atCaret('insert', result.human);
                        }
                    },
                    success: {
                        label: "Valider",
                        className: "btn-primary",
                        callback: function () {
                            var condition = result.human;
                            var operatorValue = $('.conditionAttr[data-l1key=operator]').value();
                            var operandeValue = $('.conditionAttr[data-l1key=operande]').value();
                            var nextValue = $('.conditionAttr[data-l1key=next]').value();
                            condition += ' ' + operatorValue;
                            if (result.cmd.subType === 'string') {
                                if (operatorValue === 'matches') {
                                    condition += ' "/' + operandeValue + '/"';
                                } else {
                                    condition += ' "' + operandeValue + '"';
                                }
                            } else {
                                condition += ' ' + operandeValue;
                            }
                            condition += ' ' + nextValue + ' ';
                            expressionElement.find('.expressionAttr[data-l1key=expression]').atCaret('insert', condition);
                            if (nextValue !== '') {
                                elementData.click();
                            }
                        }
                    },
                }
            });
        }
    });
}

/**
 * Load scenario with the URL data
 */
function loadFromUrl() {
    var scenarioIdFromUrl = getUrlVars('id');
    if (is_numeric(scenarioIdFromUrl)) {
        if ($('.scenarioDisplayCard[data-scenario_id=' + scenarioIdFromUrl + ']').length !== 0) {
            var url = document.location.toString();
            var tabCode = GENERAL_TAB;
            if (url.match('#')) {
                tabCode = url.split('#')[1];
            }
            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
                tab = e.target.hash;
            });
            loadScenario(scenarioIdFromUrl, tabCode);
        }
    }
}

/**
 * Init modal events
 */
function initModalEvents() {
    $('#bt_graphScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Graphique de lien(s)}}"});
        modalContainer.load('index.php?v=d&modal=graph.link&filter_type=scenario&filter_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });

    $('#bt_logScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Log d'exécution du scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.log.execution&scenario_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });

    $('#bt_exportScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Export du scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.export&scenario_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });

    $('#bt_templateScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Template de scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.template&scenario_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });
}

/**
 * Init events of the list
 */
function initListEvents() {
    $('.scenarioDisplayCard').off('click').on('click', function () {
        loadScenario($(this).attr('data-scenario_id'), GENERAL_TAB);
    });
    $('.accordion-toggle').off('click').on('click', function () {
        setTimeout(function () {
            $('.scenarioListContainer').packery();
        }, 100);
    });

    $("#div_tree").jstree({
        "plugins": ["search"]
    });
    $('#in_treeSearch').keyup(function () {
        $('#div_tree').jstree(true).search($('#in_treeSearch').val());
    });

    /**
     * Back button
     */
    $('#bt_scenarioThumbnailDisplay').off('click').on('click', function () {
        loadPage('index.php?v=d&p=scenario');
    });

    setTimeout(function () {
        $('.scenarioListContainer').packery();
    }, 100);
    $("#div_listScenario").trigger('resize');
    $('.scenarioListContainer').packery();
    $("#bt_changeAllScenarioState").off('click').on('click', toggleAllScenariosState);
    $("#bt_addScenario").off('click').on('click', addScenario);
    jwerty.key('ctrl+s/⌘+s', function (e) {
        e.preventDefault();
        saveScenario();
    });
}

/**
 * Init events of the general tab
 */
function initGeneralFormEvents() {
    /**
     * Choose icon in scenario form
     */
    $('#bt_chooseIcon').on('click', function () {
        chooseIcon(function (_icon) {
            $('.scenarioAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
        });
    });
    /**
     * Group autocomplete in scenario form
     */
    $('.scenarioAttr[data-l1key=group]').autocomplete({
        source: function (request, response, url) {
            $.ajax({
                type: 'POST',
                url: 'core/ajax/scenario.ajax.php',
                data: {
                    action: 'autoCompleteGroup',
                    term: request.term
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state !== 'ok') {
                        notify("Erreur", data.result, 'error');
                        return;
                    }
                    response(data.result);
                }
            });
        },
        minLength: 1,
    });

    $("#bt_saveScenario").off('click').on('click', saveScenario);
    $("#bt_delScenario").off('click').on('click', deleteScenario);
    $("#bt_testScenario").off('click').on('click', testScenario);
    $("#bt_copyScenario").off('click').on('click', copyScenario);
    $("#bt_stopScenario").off('click').on('click', stopScenario);
    $(".bt_displayScenarioVariable").off('click').on('click', function () {
        modalContainer.dialog({title: "{{Variables des scénarios}}"});
        modalContainer.load('index.php?v=d&modal=dataStore.management&type=scenario').dialog('open');
    });

    $('.bt_showExpressionTest').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Testeur d'expression}}"});
        modalContainer.load('index.php?v=d&modal=expression.test').dialog('open');
    });

    $('.bt_showScenarioSummary').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Résumé scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.summary').dialog('open');
    });

    $('#in_addElementType').off('change').on('change', function () {
        $('.addElementTypeDescription').hide();
        $('.addElementTypeDescription.' + $(this).value()).show();
    });

    $('#bt_scenarioTab').on('click', function () {
        setTimeout(function () {
            setEditor();
            taAutosize();
        }, 50);
    });

    pageContainer.off('click', '.helpSelectCron').on('click', '.helpSelectCron', function () {
        var el = $(this).closest('.schedule').find('.scenarioAttr[data-l1key=schedule]');
        nextdom.getCronSelectModal({}, function (result) {
            el.value(result.value);
        });
    });
    $('.scenarioAttr[data-l1key=mode]').off('change').on('change', function () {
        var mode = $(this).value();
        if (mode === 'schedule' || mode === 'all') {
            $('.scheduleDisplay').show();
            $('#bt_addSchedule').show();
        } else {
            $('.scheduleDisplay').hide();
            $('#bt_addSchedule').hide();
        }
        if (mode === 'provoke' || mode === 'all') {
            $('.provokeDisplay').show();
            $('#bt_addTrigger').show();
        } else {
            $('.provokeDisplay').hide();
            $('#bt_addTrigger').hide();
        }
    });

    $('#bt_addTrigger').off('click').on('click', function () {
        addTrigger('');
    });

    $('#bt_addSchedule').off('click').on('click', function () {
        addSchedule('');
    });

    pageContainer.off('click', '.bt_removeTrigger').on('click', '.bt_removeTrigger', function (event) {
        $(this).closest('.trigger').remove();
    });

    pageContainer.off('click', '.bt_removeSchedule').on('click', '.bt_removeSchedule', function (event) {
        $(this).closest('.schedule').remove();
    });

    pageContainer.off('click', '.bt_selectTrigger').on('click', '.bt_selectTrigger', function (event) {
        var el = $(this);
        nextdom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
            el.closest('.trigger').find('.scenarioAttr[data-l1key=trigger]').value(result.human);
        });
    });

    pageContainer.off('click', '.bt_selectDataStoreTrigger').on('click', '.bt_selectDataStoreTrigger', function (event) {
        var el = $(this);
        nextdom.dataStore.getSelectModal({cmd: {type: 'info'}}, function (result) {
            el.closest('.trigger').find('.scenarioAttr[data-l1key=trigger]').value(result.human);
        });
    });

}

/**
 * Init events on the scenario editor
 */
function initScenarioEditorEvents() {
    pageContainer.off('click', '.bt_addScenarioElement').on('click', '.bt_addScenarioElement', function (event) {
        var elementDiv = $(this).closest('.element');
        if (elementDiv.html() === undefined) {
            elementDiv = scenarioContainer;
        }
        var expression = false;
        if ($(this).hasClass('fromSubElement')) {
            elementDiv = $(this).closest('.subElement').find('.expressions').eq(0);
            expression = true;
        }
        $('#md_addElement').modal('show');
        $("#bt_addElementSave").off('click').on('click', function (event) {
            if (expression) {
                elementDiv.append(addExpression({type: 'element', element: {type: $("#in_addElementType").value()}}));
            } else {
                $('#div_scenarioElement .span_noScenarioElement').remove();
                elementDiv.append(addElement({type: $("#in_addElementType").value()}));
            }
            setEditor();
            updateSortable();
            setInputExpressionsEvent();
            $('#md_addElement').modal('hide');
        });
    });

    pageContainer.off('click', '.bt_removeElement').on('click', '.bt_removeElement', function (event) {
        if ($(this).closest('.expression').length !== 0) {
            $(this).closest('.expression').remove();
        } else {
            $(this).closest('.element').remove();
        }
    });

    pageContainer.off('click', '.bt_addAction').on('click', '.bt_addAction', function (event) {
        $(this).closest('.subElement').children('.expressions').append(addExpression({type: 'action'}));
        setAutocomplete();
        updateSortable();
    });

    pageContainer.off('click', '.bt_addSinon').on('click', '.bt_addSinon', function (event) {

        if ($(this).children("i").hasClass('fa-chevron-right')) {
            $(this).children("i").removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $(this).closest('.subElement').next().css('display', 'table');
        }
        else {
            if ($(this).closest('.subElement').next().children('.expressions').children('.expression').length > 0) {
                alert("{{Le bloc Sinon ne peut être supprimé s'il contient des éléments}}");
            }
            else {
                $(this).children("i").removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $(this).closest('.subElement').next().css('display', 'none');
            }
        }
    });

    pageContainer.off('click', '.bt_addSinon').on('click', '.bt_addSinon', function (event) {
        if ($(this).children("i").hasClass('fa-chevron-right')) {
            $(this).children("i").removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $(this).closest('.subElement').next().css('display', 'table');
        }
        else {
            if ($(this).closest('.subElement').next().children('.expressions').children('.expression').length > 0) {
                alert("{{Le bloc Sinon ne peut être supprimé s'il contient des éléments}}");
            }
            else {
                $(this).children("i").removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $(this).closest('.subElement').next().css('display', 'none');
            }
        }
    });

    pageContainer.off('click', '.bt_removeExpression').on('click', '.bt_removeExpression', function () {
        $(this).closest('.expression').remove();
        updateSortable();
    });

    pageContainer.off('click', '.bt_selectCmdExpression').on('click', '.bt_selectCmdExpression', function () {
        selectCmdExpression($(this), $(this).closest('.expression'));
    });

    pageContainer.off('click', '.bt_selectOtherActionExpression').on('click', '.bt_selectOtherActionExpression', function (event) {
        var expression = $(this).closest('.expression');
        nextdom.getSelectActionModal({scenario: true}, function (result) {
            expression.find('.expressionAttr[data-l1key=expression]').value(result.human);
            nextdom.cmd.displayActionOption(expression.find('.expressionAttr[data-l1key=expression]').value(), '', function (html) {
                expression.find('.expressionOptions').html(html);
                taAutosize();
            });
        });
    });

    pageContainer.off('click', '.bt_selectScenarioExpression').on('click', '.bt_selectScenarioExpression', function (event) {
        var expression = $(this).closest('.expression');
        nextdom.scenario.getSelectModal({}, function (result) {
            if (expression.find('.expressionAttr[data-l1key=type]').value() === 'action') {
                expression.find('.expressionAttr[data-l1key=expression]').value(result.human);
            }
            if (expression.find('.expressionAttr[data-l1key=type]').value() === 'condition') {
                expression.find('.expressionAttr[data-l1key=expression]').atCaret('insert', result.human);
            }
        });
    });

    pageContainer.off('click', '.bt_selectEqLogicExpression').on('click', '.bt_selectEqLogicExpression', function (event) {
        var expression = $(this).closest('.expression');
        nextdom.eqLogic.getSelectModal({}, function (result) {
            if (expression.find('.expressionAttr[data-l1key=type]').value() === 'action') {
                expression.find('.expressionAttr[data-l1key=expression]').value(result.human);
            }
            if (expression.find('.expressionAttr[data-l1key=type]').value() === 'condition') {
                expression.find('.expressionAttr[data-l1key=expression]').atCaret('insert', result.human);
            }
        });
    });

    pageContainer.off('focusout', '.expression .expressionAttr[data-l1key=expression]').on('focusout', '.expression .expressionAttr[data-l1key=expression]', function (event) {
        var el = $(this);
        if (el.closest('.expression').find('.expressionAttr[data-l1key=type]').value() === 'action') {
            var expression = el.closest('.expression').getValues('.expressionAttr');
            nextdom.cmd.displayActionOption(el.value(), init(expression[0].options), function (html) {
                el.closest('.expression').find('.expressionOptions').html(html);
                taAutosize();
            });
        }
    });

    pageContainer.on('click', '.subElementAttr[data-l1key=options][data-l2key=allowRepeatCondition]', function () {
        if (parseInt($(this).attr('value')) === 0) {
            $(this).attr('value', 1);
            $(this).html('<i class="fas fa-ban text-danger"></i>');
        } else {
            $(this).attr('value', 0);
            $(this).html('<i class="fas fa-refresh">');
        }
    });

    pageContainer.off('mouseenter', '.bt_sortable').on('mouseenter', '.bt_sortable', function () {
        var expressions = $(this).closest('.expressions');
        scenarioContainer.sortable({
            items: ".sortable",
            opacity: 0.7,
            forcePlaceholderSize: true,
            forceHelperSize: true,
            placeholder: "sortable-placeholder",
            tolerance: "intersect",
            grid: [30, 15],
            update: function (event, ui) {
                if (ui.item.findAtDepth('.element', 2).length === 1 && ui.item.parent().attr('id') === 'div_scenarioElement') {
                    ui.item.replaceWith(ui.item.findAtDepth('.element', 2));
                }
                if (ui.item.hasClass('element') && ui.item.parent().attr('id') !== 'div_scenarioElement') {
                    ui.item.replaceWith(addExpression({
                        type: 'element',
                        element: {html: ui.item.clone().wrapAll("<div/>").parent().html()}
                    }));
                }
                if (ui.item.hasClass('expression') && ui.item.parent().attr('id') === 'div_scenarioElement') {
                    scenarioContainer.sortable("cancel");
                }
                if (ui.item.closest('.subElement').hasClass('noSortable')) {
                    scenarioContainer.sortable("cancel");
                }
                updateSortable();
            },
            start: function (event, ui) {
                if (expressions.find('.sortable').length < 3) {
                    expressions.find('.sortable.empty').show();
                }
            }
        });
        scenarioContainer.sortable("enable");
    });

    pageContainer.off('mouseout', '.bt_sortable').on('mouseout', '.bt_sortable', function () {
        scenarioContainer.sortable("disable");
    });

    /**
     * Events detect change and ask for save
     */
    pageContainer.on('change', '.scenarioAttr', function () {
        modifyWithoutSave = true;
    });
    pageContainer.on('change', '.expressionAttr', function () {
        modifyWithoutSave = true;
    });
    pageContainer.on('change', '.elementAttr', function () {
        modifyWithoutSave = true;
    });
    pageContainer.on('change', '.subElementAttr', function () {
        modifyWithoutSave = true;
    });
}

$('#bt_scenarioCollapse').on('click',function(){
   $('#accordionScenario .panel-collapse').each(function () {
      if (!$(this).hasClass("in")) {
          $(this).css({'height' : '' });
          $(this).addClass("in");
      }
   });
   $('#bt_scenarioCollapse').hide();
   $('#bt_scenarioUncollapse').show()
});

$('#bt_scenarioUncollapse').on('click',function(){
   $('#accordionScenario .panel-collapse').each(function () {
      if ($(this).hasClass("in")) {
          $(this).removeClass("in");
      }
   });
   $('#bt_scenarioUncollapse').hide();
   $('#bt_scenarioCollapse').show()
});

initListEvents();
initGeneralFormEvents();
initScenarioEditorEvents();
initModalEvents();
loadFromUrl();
