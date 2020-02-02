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
    $("img.lazy").lazyload({
      threshold : 400
    });
    initTableSorter();
    marketFilter();
    setTimeout(function(){
        $('.pluginContainer').packery();
    },200);
}

/**
 * Init events on the profils page
 */
function initEvents() {
    $('.bt_pluginFilterCost').on('click', function () {
        $('.bt_pluginFilterCost').removeClass('btn-primary');
        $('.bt_pluginFilterCost').addClass('btn-default');
        $(this).addClass('btn-primary');
        $(this).removeClass('btn-default');
        marketFilter();
    });

    $('#sel_certif').on('change', function () {
        marketFilter();
    });

    $('.bt_pluginFilterInstall').on('click', function () {
        $('.bt_pluginFilterInstall').removeClass('btn-primary');
        $('.bt_pluginFilterInstall').addClass('btn-default');
        $(this).addClass('btn-primary');
        $(this).removeClass('btn-default');
        marketFilter();
    });

    $('#sel_categorie').on('change', function () {
        if ($(this).value() == '') {
            loadPage('index.php?v=d&p=marketJee' + '&type=' + marketType);
        } else {
            loadPage('index.php?v=d&p=marketJee' + '&type=' + marketType + '&categorie=' + encodeURI($(this).value()));
        }
    });

    $('#bt_marketCollapse').on('click',function(){
       $('.panel-collapse').each(function () {
          if (!$(this).hasClass("in")) {
              $(this).css({'height' : '' });
              $(this).addClass("in");
          }
       });
       $('#bt_marketCollapse').hide();
       $('#bt_marketUncollapse').show()
    });

    $('#bt_marketUncollapse').on('click',function(){
       $('.panel-collapse').each(function () {
          if ($(this).hasClass("in")) {
              $(this).removeClass("in");
          }
       });
       $('#bt_marketUncollapse').hide();
       $('#bt_marketCollapse').show()
    });

    $('#sel_type').on('change', function () {
        loadPage('index.php?v=d&p=marketJee' + '&type=' + encodeURI($(this).value()));
    });

    $('.market').on('click', function () {
        $('#md_modal2').dialog({title: "{{Market JeeDom}}"});
        $('#md_modal2').load('index.php?v=d&modal=update.display&type=' + $(this).attr('data-market_type') + '&id=' + $(this).attr('data-market_id')+'&repo=market').dialog('open');
    });

    $('#pluginNameSearch').on('click', function () {
        bootbox.prompt("{{Nom du plugin ?}}", function (result) {
          loadPage('index.php?v=d&p=marketJee' + '&type=' + marketType + '&name=' + encodeURI(result));
        });
        $('#generalSearch').value('');
    });

    $('#authorSearch').on('click', function () {
        bootbox.prompt("{{Nom de l'auteur ?}}", function (result) {
          loadPage('index.php?v=d&p=marketJee' + '&type=' + marketType + '&author=' + encodeURI(result));
        });
        $('#generalSearch').value('');
    });

    $('#resetSearch').on('click', function () {
        loadPage('index.php?v=d&p=marketJee&type=' + marketType);
        $('#generalSearch').value('');
    });

    $('#bt_resetSearchLimit').on('click', function () {
        loadPage('index.php?v=d&p=marketJee&type=' + marketType + '&categorie=' + encodeURI(marketCategory) + '&limit=');
        $('#generalSearch').value('');
    });

    $('#bt_SearchLimit').on('click', function () {
        loadPage('index.php?v=d&p=marketJee&type=' + marketType + '&categorie=' + encodeURI(marketCategory));
        $('#generalSearch').value('');
    });
}

function displayWidgetName(name) {
    var result = '';
    var nameArray = explode('.', name);
    if (count(nameArray) != 4) {
        return nameArray;
    }
    switch (nameArray[1]) {
        case 'info':
            result += '<i class="fa fa-eye fa-fw" title="{{Widget de type information}}"></i> ';
            break;
        case 'action':
            result += '<i class="fa fa-exclamation-circle fa-fw" title="{{Widget de type action}}"></i> ';
            break;
        default:
            result += $name[1];
            break;
    }
    switch (nameArray[2]) {
        case 'other':
            result += '<span class="label label-warning" style="text-shadow: none;">other</span> ';
            break;
        case 'color':
            result += '<span class="label label-success" style="text-shadow: none;">color</span> ';
            break;
        case 'slider':
            result += '<span class="label label-primary" style="text-shadow: none;">slider</span> ';
            break;
        case 'binary':
            result += '<span class="label label-info" style="text-shadow: none;">binary</span> ';
            break;
        case 'numeric':
            result += '<span class="label label-danger" style="text-shadow: none;">numeric</span> ';
            break;
        case 'string':
            result += '<span class="label label-default" style="text-shadow: none;">string</span> ';
            break;
        default:
            result += nameArray[2];
            break;
    }
    return result += nameArray[3];
}

function displayWidgetType(name) {
    var result = '';
    var nameArray = explode('.', name);
    if (count(nameArray) != 4) {
        return "";
    }
    switch (nameArray[1]) {
        case 'info':
            result += '<i class="fa fa-eye fa-fw" title="Widget de type information" style="position: absolute;top: 31px; left: 15px;"></i> ';
            break;
        case 'action':
            result += '<i class="fa fa-exclamation-circle fa-fw" title="Widget de type action" style="position: absolute;top: 31px; left: 15px;"></i> ';
            break;
        default:
            result += "";
            break;
    }
    return result;
}

function displayWidgetSubtype(name) {
    var result = '';
    var nameArray = explode('.', name);
    if (count(nameArray) != 4) {
        return "";
    }
    switch (nameArray[2]) {
        case 'other':
            result += '<span class="label label-warning" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 38px 16px;">other</span> ';
            break;
        case 'color':
            result += '<span class="label label-success" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 38px 16px;">color</span> ';
            break;
        case 'slider':
            result += '<span class="label label-primary" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 41px 16px;">slider</span> ';
            break;
        case 'binary':
            result += '<span class="label label-info" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 44px 16px;">binary</span> ';
            break;
        case 'numeric':
            result += '<span class="label label-danger" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 53px 16px;">numeric</span> ';
            break;
        case 'string':
            result += '<span class="label label-default" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 41px 16px;">string</span> ';
            break;
        default:
            result += "";
            break;
    }
    return result;
}

function marketFilter() {
    var filterCost = '';
    var filterInstall = '';
    var pluginValue = '';
    $('.bt_pluginFilterCost').each(function () {
        if ($(this).hasClass("btn-primary")) {
            filterCost = $(this).attr('data-filter');
        }
    });
        $('.bt_pluginFilterInstall').each(function () {
        if ($(this).hasClass("btn-primary")) {
            filterInstall = $(this).attr('data-filter');
        }
    });
    var filterCertification = $('#sel_certif').value();
    var filterCategory = $('#sel_categorie').value();
    var currentSearchValue = $('#generalSearch').val().toLowerCase();
    $('.market').show();

    $('.market').each(function () {
        if (currentSearchValue != '') {
            pluginValue = $(this).attr('data-name').toLowerCase();
            if (pluginValue.indexOf(currentSearchValue) == -1) {
                $(this).hide();
            }
        }

        if (filterCertification != '') {
            pluginValue = $(this).attr('data-certification');
            if (pluginValue.indexOf(filterCertification) == -1) {
                $(this).hide();
            }
        }

        if (filterCost != '') {
            pluginValue = $(this).attr('data-cost');
            if ((pluginValue == 0 && filterCost == 'paying') || (pluginValue > 0 && filterCost == 'free')) {
                $(this).hide();
            }
        }

        if (filterCategory != '') {
            pluginValue = $(this).attr('data-category');
            if (pluginValue.indexOf(filterCategory) == -1) {
                $(this).hide();
            }
        }

        if (filterInstall != '') {
            pluginValue = $(this).attr('data-install');
            if (pluginValue.indexOf(filterInstall) == -1) {
                $(this).hide();
            }
        }
    });
    $('.pluginContainer').packery();
};
