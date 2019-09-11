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
    if (category == 'all') {
        filterValue = '*';
        $("#fabEditor").show();
        $("#fabCategory").removeClass().addClass("fab-action-button__icon fas fa-filter");
    } else {
        filterValue = '.' + category;
        $("#fabEditor").hide();
        $("#fabCategory").removeClass("fas fa-filter").addClass(_selectedIcon);
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
        noScenario: 1,
        error: function (error) {
            notify('Core', error.message, 'error');
        },
        success: function (html) {
            if ($.trim(html) == '') {
                $('#div_ob' + _object_id).parent().children('.alert-no-child').show();
                return;
            } else {
                $('#div_ob' + _object_id).parent().children('.alert-no-child').hide();
            }
            try {
                $('#div_ob' + _object_id).empty().html(html).parent().show();
            } catch (err) {
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
                      $(itemElem).prepend('<span class="card-order-number pull-left">' + value + '</span>');
                  }
              }
          });
      });
    }, 200);
}
