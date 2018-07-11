function initScenario() {
    nextdom.scenario.toHtml({
        id: 'all',
        version: 'mobile',
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (htmls) {
            $('#div_displayScenario').empty().html(htmls).trigger('create');
            setTileSize('.scenario');
            $('#div_displayScenario').packery({gutter : 4});
        }
    });
    $(window).on("orientationchange", function (event) {
        setTileSize('.scenario');
        $('#div_displayScenario').packery({gutter : 4});
    });
}