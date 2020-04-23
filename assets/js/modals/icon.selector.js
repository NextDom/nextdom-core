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
 */

$(function() {
    setTimeout(function () {
        container = $('#mod_selectIcon');

        elemIcon = $('#tabicon .iconSelected');
        elemImg = $('#tabimg .iconSelected');
        if(elemImg.length === 0 && elemIcon.length === 0) {
            $('#mod_selectIcon ul li a[href="#tabicon"]').click();
        } else {
            if (elemIcon.length === 1) {
                $('#mod_selectIcon ul li a[href="#tabicon"]').click();
                var positionElem = elemIcon.position();
                if (positionElem) {
                    var pos = positionElem.top + elemIcon.parent().parent().parent().position().top - container.scrollTop();
                    $('#tabicon').animate({scrollTop: pos});
                }
            }
            if (elemImg.length === 1) {
                $('#mod_selectIcon ul li a[href="#tabimg"]').click();
                var positionElem = elemImg.position();
                if (positionElem) {
                    var pos = positionElem.top + elemImg.parent().parent().parent().position().top - container.scrollTop();
                    $('#tabimg').animate({scrollTop: pos});
                }
            }
        }
    }, 250);
});

//*                                         */
// Search
//*                                         */
$('#in_iconSelectorSearch').on('keyup', function () {
    iconSearch($('#in_iconSelectorSearch').value());
});

function iconSearch(search) {
    $('.btn-selector').hide();
    $('.panel').hide();
    if (search !== '') {
        $('.btn-selector span').each(function () {
            if ($(this).text().indexOf(search) >= 0) {
                $(this).closest('.btn-selector').show();
                $(this).closest('.panel').show();
            }
        });
    } else {
        $('.btn-selector').show();
        $('.panel').show();
    }
}

$('#bt_iconReset').on('click', function () {
    $('#in_iconSelectorSearch').val('');
    iconSearch('');
});

//*                                         */
//Color Icon
//*                                         */
$('#sel_colorIcon').off('change').on('change', function () {
    $('.iconSel i').removeClass('icon_green icon_blue icon_orange icon_red icon_yellow').addClass($(this).value());
});

//*                                         */
//Icon Events
//*                                         */
$('.btn-selector').on('click', function () {
    $('.btn-selector').removeClass('iconSelected');
    $(this).closest('.btn-selector').addClass('iconSelected');
});

$('.btn-selector').on('dblclick', function () {
    this.click();
    var mod_selectIcon = $('#mod_selectIcon');
    mod_selectIcon.dialog("option", "buttons")['Valider'].apply(mod_selectIcon);
});

$('#mod_selectIcon ul li a[href="#tabicon"]').click(function(e) {
    $('#sel_colorIcon').show();
    $('#uploadImageIcon').hide();
    $('#tabicon').show();
    $('#tabimg').hide();
});
$('#mod_selectIcon ul li a[href="#tabimg"]').click(function(e) {
    $('#uploadImageIcon').show();
    $('#sel_colorIcon').hide();
    $('#tabimg').show();
    $('#tabicon').hide();
});

$('#bt_uploadImageIcon').fileupload({
    replaceFileInput: false,
    url: 'core/ajax/nextdom.ajax.php?action=uploadImageIcon&nextdom_token='+NEXTDOM_AJAX_TOKEN,
    dataType: 'json',
    done: function (e, data) {
        if (data.result.state !== 'ok') {
            $('#div_iconSelectorAlert').showAlert({message: data.result.result, level: 'danger'});
            return;
        }
        $('#mod_selectIcon').empty().load('index.php?v=d&modal=icon.selector&showimg=1&selectImg=' + data.result.result);
    }
});

//*                                         */
//Collapse Events
//*                                         */
$('#bt_iconCollapse').on('click', function () {
    $('.panel-collapse').each(function () {
        if (!$(this).hasClass("in")) {
            $(this).css({'height': ''});
            $(this).addClass("in");
        }
    });
    $('#bt_iconCollapse').hide();
    $('#bt_iconUncollapse').show();
});

$('#bt_iconUncollapse').on('click', function () {
    $('.panel-collapse').each(function () {
        if ($(this).hasClass("in")) {
            $(this).removeClass("in");
        }
    });
    $('#bt_iconUncollapse').hide();
    $('#bt_iconCollapse').show();
});
