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

$('#room_filters a').off('click').on('click', function () {
  var selected = $(this);
  var object_id = selected.attr('data-id');
  if (object_id !== undefined) {
    $('.div_displayEquipement').parent().empty().append('<legend>'+selected.attr('data-name')+'</legend><div id="dashboard-content" class="div_displayEquipement"></div>');
    getObjectHtml(object_id);
  }
});


function getObjectHtml(_object_id){
  addOrUpdateUrl('object_id', _object_id);
  nextdom.object.toHtml({
    id: _object_id,
    version: 'dashboard',
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (result) {
      var div = $('.div_displayEquipement');
      try {
        div.html(result['objectHtml']);
      }catch(err) {
        console.log(err);
      }
      setTimeout(function(){
        positionEqLogic();
        div.find('.eqLogic').css("margin", "4px");
        div.packery();
        div.disableSelection();
        $("input").click(function() { $(this).focus(); });
        $("textarea").click(function() { $(this).focus(); });
        $("select").click(function() { $(this).focus(); });
      },10);
    }
  });
}


var idFromUrl = getUrlVars('object_id');
if (idFromUrl) {
    var selected = $('a[data-id=' + idFromUrl + ']');
    if (selected.length !== 0) {
        $('.div_displayEquipement').parent().empty().append('<legend>' + selected.attr('data-name') + '</legend><div class="div_displayEquipement"></div>');
        getObjectHtml(idFromUrl);
    } else {
        notify("Erreur", 'Id ' + idFromUrl + ' not found', 'error');
    }
}
