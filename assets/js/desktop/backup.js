
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
 jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_saveBackup").click();
});

$("#md_backupInfo").dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    height: 600,
    width: 900,
    open: function () {
        $("body").css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
        $("body").css({overflow: 'inherit'});
    }
});

 $('#pre_backupInfo').height($(window).height() - $('header').height() - $('footer').height() - 150);

 $("#bt_saveBackup").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#backup').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#backup').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#backup').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

 $(".bt_backupNextDom").on('click', function (event) {
    var el = $(this);
    bootbox.confirm('{{Etes-vous sûr de vouloir faire une sauvegarde de}} '+NEXTDOM_PRODUCT_NAME+' {{? Une fois lancée cette opération ne peut être annulée}}', function (result) {
        if (result) {
            $.hideAlert();
            el.find('.fa-refresh').show();
            $('#md_backupInfo').dialog({title: "{{Avancement de la sauvegarde}}"});
            $("#md_backupInfo").dialog('open');
            nextdom.backup.backup({
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    getNextDomLog(1, 'backup');
                }
            });
        }
    });
});

 $("#bt_restoreNextDom").on('click', function (event) {
    var el = $(this);
    bootbox.confirm('{{Etes-vous sûr de vouloir restaurer}} '+NEXTDOM_PRODUCT_NAME+' {{avec la sauvegarde}} <b>' + $('#sel_restoreBackup option:selected').text() + '</b> ? {{Une fois lancée cette opération ne peut être annulée.}}<span style="color:red;font-weight: bold;">IMPORTANT la restauration d\'un backup est une opération risquée et n\'est à utiliser qu\'en dernier recours.</span>', function (result) {
        if (result) {
            $.hideAlert();
            el.find('.fa-refresh').show();
            $('#md_backupInfo').dialog({title: "{{Avancement de la restauration}}"});
            $("#md_backupInfo").dialog('open');
            nextdom.backup.restoreLocal({
                backup: $('#sel_restoreBackup').value(),
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    getNextDomLog(1, 'restore');
                }
            });
        }
    });
});

 $("#bt_removeBackup").on('click', function (event) {
    var el = $(this);
    bootbox.confirm('{{Etes-vous sûr de vouloir supprimer la sauvegarde}} <b>' + $('#sel_restoreBackup option:selected').text() + '</b> ?', function (result) {
        if (result) {
            el.find('.fa-refresh').show();
            nextdom.backup.remove({
                backup: $('#sel_restoreBackup').value(),
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    updateListBackup();
                    notify("Info", '{{Sauvegarde supprimée avec succès}}', 'success');
                }
            });
        }
    });
});

 $('#bt_downloadBackup').on('click', function () {
    window.open('core/php/downloadFile.php?pathfile=backup/' + $('#sel_restoreBackup option:selected').text(), "_blank", null);
});

 $('#bt_uploadBackup').fileupload({
    dataType: 'json',
    replaceFileInput: false,
    done: function (e, data) {
        if (data.result.state != 'ok') {
            notify("Erreur", data.result.result, 'error');
            return;
        }
        updateListBackup();
        notify("Info", '{{Fichier(s) ajouté(s) avec succès}}', 'success');
    }
});

 $.showLoading();
 nextdom.config.load({
    configuration: $('#backup').getValues('.configKey')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#backup').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});
 updateListBackup();

 $('#backup').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

 /********************Log************************/

 function getNextDomLog(_autoUpdate, _log) {
    $.ajax({
        type: 'POST',
        url: 'core/ajax/log.ajax.php',
        data: {
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
                    if(data.result[i].indexOf('[END ' + _log.toUpperCase() + ' SUCCESS]') != -1){
                        notify("Info", '{{L\'opération est réussie}}', 'success');
                        if(_log == 'restore'){
                            nextdom.user.refresh();
                        }
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('[END ' + _log.toUpperCase() + ' ERROR]') != -1){
                        notify("Erreur", '{{L\'opération a échoué}}', 'error');
                        if(_log == 'restore'){
                            nextdom.user.refresh();
                        }
                        _autoUpdate = 0;
                    }
                }
            }
            $('#pre_backupInfo').text(log);
            if (init(_autoUpdate, 0) == 1) {
                setTimeout(function () {
                    getNextDomLog(_autoUpdate, _log)
                }, 1000);
            } else {
                $('#bt_' + _log + 'NextDom .fa-refresh').hide();
                $('.bt_' + _log + 'NextDom .fa-refresh').hide();
                updateListBackup();
                for(var i in REPO_LIST){
                    updateRepoListBackup(REPO_LIST[i]);
                }
            }
        }
    });
}

function updateListBackup() {
    nextdom.backup.list({
        error: function (error) {
            notify("Erreur", error.message, 'error');
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

