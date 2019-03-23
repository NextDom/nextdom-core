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

function selectCategory(_selectedCategory) {
    var category = _selectedCategory;
    var filterValue = '';
    if (category == 'all') {
        filterValue = '*';
    } else {
        filterValue = '.' + category;

    }
    var $grid = $('.div_displayEquipement').isotope({
        itemSelector: '.eqLogic-widget',
        layoutMode: 'fitRows'
    });
    $grid.isotope({filter: filterValue});
    setTimeout(function () {
        $('.div_displayEquipement').packery();
    }, 500);
}

function editWidgetMode(_mode, _save) {
    if (!isset(_mode)) {
        if ($('#bt_editDashboardWidgetOrder').attr('data-mode') != undefined && $('#bt_editDashboardWidgetOrder').attr('data-mode') == 1) {
            editWidgetMode(0, false);
            editWidgetMode(1, false);
        }
        return;
    }
    if (_mode == 0) {
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
    } else {
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
    }
    editWidgetCmdMode(_mode);
}

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
                $('#div_ob' + _object_id).parent().remove();
                return;
            }
            try {
                $('#div_ob' + _object_id).empty().html(html).parent().show();
            } catch (err) {
                console.log(err);
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

                    function orderItems() {
                        setTimeout(function () {
                            $('.div_displayEquipement').packery();
                        }, 1);
                        var itemElems = container.packery('getItemElements');
                        $(itemElems).each(function (i, itemElem) {
                            $(itemElem).attr('data-order', i + 1);
                            value = i + 1;
                            if ($('#bt_editDashboardWidgetOrder').attr('data-mode') == 1) {
                                if ($(itemElem).find(".counterReorderNextDom").length) {
                                    $(itemElem).find(".counterReorderNextDom").text(value);
                                } else {
                                    $(itemElem).prepend('<span class="counterReorderNextDom pull-left">' + value + '</span>');
                                }
                            }
                        });
                    }

                    container.on('dragItemPositioned', orderItems);
                });
                $('#div_ob' + _object_id + '.div_displayEquipement .eqLogic-widget').draggable('disable');
            }, 10);
        }
    });
}

function displayChildObject(_object_id, _recursion) {
    if (_recursion === false) {
        $('.div_object').hide();
    }
    $('.div_object[data-object_id=' + _object_id + ']').show({effect: 'drop', queue: false});
    $('.div_object[data-father_id=' + _object_id + ']').each(function () {
        $(this).show({effect: 'drop', queue: false});
        displayChildObject($(this).attr('data-object_id'), true);
    });
}
