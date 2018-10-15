jwerty.key('ctrl+s', function (e) {
    e.preventDefault();
    $("#bt_saveinteract_admin").click();
});

jwerty.key('esc', function (e) {
    e.preventDefault();
    $("#back").click();
});

 $("#bt_saveinteract_admin").on('click', function (event) {
    $.hideAlert();
    nextdom.config.save({
        configuration: $('#interact_admin').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#interact_admin').getValues('.configKey')[0],
                plugin: 'core',
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#interact_admin').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#interact_admin').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#interact_admin').setValues(data, '.configKey');

        modifyWithoutSave = false;
    }
});

$('#interact_admin').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('#bt_addColorConvert').on('click', function () {
    addConvertColor();
});

/********************Convertion************************/
function printConvertColor() {
    $.ajax({
        type: "POST",
        url: "core/ajax/config.ajax.php",
        data: {
            action: "getKey",
            key: 'convertColor'
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

            $('#table_convertColor tbody').empty();
            for (var color in data.result) {
                addConvertColor(color, data.result[color]);
            }
            modifyWithoutSave = false;
        }
    });
}

function addConvertColor(_color, _html) {
    var tr = '<tr>';
    tr += '<td>';
    tr += '<input class="color form-control input-sm" value="' + init(_color) + '"/>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input type="color" class="html form-control input-sm" value="' + init(_html) + '" />';
    tr += '</td>';
    tr += '</tr>';
    $('#table_convertColor tbody').append(tr);
    modifyWithoutSave = true;
}

function saveConvertColor() {
    var value = {};
    var colors = {};
    $('#table_convertColor tbody tr').each(function () {
        colors[$(this).find('.color').value()] = $(this).find('.html').value();
    });
    value.convertColor = colors;
    $.ajax({
        type: "POST",
        url: "core/ajax/config.ajax.php",
        data: {
            action: 'addKey',
            value: json_encode(value)
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
            modifyWithoutSave = false;
        }
    });
}

/*CMD color*/

$('.bt_resetColor').on('click', function () {
    var el = $(this);
    nextdom.getConfiguration({
        key: $(this).attr('data-l1key'),
        default: 1,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $('.configKey[data-l1key="' + el.attr('data-l1key') + '"]').value(data);
        }
    });
});

$('.bt_selectAlertCmd').on('click', function () {
    var type=$(this).attr('data-type');
    nextdom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
        $('.configKey[data-l1key="alert::'+type+'Cmd"]').atCaret('insert', result.human);
    });
});

$('.bt_selectWarnMeCmd').on('click', function () {
    nextdom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
        $('.configKey[data-l1key="interact::warnme::defaultreturncmd"]').value(result.human);
    });
});
