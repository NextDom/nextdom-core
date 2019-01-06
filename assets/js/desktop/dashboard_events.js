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
        $('.bt_editDashboardWidgetAutoResize').hide();
        $('.counterReorderNextDom').remove();
        $('.div_displayEquipement').packery();
    } else {
        notify('Core', '{{Vous êtes en mode édition vous pouvez déplacer les widgets, les redimensionner et changer l\'ordre des commandes dans les widgets. N\'oubliez pas de quitter le mode édition pour sauvegarder}}', 'success');
        $(this).attr('data-mode', 1);
        $('.bt_editDashboardWidgetAutoResize').show();
        $('.bt_editDashboardWidgetAutoResize').off('click').on('click', function () {
            var id_object = $(this).attr('id');
            id_object = id_object.replace('edit_object_', '');
            $('#div_ob' + id_object + '.div_displayEquipement .eqLogic-widget').each(function (index, element) {
                var widthObject = this.style.width;
                widthObject = eval(widthObject.replace('px', ''));
                if (widthObject <= parseInt(widget_size)) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).width(parseInt(widget_size));
                    }
                } else if (widthObject > parseInt(widget_size) && widthObject <= parseInt(widget_size)*2 + parseInt(widget_margin)*2) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).width(parseInt(widget_size)*2 + parseInt(widget_margin)*2);
                    }
                } else if(widthObject > parseInt(widget_size)*2 + parseInt(widget_margin)*2 && widthObject <= parseInt(widget_size)*3 + parseInt(widget_margin)*4) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).width(parseInt(widget_size)*3 + parseInt(widget_margin)*4);
                    }
                }
                var heightObject = this.style.height;
                heightObject = eval(heightObject.replace('px', ''));
                if (heightObject <= parseInt(widget_size)) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).height(parseInt(widget_size));
                    }
                } else if (heightObject > parseInt(widget_size) + parseInt(widget_margin)*2 && heightObject <= parseInt(widget_size)*2 + parseInt(widget_margin)*4 ) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).height(parseInt(widget_size)*2 + parseInt(widget_margin)*2);
                    }
                } else if(heightObject > (parseInt(widget_size)*2 + parseInt(widget_margin)*2) && heightObject <= parseInt(widget_size)*3 + parseInt(widget_margin)*2 ) {
                    if ($(element).hasClass('allowResize')) {
                        $(element).height(parseInt(widget_size)*3 + parseInt(widget_margin)*4);
                    }
                }
            });
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