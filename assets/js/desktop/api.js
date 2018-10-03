jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_saveapi").click();
});

 $("#bt_saveapi").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#API').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#API').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#API').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#API').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#API').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#API').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$(".bt_regenerate_api").on('click', function (event) {
    $.hideAlert();
    var el = $(this);
    bootbox.confirm('{{Etes-vous sûr de vouloir réinitialiser la clef API de }}'+el.attr('data-plugin')+' ?', function (result) {
        if (result) {
           $.ajax({
            type: "POST",
            url: "core/ajax/config.ajax.php",
            data: {
                action: "genApiKey",
                plugin:el.attr('data-plugin'),
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
                el.closest('.input-group').find('.span_apikey').value(data.result);
            }
        });
       }
   });
});
