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

/**
 * Fab category click > filtering dashboard on this category
 *
 * @param _selectedCategory string filter category
 * @param _selectedIcon string filter category icon
 */
function selectCategory(_selectedCategory, _selectedIcon) {
    var category = _selectedCategory;
    var filterValue = '';
    if (category === 'all') {
        filterValue = '*';
        $('#fabEditor').show();
        $('#fabCategory').removeClass().addClass("fab-action-button__icon fas fa-filter");
    } else {
        filterValue = '.' + category;
        $('#fabEditor').hide();
        $('#fabCategory').removeClass('fas fa-filter').addClass(_selectedIcon);
    }
    var $grid = $('.div_displayEquipement').isotope({
        itemSelector: '.eqLogic-widget',
        layoutMode: 'fitRows'
    });
    $grid.isotope({filter: filterValue});
    setTimeout(function () {
        $('.div_displayEquipement').packery();
    }, 200);
}

/**
 * Display elements from html
 *
 * @param _mode integer 0=exit edition mode, 1=start edition mode
 * @param _save boolean save widget position and size
 */
function editWidgetMode(_mode, _save) {
    if (!isset(_mode)) {
        if ($('#bt_editDashboardWidgetOrder').attr('data-mode') != undefined && $('#bt_editDashboardWidgetOrder').attr('data-mode') == 1) {
            editWidgetMode(0, false);
            editWidgetMode(1, false);
        }
        return;
    }
    if (_mode == 0) {
        // Edit widget mode exit
        if (!isset(_save) || _save) {
            saveWidgetDisplay({dashboard: 1});
        }
        if ($('.div_displayEquipement .eqLogic-widget.ui-resizable').length > 0) {
            $('.div_displayEquipement .eqLogic-widget.allowResize').resizable('destroy');
        }
        if ($('.div_displayEquipement .eqLogic-widget.ui-draggable').length > 0) {
            $('.div_displayEquipement .eqLogic-widget').draggable('disable');
        }
        $('.div_displayEquipement .eqLogic-widget').css('box-shadow', '');
        // Summary re-displaying
        $('.card-summary').show();
    } else {
        // Edit widget mode starting
        $('.div_displayEquipement .eqLogic-widget').css('box-shadow', '#33B8CC80 0px 0px 10px');
        $('.div_displayEquipement .eqLogic-widget').draggable('enable');
        $('.div_displayEquipement .eqLogic-widget').draggable({
            grid: [ parseInt(widget_size) + parseInt(widget_margin), parseInt(widget_size) + parseInt(widget_margin) ]
        });
        $(".div_displayEquipement .eqLogic-widget.allowResize").resizable({
            grid: parseInt(widget_size) + parseInt(widget_margin),
            resize: function (event, ui) {
                positionEqLogic(ui.element.attr('data-eqlogic_id'), false);
                ui.element.closest('.div_displayEquipement').packery('fit', event.target, ui.position.left, ui.position.top );
            },
            refreshPositions: true,
            function (event, ui) {
                ui.element.closest('.div_displayEquipement').packery('bindUIDraggableEvents', $itemElems)
            },
            stop: function (event, ui) {
                positionEqLogic(ui.element.attr('data-eqlogic_id'), false);
                ui.element.closest('.div_displayEquipement').packery();
            }
        });
        // Display order items
        orderItems();
        // Summry hidding
        $('.card-summary').hide();
    }
    editWidgetCmdMode(_mode);
}

/**
 * Display elements from html
 *
 * @param _object_id object id
 */
function getObjectHtml(_object_id) {
    nextdom.object.toHtml({
        id: _object_id,
        version: 'dashboard',
        category: SEL_CATEGORY,
        summary: SEL_SUMMARY,
        tag: SEL_TAG,
        error: function (error) {
            notify('Core', error.message, 'error');
        },
        success: function (result) {
            var html = result['objectHtml'];
            var scenarios = result['scenarios'];
            if ($.trim(html) == '') {
                $('#div_ob' + _object_id).siblings('.alert-no-child').show();
                return;
            } else {
                $('#div_ob' + _object_id).siblings('.alert-no-child').hide();
            }
            try {
                $('#div_ob' + _object_id).empty().html(html).parent().show();
            } catch (err) {
            }
            var scenarioContainer = $('#div_sc' + _object_id);
            for (var scenarioIndex = 0; scenarioIndex < scenarios.length; ++scenarioIndex) {
                scenarioContainer.append(createScenarioWidget(scenarios[scenarioIndex]));
                updateScenarioControls(scenarios[scenarioIndex].id, scenarios[scenarioIndex]);
            }
            setTimeout(function () {
                positionEqLogic();
                $('#div_ob' + _object_id + '.div_displayEquipement').disableSelection();
                $("input").click(function () {
                    $(this).focus();
                });
                $("textarea").click(function () {
                    $(this).focus();
                });
                $("select").click(function () {
                    $(this).focus();
                });
                $('#div_ob' + _object_id + '.div_displayEquipement').each(function () {
                    var container = $(this).packery({
                        itemSelector: ".eqLogic-widget",
                        gutter: parseInt(widget_margin),
                        columnWidth: parseInt(widget_size) ,
                        rowHeight: parseInt(widget_size)
                    });
                    var itemElems = container.find('.eqLogic-widget').draggable({ grid: [ (parseInt(widget_size) + (parseInt(widget_margin))), (parseInt(widget_size) + (parseInt(widget_margin)))]});
                    container.packery('bindUIDraggableEvents', itemElems);
                    $(this).on('dragItemPositioned', orderItems);
                });
                $('#div_ob' + _object_id + '.div_displayEquipement .eqLogic-widget').draggable('disable');
            }, 10);
        }
    });
}

/**
 * Create a scenario widget for dashboard control
 * @param scenarioData
 */
function createScenarioWidget(scenarioData) {
    var widgetDiv = $('<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 scenario">');
    var widgetDiv2 = $('<div class="div_scenario" data-scenario_id="' + scenarioData.id + '">');
    var nameContainer = $('<a class="scenario-label scenario-open-button cursor">');
    if (scenarioData.icon !== '') {
        scenarioData.icon = '<i class="fas fa-film"></i>';
    }
    nameContainer.append(scenarioData.icon + scenarioData.name);
    widgetDiv2.append(nameContainer);
    var enableButton = $('<a class="btn btn-default scenario-cmd scenario-enable-button" data-toggle="tooltip" title="" data-original-title="Activer le scénario"><i class="fas fa-toggle-on no-spacing text-good"></i></a>');
    var playButton = $('<a class="btn btn-success scenario-cmd scenario-play-button" data-toggle="tooltip" title="" data-original-title="Lancer le scénario"><i class="fas fa-play no-spacing"></i></a>');
    var stopButton = $('<a class="btn btn-danger scenario-cmd scenario-stop-button" data-toggle="tooltip" title="" data-original-title="Arrêter le scénario"><i class="fas fa-stop no-spacing"></i></a>');
    playButton.on('click', function() {
        nextdom.scenario.changeState({'id': $(this).parent().data('scenario_id'), 'state': 'start'});
    });
    stopButton.on('click', function() {
        nextdom.scenario.changeState({'id': $(this).parent().data('scenario_id'), 'state': 'stop'});
    });
    enableButton.on('click', function() {
        nextdom.scenario.changeState({'id': $(this).parent().data('scenario_id'), 'state': 'activate'});
    });
    $('.scenario-open-button').on('click', function() {
        loadPage("index.php?v=d&p=scenario&id=" + $(this).parent().attr('data-scenario_id'));
    });
    widgetDiv2.append(enableButton);
    widgetDiv2.append(playButton);
    widgetDiv2.append(stopButton);
    widgetDiv2.append('<a class="label scenario-state scenario-open-button">' + scenarioData.state + '</a>');
    widgetDiv.append(widgetDiv2);
    nextdom.scenario.update[scenarioData.id] = function (data) {
        updateScenarioControls(data.scenario_id, data)
    };
    return widgetDiv;
}

/**
 * Update display of scenario controls
 * @param scenarioId
 * @param data
 */
function updateScenarioControls(scenarioId, data) {
    var scenarioContainer = $('.scenario[data-scenario_id=' + scenarioId + ']');
    // Search fields
    var enableButton = scenarioContainer.find('.scenario-enable-button');
    var playButton = scenarioContainer.find('.scenario-play-button');
    var stopButton = scenarioContainer.find('.scenario-stop-button');
    var stateField = scenarioContainer.find('.scenario-state');
    // Cmd button
    if (data.state === 'in progress' || data.state === 'starting') {
        playButton.hide();
        stopButton.show();
    }
    else {
        playButton.show();
        stopButton.hide();
    }
    // Frame color
    var color = $('.scenario[data-scenario_id="' + scenarioId + '"]').closest(".card").find(".card-icon").css("backgroundColor");
    scenarioContainer.find(".div_scenario").css("background-color", color.replace(')', ', 0.2)').replace('rgb', 'rgba'));
    // Status Label
    stateField.removeClass('label-danger label-info label-success label-warning label-default')
    if (isset(data.active) && data.active != 1) {
        stateField.text('');
        scenarioContainer.css('opacity','0.6');
        enableButton.show();
        playButton.hide();
    } else {
        scenarioContainer.css('opacity','');
        enableButton.hide();
        switch (data.state) {
            case 'error' :
                stateField.text('{{Erreur}}');
                stateField.addClass('label-warning');
                break;
            case 'on' :
                stateField.text('{{Actif}}');
                stateField.addClass('label-success');
                break;
            case 'in progress' :
                stateField.text('{{En cours}}');
                stateField.addClass('label-info');
                break;
            case 'stop' :
            default :
                stateField.text('{{Arrêté}}');
                stateField.addClass('label-danger');
        }
    }
}

/**
 * Display order information of elements
 */
function orderItems() {
    setTimeout(function () {
      $('.div_displayEquipement').each(function () {
          var itemElems = $(this).packery('getItemElements');
          $(itemElems).each(function (i, itemElem) {
              $(itemElem).attr('data-order', i + 1);
              value = i + 1;
              if ($('#bt_editDashboardWidgetOrder').attr('data-mode') == 1) {
                  if ($(itemElem).find(".card-order-number").length) {
                      $(itemElem).find(".card-order-number").text(value);
                  } else {
                      $(itemElem).find(".widget-name").prepend('<span class="card-order-number pull-left">' + value + '</span>');
                  }
              }
          });
      });
    }, 200);
}
