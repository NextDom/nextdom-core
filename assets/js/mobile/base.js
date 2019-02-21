/**
 * Empty function useless but avoid errors
 */
function setTileSize() {

}

/**
 * Copy of getDeviceType func from core
 */
function getDeviceType() {
    var result = {};
    var body = $('body');
    result.type = 'desktop';
    result.width = body.width();
    if (navigator.userAgent.match(/(android)/gi)) {
        result.width = screen.width;
        result.type = 'phone';
        if (body.width() > 899) {
            result.type = 'tablet';
        }
    }
    if (navigator.userAgent.match(/(phone)/gi)) {
        result.type = 'phone';
    }
    if (navigator.userAgent.match(/(Iphone)/gi)) {
        result.type = 'phone';
    }
    if (navigator.userAgent.match(/(Lumia)/gi)) {
        result.type = 'phone';
    }
    if (navigator.userAgent.match(/(IEMobile)/gi)) {
        result.type = 'phone';
    }
    if (navigator.userAgent.match(/(Ipad)/gi)) {
        result.type = 'tablet';
    }
    result.bSize = 220;
    if (result.type === 'phone') {
        var ori = window.orientation;
        if (ori === 90 || ori === -90) {
            result.bSize = (result.width / 3) - 20;
        } else {
            result.bSize = (result.width / 2) - 6;
        }
    }
    return result;
}

/**
 * Show error message
 * @param msg Message to show
 */
function notifyError(msg) {
    iziToast.show({
        title: 'Erreur',
        theme: 'dark',
        backgroundColor: '#8b0000',
        message: msg
    });
}

/**
 * Show loading div
 */
function showLoading() {
    $('#loading-div').fadeIn();
}

/**
 * Hide loading div
 */
function hideLoading() {
    $('#loading-div').fadeOut();
}

/**
 * Load page
 *
 * @param pageName Name of the page
 */
function loadPageFromAjax(pageName) {
    $('#content').load('index.php?v=m&ajax=1&p=' + pageName, function () {
        hideLoading();
    });
}
