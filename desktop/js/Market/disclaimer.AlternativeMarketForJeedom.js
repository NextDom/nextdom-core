// Point d'entrée du script
$(document).ready(function () {
    // Evènement du bouton fermer
    $('#disclaimer-close').click(function() {
        $('#md_modal').dialog('close');
    });
    // Evènement du bouton supprimer
    $('#delete-plugin').click(function() {
        $.post({
            url: 'core/ajax/update.ajax.php',
            data: {action: 'remove', id: 'AlternativeMarketForJeedom'},
            dataType: 'json',
            success: function (data, status) {
                // Test si l'appel a échoué
                if (data.state !== 'ok' || status !== 'success') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                }
            },
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            }
        });
    });
});

