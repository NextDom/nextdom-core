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

$('#generalSearch').keyup(function () {
    generalSearchOnPages($(this).value().toLowerCase());
});

function generalSearchOnPages(value) {
    var search = value;
    var page = document.location.toString().split('p=')[1].replace('#', '').split('&')[0];
    switch (page) {
        case 'plugin':
            if (search === '') {
                $('.pluginListContainer .box').show();
            }
            else {
                $('.pluginListContainer .box').hide();
                $('.pluginListContainer .box .box-title').each(function () {
                    var boxTitle = $(this).text().toLowerCase();
                    if (boxTitle.indexOf(search) !== -1) {
                        $(this).closest('.box').show();
                    }
                });
            }
            $('.pluginListContainer').packery();
            break;
        case 'interact':
            if (search === '') {
                $('.panel-collapse.in').closest('.panel').find('.accordion-toggle.collapsed').click();
                $('.interactDisplayCard').show();
            }
            else {
                $('.panel-collapse:not(.in)').closest('.panel').find('.accordion-toggle').click();
                $('.interactDisplayCard').hide();
                $('.interactDisplayCard').each(function () {
                    var text = $(this).text().toLowerCase();
                    if (text.indexOf(search) >= 0) {
                        $(this).closest('.interactDisplayCard').show();
                    }
                });
            }
            $('.interactListContainer').packery();
            break;
        case 'scenario':
            if (search === '') {
                $('.panel-collapse.in').closest('.panel').find('.accordion-toggle.collapsed').click();
                $('.scenarioDisplayCard').show();
            }
            else {
                $('.panel-collapse:not(.in)').closest('.panel').find('.accordion-toggle').click();
                $('.scenarioDisplayCard').hide();
                $('.scenarioDisplayCard .title').each(function () {
                    var cardTitle = $(this).text().toLowerCase();
                    if (cardTitle.indexOf(search) !== -1) {
                        $(this).closest('.scenarioDisplayCard').show();
                    }
                });
            }
            $('.scenarioListContainer').packery();
            break;
        case 'dashboard':
            if (search === '') {
                $('.eqLogic-widget').show();
                $('.div_displayEquipement').show();
                $('.div_displayEquipement').packery();
            }
            else {
                $('.div_displayEquipement').each(function () {
                    var eqLogicWidgets = $(this).children('.eqLogic-widget').toArray();
                    var showedEqLogics = 0;
                    for (var eqLogicWidgetIndex = 0; eqLogicWidgetIndex < eqLogicWidgets.length; ++eqLogicWidgetIndex) {
                        var match = false;
                        var eqLogicWidget = $(eqLogicWidgets[eqLogicWidgetIndex]);
                        if (eqLogicWidget.find('.widget-name').text().toLowerCase().indexOf(search) !== -1) {
                            match = true;
                        }
                        if (eqLogicWidget.attr('data-tags') !== undefined && eqLogicWidget.attr('data-tags').toLowerCase().indexOf(search) !== -1) {
                            match = true;
                        }
                        if (eqLogicWidget.attr('data-category') !== undefined && eqLogicWidget.attr('data-category').toLowerCase().indexOf(search) !== -1) {
                            match = true;
                        }
                        if (eqLogicWidget.attr('data-eqType') !== undefined && eqLogicWidget.attr('data-eqType').toLowerCase().indexOf(search) !== -1) {
                            match = true;
                        }
                        if (eqLogicWidget.attr('data-translate-category') !== undefined && eqLogicWidget.attr('data-translate-category').toLowerCase().indexOf(search) !== -1) {
                            match = true;
                        }
                        if (match) {
                            eqLogicWidget.show();
                            ++showedEqLogics;
                        } else {
                            eqLogicWidget.hide();
                        }
                    }
                    if (showedEqLogics > 0) {
                        $(this).parent().parent().show();
                    }
                    else {
                        $(this).parent().parent().hide();
                    }
                    $(this).packery();
                });
            }
            break;
        case 'object':
            if (search == '') {
                $('.objectDisplayCard').show();
                $('.objectListContainer').packery();
                return;
            }
            $('.objectDisplayCard').hide();
            $('.objectDisplayCard .name').each(function () {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(search) >= 0) {
                    $(this)
                    $(this).closest('.objectDisplayCard').show();
                }
            });
            $('.objectListContainer').packery();
            break;

        case 'display':
            $('.cmd').show().removeClass('alert-success').addClass('alert-warning');
            $('.cmdSortable').hide();
            $('.object').show();
            if (search == '') {

                $('.displayListContainer').packery();
                return;
            }
            $('.eqLogic').each(function () {
                var eqLogic = $(this);
                var name = eqLogic.attr('data-name').toLowerCase();
                var type = eqLogic.attr('data-type').toLowerCase();
                if (name.indexOf(search) === -1 && type.indexOf(search) === -1) {
                    eqLogic.hide();
                }
                $(this).find('.cmd').each(function () {
                    var cmd = $(this);
                    var name = cmd.attr('data-name').toLowerCase();
                    if (name.indexOf(search) >= 0) {
                        eqLogic.show();
                        eqLogic.find('.cmdSortable').show();
                        cmd.removeClass('alert-warning').addClass('alert-success');
                    }
                });
            });

            $('.eqLogicSortable').each(function () {
                if ($(this).height() <= 30) {
                    $(this).parent().parent().hide();
                } else {
                    $(this).parent().parent().show();
                }
            });

            $('.displayListContainer').packery();
            break;
    }
};

$('#search-toggle').on('click', function () {
    $('.navbar-search').toggle();
    $('.search-toggle').toggle();
    $('.objectSummaryGlobalHeader').toggle();
});

$('#search-toggle').hover(function () {
    $('.navbar-search').show();
    $('.search-toggle').hide();
    $('.objectSummaryGlobalHeader').hide();
});

$('#search-close').on('click', function () {
    $('#generalSearch').val('');
    generalSearchOnPages('');
    $('.navbar-search').toggle();
    $('.search-toggle').toggle();
    $('.objectSummaryGlobalHeader').toggle();
});
