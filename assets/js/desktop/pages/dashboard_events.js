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
        $('.bt_editDashboardWidgetAutoResize').off('click').on('click', function () {
            var id_object = $(this).attr('id');
            id_object = id_object.replace('edit_object_', '');
            $('#div_ob' + id_object + '.div_displayEquipement .eqLogic-widget').each(function (index, element) {
                  var id_object_2 = $(this).attr('data-eqlogic_id');
                  if ($(element).hasClass('allowResize')) {
                      if (($(element).height() + parseInt(widget_margin)) % (parseInt(widget_size) + parseInt(widget_margin)) != 0) {
                          var autoHeight = Math.ceil($(element).height() / parseInt(widget_size));
                          $(element).height(parseInt(autoHeight) * parseInt(widget_size) + ((parseInt(autoHeight)-1) * widget_margin));
                      }
                      if (($(element).width() + parseInt(widget_margin)) % (parseInt(widget_size) + parseInt(widget_margin)) != 0) {
                          var autoWidth = Math.ceil($(element).width() / parseInt(widget_size));
                          $(element).width(parseInt(autoWidth) * parseInt(widget_size) + ((parseInt(autoWidth)-1) * widget_margin));
                      }
                  }
            });
            $('#div_ob' + id_object).trigger('resize');
            $('#div_ob' + id_object).packery();
        });
        $('.bt_editDashboardWidgetGridResize').off('click').on('click', function () {
            var id_object = $(this).attr('id');
            id_object = id_object.replace('edit_object_', '');
            $('#div_ob' + id_object + '.div_displayEquipement .eqLogic-widget').each(function (index, element) {
                  var id_object_2 = $(this).attr('data-eqlogic_id');
                  if ($(element).hasClass('allowResize')) {
                      $(element).height('auto');
                      $(element).width('auto');
                      var autoWidth = Math.ceil($(element).width() / parseInt(widget_size));
                      $(element).width(parseInt(autoWidth) * parseInt(widget_size) + ((parseInt(autoWidth)-1) * widget_margin));
                      var autoHeight = Math.ceil($(element).height() / parseInt(widget_size));
                      $(element).height(parseInt(autoHeight) * parseInt(widget_size) + ((parseInt(autoHeight)-1) * widget_margin));
                  }
            });
            $('#div_ob' + id_object).trigger('resize');
            $('#div_ob' + id_object).packery();
        });
        editWidgetMode(1);
        $(this).html('<i class="fas fa-stop"></i>');
    }
});

$('#div_pageContainer').on( 'click','.eqLogic-widget .history', function () {
    $('#md_modal2').dialog({title: "Historique"});
    $("#md_modal2").load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');
});

$('.openObject').on('click',function(){
    loadPage($(this).attr('data-id'));
});

function initObjectMenuFilter() {
    $('.nav-tabs-custom a').off('click').click(function() {
        window.location.href = 'index.php?v=d&p=dashboard&object_id=' + $(this).data('object-id');
        return false;
    });
}

initObjectMenuFilter();
