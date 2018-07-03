
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

 $('#pre_backupInfo').height($(window).height() - $('header').height() - $('footer').height() - 150);

$("#bt_migrateNextDom").on('click', function (event) {
   var el = $(this);
   bootbox.confirm('{{Etes-vous sûr de vouloir migrer}} '+NEXTDOM_PRODUCT_NAME+' {{avec}} <b>' + $('#sel_restoreBackup option:selected').text() + '</b> ? {{Une fois lancée cette opération ne peut être annulée}}', function (result) {
       if (result) {
           el.find('.fa-refresh').show();
           nextdom.backup.migrate({
               backup: $('#sel_restoreBackup').value(),
               error: function (error) {
                   $('#div_alert').showAlert({message: error.message, level: 'danger'});
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
                    $('#div_alert').showAlert({message: error.message, level: 'danger'});
                },
                success: function () {
                    updateListBackup();
                    $('#div_alert').showAlert({message: '{{Sauvegarde supprimée avec succès}}', level: 'success'});
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
            $('#div_alert').showAlert({message: data.result.result, level: 'danger'});
            return;
        }
        updateListBackup();
        $('#div_alert').showAlert({message: '{{Fichier(s) ajouté(s) avec succès}}', level: 'success'});
    }
});


 $.showLoading();
 nextdom.config.load({
    configuration: $('#backup').getValues('.configKey')[0],
    error: function (error) {
        $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
        $('#backup').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});
 updateListBackup();

 $('#div_pageContainer').delegate('.configKey', 'change', function () {
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
                        $('#div_alert').showAlert({message: '{{L\'opération est réussie}}', level: 'success'});
                        if(_log == 'restore'){
                            nextdom.user.refresh();
                        }
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('[END ' + _log.toUpperCase() + ' ERROR]') != -1){
                        $('#div_alert').showAlert({message: '{{L\'opération a échoué}}', level: 'danger'});
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
            }
        }
    });
}

function updateListBackup() {
    nextdom.backup.list({
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
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
