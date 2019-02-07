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
    var search = $(this).value().toLowerCase();
    var page = document.location.toString().split('p=')[1].replace('#', '').split('&')[0];
    switch (page) {
        case 'plugin':
            if(search == ''){
                $('.pluginListContainer .box').show();
                $('.pluginListContainer').packery();
                return;
            }
            $('.pluginListContainer .box').hide();
            $('.box .box-title').each(function(){
                var text = $(this).text().toLowerCase();
                if(text.indexOf(search) >= 0){
                    $(this).closest('.box').show();
                }
            });
            $('.pluginListContainer').packery();
            break;
        case 'interact':
            if(search == ''){
                $('.panel-collapse.in').closest('.panel').find('.accordion-toggle').click()
                $('.interactDisplayCard').show();
                $('.interactListContainer').packery();
                return;
            }
            $('.panel-collapse:not(.in)').closest('.panel').find('.accordion-toggle').click()
            $('.interactDisplayCard').hide();
            $('.interactDisplayCard').each(function(){
                var text = $(this).text().toLowerCase();
                if(text.indexOf(search) >= 0){
                    $(this).closest('.interactDisplayCard').show();
                }
            });
            $('.interactListContainer').packery();
            break;
        case 'scenario':
            if(search == ''){
                $('.panel-collapse.in').closest('.panel').find('.accordion-toggle').click();
                $('.scenarioDisplayCard').show();
                $('.scenarioListContainer').packery();
                return;
            }
            $('.panel-collapse:not(.in)').closest('.panel').find('.accordion-toggle').click();
            $('.scenarioDisplayCard').hide();
            $('.scenarioDisplayCard .title').each(function(){
                var cardTitle = $(this).text().toLowerCase();
                if (cardTitle.indexOf(search) !== -1){
                    $(this).closest('.scenarioDisplayCard').show();
                }
            });

            $('.scenarioListContainer').packery();
            break;
        case 'dashboard':
            if(search == ''){
                $('.eqLogic-widget').show();
                $('.div_displayEquipement').packery();
                return;
            }
            $('.eqLogic-widget').each(function(){
                var match = false;
                if(match || $(this).find('.widget-name').text().toLowerCase().indexOf(search) >= 0){
                    match = true;
                }
                if(match || ($(this).attr('data-tags') != undefined && $(this).attr('data-tags').toLowerCase().indexOf(search) >= 0)){
                    match = true;
                }
                if(match ||($(this).attr('data-category') != undefined && $(this).attr('data-category').toLowerCase().indexOf(search) >= 0)){
                    match = true;
                }
                if(match ||($(this).attr('data-eqType') != undefined && $(this).attr('data-eqType').toLowerCase().indexOf(search) >= 0)){
                    match = true;
                }
                if(match ||($(this).attr('data-translate-category') != undefined && $(this).attr('data-translate-category').toLowerCase().indexOf(search) >= 0)){
                    match = true;
                }
                if(match){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });

            $('.div_displayEquipement').each(function(){
                if($(this).height() < 10 && search != '') {
                    $(this).parent().parent().hide();
                } else{
                    $(this).parent().parent().show();
                }
            });

            $('.div_displayEquipement').packery();
            break;
        case 'object':
            if(search == ''){
                $('.objectDisplayCard').show();
                $('.objectListContainer').packery();
                return;
            }
            $('.objectDisplayCard').hide();
            $('.objectDisplayCard .name').each(function(){
                var text = $(this).text().toLowerCase();
                if(text.indexOf(search) >= 0){
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
            if(search == ''){

                $('.displayListContainer').packery();
                return;
            }
            $('.eqLogic').each(function(){
                var eqLogic = $(this);
                var name = eqLogic.attr('data-name').toLowerCase();
                var type = eqLogic.attr('data-type').toLowerCase();
                if(name.indexOf(search) < 0 && type.indexOf(search) < 0){
                    eqLogic.hide();
                }
                $(this).find('.cmd').each(function(){
                    var cmd = $(this);
                    var name = cmd.attr('data-name').toLowerCase();
                    if(name.indexOf(search) >= 0){
                        eqLogic.show();
                        eqLogic.find('.cmdSortable').show();
                        cmd.removeClass('alert-warning').addClass('alert-success');
                    }
                });
            });

            $('.eqLogicSortable').each(function(){
                if($(this).height() <= 30 ) {
                    $(this).parent().parent().hide();
                } else{
                    $(this).parent().parent().show();
                }
            });

            $('.displayListContainer').packery();
            break;
    }
});

$('#search-toggle').on('click', function () {
  $('.navbar-search').toggle();
  $('.search-toggle').toggle();
  $('.objectSummaryglobal').toogle();
});

$('#search-close').on('click', function () {
  $('.navbar-search').toggle();
  $('.search-toggle').toggle();
  $('.objectSummaryglobal').toogle();
});
