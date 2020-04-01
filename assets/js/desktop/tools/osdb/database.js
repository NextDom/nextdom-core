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

 function dbGenerateTableFromResponse(_response){
  var result = '<table class="table table-condensed table-bordered">';
  result += '<thead>';
  result += '<tr>';
  for(var i in _response[0]){
    result += '<th>';
    result += i;
    result += '</th>';
  }
  result += '</tr>';
  result += '</thead>';
  result += '<tbody>';
  for(var i in _response){
   result += '<tr>';
   for(var j in _response[i]){
     result += '<td>';
     result += _response[i][j];
     result += '</td>';
   }
   result += '</tr>';
 }
 result += '</tbody>';
 result += '</table>';
 return result;
}

$('.bt_dbCommand').off('click').on('click',function(){
  var command = $(this).attr('data-command');
  $('#div_commandResult').empty();
  nextdom.db({
    command : command,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success : function(log){
     $('#in_specificCommand').value(command);
     $('#div_commandResult').append(dbGenerateTableFromResponse(log));
   }
 })
});

$('#ul_listSqlHistory').off('click','.bt_dbCommand').on('click','.bt_dbCommand',function(){
  var command = $(this).attr('data-command');
  $('#div_commandResult').empty();
  nextdom.db({
    command : command,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success : function(log){
     $('#in_specificCommand').value(command);
     $('#div_commandResult').append(dbGenerateTableFromResponse(log));
   }
 })
});

$('#bt_validateSpecifiCommand').off('click').on('click',function(){
  var command = $('#in_specificCommand').value();
  $('#div_commandResult').empty();
  nextdom.db({
    command : command,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success : function(log){
      $('#div_commandResult').append(dbGenerateTableFromResponse(log));
      $('#ul_listSqlHistory').prepend('<li class="cursor list-group-item"><a class="bt_dbCommand label-list" data-command="'+command+'">'+command+'</a></li>');
      var kids = $('#ul_listSqlHistory').children();
      if (kids.length >= 10) {
        kids.last().remove();
      }
    }
  })
});

$('#in_specificCommand').keypress(function(e) {
  if(e.which == 13) {
   var command = $('#in_specificCommand').value();
   $('#div_commandResult').empty();
   nextdom.db({
    command : command,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success : function(log){
      $('#div_commandResult').append(dbGenerateTableFromResponse(log));
      $('#ul_listSqlHistory').prepend('<li class="cursor list-group-item"><a class="bt_dbCommand label-list" data-command="'+command+'">'+command+'</a></li>');
      var kids = $('#ul_listSqlHistory').children();
      if (kids.length >= 10) {
        kids.last().remove();
      }
    }
  })
 }
});

$('#bt_resetSpecifiCommand').off('click').on('click',function(){
  $('#in_specificCommand').value('');
});
