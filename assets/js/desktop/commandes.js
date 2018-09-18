jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savecommandes").click();
});

 $("#bt_savecommandes").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#commandes').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#commandes').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#commandes').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#commandes').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#commandes').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#div_pageContainer').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});