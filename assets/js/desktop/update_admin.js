jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_saveupdate_admin").click();
});

$("#bt_saveupdate_admin").on('click', function (event) {
    $.hideAlert();
    var config = $('#update_admin').getValues('.configKey')[0];
    config.actionOnMessage = json_encode($('#div_actionOnMessage .actionOnMessage').getValues('.expressionAttr'));
    nextdom.config.save({
        configuration: config,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#update_admin').getValues('.configKey:not(.noSet)')[0],
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#update_admin').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#update_admin').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#update_admin').setValues(data, '.configKey');
        
        modifyWithoutSave = false;
    }
});

$('#div_pageContainer').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('.testRepoConnection').on('click',function(){
    var repo = $(this).attr('data-repo');
    nextdom.config.save({
        configuration: $('#update_admin').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#update_admin').getValues('.configKey:not(.noSet)')[0],
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#update_admin').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    nextdom.repo.test({
                        repo: repo,
                        error: function (error) {
                            notify("Erreur", error.message, 'error');
                        },
                        success: function (data) {
                            notify("Info", '{{Test réussi}}', 'success');
                        }
                    });
                }
            });
        }
    });
});

$('#div_pageContainer').delegate('.enableRepository', 'change', function () {
    if($(this).value() == 1){
        $('.repositoryConfiguration'+$(this).attr('data-repo')).show();
    }else{
        $('.repositoryConfiguration'+$(this).attr('data-repo')).hide();
    }
});