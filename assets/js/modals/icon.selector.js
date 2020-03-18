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

$('#in_iconSelectorSearch').on('keyup',function(){
    iconSearch($('#in_iconSelectorSearch').value());
});

function iconSearch(search) {
    $('.btn-selector').hide();
    $('.panel').hide();
    if(search != ''){
        $('.btn-selector span').each(function(){
            if($(this).text().indexOf(search) >= 0){
                $(this).closest('.btn-selector').show();
                $(this).closest('.panel').show();
            }
        })
    } else {
        $('.btn-selector').show();
        $('.panel').show();
    }
}

$('#bt_iconReset').on('click', function () {
    $('#in_iconSelectorSearch').val('');
    iconSearch($('#in_iconSelectorSearch').value());
});

$('.btn-selector').on('click', function () {
    $('.btn-selector').removeClass('iconSelected');
    $(this).closest('.btn-selector').addClass('iconSelected');
});

$('.btn-selector').on('dblclick', function () {
    $('.btn-selector').removeClass('iconSelected');
    $(this).closest('.btn-selector').addClass('iconSelected');
    $('#md_modal2').dialog("option", "buttons")['Valider'].apply($('#md_modal2'));
});

$('#bt_iconCollapse').on('click',function(){
   $('.panel-collapse').each(function () {
      if (!$(this).hasClass("in")) {
          $(this).css({'height' : '' });
          $(this).addClass("in");
      }
   });
   $('#bt_iconCollapse').hide();
   $('#bt_iconUncollapse').show()
});

$('#bt_iconUncollapse').on('click',function(){
   $('.panel-collapse').each(function () {
      if ($(this).hasClass("in")) {
          $(this).removeClass("in");
      }
   });
   $('#bt_iconUncollapse').hide();
   $('#bt_iconCollapse').show()
});
