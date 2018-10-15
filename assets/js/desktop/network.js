jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savenetwork").click();
});

jwerty.key('esc', function (e) {
    e.preventDefault();
    $("#back").click();
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

$('#bt_restartDns').on('click', function () {
    $.hideAlert();
    jeedom.config.save({
        configuration: $('#config').getValues('.configKey')[0],
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function () {
            jeedom.network.restartDns({
                error: function (error) {
                    $('#div_alert').showAlert({message: error.message, level: 'danger'});
                },
                success: function (data) {
                    modifyWithoutSave = false;
                    location.reload();
                }
            });
        }
    });
});


$('#bt_haltDns').on('click', function () {
    $.hideAlert();
    jeedom.config.save({
        configuration: $('#config').getValues('.configKey')[0],
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function () {
            jeedom.network.stopDns({
                error: function (error) {
                    $('#div_alert').showAlert({message: error.message, level: 'danger'});
                },
                success: function (data) {
                    modifyWithoutSave = false;
                    location.reload();
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

$('#network').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('#network').delegate('.configKey[data-l1key="market::allowDNS"],.configKey[data-l1key="network::disableMangement"]', 'change', function () {
    if($('.configKey[data-l1key="market::allowDNS"]').value() == 1 && $('.configKey[data-l1key="network::disableMangement"]').value() == 0){
       $('.configKey[data-l1key=externalProtocol]').attr('disabled',true);
       $('.configKey[data-l1key=externalAddr]').attr('disabled',true);
       $('.configKey[data-l1key=externalPort]').attr('disabled',true);
       $('.configKey[data-l1key=externalAddr]').value('');
       $('.configKey[data-l1key=externalPort]').value('');
   }else{
    $('.configKey[data-l1key=externalProtocol]').attr('disabled',false);
    $('.configKey[data-l1key=externalAddr]').attr('disabled',false);
    $('.configKey[data-l1key=externalPort]').attr('disabled',false);
}
});
