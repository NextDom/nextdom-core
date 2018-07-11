function initEqanalyse() {
    nextdom.eqLogic.htmlAlert({
        version : 'mobile',
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (eqLogics) {
            div = '';
            for (var i in eqLogics) {
                div += eqLogics[i].html;
            }
            $('#div_displayAlert').empty().html(div).trigger('create');
            setTileSize('.eqLogic');
            setTimeout(function () {
                $('#div_displayAlert').packery({gutter : 4});
            }, 10);
            nextdom.eqLogic.htmlBattery({
                version : 'mobile',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (eqLogics) {
                    div = '';
                    for (var i in eqLogics) {
                        div += eqLogics[i].html;
                    }
                    $('#div_displayBattery').empty().html(div).trigger('create');
                    $('ul[data-role=nd2tabs]').tabs();
                    setTileSize('.eqLogic');
                    setTimeout(function () {
                        $('#div_displayBattery').packery({gutter : 4});
                    }, 10);
                }
            });
        }
    });

    
}