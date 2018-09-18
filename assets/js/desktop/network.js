jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savenetwork").click();
});

 $("#bt_savenetwork").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#network').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#network').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#network').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#network').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#network').setValues(data, '.configKey');
        $('.configKey[data-l1key="market::allowDNS"]').trigger('change');
        $('.configKey[data-l1key="ldap:enable"]').trigger('change');
        modifyWithoutSave = false;
    }
});

$('#div_pageContainer').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});