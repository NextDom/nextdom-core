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

 printUsers();
 $("#bt_addUser").on('click', function (event) {
    $.hideAlert();
    $('#in_newUserLogin').value('');
    $('#in_newUserMdp').value('');
    $('#md_newUser').modal('show');
});

 $("#bt_newUserSave").on('click', function (event) {
    $.hideAlert();
    var user = [{login: $('#in_newUserLogin').value(), password: $('#in_newUserMdp').value()}];
    nextdom.user.save({
        users: user,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            printUsers();
            notify("Info", '{{Sauvegarde effectuée}}', 'success');
            modifyWithoutSave = false;
            $('#md_newUser').modal('hide');
        }
    });
});

 jwerty.key('ctrl+s/⌘+s', function (e) {
    e.preventDefault();
    $('#bt_saveUser').click();
});

 $("#bt_saveUser").on('click', function (event) {
    nextdom.user.save({
        users: $('#table_user tbody tr').getValues('.userAttr'),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            printUsers();
            notify("Info", '{{Sauvegarde effectuée}}', 'success');
            modifyWithoutSave = false;
        }
    });
});

 $("#table_user").on('click',".bt_del_user",  function (event) {
    $.hideAlert();
    var user = {id: $(this).closest('tr').find('.userAttr[data-l1key=id]').value()};
    bootbox.confirm('{{Etes-vous sûr de vouloir supprimer cet utilisateur ?}}', function (result) {
        if (result) {
            nextdom.user.remove({
                id: user.id,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    printUsers();
                    notify("Info", '{{L\'utilisateur a bien été supprimé}}', 'success');
                }
            });
        }
    });
});

 $("#table_user").on( 'click',".bt_change_mdp_user", function (event) {
    $.hideAlert();
    var user = {id: $(this).closest('tr').find('.userAttr[data-l1key=id]').value(), login: $(this).closest('tr').find('.userAttr[data-l1key=login]').value()};
    bootbox.prompt("{{Quel est le nouveau mot de passe ?}}", function (result) {
        if (result !== null) {
            user.password = result;
            nextdom.user.save({
                users: [user],
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    printUsers();
                    notify("Info", '{{Sauvegarde effectuée}}', 'success');
                    modifyWithoutSave = false;
                }
            });
        }
    });
});

 $("#table_user").on( 'click',".bt_changeHash", function (event) {
    $.hideAlert();
    var user = {id: $(this).closest('tr').find('.userAttr[data-l1key=id]').value()};
    bootbox.confirm("{{Etes-vous sûr de vouloir changer la clef API de l\'utilisateur ?}}", function (result) {
        if (result) {
            user.hash = '';
            nextdom.user.save({
                users: [user],
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    printUsers();
                    notify("Info", '{{Modification effectuée}}', 'success');
                    modifyWithoutSave = false;
                }
            });
        }
    });
});

 $('#users').on('change','.userAttr',  function () {
    modifyWithoutSave = true;
});

 $('#users').on('change','.configKey',  function () {
    modifyWithoutSave = true;
});

 $('#bt_supportAccess').on('click',function(){
    nextdom.user.supportAccess({
        enable : $(this).attr('data-enable'),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            window.location.reload();
        }
    });
});

 function printUsers() {
    showLoadingCustom();
    var currentUser ="";
    nextdom.user.get({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            currentUser=data.login;
        }
    });
    nextdom.user.all({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $('#table_user tbody').empty();
            var tr = [];
            for (var i in data) {
                var disable = '';
                if(data[i].login == 'internal_report' || data[i].login == 'nextdom_support'){
                    disable = 'disabled';
                }
                var ligne = '<tr><td class="login">';
                ligne += '<span class="userAttr" data-l1key="id" style="display : none;"/>';
                ligne += '<span class="userAttr" data-l1key="login" />';
                ligne += '</td>';
                ligne += '<td>';
                ligne += '<label><input type="checkbox" class="userAttr" data-l1key="enable" '+disable+' />{{Actif}}</label><i class="spacing-right"></i>';
                ligne += '<label><input type="checkbox" class="userAttr" data-l1key="options" data-l2key="localOnly" '+disable+' />{{Local}}</label>';
                ligne += '</td>';
                ligne += '<td style="width:175px;">';
                ligne += '<select class="userAttr form-control input-sm" data-l1key="profils" '+disable+'>';
                ligne += '<option value="admin">{{Administrateur}}</option>';
                ligne += '<option value="user">{{Utilisateur}}</option>';
                ligne += '<option value="restrict">{{Utilisateur limité}}</option>';
                ligne += '</select>';
                ligne += '</td>';
                ligne += '<td style="width:320px">';
                ligne += '<input class="userAttr form-control input-sm" data-l1key="hash" disabled />';
                ligne += '</td>';
                ligne += '<td>';
                if(isset(data[i].options) && isset(data[i].options.twoFactorAuthentification) && data[i].options.twoFactorAuthentification == 1 && isset(data[i].options.twoFactorAuthentificationSecret) && data[i].options.twoFactorAuthentificationSecret != ''){
                    ligne += '<span class="label label-success label-sticker btn-action-bar">{{OK}}</span>';
                    ligne += ' <a class="btn btn-sm btn-danger bt_disableTwoFactorAuthentification pull-right btn-action-bar"><i class="fas fa-ban"></i>{{Désactiver}}</span>';
                    if (isset(data[i].login) && data[i].login == currentUser){
                        ligne += ' <a class="btn btn-sm btn-warning pull-right btn-action-bar" href="index.php?v=d&p=profils#securitytab"><i class="fas fa-cog"></i>{{Configurer}}</span>';
                    }else{
                        ligne += ' <a class="btn btn-sm btn-warning pull-right btn-action-bar" href="index.php?v=d&logout=1" class="noOnePageLoad"><i class="fas fa-lock"></i>{{Se déconnecter}}</span>';
                    }
                }else{
                   ligne += '<span class="label label-danger label-sticker btn-action-bar">{{NOK}}</span>';
               }
               ligne += '</td>';
               ligne += '<td>';
               ligne += '<span class="userAttr label label-config" data-l1key="options" data-l2key="lastConnection"></span>';
               ligne += '</td>';
               ligne += '<td>';
               if(disable == ''){
                   ligne += '<a class="cursor bt_changeHash btn btn-sm btn-warning pull-right btn-action-bar" title="{{Renouveler la clef API}}"><i class="fas fa-refresh"></i>{{Regénération API}}</a>';
                   if (ldapEnable != '1') {
                    ligne += '<a class="btn btn-sm btn-danger pull-right bt_del_user btn-action-bar" style="margin-bottom : 5px;"><i class="fas fa-trash"></i>{{Supprimer}}</a>';
                    ligne += '<a class="btn btn-sm btn-warning pull-right bt_change_mdp_user btn-action-bar"><i class="fas fa-lock"></i>{{Mot de passe}}</a>';
                }
                ligne += '<a class="btn btn-sm btn-warning pull-right bt_manage_restrict_rights btn-action-bar"><i class="fas fa-align-right"></i>{{Droits}}</a>';
            }
            ligne += '</td>';
            ligne += '</tr>';
            var result = $(ligne);
            result.setValues(data[i], '.userAttr');
            tr.push(result);
        }
        $('#table_user tbody').append(tr);
        modifyWithoutSave = false;
        hideLoadingCustom();
    }
});
}

$('#table_user').on( 'click','.bt_manage_restrict_rights', function () {
    $('#md_modal').dialog({title: "Gestion des droits"});
    $("#md_modal").load('index.php?v=d&modal=user.rights&id=' + $(this).closest('tr').find('.userAttr[data-l1key=id]').value()).dialog('open');
});


$('#table_user').on( 'click', '.bt_disableTwoFactorAuthentification',function () {
    nextdom.user.removeTwoFactorCode({
        id :  $(this).closest('tr').find('.userAttr[data-l1key=id]').value(),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            printUsers();
        }
    });

});

$('.bt_deleteSession').on('click',function(){
 var id = $(this).closest('tr').attr('data-id');
 nextdom.user.deleteSession({
    id : id,
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        window.location.reload();
    }
});
});


$('.bt_removeRegisterDevice').on('click',function(){
    var key = $(this).closest('tr').attr('data-key');
    var user_id = $(this).closest('tr').attr('data-user_id');
    nextdom.user.removeRegisterDevice({
        key : key,
        user_id : user_id,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            window.location.reload();
        }
    });
});

$('#bt_removeAllRegisterDevice').on('click',function(){
    nextdom.user.removeRegisterDevice({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            window.location.reload();
        }
    });
});
