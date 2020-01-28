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

/* JS file for all that talk about SEARCH */

/**
 * Events declaration on load
 */
$(function () {
    // Search input field toggles
    $('#search-toggle').on('click', function () {
        $('.navbar-search').toggle();
        $('.search-toggle').toggle();
        $('.objectSummaryGlobalHeader').toggle();
    });

    var delay=200, setTimeoutConst;
    $('#search-toggle').hover(function () {
      setTimeoutConst = setTimeout(function() {
          $('.navbar-search').show();
          $('.search-toggle').hide();
          $('.objectSummaryGlobalHeader').hide();
        }, delay);
      }, function() {
      clearTimeout(setTimeoutConst);
    });

    // Close search input field
    $('#search-close').on('click', function () {
        $('#generalSearch').val('');
        generalSearchOnPages('');
        $('.navbar-search').toggle();
        $('.search-toggle').toggle();
        $('.objectSummaryGlobalHeader').toggle();
    });

    // Run search when typing
    $('#generalSearch').keyup(function () {
        generalSearchOnPages($(this).value().toLowerCase());
    });
});

/**
 * Search input field activation on dedicated pages
 */
function activateGlobalSearch() {
    var fullUrl = document.location.toString();
    var page ='';
    var availableSearchPage = [
        "plugin",
        "dashboard",
        "interact",
        "scenario",
        "object",
        "display",
        "database",
        "note",
        "system",
        "log",
        "market",
        "marketJee",
        "update.list",
        "update",
        "health",
    ];
    if (fullUrl.indexOf('p=') != -1) {
        page = fullUrl.split('p=')[1].replace('#', '').split('&')[0];
    } else {
          if (fullUrl.indexOf('modal=') != -1) {
              page = fullUrl.split('modal=')[1].replace('#', '').split('&')[0];
          }
    }

    if(jQuery.inArray(page, availableSearchPage) != -1) {
        $("#generalSearch").prop('disabled', false);
    } else {
        $("#generalSearch").prop('disabled', true);
    }
}

/**
 * Search elements in page
 *
 * @param value search caracters
 */
function generalSearchOnPages(value) {
    var search = value;
    var fullUrl = document.location.toString();
    var page = '';
    if (fullUrl.indexOf('p=') != -1) {
        page = fullUrl.split('p=')[1].replace('#', '').split('&')[0];
    } else {
          if (fullUrl.indexOf('modal=') != -1) {
              page = fullUrl.split('modal=')[1].replace('#', '').split('&')[0];
          }
    }
    switch (page) {
        case 'plugin':
            if (search === '') {
                $('.box').show();
            } else {
                $('.box').hide();
                $('.box .box-title').each(function () {
                    var boxTitle = $(this).text().toLowerCase();
                    if (boxTitle.indexOf(search) !== -1) {
                        $(this).closest('.box').show();
                    }
                });
            }
            break;

        case 'interact':
            if (search === '') {
                $('.panel-collapse.in').closest('.panel').find('.accordion-toggle.collapsed').click();
                $('.interactDisplayCard').show();
            } else {
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
            } else {
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
                $('.card').show();
                $('.eqLogic-widget').show();
                $('.div_displayEquipement').show();
                $('.div_displayEquipement').packery();
            } else {
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
                    } else {
                        $(this).parent().parent().hide();
                    }
                    $(this).packery();
                });
                $('.card').each(function () {
                    if ($(this).children(".card-body").length === 0) {
                        $(this).hide();
                    }
                });
            }
            break;

        case 'object':
            if (search == '') {
                $('.objectDisplayCard').parent().show();
            } else {
                $('.objectDisplayCard').parent().hide();
                $('.objectDisplayCard .name').each(function () {
                    var text = $(this).text().toLowerCase();
                    if (text.indexOf(search) >= 0) {
                        $(this).closest('.objectDisplayCard').parent().show();
                    }
                });
            }
            break;

        case 'display':
            $('.cmd').show().removeClass('alert-success').addClass('alert-warning');
            $('.cmdSortable').hide();
            $('.eqLogic').hide();
            $('.object').hide();
            $('.panel-collapse').each(function () {
                if ($(this).hasClass("in")) {
                    $(this).removeClass("in");
                }
            });
            $('.eqLogic').each(function () {
                var eqLogic = $(this);
                var name = eqLogic.attr('data-name').toLowerCase();
                var type = eqLogic.attr('data-type').toLowerCase();
                $(this).find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-right");
                if (search == '' || name.indexOf(search) >= 0 || type.indexOf(search) >= 0) {
                    eqLogic.show();
                    eqLogic.parents('.object').show();
                    if (search != '' && !eqLogic.closest('.panel-collapse').hasClass("in")) {
                        eqLogic.closest('.panel-collapse').css({'height' : '' });
                        eqLogic.closest('.panel-collapse').addClass("in");
                    }
                }
                $(this).find('.cmd').each(function () {
                    var cmd = $(this);
                    var nameCmd = cmd.attr('data-name').toLowerCase();
                    if (nameCmd.indexOf(search) >= 0 || search == '') {
                        eqLogic.show();
                        eqLogic.parents('.object').show();
                        if (search != '') {
                            eqLogic.find('.cmdSortable').show();
                            eqLogic.find(".fa-chevron-right").removeClass("fa-chevron-right").addClass("fa-chevron-down");
                            cmd.removeClass('alert-warning').addClass('alert-success');
                            if (!cmd.closest('.panel-collapse').hasClass("in")) {
                                cmd.closest('.panel-collapse').css({'height' : '' });
                                cmd.closest('.panel-collapse').addClass("in");
                            }
                        }
                    }
                });
            });
            break;

        case 'database':
            if (search === '') {
                $('.list-group-item').show();
            } else {
                $('.list-group-item').hide();
                $('.list-group-item .label-list').each(function () {
                    var listTitle = $(this).text().toLowerCase();
                    if (listTitle.indexOf(search) !== -1) {
                        $(this).closest('.list-group-item').show();
                    }
                });
            }
            break;

        case 'system':
            if (search === '') {
                $('.list-group-item').show();
            } else {
                $('.list-group-item').hide();
                $('.list-group-item .label-list').each(function () {
                    var listTitle = $(this).text().toLowerCase();
                    if (listTitle.indexOf(search) !== -1) {
                        $(this).closest('.list-group-item').show();
                    }
                });
            }
            break;

        case 'note':
            if (search === '') {
                $('.li_noteDisplay').show();
            } else {
                $('.li_noteDisplay').hide();
                $('#div_noteDisplay').hide();
                $('.li_noteDisplay .label-list').each(function () {
                    var listTitle = $(this).text().toLowerCase();
                    if (listTitle.indexOf(search) !== -1) {
                        $(this).closest('.li_noteDisplay').show();
                    }
                });
            }
            break;

        case 'log':
            if (search === '') {
                $('.label-log').show();
            } else {
                $('.label-log').hide();
                $('#div_logDisplay').hide();
                $('.label-log').each(function () {
                    var listTitle = $(this).text().toLowerCase();
                    if (listTitle.indexOf(search) !== -1) {
                        $(this).show();
                    }
                });
            }
            break;

        case 'market':
            updateFilteredList();
            break;

        case 'update.list':
            marketFilter();
            break;

        case 'marketJee':
            marketFilter();
            break;

        case 'update':
            if(search == ''){
                $('.box').show();
            } else {
                $('.box').hide();
                $('.box .box-title').each(function(){
                    var boxTitle = $(this).text().toLowerCase();
                    if(boxTitle.indexOf(search.toLowerCase()) >= 0){
                        $(this).closest('.box').show();
                    }
                });
            }
            break;

        case 'health':
            if (search == '') {
                $('.box').parent().show();
            } else {
                $('.box').parent().hide();
                $('.box .box-title').each(function () {
                    var boxTitle = $(this).text().toLowerCase();
                    if (boxTitle.indexOf(search.toLowerCase()) >= 0) {
                        $(this).closest('.box').parent().show();
                    }
                });
            }
            break;
    }
};
