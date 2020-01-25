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

/* JS file for page and modals loading management */

// INIT, EVENT, FIRST Loading

/**
 * Event for Ajax start request
 */
$(document).ajaxStart(function () {
    nbActiveAjaxRequest++;
    Pace.restart();
});

/**
 * Event for Ajax stop request
 */
$(document).ajaxStop(function () {
    nbActiveAjaxRequest--;
    if (nbActiveAjaxRequest <= 0) {
        nbActiveAjaxRequest = 0;
    }
});

/**
 * Event for first page loading or F5 loading
 */
$(function () {
    // OBSOLETE ?
    $.alertTrigger = function(){
        initRowOverflow();
    };

    // ?
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
    };

    // Clock actualisation timer
    displayClock();
    setInterval(function () {
        displayClock();
    }, 1000);

    // History push listener declaration
    window.addEventListener('popstate', function (event){
        if(event.state === null){
            return;
        }
        var url = window.location.href.split("index.php?");
        loadPage('index.php?'+url[1],true)
    });

    // Opening welcome modal if not saved "not display anymore"
    if (typeof nextdom_Welcome != 'undefined' && isset(nextdom_Welcome) && nextdom_Welcome == 1 && getUrlVars('noWelcome') != 1) {
        $('#md_modal').dialog({title: "{{Bienvenue dans NextDom}}"});
        $("#md_modal").load('index.php?v=d&modal=welcome').dialog('open');
    }

    // BUTTONS, LINKS HANDLERS
    // About buttons event handler declaration
    $('#bt_nextdomAbout,#bt_nextdomAbout2, #bt_nextdomAboutSide').on('click', function () {
        $('#md_modal').dialog({title: "{{A propos}}"});
        $('#md_modal').load('index.php?v=d&modal=about').dialog('open');
    });

    // Quick note link event handler declaration
    $('#bt_quickNote').on('click',function(){
      $('#md_modal').dialog({title: "{{Quick Notes}}"});
      $("#md_modal").load('index.php?v=d&modal=note.manager').dialog('open');
    });

    // Quick note link event handler declaration
    $('#bt_showExpressionTest').on('click',function(){
      $('#md_modal').dialog({title: "{{Testeur d'expression}}"});
      $("#md_modal").load('index.php?v=d&modal=expression.test').dialog('open');
    });

    // View page link event handler declaration
    $('#bt_gotoView').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
            return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=view');
    });

    // Designs page link event handler declaration
    $('#bt_gotoPlan').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
            return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=plan');
    });

    // Plan3D page link event handler declaration
    $('#bt_gotoPlan3d').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
            return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=plan3d');
    });

    // Messages link event handler declaration
    $('#bt_messageModal').on('click',function() {
        var tableMessageContainer = $('#table_message .menu');
        tableMessageContainer.html('<i class="fas fa-circle-notch fa-spin"></i>');
        nextdom.message.all({
            error: function (error) {
                notify('Core', error.message, 'error');
            },
            success: function (messages) {
                tableMessageContainer.html('');
                for (var messageIndex in messages) {
                    tableMessageContainer.append('' +
                      '<li data-message_id="' + messages[messageIndex]['id'] + '">' +
                      '<a href="#">' +
                      '<div class="pull-left">' +
                      '<img class="' + messages[messageIndex]['iconClass'] + '" src="' + messages[messageIndex]['icon'] + '">' +
                      '</div>' +
                      '<h4>' + messages[messageIndex]['plugin'] +
                      '<div class="btn btn-sm btn-danger removeMessage pull-right"><i class="fas fa-trash no-spacing"></i></div>' +
                      '<small class="pull-right"><i class="fas fa-clock spacing-right"></i>' + messages[messageIndex]['date'] + '</small>' +
                      '</h4>' +
                      '<p>' + decodeHtmlEntities(messages[messageIndex]['message']) + '</p>' +
                      '<p>' +  messages[messageIndex]['action'] + '</p>' +
                      '</a>' +
                      '</li>');
                }
            }
        });
    });

    // Restart event handler declaration
    $('#bt_rebootSystem').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir redémarrer le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=reboot';
            }
        });
    });

    // Shutdown event handler declaration
    $('#bt_haltSystem').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir arrêter le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=shutdown';
            }
        });
    });

    // Quick note button event handler declaration
    $('#bt_showNoteManager').on('click',function(){
        $('#md_modal').dialog({title: "{{Note}}"});
        $("#md_modal").load('index.php?v=d&modal=note.manager').dialog('open');
    });

    // Dashboard link event handler button
    $('#bt_gotoDashboard').on('click',function(){
        if('ontouchstart' in window || navigator.msMaxTouchPoints){
              return;
        }
        $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
        loadPage('index.php?v=d&p=dashboard');
    });

    // Go on top fab button link event handler declaration
    $('#bt_goOnTop').click(function (){
        window.scrollTo(0, 0);
    });

    // Link click event handler declaration
    $('body').on('click','a',function(e){
        if ($(this).hasClass('noOnePageLoad')
           || $(this).attr('href') == undefined
           || $(this).attr('href') == ''
           || $(this).attr('href') == '#'
           || $(this).attr('href').match("^http")
           || $(this).attr('href').match("^#")
           || $(this).attr('target') === '_blank'){
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

    // OTHERS HANDLER
    // Dropdown menu event handler declaration
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open');
    });

    // Dropdown menu event handler declaration for tactile utilisation
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

    // Dashboard categorie filter button event handler declaration
    $('.fab-filter').on('mouseleave',function() {
        $('.blur-div').removeClass('blur');
    });
    $('.fab-filter').on('mouseenter',function() {
        $('.blur-div').addClass('blur');
    });

    // adminLTE left menu toggle link event handler declaration
    $('.sidebar-toggle').on("click", function () {
        setTimeout(function () {
            // Resize menu
            sideMenuResize();
            limitTreeviewMenu();
            // Header repositionning
            setHeaderPosition(false);
            // Gui automatic adjusting
            adjustNextDomTheme();
            // Equipement reorganisation
            $('.div_displayEquipement').packery();
        }, 100);
    });

    // Modal opening event handler
    $('body').on( "show", ".modal",function () {
        document.activeElement.blur();
        $(this).find(".modal-body :input:visible:first").focus();
    });

    // Define question box language
    if (isset(nextdom_language)) {
        bootbox.setDefaults({
            locale: nextdom_language.substr(0, 2),
        });
    }

    // Help triggers declaration
    $('#bt_getHelpPage').on('click',function(){
        // Init help button
        var pageName = getUrlVars('p');
        var pluginName = getUrlVars('m');
        if (pluginName === false) {
            pluginName = '';
        }
        nextdom.getDocumentationUrl({
            plugin: pluginName,
            page: pageName,
            error: function(error) {
                notify("Erreur", error.message, 'error');
            },
            success: function(url) {
                window.open(url,'_blank');
            }
        });
    });

    // Prevent close event handler declaration to advise user for exit without saving
    $(window).bind('beforeunload', function (e) {
        if (modifyWithoutSave) {
            return '{{Attention vous quittez une page ayant des données modifiées non sauvegardées. Voulez-vous continuer ?}}';
        }
    });

    // Summary link event handler declaration
    $('body').on('click','.objectSummaryParent',function(){
        loadPage('index.php?v=d&p=dashboard&summary='+$(this).data('summary')+'&object_id='+root_object_id);
    });

    // Inits launch
    initPage();

    // Post Inits launch
    $(window).on('load', function () {
       postInitPage();
    });
});

// FUNCTIONS

/**
 * Page loading when navigation by link
 *
 * @param pageUrl url of the page to load
 * @param noPushHistory TRUE to not have the new page in history, so go back to previous page if F5
 */
function loadPage(pageUrl,noPushHistory){
    // Catch a page leaving when setting not saved
    if (modifyWithoutSave) {
        if (!confirm('{{Attention vous quittez une page ayant des données modifiées non sauvegardées. Voulez-vous continuer ?}}')) {
            return;
        }
        modifyWithoutSave = false;
    }

    // Unload current page before loading
    if (typeof unload_page !== "undefined") {
        unload_page();
    }

    // CLosing modals
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

    // Closing question boxs
    if(isset(bootbox)){
        bootbox.hideAll();
    }

    // Closing alerts
    $.hideAlert();

    // Navigator history management
    if(!isset(noPushHistory) || noPushHistory == false){
        window.history.pushState('','', pageUrl);
    }

    // Variables reset
    nextdom.cmd.update = Array();
    nextdom.scenario.update = Array();

    // GUI reset
    $('main').css('padding-right','').css('padding-left','').css('margin-right','').css('margin-left','');
    $('#div_pageContainer').add("#div_pageContainer *").off();
    $('.bt_pluginTemplateShowSidebar').remove();
    $('.backgroundforJeedom').css('background-image','');
    nextdomBackgroundImg = null;

    // Remove a equipements context
    removeContextualFunction();

    // Url ajax adjusting
    if(pageUrl.indexOf('#') == -1){
        var url = pageUrl+'&ajax=1';
    }else{
        var n = pageUrl.lastIndexOf("#");
        var url = pageUrl.substring(0,n)+"&ajax=1"+pageUrl.substring(n)
    }

    // Page content loading
    $('#div_pageContainer').empty().load(url, function(){
        // Page title formatting
        var title = getUrlVars('p');
        if(title !== false){
            document.title = title[0].toUpperCase() + title.slice(1) +' - NextDom';
        }

        // Inits launch
        initPage();

        // Post Inits launch
        postInitPage();
    });
}
