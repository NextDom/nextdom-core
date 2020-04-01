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
    refreshScenarioTemplateList();
    refreshTemplateMarkets();
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Create a template from ative scenario
    $('#bt_scenarioTemplateConvert').on('click', function () {
        bootbox.prompt("Nom du template ?", function (result) {
            var newTemplate =  result + '.json';
            if (result !== null) {
                nextdom.scenario.convertToTemplate({
                    id: scenario_template_id,
                    template: newTemplate,
                    error: function (error) {
                        notify("{{ Scénario }}", error.message, 'error');
                    },
                    success: function (data) {
                        refreshScenarioTemplateList(newTemplate);
                        notify('Info', '{{ Création du template réussie }}', 'success');
                        $('.nav-tabs a[href="#tab_template"]').tab('show');
                    }
                });
            }
        });
    });

    // Remove a template
    $('#bt_scenarioTemplateRemove').on('click', function () {
        if ($('#ul_scenarioTemplateList li.active').attr('data-template') == undefined) {
            notify('Info', '{{ Vous devez d\'abord sélectionner un template }}', 'error');
            return;
        }
        bootbox.confirm('{{ Etes-vous sûr de vouloir supprimer le template ? }}', function (result) {
            if (result) {
                nextdom.scenario.removeTemplate({
                    template: $('#ul_scenarioTemplateList li.active').attr('data-template'),
                    error: function (error) {
                        notify("{{ Scénario }}", error.message, 'error');
                    },
                    success: function (data) {
                        refreshScenarioTemplateList();
                        notify('Info', '{{ Suppression du template réussie }}', 'success');
                    }
                });
            }
        });
    });

    // Apply a template on active scenario
    $('#bt_scenarioTemplateApply').on('click', function () {
        bootbox.confirm('{{ Etes-vous sûr de vouloir appliquer le template ? Cela écrasera votre scénario... }}', function (result) {
            if (result) {
                bootbox.confirm('{{ Avez-vous bien configurer les paramètres du template pour le remplacement des Paramètres/Variables/Commandes ? }}', function (result2) {
                    if (result2) {
                        var convert = $('.templateScenario').getValues('.templateScenarioAttr');
                        nextdom.scenario.applyTemplate({
                            template: $('#ul_scenarioTemplateList li.active').attr('data-template'),
                            id: scenario_template_id,
                            convert: json_encode(convert),
                            newValues: '1',
                            error: function (error) {
                                notify("{{ Scénario }}", error.message, 'error');
                            },
                            success: function (data) {
                                refreshScenarioTemplateList($('#ul_scenarioTemplateList li.active').attr('data-template'));
                                notify('Info', '{{ Template appliqué avec succès }}', 'success');
                                loadScenario(scenario_template_id, 'generaltab');
                            }
                        });
                    }
                });
            }
        });
    });

    // Apply a template on active scenario
    $('#bt_scenarioTemplateApplyOldVar').on('click', function () {
        bootbox.confirm('{{ Etes-vous sûr de vouloir appliquer le template ? Cela écrasera votre scénario... }}', function (result) {
            if (result) {
                var convert = $('.templateScenario').getValues('.templateScenarioAttr');
                nextdom.scenario.applyTemplate({
                    template: $('#ul_scenarioTemplateList li.active').attr('data-template'),
                    id: scenario_template_id,
                    convert: json_encode(convert),
                    newValues: '0',
                    error: function (error) {
                        notify("{{ Scénario }}", error.message, 'error');
                    },
                    success: function (data) {
                        refreshScenarioTemplateList($('#ul_scenarioTemplateList li.active').attr('data-template'));
                        notify('Info', '{{ Template appliqué avec succès }}', 'success');
                        loadScenario(scenario_template_id, 'generaltab');
                    }
                });
            }
        });
    });

    // Template liste element click to display informations
    $('#ul_scenarioTemplateList').delegate('.li_scenarioTemplate', 'click', function () {
        getScenarioTemplateInfo($(this));
    });

    // Template download
    $('#bt_scenarioTemplateDownload').on('click', function () {
        if ($('#ul_scenarioTemplateList li.active').attr('data-template') == undefined) {
            notify("{{ Scénario }}", '{{ Vous devez d\'abord sélectionner un template }}', 'error');
            return;
        }
        window.open('src/Api/downloadFile.php?pathfile=' + filePath + $('#ul_scenarioTemplateList li.active').attr('data-template'), "_blank", null);
    });

    // Template command choice
    $('#div_scenarioTemplates').delegate('.bt_scenarioTemplateSelectCmd', 'click', function () {
        var el = $(this);
        nextdom.cmd.getSelectModal({}, function (result) {
            el.closest('.templateScenario').find('.templateScenarioAttr[data-l1key=end]').value(result.human);
        });
    });

    // Upload of scenario template
    $('#bt_uploadScenarioTemplate').fileupload({
        dataType: 'json',
        replaceFileInput: false,
        formData: {'nextdom_token': NEXTDOM_AJAX_TOKEN},
        done: function (e, data) {
            if (data.result.state != 'ok') {
                notify("{{ Scénario }}", data.result.result, 'error');
                return;
            }
            notify("{{ Scénario }}", '{{ Template ajouté avec succès }}', 'success');
            refreshScenarioTemplateList();
        }
    });

    // Programmation tab click
    $('.marketTab').on("shown.bs.tab", function () {
        $('.pluginContainer').packery();
        $("img.lazy").lazyload({
          threshold : 400
        });
    });
}

/**
 * Refresh the scenario template list
 * @param _templateName template name to display, empty = first
 */
function refreshScenarioTemplateList(_templateName = '') {
    nextdom.scenario.getTemplate({
        error: function (error) {
            notify("{{ Scénario }}", error.message, 'error');
        },
        success: function (data) {
            $('#ul_scenarioTemplateList').empty();
            $("#noTemplate").show();
            $('#div_scenarioTemplateParametreConfiguration').hide();
            var li = '';
            for (var i in data) {
                $("#noTemplate").hide();
                li += "<li class='cursor li_scenarioTemplate' data-template='" + data[i] + "'><a class='label-list'>" + data[i].replace(".json", "") + "</a></li>";
            }
            $('#ul_scenarioTemplateList').html(li);
            if (_templateName == '') {
                $('.li_scenarioTemplate').first().click();
            } else {
                $(".li_scenarioTemplate[data-template='" + _templateName + "']").click();
            }
        }
    });
}

/**
 * Refresh the scenario template list after market template install
 */
function refreshListAfterMarketObjectInstall() {
    refreshScenarioTemplateList();
}

/**
 * Refresh the scenario template markets
 */
function refreshTemplateMarkets() {
    $(".templateMarketTab").each(function () {
          $(this).load('index.php?v=d&modal=update.list&type=scenario&repo=' + $(this).attr('data-code'));
    });
}

/**
 * Read and display template informations
 *
 * @param _element template element
 */
function getScenarioTemplateInfo(_element) {
    $('.nav-tabs a[href="#tab_template"]').tab('show');
    $('#ul_scenarioTemplateList .li_scenarioTemplate').removeClass('active');
    _element.addClass('active');
    nextdom.scenario.loadTemplateDiff({
        template: _element.attr('data-template'),
        id: scenario_template_id,
        error: function (error) {
            notify("{{ Scénario }}", error.message, 'error');
        },
        success: function (data) {
            var html = '';
            for (var i in data) {
                html += '<div class="form-group col-xs-12 col-padding templateScenario">';
                html += '<label class="control-label">' + i + '   >   devient : </label>';
                html += '<span class="templateScenarioAttr" data-l1key="begin" style="display : none;" >' + i + '</span>';
                html += '<div class="mix-group">';
                html += '<input class="form-control templateScenarioAttr" data-l1key="end" value="' + data[i] + '"/>';
                html += '<a class="btn btn-default cursor bt_scenarioTemplateSelectCmd"><i class="fas fa-list-alt no-spacing"></i></a>';
                html += '</div>';
                html += '</div>';
            }
            $('#div_scenarioTemplateParametreList').empty().html(html);
            $('#div_scenarioTemplateParametreConfiguration').show();
        }
    });
}
