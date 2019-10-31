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

// Page init
showSelectedTabFromUrl(document.location.toString());
loadInformations();
initEvents();

/**
 * Show the tab indicated in the url
 *
 * @param url Url to check
 */
function showSelectedTabFromUrl(url) {
    if (url.match('#')) {
      $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
    }
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
      window.location.hash = e.target.hash;
    });
}

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    nextdom.user.get({
        error: function (error) {
          notify('Erreur', error.message, 'error');
        },
        success: function (data) {
          $('#div_Profils').setValues(data, '.userAttr');
          $('#in_passwordCheck').value(data.password);
          $('#' + $('.userAttr[data-l2key="widget::theme"]').value()).attr('checked', 'checked');
          $('#avatar-preview').attr('src', $('.userAttr[data-l2key=avatar]').value());
          nextdom.config.load({
              configuration: $('#div_Profils').getValues('.configKey:not(.noSet)')[0],
              error: function (error) {
                  notify("Erreur", error.message, 'error');
              },
              success: function (data) {
                  $('#div_Profils').setValues(data, '.configKey');
                  modifyWithoutSave = false;
                  $(".bt_cancelModifs").hide();
              }
          });
        }
    });

}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Show confirm modal on non saved changes
    $('#div_Profils').delegate('.userAttr', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });
    // Show confirm modal on non saved changes
    $('#div_Profils').delegate('.configKey:not(.noSet)', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadInformations();
    });

    // Theme config changing
    $("#themeBase").on('change', function (event) {
        $('.configKey[data-l1key="nextdom::user-theme"]').value($("#themeBase").value() + "-" + $("#themeIdentity").value());
        $('#customPreview').contents().find("head").append($("<link href='/public/css/themes/" + $('.configKey[data-l1key="nextdom::user-theme"]').value() + ".css' rel='stylesheet'>"));
    });
    $("#themeIdentity").on('change', function (event) {
        $('.configKey[data-l1key="nextdom::user-theme"]').value($("#themeBase").value() + "-" + $("#themeIdentity").value());
        $('#customPreview').contents().find("head").append($("<link href='/public/css/themes/" + $('.configKey[data-l1key="nextdom::user-theme"]').value() + ".css' rel='stylesheet'>"));
    });

    // Save forms data
    $("#bt_saveProfils").on('click', function (event) {
        var profil = $('#div_pageContainer').getValues('.userAttr')[0];
        if (profil.password != $('#in_passwordCheck').value()) {
            notify('Erreur', '{{Les deux mots de passe ne sont pas identiques}}', 'error');
            return false;
        }
        nextdom.user.saveProfils({
            profils: profil,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.user.get({
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        var config = $('#div_Profils').getValues('.configKey')[0];
                        nextdom.config.save({
                            configuration: config,
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            },
                            success: function () {
                                // Change config dynamically
                                widget_size = config['widget::size'];
                                widget_margin = config['widget::margin'];
                                widget_padding = config['widget::padding'];
                                widget_radius = config['widget::radius'];
                                nextdom.config.load({
                                    configuration: $('#div_Profils').getValues('.configKey:not(.noSet)')[0],
                                    error: function (error) {
                                        notify("Erreur", error.message, 'error');
                                    },
                                    success: function (data) {
                                        $('#div_Profils').setValues(data, '.configKey');
                                        modifyWithoutSave = false;
                                        $(".bt_cancelModifs").hide();
                                        updateInformations();
                                        notify("Info", '{{Sauvegarde réussie}}', 'success');
                                        window.location.reload();
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
        return false;
    });

    // Show two factor authentication process
    $('#bt_configureTwoFactorAuthentification').on('click', function () {
        var mdModal = $('#md_modal');
        mdModal.dialog({title: '{{Authentification 2 étapes}}'});
        mdModal.load('index.php?v=d&modal=twoFactor.authentification').dialog('open');
    });

    // Generate new user API key
    $('#bt_genUserKeyAPI').on('click', function () {
        var profil = $('#div_pageContainer').getValues('.userAttr')[0];
        profil.hash = '';
        nextdom.user.saveProfils({
            profils: profil,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                notify('Info', '{{Opération effectuée}}', 'success');
                nextdom.user.get({
                  error: function (error) {
                      notify('Erreur', error.message, 'error');
                  },
                  success: function (data) {
                      $('#div_pageContainer').setValues(data, '.userAttr');
                      modifyWithoutSave = false;
                  }
                });
            }
        });
    });

    // Change notification cmd
    $('#bt_selectWarnMeCmd').on('click', function () {
        nextdom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
            $('.userAttr[data-l1key="options"][data-l2key="notification::cmd"]').value(result.human);
        });
    });

    // Remove register device from the list
    $('.bt_removeRegisterDevice').on('click', function () {
        var deviceKey = $(this).closest('tr').attr('data-key');
        nextdom.user.removeRegisterDevice({
            key: deviceKey,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                modifyWithoutSave = false;
                window.location.reload();
            }
        });
    });

    // Remove all register devices
    $('#bt_removeAllRegisterDevice').on('click', function () {
          nextdom.user.removeRegisterDevice({
            key: '',
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                modifyWithoutSave = false;
                window.location.reload();
            }
        });
    });

    // Delete all sessions
    $('.bt_deleteSession').on('click', function () {
        var id = $(this).closest('tr').attr('data-id');
        nextdom.user.deleteSession({
            id: id,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                modifyWithoutSave = false;
                window.location.reload();
            }
        });
    });

    // Uplod new picture
    $('#user_avatar').fileupload({
        dataType: 'json',
        url: 'core/ajax/profils.ajax.php?action=imageUpload',
        dropZone: '#bsImagesPanel',
        formData: {'nextdom_token': NEXTDOM_AJAX_TOKEN},
        done: function (e, data) {
            if (data.result.state !== 'ok') {
                notify('Core', data.result.result, 'error');
                return;
            }
            if ($('.userAttr[data-l2key=avatar]') == '') {
                $('.userAttr[data-l2key=avatar]').value('/public/img/profils/avatar_00.png');
            } else {
                $('.userAttr[data-l2key=avatar]').value('/public/img/profils/' + data.files[0]['name']);
                $('#avatar-preview').attr('src', '/public/img/profils/' + data.files[0]['name']);
                notify('{{Ajout d\'une Image}}', '{{Image ajoutée avec succès}}', 'success');
            }
        }
    });

    // Change avatar picture
    $(".avatar").on('click', function (event) {
        var newPicture = $(this).attr('src');
        $('.userAttr[data-l2key=avatar]').value(newPicture);
        $('#avatar-preview').attr('src', newPicture);
        modifyWithoutSave = true;
    });

    // Change widget theme
    $('input[name=themeWidget]').on('click', function (event) {
        var radio = $(this).val();
        $('.userAttr[data-l2key="widget::theme"]').value(radio);
        modifyWithoutSave = true;
    });
}

/**
 * Update visible data
 */
function updateInformations() {
    // Home link
    var homeTarget = $('select[data-l2key=homePage]').val().substr(6);
    $('header a.logo').attr('href', 'index.php?v=d&p=' + homeTarget);

    // Avatar picture
    var newAvatarPicture = $('#avatar-preview').attr('src');
    $('#avatar-img').attr('src', newAvatarPicture);
}
