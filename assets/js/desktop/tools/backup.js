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
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    nextdom.config.load({
        configuration: $('#backup').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#backup').setValues(data, '.configKey');
            modifyWithoutSave = false;
            updateListBackup();
            for(var i in REPO_LIST){
                updateRepoListBackup(REPO_LIST[i]);
            }
            $(".bt_cancelModifs").hide();
        }
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#backup').delegate('.configKey', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadInformations();
    });

    // Save button
    $("#bt_saveBackup").on('click', function (event) {
        nextdom.config.save({
            configuration: $('#backup').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#backup').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#backup').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // Backup modale init
    $("#md_backupInfo").dialog({
        closeText: '',
        autoOpen: false,
        modal: true,
        height: jQuery(window).height() - 100,
        width: getModalWidth(),
        open: function () {
            $("body").css({overflow: 'hidden'});
            $(this).dialog("option", "position", {my: "center", at: "center", of: window});
            $('#pre_backupInfo').css('height', $('#md_backupInfo').height());
        },
        beforeClose: function (event, ui) {
            $("body").css({overflow: 'inherit'});
        }
    });

    // Open log button
    $("#bt_saveOpenLog").on('click', function (event) {
        $('#md_backupInfo').dialog({title: "{{Avancement de la sauvegarde}}"});
        $("#md_backupInfo").dialog('open');
    });

    // Backup button
    $("#bt_backupNextDom").on('click', function (event) {
        var el = $(this);
        bootbox.confirm('{{Etes-vous sûr de vouloir faire une sauvegarde de votre NextDom ?</br>Une fois lancée cette opération ne peut pas être annulée...}}', function (result) {
            if (result) {
                nextdom.backup.backup({
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function () {
                      $('#bt_backupNextDom').addClass('disabled');
                      el.find('.fa-refresh').show();
                      el.find('.fa-floppy-o').hide();
                      $('#md_backupInfo').dialog({title: "{{Avancement de la sauvegarde}}"});
                      $("#md_backupInfo").dialog('open');
                      getNextDomLog(1, 'backup');
                    }
                });
            }
        });
    });

    // Restore button
    $("#bt_restoreNextDom").on('click', function (event) {
        var el = $(this);
        bootbox.confirm('{{Etes-vous sûr de vouloir restaurer NextDom avec la sauvegarde}} <b>' + $('#sel_restoreBackup option:selected').text() + '</b> ?</br>{{Une fois lancée cette opération ne peut pas être annulée...}}</br><span style="color:red;font-weight: bold;">{{IMPORTANT la restauration d\'un backup est une opération risquée et n\'est à utiliser qu\'en dernier recours.}}</span>', function (result) {
            if (result) {
                nextdom.backup.restoreLocal({
                    backup: $('#sel_restoreBackup').value(),
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function () {
                      switchNotify(0);
                      $('#bt_restoreNextDom').addClass('disabled');
                      $('#bt_restoreRepoNextDom').addClass('disabled');
                      el.find('.fa-refresh').show();
                      el.find('.fa-window-restore').hide();
                      $('#md_backupInfo').dialog({title: "{{Avancement de la restauration}}"});
                      $("#md_backupInfo").dialog('open');
                      getNextDomLog(1, 'restore');
                    }
                });
            }
        });
    });

    // Remove button
    $("#bt_removeBackup").on('click', function (event) {
        var el = $(this);
        bootbox.confirm('{{Etes-vous sûr de vouloir supprimer la sauvegarde}} <b>' + $('#sel_restoreBackup option:selected').text() + '</b> ?', function (result) {
            if (result) {
                nextdom.backup.remove({
                    backup: $('#sel_restoreBackup').value(),
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function () {
                        updateListBackup();
                        notify('Info', '{{Sauvegarde supprimée avec succès}}', 'success');
                    }
                });
            }
        });
    });

    // Download button
    $('#bt_downloadBackup').on('click', function () {
        if ($('#sel_restoreBackup option:selected').text() != '') {
            window.open('src/Api/downloadFile.php?pathfile=backup/' + $('#sel_restoreBackup option:selected').text(), "_blank", null);
        }
    });

    // Upload button
    $('#bt_uploadBackup').fileupload({
        dataType: 'json',
        replaceFileInput: false,
        start: function (e, data) {
          $('#bt_uploadBackup').parent().addClass('disabled');
          $('#bt_uploadBackup').parent().find('.fa-refresh').show();
          $('#bt_uploadBackup').parent().find('.fa-cloud-upload-alt').hide();
        },
        done: function (e, data) {
            if (data.result.state != 'ok') {
                notify('Erreur', data.result.result, 'error');
                return;
            }
            updateListBackup();
            notify('Info', '{{Fichier(s) ajouté(s) avec succès}}', 'success');
        },
        always: function (e, data) {
          $('#bt_uploadBackup').parent().removeClass('disabled');
          $('#bt_uploadBackup').parent().find('.fa-refresh').hide();
          $('#bt_uploadBackup').parent().find('.fa-cloud-upload-alt').show();
        },
    });

    // Samba restore button
    $("#bt_restoreRepoNextDom").on('click', function (event) {
        var el = $(this);
        bootbox.confirm('{{Etes-vous sûr de vouloir restaurer NextDom avec la sauvegarde Cloud}} <b>' + $('#sel_restoreRepoNextDom option:selected').text() + '</b> ?</br>{{Une fois lancée cette opération ne peut pas être annulée...}}</br><span style="color:red;font-weight: bold;">{{IMPORTANT la restauration d\'un backup est une opération risquée et n\'est à utiliser qu\'en dernier recours.}}</span>', function (result) {
            if (result) {
                nextdom.backup.restoreCloud({
                    backup: $('#sel_restoreRepoNextDom').value(),
                    repo: el.attr('data-repo'),
                    error: function (error) {
                      notify('Erreur', error.message, 'error');
                    },
                    success: function () {
                      switchNotify(0);
                      $('#bt_restoreNextDom').addClass('disabled');
                      $('#bt_restoreRepoNextDom').addClass('disabled');
                      el.find('.fa-refresh').show();
                      el.find('.fa-window-restore').hide();
                      $('#md_backupInfo').dialog({title: "{{Avancement de la restauration}}"});
                      $("#md_backupInfo").dialog('open');
                      getNextDomLog(1, 'restore');
                    }
                });
            }
        });
    });
}

function getNextDomLog(_autoUpdate, _log) {
    $.ajax({
        type: 'POST',
        url: 'src/ajax.php',
        data: {
            target: 'Log',
            action: 'get',
            log: _log,
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            setTimeout(function () {
                getNextDomLog(_autoUpdate, _log)
            }, 1000);
        },
        success: function (data) {
            if (data.state != 'ok') {
                setTimeout(function () {
                    getNextDomLog(_autoUpdate, _log)
                }, 1000);
                return;
            }
            var log = '';
            if($.isArray(data.result)){
                for (var i in data.result.reverse()) {
                    log += data.result[i]+"\n";
                    if(data.result[i].indexOf('Closing with success') != -1){
                        switchNotify(1);
                        nextdom.user.refresh();
                        notify('Info', '{{L\'opération est réussie}}', 'success');
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('Closing with error') != -1){
                        switchNotify(1);
                        nextdom.user.refresh();
                        notify('Erreur', '{{L\'opération a échoué}}', 'error');
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('Fatal error') != -1){
                        switchNotify(1);
                        nextdom.user.refresh();
                        notify('Erreur', '{{L\'opération a échoué}}', 'error');
                        _autoUpdate = 0;
                    }
                }
            }
            $('#pre_backupInfo').text(log);
            if (init(_autoUpdate, 0) == 1) {
                setTimeout(function () {
                    getNextDomLog(_autoUpdate, _log)
                }, 500);
            } else {
                $('#bt_' + _log + 'NextDom').removeClass('disabled');
                $('#bt_' + _log + 'RepoNextDom').removeClass('disabled');
                $('#bt_' + _log + 'NextDom .fa-refresh').hide();
                $('#bt_' + _log + 'RepoNextDom .fa-refresh').hide();
                $('#bt_' + _log + 'NextDom .fa-floppy-o').show();
                $('#bt_' + _log + 'NextDom .fa-window-restore').show();
                $('#bt_' + _log + 'RepoNextDom .fa-window-restore').show();
                $('#bt_' + _log + 'NextDom .fa-cloud-upload-alt').show();
                $('#bt_' + _log + 'NextDom .fa-cloud-dowload-alt').show();
                updateListBackup();
                for(var i in REPO_LIST){
                    updateRepoListBackup(REPO_LIST[i]);
                }
                refreshMessageNumber();
            }
        }
    });
}

function updateListBackup() {
    nextdom.backup.list({
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            var options = '';
            for (var i in data) {
                options += '<option value="' + i + '">' + data[i] + '</option>';
            }
            $('#sel_restoreBackup').html(options);
        }
    });
}

function updateRepoListBackup(_repo) {
    nextdom.repo.backupList({
        repo : _repo,
        global : false,
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            var options = '';
            for (var i in data) {
                options += '<option value="' + data[i] + '">' + data[i] + '</option>';
            }
            $('#sel_restoreRepoNextDom[data-repo='+_repo+']').empty().html(options);
        }
    });
}
