jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savereports_admin").click();
});

jwerty.key('esc', function (e) {
    e.preventDefault();
    $("#back").click();
});

 $("#bt_savereports_admin").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#reports_admin').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#reports_admin').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#reports_admin').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#reports_admin').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#reports_admin').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#reports_admin').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});
