
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
    $("#bt_saveMigration").click();
});

$("#md_migrationInfo").dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    height: (jQuery(window).height() - 100),
    width: ((jQuery(window).width() - 50) < 1500) ? (jQuery(window).width() - 50) : 1500,
    open: function () {
        $("body").css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
        $("body").css({overflow: 'inherit'});
    }
});

$("#bt_saveMigration").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#migration').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#migration').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#migration').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                    window.location.reload();
                }
            });
        }
    });
});

$('#pre_migrationInfo').height($(window).height() - $('header').height() - $('footer').height() - 150);

$("#bt_migrateNextDom").on('click', function (event) {
    var el = $(this);
    bootbox.confirm('{{Etes-vous sûr de vouloir migrer}} '+NEXTDOM_PRODUCT_NAME+' {{avec}} <b>' + $('#sel_restoreBackupforMigration option:selected').text() + '</b> ? {{Une fois lancée, cette opération ne peut être annulée}}', function (result) {
        if (result) {
            el.find('.fa-refresh').show();
            el.find('.fa-file').hide();
            $('#md_migrationInfo').dialog({title: "{{Avancement de la migration}}"});
            $("#md_migrationInfo").dialog('open');
            nextdom.backup.migrate({
                backup: $('#sel_restoreBackupforMigration').value(),
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {

                    getNextDomLog(1, 'migration');
                }
            });
        }
    });
});

$('#bt_downloadBackupforMigration').on('click', function () {
    window.open('core/php/downloadFile.php?pathfile=backup/' + $('#sel_restoreBackupforMigration option:selected').text(), "_blank", null);
});

$('#bt_uploadBackupforMigration').fileupload({
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
    configuration: $('#migration').getValues('.configKey')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#migration').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});
updateListBackup();

$('#migration').delegate('.configKey', 'change', function () {
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
                        if(_log == 'migration'){
                            nextdom.user.refresh();
                        }
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('[END ' + _log.toUpperCase() + ' ERROR]') != -1){
                        notify("Erreur", '{{L\'opération a échoué}}', 'error');
                        if(_log == 'migration'){
                            nextdom.user.refresh();
                        }
                        _autoUpdate = 0;
                    }
                }
            }
            $('#pre_migrationInfo').text(log);
            if (init(_autoUpdate, 0) == 1) {
                setTimeout(function () {
                    getNextDomLog(_autoUpdate, _log)
                }, 1000);
            } else {
                $('#bt_' + _log + 'NextDom .fa-refresh').hide();
                $('.bt_' + _log + 'NextDom .fa-refresh').hide();
                $('#bt_' + _log + 'NextDom .fa-file').show();
                $('.bt_' + _log + 'NextDom .fa-file').show();
                updateListBackup();
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
            $('#sel_restoreBackupforMigration').html(options);
        }
    });
}
