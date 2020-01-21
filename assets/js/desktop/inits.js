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

/* JS file for all that talk INIT */

/**
 * Init of page, master of all inits
 */
function initPage(){
    // Init functions calls
    initTableSorter();
    initReportMode();
    $.initTableFilter();
    initRowOverflow();
    initHelp();
    initTextArea();
    initEventHandler();
    initFields();
    initModals();

    // Trig page loaded
    $('body').trigger('nextdom_page_load');
}

/**
 * post Init of page, after page loaded
 */
function postInitPage(){
    // Scroll to top
    window.scrollTo(0, 0);

    // post init functions calls
    setHeaderPosition(true);
    sideMenuResize();
    limitTreeviewMenu();
    adjustNextDomTheme();
    activateGlobalSearch();
    fixLoadingHide();
}

/**
 * Init of button and other event handler
 */
function initEventHandler(){
    // Tabs change event handler declaration
    $('.nav-tabs a').on('click',function (e) {
        var scrollHeight = $(document).scrollTop();
        $(this).tab('show');
        $(window).scrollTop(scrollHeight);
        setTimeout(function() {
            $(window).scrollTop(scrollHeight);
        }, 0);
    });

    // Sliders init & event handler
    $('input[type=range]').on('change mousemove', function () {
        $(this).parent().children('.input-range-value').html($(this).val());
    });
    $('input[type=range]').each(function () {
        $(this).parent().children('.input-range-value').html($(this).val());
        $(this).prev('.input-range-min').html($(this).attr('min'));
        $(this).next('.input-range-max').html($(this).attr('max'));
    });

    // Reset config param to default value
    $('.bt_resetConfigParam').on('click', function () {
        resetConfigParamKey($(this));
        notify("Info", '{{Reset effectué et sauvegardé}}', 'success');
    });
}

/**
 * Init of fields
 */
function initFields(){
    $('.colorpick_inline').colorpicker({
        container: true,
        inline: true
    });
    $('.colorpick').colorpicker({
        horizontal: true
    });
    $(":input").inputmask();
    $(".slimScrollDiv").css("overflow", "");
    $(".sidebar").css("overflow", "");
    var options = $.extend(
    {},
        $.datepicker.regional["fr"],         
        { showOtherMonths: true,
        showWeek: true,
        showButtonPanel: true,
        numberOfMonths: 2,
        firstDay: 1,
        dateFormat: "yy-mm-dd" }
    );
    $.datepicker.setDefaults(options);
}

/**
 * Init of text area
 */
function initTextArea(){
    $('body').on('change keyup keydown paste cut', 'textarea.autogrow', function () {
        $(this).height(0).height(this.scrollHeight);
    });
}

// OBSOLETE ?
/**
 * Init of row-overflow classe
 */
 function initRowOverflow() {
     var hWindow = $(window).outerHeight() - $('header').outerHeight();
     $('.row-overflow > div').css('padding-top','0px').height(hWindow).css('overflow-y', 'auto').css('overflow-x', 'hidden').css('padding-top','5px');
 }

 /**
  * Init of report mode
  */
 function initReportMode() {
     if (getUrlVars('report') == 1) {
         $('header').hide();
         $('#div_mainContainer').css('margin-top', '-50px');
         $('#wrap').css('margin-bottom', '0px');
         $('.reportModeVisible').show();
         $('.reportModeHidden').hide();
     }
 }

 /**
  * Init of tablesorter fields
  */
 function initTableSorter() {
     $(".tablesorter").each(function () {
         var widgets = ['uitheme', 'filter', 'zebra', 'resizable'];
         $(".tablesorter").tablesorter({
             theme: "bootstrap",
             widthFixed: true,
             headerTemplate: '{content} {icon}',
             widgets: widgets,
             widgetOptions: {
                 filter_ignoreCase: true,
                 resizable: true,
                 stickyHeaders_offset: $('header.navbar-fixed-top').height(),
                 zebra: ["", ""],
             }
         });
     });
 }

 /**
  * Init of help fields, add ? icon
  */
 function initHelp(){
     $('.help').each(function(){
         if($(this).attr('data-help') != undefined){
             $(this).append(' <sup><i class="fas fa-question-circle tooltips text-normal" title="'+$(this).attr('data-help')+'" style="color:grey;"></i></sup>');
         }
     });
 }

 /**
  * Init of modals pages
  */
 function initModals(){
     // Help modal trigger declaration
     $("#md_pageHelp").dialog({
         autoOpen: false,
         modal: false,
         closeText: '',
         height: getModalHeight(),
         width: getModalWidth(),
         resizable: false,
         open: function () {
             $("body").css({overflow: 'hidden'});
             modalesAdjust();
             $(".wrapper").addClass("blur");
         },
         beforeClose: function (event, ui) {
             $("body").css({overflow: 'inherit'});
             $("#md_pageHelp").empty();
             $(".wrapper").removeClass("blur");
         }
     });

     // modal trigger declaration
     $("#md_modal").dialog({
         autoOpen: false,
         modal: false,
         closeText: '',
         height: getModalHeight(),
         width: getModalWidth(),
         resizable: false,
         open: function () {
             $("body").css({overflow: 'hidden'});
             modalesAdjust();
             $(".wrapper").addClass("blur");
         },
         beforeClose: function (event, ui) {
             $("body").css({overflow: 'inherit'});
             $("#md_modal").empty();
             $("#md_modal").dialog('option', 'buttons', []);
             $(".wrapper").removeClass("blur");
         }
     });

     // modal bis trigger declaration
     $("#md_modal2").dialog({
         autoOpen: false,
         modal: false,
         closeText: '',
         height: getModalHeight(),
         width: getModalWidth(),
         resizable: false,
         open: function () {
             $("body").css({overflow: 'hidden'});
             modalesAdjust();
             $(".wrapper").addClass("blur");
         },
         beforeClose: function (event, ui) {
             $("body").css({overflow: 'inherit'});
             $("#md_modal2").empty();
             $("#md_modal2").dialog('option', 'buttons', []);
             $(".wrapper").removeClass("blur");
         }
     });
}
