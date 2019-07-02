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

/* Initialisations */
uniqId_count = 0;
modifyWithoutSave = false;
nbActiveAjaxRequest = 0;
nextdomBackgroundImg = null;
utid = Date.now();

$(document).ajaxStart(function () {
    nbActiveAjaxRequest++;
    showLoadingCustom();
});
$(document).ajaxStop(function () {
    nbActiveAjaxRequest--;
    if (nbActiveAjaxRequest <= 0) {
        nbActiveAjaxRequest = 0;
        hideLoadingCustom();
    }
});

/* Page loading when navigation by link */
function loadPage(_url,_noPushHistory){
    /* Loading spinner */
    showLoadingCustom();
    /* Catch a page leaving when setting not saved */
    if (modifyWithoutSave) {
        if (!confirm('{{Attention vous quittez une page ayant des données modifiées non sauvegardées. Voulez-vous continuer ?}}')) {
            hideLoadingCustom();
            return;
        }
        modifyWithoutSave = false;
    }
    /* Unload current page before loading */
    if (typeof unload_page !== "undefined") {
        unload_page();
    }
    /* CLosing modals */
    $("#md_modal").dialog('close');
    $("#md_modal2").dialog('close');
    if ($("#mod_insertCmdValue").length != 0) {
        $("#mod_insertCmdValue").dialog('close');
    }
    if ($("#mod_insertDataStoreValue").length != 0) {
        $("#mod_insertDataStoreValue").dialog('close');
    }
    if ($("#mod_insertEqLogicValue").length != 0) {
        $("#mod_insertEqLogicValue").dialog('close');
    }
    if ($("#mod_insertCronValue").length != 0) {
        $("#mod_insertCronValue").dialog('close');
    }
    if ($("#mod_insertActionValue").length != 0) {
        $("#mod_insertActionValue").dialog('close');
    }
    if ($("#mod_insertScenarioValue").length != 0) {
        $("#mod_insertScenarioValue").dialog('close');
    }
    if(isset(bootbox)){
        bootbox.hideAll();
    }

    if(!isset(_noPushHistory) || _noPushHistory == false){
        window.history.pushState('','', _url);
    }
    nextdom.cmd.update = Array();
    nextdom.scenario.update = Array();
    $('main').css('padding-right','').css('padding-left','').css('margin-right','').css('margin-left','');
    $('#div_pageContainer').add("#div_pageContainer *").off();
    $.hideAlert();
    $('.bt_pluginTemplateShowSidebar').remove();
    removeContextualFunction();
    if(_url.indexOf('#') == -1){
        var url = _url+'&ajax=1';
    }else{
        var n=_url.lastIndexOf("#");
        var url = _url.substring(0,n)+"&ajax=1"+_url.substring(n)
    }
    $('.backgroundforJeedom').css('background-image','');
    nextdomBackgroundImg = null;
    /* Page content loading */
    $('#div_pageContainer').empty().load(url, function(){
        $('#bt_getHelpPage').attr('data-page',getUrlVars('p')).attr('data-plugin',getUrlVars('m'));
        /* Page title formatting */
        var title = getUrlVars('p');
        if(title !== false){
            document.title = title[0].toUpperCase() + title.slice(1) +' - NextDom';
        }
        initPage();
        $('body').trigger('nextdom_page_load');
        window.scrollTo(0, 0);
        setHeaderPosition(true);
        limitTreeviewMenu();
        adjustNextDomTheme();
        activateGlobalSearch();
        $('[data-toggle="tooltip"]').tooltip();
    });
    /* Loading end, remove wait spinner */
    hideLoadingCustom();
    return;
}

$(function () {
    $.alertTrigger = function(){
        initRowOverflow();
    }

    window.addEventListener('popstate', function (event){
        if(event.state === null){
            return;
        }
        var url = window.location.href.split("index.php?");
        loadPage('index.php?'+url[1],true)
    });

    $('body').on('click','a',function(e){
        if($(this).hasClass('noOnePageLoad')){
            return;
        }
        if($(this).hasClass('fancybox-nav')){
            return;
        }
        if($(this).attr('href') == undefined || $(this).attr('href') == '' || $(this).attr('href') == '#'){
            return;
        }
        if ($(this).attr('href').match("^http")) {
            return;
        }
        if ($(this).attr('href').match("^#")) {
            return;
        }
        if($(this).attr('target') === '_blank'){
            return;
        }
        $('li.dropdown.open').click();
        if ($(this).data('reload') === 'yes') {
            window.location.href= window.location.protocol + '//' + window.location.hostname + ':' + window.location.port + $(this).attr('href');
        }
        else {
            loadPage($(this).attr('href'));
        }
        e.preventDefault();
        e.stopPropagation();
    });

    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open');
    });
    if (!navigator.userAgent.match(/Android/i)
        && !navigator.userAgent.match(/webOS/i)
        && !navigator.userAgent.match(/iPhone/i)
        && !navigator.userAgent.match(/iPad/i)
        && !navigator.userAgent.match(/iPod/i)
        && !navigator.userAgent.match(/BlackBerry/i)
        & !navigator.userAgent.match(/Windows Phone/i)
    ) {
        $('ul.dropdown-menu [data-toggle=dropdown]').on('mouseenter', function (event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).parent().siblings().removeClass('open');
            $(this).parent().toggleClass('open');
        });
    }

    /********************* Date and Time********************************/
    setInterval(function () {
        var date = new Date();
        // Get NextDom language for format
        var locale = 'en-EN';
        if (isset(nextdom_langage)) {
            locale = nextdom_langage.replace('_','-');
        }
        //Date
        var dateFormat = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        $('#horloge_date').text(date.toLocaleDateString(locale, dateFormat));
        // Time
        $('#horloge_time').text(date.toLocaleTimeString(locale));
    }, 1000);

    $.fn.modal.Constructor.prototype.enforceFocus = function () {
    };

    $('body').on( "show", ".modal",function () {
        document.activeElement.blur();
        $(this).find(".modal-body :input:visible:first").focus();
    });

    /************************Help*************************/
    if (isset(nextdom_langage)) {
        bootbox.setDefaults({
            locale: nextdom_langage.substr(0, 2),
        });

    }

    //Display help
    $("#md_pageHelp").dialog({
        autoOpen: false,
        modal: false,
        closeText: '',
        height: (jQuery(window).height() - 100),
        width: ((jQuery(window).width() - 50) < 1500) ? (jQuery(window).width() - 50) : 1500,
        position: { my: "center bottom-10", at: "center bottom", of: window },
        open: function () {
            $("body").css({overflow: 'hidden'});
            $(this).closest( ".ui-dialog" ).find(":button").blur();
        },
        beforeClose: function (event, ui) {
            $("body").css({overflow: 'inherit'});
            $("#md_pageHelp").empty();
        }
    });

    $("#md_modal").dialog({
        autoOpen: false,
        modal: false,
        closeText: '',
        height: (jQuery(window).height() - 100),
        width: ((jQuery(window).width() - 50) < 1500) ? (jQuery(window).width() - 50) : 1500,
        position: {my: 'center', at: 'center', of: window},
        open: function () {
            $("body").css({overflow: 'hidden'});
            $(this).closest( ".ui-dialog" ).find(":button").blur();
        },
        beforeClose: function (event, ui) {
            $("body").css({overflow: 'inherit'});
            $("#md_modal").empty();
        }
    });

    $("#md_modal2").dialog({
        autoOpen: false,
        modal: false,
        closeText: '',
        height: (jQuery(window).height() - 100),
        width: ((jQuery(window).width() - 50) < 1500) ? (jQuery(window).width() - 50) : 1500,
        position: {my: 'center', at: 'center', of: window},
        open: function () {
            $("body").css({overflow: 'hidden'});
            $(this).closest( ".ui-dialog" ).find(":button").blur();
        },
        beforeClose: function (event, ui) {
            $("body").css({overflow: 'inherit'});
            $("#md_modal2").empty();
        }
    });

    $('#bt_nextdomAbout,#bt_nextdomAbout2, #bt_nextdomAboutFooter').on('click', function () {
        $('#md_modal').dialog({title: "{{A propos}}"});
        $('#md_modal').load('index.php?v=d&modal=about').dialog('open');
    });

    $('#bt_getHelpPage').on('click',function(){
        nextdom.getDocumentationUrl({
            plugin: $(this).attr('data-plugin'),
            page: $(this).attr('data-page'),
            error: function(error) {
                notify("Erreur", error.message, 'error');
            },
            success: function(url) {
                window.open(url,'_blank');
            }
        });
    });

    $('body').on( 'click','.bt_pageHelp', function () {
        showHelpModal($(this).attr('data-name'), $(this).attr('data-plugin'));
    });

    $(window).bind('beforeunload', function (e) {
        if (modifyWithoutSave) {
            return '{{Attention vous quittez une page ayant des données modifiées non sauvegardées. Voulez-vous continuer ?}}';
        }
    });

    $(window).resize(function () {
        initRowOverflow();
    });

    if (typeof nextdom_Welcome != 'undefined' && isset(nextdom_Welcome) && nextdom_Welcome == 1 && getUrlVars('noWelcome') != 1) {
        $('#md_modal').dialog({title: "{{Bienvenue dans NextDom}}"});
        $("#md_modal").load('index.php?v=d&modal=welcome').dialog('open');
    }

    $('#bt_haltSystem,#bt_haltSystemAdmin').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir arrêter le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=shutdown';
            }
        });
    });

    $('#bt_rebootSystem,#bt_rebootSystemAdmin').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir redémarrer le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=reboot';
            }
        });
    });

    $('#bt_showEventInRealTime').on('click',function(){
        $('#md_modal').dialog({title: "{{Evénement en temps réel}}"});
        $("#md_modal").load('index.php?v=d&modal=log.display&log=event').dialog('open');
    });

    $('#bt_showNoteManager').on('click',function(){
        $('#md_modal').dialog({title: "{{Note}}"});
        $("#md_modal").load('index.php?v=d&modal=note.manager').dialog('open');
    });

    $('#bt_gotoDashboard').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
              return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=dashboard');
    });

    $('#bt_gotoView').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
            return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=view');
    });

    $('#bt_gotoPlan').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
            return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=plan');
    });

    $('#bt_gotoPlan3d').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
            return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=plan3d');
    });

    $('#bt_messageModal').on('click',function(){
        $('#md_modal').dialog({title: "{{Messages NextDom}}"});
        $('#md_modal').load('index.php?v=d&modal=message').dialog('open');
    });

    $('#bt_goOnTop').click(function (){
        window.scrollTo(0, 0);
    });

    $('body').on('click','.objectSummaryParent',function(){
        loadPage('index.php?v=d&p=dashboard&summary='+$(this).data('summary')+'&object_id='+$(this).data('object_id'));
    });
    initPage();
    setHeaderPosition(true);
    setTimeout(function(){
        $('body').trigger('nextdom_page_load');
    }, 1);

    $('.colorpick_inline').colorpicker({
        container: true,
        inline: true
    });
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimScrollDiv").css("overflow", "");
    $(".sidebar").css("overflow", "");

    /* Survol fabs filtre categorie dashboard */
    $('.fab-filter').on('mouseleave',function() {
        $('.blur-div').removeClass('blur');
    });
    $('.fab-filter').on('mouseenter',function() {
        $('.blur-div').addClass('blur');
    });

    /**
     * Get access to plugins
     */
    $('[data-toggle="push-menu"]').pushMenu();
    var $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu');
    var $layout = $('body').data('lte.layout');
    $(window).on('load', function () {
        // Reinitialize variables on load
        $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu');
        $layout = $('body').data('lte.layout')
        window.scrollTo(0, 0);
        setHeaderPosition(true);
        limitTreeviewMenu();
        adjustNextDomTheme();
    });

    /* Toggle du menu de gauche adminLTE */
    $('.sidebar-toggle').on("click", function () {
        if ($('body').hasClass("sidebar-collapse") || ($(window).width() < 768 && !$('body').hasClass("sidebar-open"))) {
            $(".treeview-menu").css("overflow", "");
            $(".sidebar-menu").css("overflow-y", "auto");
            sideMenuResize(false);
        } else {
            $(".sidebar-menu").css("overflow", "");
            $(".treeview-menu").css("overflow-y", "auto");
            sideMenuResize(true);
        }
        setTimeout(function () {
            setHeaderPosition(false);
            adjustNextDomTheme();
            $('.div_displayEquipement').packery();
        }, 100);
    });

    /**
     * Toggles layout classes
     *
     * @param String cls the layout class to toggle
     * @returns void
     */
    function changeLayout(cls) {
        $('body').toggleClass(cls);
        $layout.fixSidebar();
        if ($('body').hasClass('fixed') && cls == 'fixed') {
            $pushMenu.expandOnHover();
            $layout.activate()
        }
    }

    /**
     * Retrieve default settings and apply them to the template
     *
     * @returns void
     */
    function setup() {
        // Add the layout manager
        $('[data-layout]').on('click', function () {
            changeLayout($(this).data('layout'));
        });

        $('[data-enable="expandOnHover"]').on('click', function () {
            $(this).attr('disabled', true);
            $pushMenu.expandOnHover();
            if (!$('body').hasClass('sidebar-collapse'))
                $('[data-layout="sidebar-collapse"]').click();
        });
    }

    setup();

    activateGlobalSearch();
});

function showHelpModal(_name, _plugin) {
    if (init(_plugin) != '' && _plugin != undefined) {
        $('#div_helpWebsite').load('index.php?v=d&modal=help.website&page=doc_plugin_' + _plugin + '.php #primary', function () {
            if ($('#div_helpWebsite').find('.alert.alert-danger').length > 0 || $.trim($('#div_helpWebsite').text()) == '') {
                $('a[href="#div_helpSpe"]').click();
                $('a[href="#div_helpWebsite"]').hide();
            } else {
                $('a[href="#div_helpWebsite"]').show();
                $('a[href="#div_helpWebsite"]').click();
            }
        });
        $('#div_helpSpe').load('index.php?v=d&plugin=' + _plugin + '&modal=help.' + init(_name));
    } else {
        $('#div_helpWebsite').load('index.php?v=d&modal=help.website&page=doc_' + init(_name) + '.php #primary', function () {
            if ($('#div_helpWebsite').find('.alert.alert-danger').length > 0 || $.trim($('#div_helpWebsite').text()) == '') {
                $('a[href="#div_helpSpe"]').click();
                $('a[href="#div_helpWebsite"]').hide();
            } else {
                $('a[href="#div_helpWebsite"]').show();
                $('a[href="#div_helpWebsite"]').click();
            }
        });
        $('#div_helpSpe').load('index.php?v=d&modal=help.' + init(_name));
    }
    $('#md_pageHelp').dialog('open');
}

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

function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
            break;
        }
    }
}

function chooseIcon(_callback) {
    if ($("#mod_selectIcon").length == 0) {
        $('#div_pageContainer').append('<div id="mod_selectIcon" title="{{Choisissez votre icône}}" ></div>');

        $("#mod_selectIcon").dialog({
            closeText: '',
            autoOpen: false,
            modal: true,
            height: (jQuery(window).height() - 150),
            width: 1500,
            open: function () {
                if ((jQuery(window).width() - 50) < 1500) {
                    $('#mod_selectIcon').dialog({width: jQuery(window).width() - 50});
                }
                $("body").css({overflow: 'hidden'});
            },
            beforeClose: function (event, ui) {
                $("body").css({overflow: 'inherit'});
            }
        });
        jQuery.ajaxSetup({async: false});
        $('#mod_selectIcon').load('index.php?v=d&modal=icon.selector');
        jQuery.ajaxSetup({async: true});
    }
    $("#mod_selectIcon").dialog('option', 'buttons', {
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
    $('#mod_selectIcon').dialog('open');
}

function positionEqLogic(_id,_preResize,_scenario) {
    if(_id != undefined){
        var eqLogic = $('.eqLogic-widget[data-eqlogic_id='+_id+']');
        var widget = (_scenario) ? $('.scenario-widget[data-scenario_id='+_id+']') : $('.eqLogic-widget[data-eqlogic_id='+_id+']');
        widget.css('margin','0px').css('padding','0px');
        eqLogic.trigger('resize');
        eqLogic.addClass(eqLogic.attr('data-category'));
        eqLogic.css('border-radius',widget_radius+'px');
    }else{
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

function taAutosize(){
    autosize($('.ta_autosize'));
    autosize.update($('.ta_autosize'));
}

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
                        saveWidgetDisplay()
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
                        saveWidgetDisplay()
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
                        saveWidgetDisplay()
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbColumn' : parseInt($(this).find('table.tableCmd').attr('data-column')) + 1},
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
                        saveWidgetDisplay()
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbLine' : parseInt($(this).find('table.tableCmd').attr('data-line')) + 1},
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
                        saveWidgetDisplay()
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbColumn' : parseInt($(this).find('table.tableCmd').attr('data-column')) - 1},
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
                        saveWidgetDisplay()
                        nextdom.eqLogic.simpleSave({
                            eqLogic : {
                                id : $(this).attr('data-eqLogic_id'),
                                display : {'layout::dashboard::table::nbLine' : parseInt($(this).find('table.tableCmd').attr('data-line')) - 1},
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

// TOOLS

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
