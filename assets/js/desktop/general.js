jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savegeneral").click();
});

 $("#bt_savegeneral").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#general').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#general').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#general').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#general').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#general').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#general').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('#bt_forceSyncHour').on('click', function () {
    $.hideAlert();
    nextdom.forceSyncHour({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            notify("Info", '{{Commande réalisée avec succès}}', 'success');
        }
    });
});

$("#bt_clearNextDomLastDate").on('click', function (event) {
    $.hideAlert();
    clearNextDomDate();
});

function clearNextDomDate() {
    $.ajax({
        type: "POST",
        url: "core/ajax/nextdom.ajax.php",
        data: {
            action: "clearDate"
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify("Erreur", data.result, 'error');
                return;
            }
            $('#in_nextdomLastDate').value('');
        }
    });
}

$('#bt_resetHour').on('click',function(){
 $.ajax({
    type: "POST",
    url: "core/ajax/nextdom.ajax.php",
    data: {
        action: "resetHour"
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {
        if (data.state != 'ok') {
            notify("Erreur", data.result, 'error');
            return;
        }
         location.reload();
    }
});
});

$('#bt_resetHwKey').on('click',function(){
 $.ajax({
    type: "POST",
    url: "core/ajax/nextdom.ajax.php",
    data: {
        action: "resetHwKey"
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {
        if (data.state != 'ok') {
            notify("Erreur", data.result, 'error');
            return;
        }
         location.reload();
    }
});
});

$('#bt_resetHardwareType').on('click',function(){
    nextdom.config.save({
        configuration: {hardware_name : ''},
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
                     location.reload();
        }
    });
});
