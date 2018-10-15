jwerty.key('esc', function (e) {
    e.preventDefault();
    $("#back").click();
});

nextdom.config.load({
    configuration: $('#osdb').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#osdb').setValues(data, '.configKey');
    }
});
