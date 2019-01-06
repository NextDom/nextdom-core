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

 $('#bt_downloadLog').click(function() {
     var logFile = $('.li_log.active').attr('data-log')
     if (logFile !== undefined) {
         window.open('core/php/downloadFile.php?pathfile=log/' + logFile, "_blank", null);
     }
});

 $(".li_log").on('click', function() {
  $('#pre_globallog').empty();
  $(".li_log").removeClass('active');
  $(this).addClass('active');
  $('#bt_globalLogStopStart').removeClass('btn-success').addClass('btn-warning');
  $('#bt_globalLogStopStart').html('<div><i class="fas fa-pause">&nbsp;&nbsp;</i>{{Pause}}</div>');
  $('#bt_globalLogStopStart').attr('data-state',1);
  nextdom.log.autoupdate({
    log : $(this).attr('data-log'),
    display : $('#pre_globallog'),
    search : $('#in_globalLogSearch'),
    control : $('#bt_globalLogStopStart'),
  });
  $('#bt_downloadLog').removeAttr('disabled');
});

 $("#bt_clearLog").on('click', function(event) {
  nextdom.log.clear({
    log : $('.li_log.active').attr('data-log'),
    success: function(data) {
        $('.li_log.active a').html($('.li_log.active').attr('data-log') + ' (0 Ko)');
        if($('#bt_globalLogStopStart').attr('data-state') == 0){
            $('#bt_globalLogStopStart').click();
        }
    }
  });
});

 $("#bt_removeLog").on('click', function(event) {
  nextdom.log.remove({
    log : $('.li_log.active').attr('data-log'),
    success: function(data) {
       loadPage('index.php?v=d&p=log');
    }
  });
});

$("#bt_removeAllLog").on('click', function(event) {
  bootbox.confirm("{{Êtes-vous sûr de vouloir supprimer tous les logs ?}}", function(result) {
   if (result) {
    nextdom.log.removeAll({
      error: function (error) {
       $('#div_alertError').showAlert({message: error.message, level: 'danger'});
     },
     success: function(data) {
      loadPage('index.php?v=d&p=log');
    }
  });
  }
});
});
