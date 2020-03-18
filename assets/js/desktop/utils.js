/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*
* @Support <https://www.nextdom.org>
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

/* Global variables Initialisations */
var modifyWithoutSave = false;
var lockModify = false;
uniqId_count = 0;
modifyWithoutSave = false;
nbActiveAjaxRequest = 0;
nextdomBackgroundImg = null;
utid = Date.now();

// ?
jQuery.fn.findAtDepth = function (selector, maxDepth) {
    var depths = [], i;

    if (maxDepth > 0) {
        for (i = 1; i <= maxDepth; i++) {
            depths.push('> ' + new Array(i).join('* > ') + selector);
        }

        selector = depths.join(', ');
    }
    return this.find(selector);
};

/**
 * Floating notification
 *
 * @param _title title of the notification
 * @param _text text of the notification
 * @param _class_name equivalent to the color of notification (success, warning, error, nextdom color)
 */
function notify(_title, _text, _class_name) {
    if (typeof notify_status != 'undefined' && isset(notify_status) && notify_status == 1) {
        var _backgroundColor = "";
        var _icon = "";

        if (_title == "") {
            _title = "Core";
        }
        if (_text == "") {
            _text = "Erreur inconnue";
        }
        if (_class_name == "success") {
            _backgroundColor = '#00a65a';
            _icon = 'far fa-check-circle fa-3x';
        } else if (_class_name == "warning") {
            _backgroundColor = '#f39c12';
            _icon = 'fas fa-exclamation-triangle fa-3x';
        } else if (_class_name == "error") {
            _backgroundColor = '#dd4b39';
            _icon = 'fas fa-times fa-3x';
        } else {
            _backgroundColor = '#33B8CC';
            _icon = 'fas fa-info fa-3x';
        }

        iziToast.show({
            id: null,
            class: '',
            title: _title,
            titleColor: 'white',
            titleSize: '1.5em',
            titleLineHeight: '30px',
            message: _text,
            messageColor: 'white',
            messageSize: '',
            messageLineHeight: '',
            theme: 'dark', // dark
            iconText: '',
            backgroundColor: _backgroundColor,
            icon: _icon,
            iconColor: 'white',
            iconUrl: null,
            image: '',
            imageWidth: 50,
            maxWidth: jQuery(window).width() - 500,
            zindex: null,
            layout: 2,
            balloon: false,
            close: true,
            closeOnEscape: false,
            closeOnClick: false,
            displayMode: 0, // once, replace
            position: notify_position, // bottomRight, bottomLeft, topRight, topLeft, topCenter, bottomCenter, center
            target: '',
            targetFirst: true,
            timeout: notify_timeout * 1000,
            rtl: false,
            animateInside: true,
            drag: true,
            pauseOnHover: true,
            resetOnHover: false,
            progressBar: true,
            progressBarColor: '',
            progressBarEasing: 'linear',
            overlay: false,
            overlayClose: false,
            overlayColor: 'rgba(0, 0, 0, 0.6)',
            transitionIn: 'fadeInUp',
            transitionOut: 'fadeOut',
            transitionInMobile: 'fadeInUp',
            transitionOutMobile: 'fadeOutDown',
            buttons: {},
            inputs: {},
            onOpening: function () {
            },
            onOpened: function () {
            },
            onClosing: function () {
            },
            onClosed: function () {
            }
        });
    }
}

/**
 * Opening a icone selector modal to choose one
 *
 * @param _callback callback who receive the icon code
 */
function chooseIcon(_callback) {
    $('#md_modal2').dialog({title: "{{Choisissez votre icône}}"});
    $('#md_modal2').load('index.php?v=d&modal=icon.selector');
    $("#md_modal2").dialog('option', 'buttons', {
        "Annuler": function () {
            $(this).dialog("close");
        },
        "Valider": function () {
            var icon = $('.iconSelected .iconSel').html();
            if (icon == undefined) {
                icon = '';
            }
            icon = icon.replace(/"/g, "'");
            _callback(icon);
            $(this).dialog('close');
        }
    });
    $('#md_modal2').dialog('open');
}

/**
 * Blocking sleep loop for a while
 *
 * @param milliseconds number of milliseconds you want to sleep execution
 */
function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
            break;
        }
    }
}

/**
 * Create a unique cmd ID
 *
 * @param _prefix ID prefix
 */
function uniqId(_prefix){
    if(typeof _prefix == 'undefined'){
        _prefix = 'jee-uniq';
    }
    var result = _prefix +'-'+ uniqId_count + '-'+Math.random().toString(36).substring(8);;
    uniqId_count++;
    if($('#'+result).length){
        return uniqId(_prefix);
    }
    return result;
}

/**
 * Save position and sizes of widgets
 *
 * @param _params array of param dedicated for know page in edition
 */
function saveWidgetDisplay(_params){
    if(init(_params) == ''){
        _params = {};
    }
    var cmds = [];
    var eqLogics = [];
    var scenarios = [];
    $('.eqLogic-widget:not(.eqLogic_layout_table)').each(function(){
        var eqLogic = $(this);
        order = 1;
        eqLogic.find('.cmd').each(function(){
            cmd = {};
            cmd.id = $(this).attr('data-cmd_id');
            cmd.order = order;
            cmds.push(cmd);
            order++;
        });
    });
    $('.eqLogic-widget.eqLogic_layout_table').each(function(){
        var eqLogic = $(this);
        order = 1;
        eqLogic.find('.cmd').each(function(){
            cmd = {};
            cmd.id = $(this).attr('data-cmd_id');
            cmd.line = $(this).closest('td').attr('data-line');
            cmd.column = $(this).closest('td').attr('data-column');
            cmd.order = order;
            cmds.push(cmd);
            order++;
        });
    });
    if(init(_params['dashboard']) == 1){
        $('.div_displayEquipement').each(function(){
            order = 1;
            $(this).find('.eqLogic-widget').each(function(){
                var eqLogic = {id :$(this).attr('data-eqlogic_id')}
                eqLogic.display = {};
                eqLogic.display.width =  Math.floor($(this).width() / 2) * 2 + 'px';
                eqLogic.display.height = Math.floor($(this).height() / 2) * 2+ 'px';
                if($(this).attr('data-order') != undefined){
                    eqLogic.order = $(this).attr('data-order');
                }else{
                    eqLogic.order = order;
                }
                eqLogics.push(eqLogic);
                order++;
            });
        });
        nextdom.eqLogic.setOrder({
            eqLogics: eqLogics,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success:function(data){
                nextdom.cmd.setOrder({
                    cmds: cmds,
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    }
                });
            }
        });
    }
    if(init(_params['view']) == 1){
        $('.eqLogicZone').each(function(){
            order = 1;
            $(this).find('.eqLogic-widget').each(function(){
                var eqLogic = {id :$(this).attr('data-eqlogic_id')}
                eqLogic.display = {};
                eqLogic.viewZone_id = $(this).closest('.eqLogicZone').attr('data-viewZone-id');
                eqLogic.order = order;
                eqLogics.push(eqLogic);
                order++;
            });
        });
        nextdom.view.setEqLogicOrder({
            eqLogics: eqLogics,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success:function(data){
                nextdom.cmd.setOrder({
                    cmds: cmds,
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    }
                });
            }
        });
    }
}

/**
 * Create or Destroy the right context menu
 *
 * @param _mode 0=destroy, 1=initialize
 */
function editWidgetCmdMode(_mode){
    if(!isset(_mode)){
        if($('#bt_editDashboardWidgetOrder').attr('data-mode') != undefined && $('#bt_editDashboardWidgetOrder').attr('data-mode') == 1){
            editWidgetMode(0);
            editWidgetMode(1);
        }
        return;
    }
    if(_mode == 0){
        $( ".eqLogic-widget.eqLogic_layout_table table.tableCmd").removeClass('table-bordered');
        $.contextMenu('destroy');
        if( $('.eqLogic-widget.allowReorderCmd.eqLogic_layout_table table.tableCmd.ui-sortable').length > 0){
            try{
                $('.eqLogic-widget.allowReorderCmd.eqLogic_layout_table table.tableCmd').sortable('destroy');
            }catch(e){

            }
        }
        if( $('.eqLogic-widget.allowReorderCmd.eqLogic_layout_default.ui-sortable').length > 0){
            try{
                $('.eqLogic-widget.allowReorderCmd.eqLogic_layout_default').sortable('destroy');
            }catch(e){

            }
        }
        if( $('.eqLogic-widget.ui-draggable').length > 0){
            $('.eqLogic-widget.allowReorderCmd').off('mouseover','.cmd');
            $('.eqLogic-widget.allowReorderCmd').off('mouseleave','.cmd');
        }
    }else{
        $( ".eqLogic-widget.allowReorderCmd.eqLogic_layout_default").sortable({items: ".cmd"});
        $(".eqLogic-widget.eqLogic_layout_table table.tableCmd").addClass('table-bordered');
        $('.eqLogic-widget.eqLogic_layout_table table.tableCmd td').sortable({
            connectWith: '.eqLogic-widget.eqLogic_layout_table table.tableCmd td',items: ".cmd"});
        $('.eqLogic-widget.allowReorderCmd').on('mouseover','.cmd',function(){
            $('.eqLogic-widget').draggable('disable');
        });
        $('.eqLogic-widget.allowReorderCmd').on('mouseleave','.cmd',function(){
            $('.eqLogic-widget').draggable('enable');
        });
        $.contextMenu({
            selector: '.eqLogic-widget',
            zIndex: 9999,
            events: {
                show: function(opt) {
                    $.contextMenu.setInputValues(opt, this.data());
                },
                hide: function(opt) {
                    $.contextMenu.getInputValues(opt, this.data());
                }
            },
            items: {
                configuration: {
                    name: "{{Configuration avancée}}",
                    icon : 'fa-cog',
                    callback: function(key, opt){
                        saveWidgetDisplay()
                        $('#md_modal').dialog({title: "{{Configuration du widget}}"});
                        $('#md_modal').load('index.php?v=d&modal=eqLogic.configure&eqLogic_id='+$(this).attr('data-eqLogic_id')).dialog('open');
                    }
                },
                sep1 : "---------",
                layoutDefaut: {
                    name: "{{Defaut}}",
                    icon : 'fa-square-o',
                    disabled:function(key, opt) {
                        return !$(this).hasClass('allowLayout') || !$(this).hasClass('eqLogic_layout_table');
                    },
                    callback: function(key, opt){
                        saveWidgetDisplay();
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard' : 'default'},
                            },
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            }
                        });
                    }
                },
                layoutTable: {
                    name: "{{Table}}",
                    icon : 'fa-table',
                    disabled:function(key, opt) {
                        return !$(this).hasClass('allowLayout') || $(this).hasClass('eqLogic_layout_table');
                    },
                    callback: function(key, opt){
                        saveWidgetDisplay();
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard' : 'table'},
                            },
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            }
                        });
                    }
                },
                sep2 : "---------",
                addTableColumn: {
                    name: "{{Ajouter colonne}}",
                    icon : 'fa-plus',
                    disabled:function(key, opt) {
                        return !$(this).hasClass('eqLogic_layout_table');
                    },
                    callback: function(key, opt){
                        saveWidgetDisplay();
                        var column = 0;
                        if($(this).find('table.tableCmd').attr('data-column') !== undefined){
                            column = parseInt($(this).find('table.tableCmd').attr('data-column'));
                        }
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbColumn' : column + 1},
                            },
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            }
                        });
                    }
                },
                addTableLine: {
                    name: "{{Ajouter ligne}}",
                    icon : 'fa-plus',
                    disabled:function(key, opt) {
                        return !$(this).hasClass('eqLogic_layout_table');
                    },
                    callback: function(key, opt){
                        saveWidgetDisplay();
                        var line = 0;
                        if($(this).find('table.tableCmd').attr('data-line') !== undefined){
                            line = parseInt($(this).find('table.tableCmd').attr('data-line'));
                        }
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbLine' : line + 1},
                            },
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            }
                        });
                    }
                },
                removeTableColumn: {
                    name: "{{Supprimer colonne}}",
                    icon : 'fa-minus',
                    disabled:function(key, opt) {
                        return !$(this).hasClass('eqLogic_layout_table');
                    },
                    callback: function(key, opt){
                        saveWidgetDisplay();
                        var column = 0;
                        if($(this).find('table.tableCmd').attr('data-column') !== undefined){
                            column = parseInt($(this).find('table.tableCmd').attr('data-column')) - 1;
                            column = (column < 0) ? 0 : column;
                        }
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbColumn' : column},
                            },
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            }
                        });
                    }
                },
                removeTableLine: {
                    name: "{{Supprimer ligne}}",
                    icon : 'fa-minus',
                    disabled:function(key, opt) {
                        return !$(this).hasClass('eqLogic_layout_table');
                    },
                    callback: function(key, opt){
                        saveWidgetDisplay();
                        var line = 0;
                        if($(this).find('table.tableCmd').attr('data-line') !== undefined){
                            line = parseInt($(this).find('table.tableCmd').attr('data-line')) - 1;
                            line = (line < 0) ? 0 : line;
                        }
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbLine' : line},
                            },
                            error: function (error) {
                                notify("Erreur", error.message, 'error');
                            }
                        });
                    }
                },
            }
        });
    }
}

/**
 * Control size of widget, and assign class category for filtering
 *
 * @param _id EqLogic ID, if null>ALL
 * @param _preResize TRUE if pre-resizing
 * @param _scenario TRUE if it's a scenario widget
 */
function positionEqLogic(_id,_preResize,_scenario) {
    if(_id != undefined){
        var eqLogic = $('.eqLogic-widget[data-eqlogic_id='+_id+']');
        var widget = (_scenario) ? $('.scenario-widget[data-scenario_id='+_id+']') : $('.eqLogic-widget[data-eqlogic_id='+_id+']');
        widget.css('margin','0px').css('padding','0px');
        eqLogic.trigger('resize');
        eqLogic.addClass(eqLogic.attr('data-category'));
        eqLogic.css('border-radius',widget_radius+'px');
    } else {
        $('.eqLogic-widget:not(.nextdomAlreadyPosition)').css('margin','0px').css('padding','0px');
        $('.eqLogic-widget:not(.nextdomAlreadyPosition)').each(function () {
            if($(this).width() == 0){
                $(this).width('100px');
            }
            if($(this).height() == 0){
                $(this).height('100px');
            }
            $(this).trigger('resize');
            $(this).addClass($(this).attr('data-category'));
        });
        $('.eqLogic-widget:not(.nextdomAlreadyPosition)').css('border-radius',widget_radius+'px');
        $('.eqLogic-widget').addClass('nextdomAlreadyPosition');
    }
}

/**
 * Remove a Equipement context
 */
function removeContextualFunction(){
    printEqLogic = undefined
}

/**
 * Convert a text on a link
 *
 * @param inputText text to convert
 */
function linkify(inputText) {
    var replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    var replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');
    var replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
    var replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');
    var replacePattern3 = /(\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6})/gim;
    var replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');
    return replacedText
}

/**
 * Widget size calcul
 */
function calculWidgetSize(_size,_step,_margin){
    var result = Math.ceil(_size / _step) * _step - (2*_margin);
    if(result < _size){
        result += Math.ceil((_size - result) / _step)* _step;
    }
    return result;
}

/**
 * Convert an hex color to RGB color
 *
 * @param hex color in HEX format
 */
function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

/**
 * Reset à config param to his default value in default.config.ini
 *
 * @param keyElt Elt or button who handle the reset and contain the config key
 */
function resetConfigParamKey(keyElt) {
    lockModify = true;
    var paramKey = keyElt.attr('data-l1key');
    var defaultValue = "";
    var arrayKey = paramKey.split("::");
    arrayKey.pop();
    var paramSubKey = arrayKey.join("::");
    nextdom.config.remove({
        configuration: paramKey,
        error: function (error) {
            notify("Core", error.message, 'error');
        },
        success: function (dataRemove) {
            nextdom.config.load({
                configuration: paramKey,
                error: function (error) {
                    notify("Core", error.message, 'error');
                },
                success: function (dataLoad) {
                    if (isset(dataLoad) && dataLoad != "") {
                        // Direct slider
                        keyElt.siblings(".slider").value(dataLoad);
                        // Or associate fields
                        $('.configKey[data-l1key="' + paramKey + '"]').value(dataLoad)
                        lockModify=false;
                    } else {
                        nextdom.config.load({
                            configuration: paramSubKey,
                            error: function (error) {
                                notify("Core", error.message, 'error');
                            },
                            success: function (dataSubLoad) {
                                if (isset(dataSubLoad) && dataSubLoad != "") {
                                    defaultValue = dataSubLoad;
                                } else {
                                    defaultValue = 0;
                                }
                                // Direct slider
                                keyElt.siblings(".slider").value(defaultValue);
                                // Or associate fields
                                $('.configKey[data-l1key="' + paramKey + '"]').value(dataSubLoad)
                                lockModify=false;
                            }
                        });
                    }
                }
            });
        }
    });
}

/**
 * Calcul a score password from 0=none, 2/4=low, 8/16=Middle, 32/64=High, 128/256=VeryHigh
 *
 * @param password password value
 * @param progressbar progressbar component with role="progressbar"
 * @param spanLevel span id to write level
 */
function passwordScore(password, progressbar=null, spanLevel=null) {
    let passwordToScore = password.toString();
    let scoreNumber = passwordToScore.match(/\d+/) ? 15 : 0;
    let scoreNumberThree = passwordToScore.match(/(.*[0-9].*[0-9].*[0-9])/) ? 15 : 0;
    let scoreCase = passwordToScore.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/) ? 15 : 0;
    let scoreSpecial = passwordToScore.match(/[!,@,#,$,%,^,&,*,?,-,_,~,€,§]/) ? 15 : 0;
    let scoreSpecialTwo = passwordToScore.match(/(.*[!,@,#,$,%,^,&,*,?,-,_,~,€,§].*[!,@,#,$,%,^,&,*,?,-,_,~,€,§])/) ? 15 : 0;
    let scoreLength = passwordToScore.length >= 3 ? 10 : 0;
    let scoreLengthMore = passwordToScore.length >= 10 ? 15 : 0;
    let textLevel = '';
    score = (scoreNumber + scoreNumberThree + scoreCase + scoreSpecial + scoreSpecialTwo + scoreLength + scoreLengthMore).toString();
    if (progressbar != null) {
        progressbar.width(score + '%');
        progressbar.removeClass('progress-bar-green').removeClass('progress-bar-yellow').removeClass('progress-bar-red');
        if(score > 0 && score <= 25) {
            progressbar.addClass('progress-bar-red');
        } else if (score >= 25 && score <= 70) {
            progressbar.addClass('progress-bar-yellow');
        } else if (score > 70) {
            progressbar.addClass('progress-bar-green');
        }
    }
    if (spanLevel != null) {
        if(score > 0 && score <= 25) {
            textLevel='{{Sécurité Faible}}';
        } else if (score > 25 && score <= 70) {
            textLevel='{{Sécurité Moyenne}}';
        } else if (score > 70 && score < 100) {
            textLevel='{{Sécurité Forte}}';
        } else if (score == 0) {
            textLevel='{{Sécurité Trés Faible}}';
        } else if (score == 100) {
            textLevel='{{Sécurité Trés Forte}}';
        }
        spanLevel.html('<i class="fas fa-shield-alt"></i>' + textLevel)
    }
    return score;
}

/**
 * Decode HTML entities in string like &eacute;
 * @param string message
 */
function decodeHtmlEntities(message)
{
    var temporaryTextArea = document.createElement('textarea');
    temporaryTextArea.innerHTML = message;
    return temporaryTextArea.value;
}
