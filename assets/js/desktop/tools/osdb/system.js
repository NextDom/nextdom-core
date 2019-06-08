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

 $('.bt_systemCommand').off('click').on('click',function(){
     var command = $(this).attr('data-command');
     $('#pre_commandResult').empty();
     if($(this).hasClass('list-group-item-danger')){
         bootbox.confirm('{{Etes-vous sûr de vouloir éxécuter cette commande : }}<strong>'+command+'</strong> ? {{Celle-ci est classée en dangereuse...}}', function (result) {
             if (result) {
                 nextdom.ssh({
                     command : command,
                     success : function(log){
                         $('#in_specificCommand').value(command);
                         $('#pre_commandResult').append(log);
                     }
                 })
             }
         });
     }else{
         nextdom.ssh({
             command : command,
             success : function(log){
                 $('#in_specificCommand').value(command);
                 $('#pre_commandResult').append(log);
             }
         })
     }
 });


 $('#ul_listSystemHistory').off('click','.bt_systemCommand').on('click','.bt_systemCommand',function(){
     var command = $(this).attr('data-command');
     $('#pre_commandResult').empty();
     nextdom.ssh({
         command : command,
         success : function(log){
             $('#in_specificCommand').value(command);
             $('#pre_commandResult').append(log);
         }
     })
 });

 $('#bt_validateSpecifiCommand').off('click').on('click',function(){
     var command = $('#in_specificCommand').value();
     $('#pre_commandResult').empty();
     nextdom.ssh({
         command : command,
         success : function(log){
             $('#pre_commandResult').append(log);
             $('#ul_listSystemHistory').prepend('<li class="cursor list-group-item"><a class="bt_systemCommand label-list" data-command="'+command+'">'+command+'</a></li>');
             var kids = $('#ul_listSystemHistory').children();
             if (kids.length >= 10) {
                 kids.last().remove();
             }
         }
     })
 });

 $('#in_specificCommand').keypress(function(e) {
     if(e.which == 13) {
         var command = $('#in_specificCommand').value();
         $('#pre_commandResult').empty();
         nextdom.ssh({
             command : command,
             success : function(log){
                 $('#pre_commandResult').append(log);
                 $('#ul_listSystemHistory').prepend('<li class="cursor list-group-item"><a class="bt_systemCommand label-list" data-command="'+command+'">'+command+'</a></li>');
                 var kids = $('#ul_listSystemHistory').children();
                 if (kids.length >= 10) {
                     kids.last().remove();
                 }
             }
         })
     }
 });


$('#bt_consitency').off('click').on('click',function(){
    nextdom.consistency({
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (data) {
            $('#div_alert').showAlert({message: '{{Exécution de la vérification effectuée, voir le log consistency pour afficher le résultat}}', level: 'success'});
        }
    });
});

 $('#bt_resetSpecifiCommand').off('click').on('click',function(){
   $('#in_specificCommand').value('');
 });
