jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_savesecurity").click();
});

 $("#bt_savesecurity").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#security').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#security').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#security').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#security').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#security').setValues(data, '.configKey');
        modifyWithoutSave = false;
    }
});

$('#security').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('#security').delegate('.configKey[data-l1key="ldap:enable"]', 'change', function () {
    if($(this).value() == 1){
        $('#div_config_ldap').show();
    }else{
        $('#div_config_ldap').hide();
    }
});

$('#bt_removeBanIp').on('click',function(){
    nextdom.user.removeBanIp({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            window.location.reload();
        }
    });
});

$("#bt_testLdapConnection").on('click', function (event) {
    nextdom.config.save({
        configuration: $('#config').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            modifyWithoutSave = false;
            $.ajax({
                type: 'POST',
                url: 'core/ajax/user.ajax.php',
                data: {
                    action: 'testLdapConnection',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        notify("Erreur", '{{Connexion échouée :}} ' + data.result, 'error');
                        return;
                    }
                    notify("Info", '{{Connexion réussie}}', 'success');
                }
            });
        }
    });

    return false;
});
