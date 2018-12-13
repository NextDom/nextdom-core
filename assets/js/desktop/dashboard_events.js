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
        $('.bt_editDashboardWidgetUniformize').hide();
        $('.bt_editDashboardWidgetAutoResize').hide();
        $('.bt_editDashboardWidgetGridResize').hide();
        $('.counterReorderNextDom').remove();
        $('.div_displayEquipement').packery();
    } else {
        notify('Core', '{{Vous êtes en mode édition vous pouvez déplacer les widgets, les redimensionner et changer l\'ordre des commandes dans les widgets. N\'oubliez pas de quitter le mode édition pour sauvegarder}}', 'success');
        $(this).attr('data-mode', 1);
        $('.bt_editDashboardWidgetUniformize').show();
        $('.bt_editDashboardWidgetAutoResize').show();
        $('.bt_editDashboardWidgetGridResize').show();
        $('.bt_editDashboardWidgetUniformize').off('click').on('click', function () {
            var id_object = $(this).attr('id');
            id_object = id_object.replace('edit_object_', '');
            var heightObjectex = 0;
            $('#div_ob' + id_object + '.div_displayEquipement .eqLogic-widget').each(function (index, element) {
                var heightObject = this.style.height;
                heightObject = eval(heightObject.replace('px', ''));
                var valueAdd = eval(heightObject * 0.20);
                var valueRemove = eval(heightObject * 0.05);
                var heightObjectadd = eval(heightObject + valueAdd);
                var heightObjectremove = eval(heightObject - valueRemove);
                if (heightObjectadd >= heightObjectex && (heightObjectex > heightObject || heightObjectremove < heightObjectex)) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).height(heightObjectex);
                        heightObject = heightObjectex;
                    }
                }
                heightObjectex = heightObject;
            });
            $('#div_ob' + id_object).trigger('resize');
            $('#div_ob' + id_object).packery();
        });
        $('.bt_editDashboardWidgetAutoResize').off('click').on('click', function () {
            var id_object = $(this).attr('id');
            id_object = id_object.replace('edit_object_', '');
            $('#div_ob' + id_object + '.div_displayEquipement .eqLogic-widget').each(function (index, element) {
                  var id_object_2 = $(this).attr('data-eqlogic_id');
                  if ($(element).hasClass('allowResize')) {
                      $(element).height('auto');
                      $(element).width('auto');
                      $(element).width(Math.ceil($(element).width() / widget_width_step) * widget_width_step - (2 * widget_margin));
                      $(element).height(Math.ceil($(element).height() / widget_height_step) * widget_height_step - (2 * widget_margin));
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
                      $(element).width(Math.ceil($(element).width() / widget_width_autostep) * widget_width_autostep - (2 * widget_margin));
                      $(element).width($(element).width()*1.1);
                      $(element).height(Math.ceil($(element).height() / widget_height_autostep) * widget_height_autostep - (2 * widget_margin));
                  }
            });
            $('#div_ob' + id_object).trigger('resize');
            $('#div_ob' + id_object).packery();
        });
        editWidgetMode(1);
        $(this).html('<i class="fas fa-stop"></i>');
    }
});


$('.li_object').on('click', function () {
    var object_id = $(this).find('a').attr('data-object_id');
    if ($('.div_object[data-object_id=' + object_id + ']').html() != undefined) {
        nextdom.object.getImgPath({
            id: object_id,
            success: function (_path) {
                $('.backgroundforNextDom').css('background-image', 'url("' + _path + '")');
            }
        });
        $('.li_object').removeClass('active');
        $(this).addClass('active');
        displayChildObject(object_id, false);
    } else {
        loadPage($(this).find('a').attr('data-href'));
    }
});

$('#category-filterBtn').click(function() {
    $('.category-filter-btn-sm').toggleClass('scale-out');
    $('#dashPanel').toggleClass('dashBlur');
});

$('.category-filter-btn-sm').click(function() {
    $('.category-filter-btn-sm').toggleClass('scale-out');
    $('#dashPanel').toggleClass('dashBlur');
});
