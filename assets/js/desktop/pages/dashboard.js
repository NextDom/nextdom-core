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
        if (document.querySelectorAll('.div_displayEquipement .eqLogic-widget.ui-resizable').length > 0) {
            $('.div_displayEquipement .eqLogic-widget.allowResize').resizable('destroy');
        }
        if (document.querySelectorAll('.div_displayEquipement .eqLogic-widget.ui-draggable').length > 0) {
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
                updateScenarioControls(scenarios[scenarioIndex]);
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
    var widgetDiv = document.createElement('div');
    widgetDiv.className = 'col-lg-3 col-md-4 col-sm-6 col-xs-12 scenario';
    widgetDiv.setAttribute('data-scenario_id', scenarioData.scenario_id);
    var widgetDiv2 = document.createElement('div');
    widgetDiv2.className = 'div_scenario';
    var nameContainer = document.createElement('a');
    nameContainer.className = 'scenario-label scenario-open-button cursor';
    if (scenarioData.icon === '') {
        scenarioData.icon = '<i class="fas fa-film"></i>';
    }
    nameContainer.innerHTML = scenarioData.icon + scenarioData.name;
    widgetDiv2.appendChild(nameContainer);
    var enableButton = document.createElement('a');
    enableButton.className = 'btn btn-default scenario-cmd scenario-enable-button';
    enableButton.setAttribute('data-toggle', 'tooltip');
    enableButton.setAttribute('title', '');
    enableButton.setAttribute('data-original-title', '{{Activer le scénario}}');
    enableButton.innerHTML = '<i class="fas fa-toggle-on no-spacing text-good">';
    enableButton.onclick = function() {
        nextdom.scenario.changeState({'id': scenarioData.scenario_id, 'state': 'activate'});
    };
    var playButton = document.createElement('a');
    playButton.className = 'btn btn-success scenario-cmd scenario-play-button';
    playButton.setAttribute('data-toggle', 'tooltip');
    playButton.setAttribute('title', '');
    playButton.setAttribute('data-original-title', '{{Lancer le scénario}}');
    playButton.innerHTML = '<i class="fas fa-play no-spacing">';
    playButton.onclick = function() {
        nextdom.scenario.changeState({'id': scenarioData.scenario_id, 'state': 'start'});
    };
    var stopButton = document.createElement('a');
    stopButton.className = 'btn btn-danger scenario-cmd scenario-stop-button';
    stopButton.setAttribute('data-toggle', 'tooltip');
    stopButton.setAttribute('title', '');
    stopButton.setAttribute('data-original-title', '{{Arrêter le scénario}}');
    stopButton.innerHTML = '<i class="fas fa-stop no-spacing">';
    stopButton.onclick = function() {
        nextdom.scenario.changeState({'id': scenarioData.scenario_id, 'state': 'stop'});
    };
    var openButton = document.createElement('a');
    openButton.className = 'label scenario-state scenario-open-button';
    openButton.textContent = scenarioData.state;
    openButton.onclick = function() {
        loadPage('index.php?v=d&p=scenario&id=' + scenarioData.scenario_id)
    };
    widgetDiv2.appendChild(enableButton);
    widgetDiv2.appendChild(playButton);
    widgetDiv2.appendChild(stopButton);
    widgetDiv2.appendChild(openButton);
    widgetDiv.appendChild(widgetDiv2);
    nextdom.scenario.update[scenarioData.scenario_id] = function (data) {
        updateScenarioControls(data)
    };
    return widgetDiv;
}

/**
 * Update display of scenario controls
 * @param scenarioData
 */
function updateScenarioControls(scenarioData) {
    var baseCssSelector = '.scenario[data-scenario_id="' + scenarioData.scenario_id + '"]';
    var scenarioContainer = document.querySelector(baseCssSelector);
    var enableButton = document.querySelector(baseCssSelector + ' .scenario-enable-button');
    var playButton = document.querySelector(baseCssSelector + ' .scenario-play-button');
    var stopButton = document.querySelector(baseCssSelector + ' .scenario-stop-button');
    var stateField = document.querySelector(baseCssSelector + ' .scenario-state');
    // Cmd button
    if (scenarioData.state === 'in progress' || scenarioData.state === 'starting') {
        $(playButton).hide();
        $(stopButton).show();
    }
    else {
        $(playButton).show();
        $(stopButton).hide();
    }
    // Status Label
    stateField.classList.remove('label-danger', 'label-info', 'label-success', 'label-warning', 'label-default');
    if (isset(scenarioData.active) && scenarioData.active != 1) {
        stateField.textContent = '';
        scenarioContainer.style.opacity = 0.6;
        $(enableButton).show();
        $(playButton).hide();
    } else {
        scenarioContainer.style.opacity = '';
        $(enableButton).hide();
        switch (scenarioData.state) {
            case 'error' :
                stateField.textContent = '{{Erreur}}';
                stateField.classList.add('label-warning');
                break;
            case 'on' :
                stateField.textContent = '{{Actif}}';
                stateField.classList.add('label-success');
                break;
            case 'in progress' :
                stateField.textContent = '{{En cours}}';
                stateField.classList.add('label-info');
                break;
            case 'stop':
            default :
                stateField.textContent = '{{Arrêté}}';
                stateField.classList.add('label-danger');
                break;
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
