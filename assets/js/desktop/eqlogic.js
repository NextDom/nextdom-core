jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_saveeqlogic").click();
});

 $("#bt_saveeqlogic").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#eqlogic').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#eqlogic').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#eqlogic').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#eqlogic').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#eqlogic').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#eqlogic').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});
