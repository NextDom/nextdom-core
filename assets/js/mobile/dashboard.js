/**
 * User hidden eqLogics
 * @type [int]
 */
var hiddenEqLogics = [];

/**
 * Start the loop that will trigger events received from the core
 */
function startEventLoop() {
    var eventLoopParams = {
        global: false,
        success: function (data) {
            if (nextdom.connect > 0) {
                nextdom.connect = 0;
            }
            nextdom.datetime = data.datetime;
            var cmdUpdate = [];
            var eqLogicUpdate = [];
            // Sort events
            for (var dataIndex = 0; dataIndex < data.result.length; ++dataIndex) {
                if (data.result[dataIndex].name === 'cmd::update') {
                    cmdUpdate.push(data.result[dataIndex].option);
                    continue;
                }
                if (data.result[dataIndex].name === 'eqLogic::update') {
                    eqLogicUpdate.push(data.result[dataIndex].option);
                    continue;
                }
            }
            // Trigger events
            if (cmdUpdate.length > 0) {
                $('body').trigger('cmd::update', [cmdUpdate]);
            }
            if (eqLogicUpdate.length > 0) {
                $('body').trigger('eqLogic::update', [eqLogicUpdate]);
            }

            // Loop again
            setTimeout(startEventLoop, 1);
        },
        error: function (_error) {
            if (typeof(user_id) !== 'undefined' && nextdom.connect === 100) {
                notify('{{Erreur de connexion}}', '{{Erreur lors de la connexion à NextDom}} : ' + _error.message);
                window.location.reload();
            }
            nextdom.connect++;
            setTimeout(nextdom.changes, 1);
        }
    };

    // Start first event loop
    var rawAjaxParams = $.extend({}, nextdom.private.default_params, eventLoopParams);
    var preparedAjaxParams = nextdom.private.getParamsAJAX(rawAjaxParams);
    preparedAjaxParams.url = 'core/ajax/event.ajax.php';
    preparedAjaxParams.data = {
        action: 'changes',
        datetime: nextdom.datetime,
    };
    $.ajax(preparedAjaxParams);
}

/**
 * Get eqLogic widget jquery
 *
 * @param eqLogicId EqLogic Id
 *
 * @returns jQuery eqLogic element
 */
function getEqLogicWidget(eqLogicId) {
    return $('.eqLogic[data-eqlogic_id="' + eqLogicId + '"]');
}

/**
 * Hide an eqLogic
 *
 * @param eqLogicId eqLogic id to hide
 */
function hideEqLogic(eqLogicId) {
    var eqLogicWidget = getEqLogicWidget(eqLogicId);
    var jeeObjectContainer = eqLogicWidget.parent();
    storeNewHiddenEqLogic(eqLogicId);
    eqLogicWidget.slideUp(200, function () {
        eqLogicWidget.remove();
        if (jeeObjectContainer.children().length === 0) {
            jeeObjectContainer.parent().remove();
        }
    });
}

/**
 *
 * @param container Container to apply
 */
function initEqLogicsEvents(container) {
    container.find('.hide-eqLogic').on('click', function () {
        var eqLogicId = $(this).parent().data('eqlogic_id');
        hideEqLogic(eqLogicId);
    });
    // Init slider jquery-ui if present
    var sliders = container.find('.slider');
    if (sliders.length > 0) {
        $.each(sliders, function (index, slider) {
            slider = $(slider);
            var sliderConfig = {
                step: 1
            };
            var inputNumber = slider.find('input[type="number"]');
            if (inputNumber.length > 0) {
                sliderConfig.min = parseInt(inputNumber.attr('min'));
                sliderConfig.max = parseInt(inputNumber.attr('max'));
            }
            slider.slider(sliderConfig);
        });
    }
}

function resetHiddens() {
    localStorage.setItem('hiddenEqLogics', JSON.stringify([]));
    hiddenEqLogics = [];
    location.reload();
}

/**
 * Init global events of the page
 */
function initGlobalEvents() {
    var body = $('body');
    body.on('cmd::update', function (_event, _options) {
        nextdom.cmd.refreshValue(_options);
    });

    body.on('eqLogic::update', function (_event, _options) {
        nextdom.eqLogic.refreshValue(_options);
    });
    $('#reset-hiddens').on('click', resetHiddens);
}

/**
 * Init local storage for user parameters
 */
function initLocalStorage() {
    if (localStorage.getItem('hiddenEqLogics') === null) {
        resetHiddens();
    }
    hiddenEqLogics = JSON.parse(localStorage.getItem('hiddenEqLogics'));
}

/**
 * Add new eqLogic to hide
 *
 * @param eqLogicId Id of the eqLogic
 */
function storeNewHiddenEqLogic(eqLogicId) {
    hiddenEqLogics.push(parseInt(eqLogicId));
    localStorage.setItem('hiddenEqLogics', JSON.stringify(hiddenEqLogics));
}

/**
 * Test if the eqLogic must be hide
 *
 * @param eqLogicId Id of the eqLogic to test
 *
 * @returns {boolean} True if the eqLogic must be hide
 */
function mustBeHidden(eqLogicId) {
    return hiddenEqLogics.indexOf(parseInt(eqLogicId)) !== -1;
}

/**
 * Show jeeObject content
 *
 * @param container JeeObject container
 * @param jeeObjectData Data from the JeeObject
 */
function showJeeObject(container, jeeObjectData) {
    var htmlToShow = '';
    for (var key in jeeObjectData.html) {
        // Remove hidden eqLogics
        if (!mustBeHidden(jeeObjectData.eqLogics[key])) {
            htmlToShow += jeeObjectData.html[key];
        }
    }
    container.html(htmlToShow);
}

/**
 * Entry point of the script
 */
function start() {
    initLocalStorage();
    $.each($('.jeeObject'), function (index, jeeObjectDiv) {
        jeeObjectDiv = $(jeeObjectDiv);
        var jeeObjectId = jeeObjectDiv.data('id');
        nextdom.object.toHtml({
            id: jeeObjectId,
            version: 'mobile',
            noScenario: 1,
            error: function (error) {
                notifyError(error.message);
            },
            success: function (jeeObjectData) {
                var container = jeeObjectDiv.find('.box-body');
                showJeeObject(container, jeeObjectData);
                initEqLogicsEvents(container);
                if (container.is(':empty')) {
                    container.parent().remove();
                }
            }
        });
    });
    initGlobalEvents();
    startEventLoop();
    hideLoading();
}

utid = Date.now();
start();

