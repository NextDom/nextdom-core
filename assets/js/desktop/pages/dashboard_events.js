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
    // Background image load
    $(function () {
        setTimeout(function () {
            if (typeof rootObjectId != 'undefined') {
                nextdom.object.getImgPath({
                    id: rootObjectId,
                    success: function (_path) {
                        $('.backgroundforNextDom').css('background-image', 'url("' + _path + '")');
                    }
                });
            }
        }, 1);
    });
    // Object menu filter init
    initObjectMenuFilter();
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Dashboard edition mode button
    $('#bt_editDashboardWidgetOrder').on('click', function () {
        if ($(this).attr('data-mode') == 1) {
            $.hideAlert();
            $(this).attr('data-mode', 0);
            editWidgetMode(0);
            $(this).html('<i class="fas fa-pencil-alt"></i>');
            $('.editDashboardButtons').hide();
            $('.card-order-number').remove();
            $('.div_displayEquipement').packery();
        } else {
            notify('Core', '{{Vous êtes en mode édition vous pouvez déplacer les widgets, les redimensionner et changer l\'ordre des commandes dans les widgets. N\'oubliez pas de quitter le mode édition pour sauvegarder}}', 'success');
            $(this).attr('data-mode', 1);
            $('.editDashboardButtons').show();
            // All Widgets resize button (father and son)
            $('.bt_editDashboardWidgetGridAllResize').off('click').on('click', function () {
                var id_object = $(this).attr('id');
                id_object = id_object.replace('edit_father_object_', '');
                $('#dashboard-content .div_displayEquipement').each(function (index, element2) {
                      if ($(element2).attr('data-father_id') == id_object) {
                          $(element2).find('.eqLogic-widget').each(function (index, element) {
                              resizeWidget(element);
                          });
                          $(element2).trigger('resize');
                          $(element2).packery();
                      }
                });
            });
            // Widgets resize button (only son)
            $('.bt_editDashboardWidgetGridResize').off('click').on('click', function () {
                var id_object = $(this).attr('id');
                id_object = id_object.replace('edit_object_', '');
                $('#div_ob' + id_object + '.div_displayEquipement .eqLogic-widget').each(function (index, element) {
                    resizeWidget(element);
                });
                $('#div_ob' + id_object).trigger('resize');
                $('#div_ob' + id_object).packery();
            });
            // Packery widget
            $('.bt_editDashboardWidgetPackery').off('click').on('click',function(){
                var id_object = $(this).attr('id');
                id_object = id_object.replace('edit_object_', '');
                $('#div_ob' + id_object).packery();
            });
            editWidgetMode(1);
            $(this).html('<i class="fas fa-stop"></i>');
        }
    });

    // Widget data click for history modele
    $('#div_pageContainer').on( 'click','.eqLogic-widget .history', function () {
        $('#md_modal2').dialog({title: "Historique"});
        $("#md_modal2").load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');
    });

    // Room title click for objets detail page
    $('.openObject').on('click',function(){
        loadPage($(this).attr('data-id'));
    });

    // Room filter accordion hover toggle
    $('#accordionRoom').hover(function(){
      $('#accordionRoomHeader').click();
      }, function(){
    });
}

/**
 * Initialisation of tabs object filter menu
 */
function initObjectMenuFilter() {
    $('.nav-tabs-custom a').off('click').click(function() {
        window.location.href = 'index.php?v=d&p=dashboard&object_id=' + $(this).data('object-id');
        return false;
    });
}

/**
 * Resize widget
 *
 * @param widgetToResize widget element
 */
function resizeWidget(widgetToResize) {
    if ($(widgetToResize).hasClass('allowResize')) {
        $(widgetToResize).width('auto').height('auto');
        var realWidth = $(widgetToResize).find('.widget-content').width();
        var realHeight = $(widgetToResize).find('.widget-content').height()+26;
        var autoWidth = Math.ceil(realWidth / parseInt(widget_size));
        var autoHeight = Math.ceil(realHeight / parseInt(widget_size));
        $(widgetToResize).width((parseInt(autoWidth) * parseInt(widget_size)) + ((parseInt(autoWidth)-1) * widget_margin));
        $(widgetToResize).height((parseInt(autoHeight) * parseInt(widget_size)) + ((parseInt(autoHeight)-1) * widget_margin));
    }
}
