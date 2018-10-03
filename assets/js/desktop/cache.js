jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savecache").click();
});

 $("#bt_savecache").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#cache').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#cache').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#cache').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#cache').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#cache').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#cache').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('#cache').delegate('.configKey[data-l1key="cache::engine"]', 'change', function () {
 $('.cacheEngine').hide();
 $('.cacheEngine.'+$(this).value()).show();
});

$("#bt_cleanCache").on('click', function (event) {
    $.hideAlert();
    cleanCache();
});

$("#bt_flushCache").on('click', function (event) {
    $.hideAlert();
    flushCache();
});

function flushCache() {
  nextdom.cache.flush({
    error: function (error) {
       notify("Erreur", data.result, 'error');
   },
   success: function (data) {
    updateCacheStats();
    notify("Info", '{{Cache vidé}}', 'success');
}
});
}

function cleanCache() {
    nextdom.cache.clean({
        error: function (error) {
           notify("Erreur", data.result, 'error');
       },
       success: function (data) {
        updateCacheStats();
        notify("Info", '{{Cache nettoyé}}', 'success');
    }
});
}

function updateCacheStats(){
   nextdom.cache.stats({
    error: function (error) {
       notify("Erreur", data.result, 'error');
   },
   success: function (data) {
    $('#span_cacheObject').html(data.count);
}
});
}
