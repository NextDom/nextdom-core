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

/**
 * Save migration configuration
 */
function saveMigrationConfiguration() {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#migration').getValues('.configKey')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#migration').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify('Erreur', error.message, 'error');
                },
                success: function (data) {
                    $('#migration').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify('Info', '{{Sauvegarde réussie}}', 'success');
                    window.location.reload();
                }
            });
        }
    });
}

/**
 * Start migration
 */
function startMigration() {
    var migrationButton = $('#bt_migrationNextDom');
    var migrationConfirmation = '{{Etes-vous sûr de vouloir migrer}} ' + NEXTDOM_PRODUCT_NAME + ' {{avec}} <b>' + $('#sel_restoreBackupforMigration option:selected').text() + '</b> ? {{Une fois lancée, cette opération ne peut être annulée}}';
    bootbox.confirm(migrationConfirmation, function (result) {
        if (result) {
            switchNotify(0);
            migrationButton.find('.fa-refresh').show();
            migrationButton.find('.fa-play').hide();
            $('#md_migrationInfo').dialog({title: '{{Avancement de la migration}}'});
            $('#md_migrationInfo').dialog('open');
            nextdom.backup.migrate({
                backup: $('#sel_restoreBackupforMigration').value(),
                error: function (error) {
                    switchNotify(1);
                    notify('Erreur', error.message, 'error');
                },
                success: function () {
                    getNextDomLog(1, 'migration');
                }
            });
        }
    });
}

/**
 * Get and update log in modal
 * @param autoUpdate
 * @param logFile
 */
function getNextDomLog(autoUpdate, logFile) {
    $.ajax({
        type: 'POST',
        url: 'core/ajax/log.ajax.php',
        data: {
            action: 'get',
            log: logFile,
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            setTimeout(function () {
                getNextDomLog(autoUpdate, logFile)
            }, 1000);
        },
        success: function (data) {
            if (data.state !== 'ok') {
                setTimeout(function () {
                    getNextDomLog(autoUpdate, logFile)
                }, 1000);
                return;
            }
            var log = '';
            if ($.isArray(data.result)) {
                var processFinish = false;
                var processSuccess = false;
                var finishMsg = '';
                for (var rowIndex in data.result.reverse()) {
                    log += data.result[rowIndex] + '\n';
                    if (data.result[rowIndex].indexOf('[END ' + logFile.toUpperCase() + ' SUCCESS]') !== -1) {
                        finishMsg = '{{L\'opération est réussie}}';
                        processSuccess = true;
                        processFinish = true;
                    }
                    else if (data.result[rowIndex].indexOf('[END ' + logFile.toUpperCase() + ' ERROR]') !== -1) {
                        finishMsg = '{{L\'opération a échoué}}';
                        processFinish = true;
                    }
                    if (processFinish) {
                        switchNotify(1);
                        if (processSuccess) {
                            notify('Info', finishMsg, 'success');
                        }
                        else {
                            notify('Erreur', finishMsg, 'error');
                        }
                        if (logFile === 'migration') {
                            nextdom.user.refresh();
                        }
                        autoUpdate = 0;
                    }
                }
            }
            $('#pre_migrationInfo').text(log);
            if (init(autoUpdate, 0) == 1) {
                setTimeout(function () {
                    getNextDomLog(autoUpdate, logFile)
                }, 1000);
            } else {
                $('#bt_' + logFile + 'NextDom .fa-refresh').hide();
                $('.bt_' + logFile + 'NextDom .fa-refresh').hide();
                $('#bt_' + logFile + 'NextDom .fa-play').show();
                $('.bt_' + logFile + 'NextDom .fa-play').show();
                $('#bt_' + logFile + 'NextDom .fa-cloud-upload-alt').show();
                $('.bt_' + logFile + 'NextDom .fa-cloud-upload-alt').show();
                updateBackupsList();
            }
        }
    });
}

/**
 * Get backup list and update select form
 */
function updateBackupsList() {
    nextdom.backup.list({
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (backupsList) {
            // Update select in form
            var options = '';
            for (var backupIndex in backupsList) {
                options += '<option value="' + backupIndex + '">' + backupsList[backupIndex] + '</option>';
            }
            $('#sel_restoreBackupforMigration').html(options);
            // Enable migration button event
            if (options !== '') {
                var startMigrationButton = $('#bt_migrationNextDom');
                startMigrationButton.on('click', startMigration);
                startMigrationButton.removeAttr('disabled');
            }
        }
    });
}

/**
 * Initialise all items in the migration page
 */
function initMigrationPageItems() {
    jwerty.key('ctrl+s', function (e) {
        e.preventDefault();
        saveMigrationConfiguration();
    });

    $('#md_migrationInfo').dialog({
        closeText: '',
        autoOpen: false,
        modal: true,
        width: (($(window).width() - 50) < 1500) ? ($(window).width() - 50) : 1500,
        open: function () {
            $('body').css({overflow: 'hidden'});
        },
        beforeClose: function (event, ui) {
            $('body').css({overflow: 'inherit'});
        }
    });

    $('#pre_migrationInfo').height($(window).height() - $('header').height() - $('footer').height() - 150);

    $.showLoading();

    nextdom.config.load({
        configuration: $('#migration').getValues('.configKey')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#migration').setValues(data, '.configKey');
            modifyWithoutSave = false;
        }
    });
    updateBackupsList();

    $('#migration').delegate('.configKey', 'change', function () {
        modifyWithoutSave = true;
    });

}

/**
 * Init all events in the migration page
 */
function initMigrationPageEvents() {
    $('#bt_saveMigration').on('click', saveMigrationConfiguration);

    $('#bt_migrateOpenLog').on('click', function (event) {
        $('#md_migrationInfo').dialog({title: '{{Avancement de la migration}}'});
        $('#md_migrationInfo').dialog('open');
    });

    $('#bt_uploadBackupforMigration').fileupload({
        dataType: 'json',
        replaceFileInput: false,
        done: function (e, data) {
            if (data.result.state != 'ok') {
                notify('Erreur', data.result.result, 'error');
                return;
            }
            updateBackupsList();
            notify('Info', '{{Fichier(s) ajouté(s) avec succès}}', 'success');
        }
    });

}

// Entry point
initMigrationPageItems();
initMigrationPageEvents();
