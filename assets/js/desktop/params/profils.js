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

var url = document.location.toString();
if (url.match('#')) {
    $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
}
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash;
})

jwerty.key('ctrl+s/⌘+s', function (e) {
    e.preventDefault();
    $("#bt_saveProfils").click();
});

$('#bt_configureTwoFactorAuthentification').on('click',function(){
    var profil = $('#div_pageContainer').getValues('.userAttr')[0];
    $('#md_modal').dialog({title: "{{Authentification 2 étapes}}"});
    $("#md_modal").load('index.php?v=d&modal=twoFactor.authentification').dialog('open');
});

$("#bt_saveProfils").on('click', function (event) {
    $.hideAlert();
    var profil = $('#div_pageContainer').getValues('.userAttr')[0];
    if (profil.password != $('#in_passwordCheck').value()) {
        notify("Erreur", "{{Les deux mots de passe ne sont pas identiques}}", 'error');
        return;
    }
    nextdom.user.saveProfils({
        profils: profil,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            notify("Info", "{{Sauvegarde effectuée}}", 'success');
            nextdom.user.get({
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    modifyWithoutSave = false;
                    updateTheme(function() {
                        window.location.reload();
                    });
                }
            });
        }
    });
    return false;
});

$('#bt_genUserKeyAPI').on('click',function(){
    var profil = $('#div_pageContainer').getValues('.userAttr')[0];
    profil.hash = '';
    nextdom.user.saveProfils({
        profils: profil,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            notify("Info", "{{Opération effectuée}}", 'success');
            nextdom.user.get({
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#div_pageContainer').setValues(data, '.userAttr');
                    modifyWithoutSave = false;
                }
            });
        }
    });
});

nextdom.user.get({
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#div_pageContainer').setValues(data, '.userAttr');
        $('#in_passwordCheck').value(data.password);
        modifyWithoutSave = false;
    }
});

$('#div_pageContainer').delegate('.userAttr', 'change', function () {
    modifyWithoutSave = true;
});

$('.bt_selectWarnMeCmd').on('click', function () {
    nextdom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
        $('.userAttr[data-l1key="options"][data-l2key="notification::cmd"]').value(result.human);
    });
});

$('.bt_removeRegisterDevice').on('click',function(){
    var key = $(this).closest('tr').attr('data-key');
    nextdom.user.removeRegisterDevice({
        key : key,
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
        key : '',
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            window.location.reload();
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

$('#user_avatar').fileupload({
    dataType: 'json',
    url: "core/ajax/profils.ajax.php?action=imageUpload&ajax_token=" + NEXTDOM_AJAX_TOKEN,
    dropZone: "#bsImagesPanel",
    done: function (e, data) {
        if (data.result.state !== 'ok') {
            notify('Core',data.result.result,'error');
            return;
        }
        if ($('.userAttr[data-l2key=avatar]') == '') {
            $('.userAttr[data-l2key=avatar]').value('/public/img/profils/avatar_00.png');
        }else{
            $('.userAttr[data-l2key=avatar]').value('/public/img/profils/' + data.files[0]['name']);
            $('#monAvatar').attr('src','/public/img/profils/' + data.files[0]['name']);
            notify("{{Ajout d'une Image}}", '{{Image ajoutée avec succès}}', 'success');
        }
    }
});

$(".Avatar").on('click', function (event) {
    $('.userAttr[data-l2key=avatar]').value($(this).attr('src'));
    $('#monAvatar').attr('src',$(this).attr('src'));
    notify("{{Profil}}", '{{Image changée}}', 'success');
});

$(".themeWidget").on('click', function (event) {
    var widgetName = $(this).attr('src').split("/").pop(-1).split(".");
    $('.userAttr[data-l2key="widget::theme"]').value(widgetName[0]);
    $('#monThemeWidget').attr('src',$(this).attr('src'));
    notify("{{Profil}}", '{{thème changé}}', 'success');
});
