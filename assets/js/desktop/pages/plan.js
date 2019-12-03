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

function unload_page() {
  if (getUrlVars('p') != 'plan') {
    return;
  }
  $.contextMenu('destroy', '#div_pageContainer');
}

$('main').css('padding-right', '0px').css('padding-left', '0px').css('margin-right', '0px').css('margin-left', '0px');

var editOption = {state: false, snap: false, highlight: true};
var clickedOpen = false;

var planHeaderContextMenu = {};

for (let i in planHeader) {
  planHeaderContextMenu[planHeader[i].id] = {
    name: planHeader[i].name,
    callback: function () {
      displayPlan();
    }
  }
}

function editionModeAction() {
  editOption.state = !editOption.state;
  initEditOption(editOption.state);
}

function fullscreen() {
  if (this.data('fullscreen') == undefined) {
    this.data('fullscreen', 1)
  }
  fullScreen(this.data('fullscreen'));
  this.data('fullscreen', !this.data('fullscreen'));
}

function addGraphWidget() {
  if(this.data('editOption.state')){
    addObject({link_type: 'graph', link_id: Math.round(Math.random() * 99999999) + 9999});
  }
}

function addTextHtmlWidget() {
  addObject({
    link_type: 'text',
    link_id: Math.round(Math.random() * 99999999) + 9999,
    display: {text: 'Texte à insérer ici'}
  });
}

function addScenarioWidget() {
  nextdom.scenario.getSelectModal({}, function (data) {
    addObject({link_type: 'scenario', link_id: data.id});
  });
}

function addViewLinkWidget() {
  addObject({
    link_type: 'view',
    link_id: -(Math.round(Math.random() * 99999999) + 9999),
    display: {name: 'A configurer'}
  });
}

function addDesignLinkWidget() {
  addObject({
    link_type: 'plan',
    link_id: -(Math.round(Math.random() * 99999999) + 9999),
    display: {name: 'A configurer'}
  });
}

function addDeviceWidget() {
  nextdom.eqLogic.getSelectModal({}, function (data) {
    addObject({link_type: 'eqLogic', link_id: data.id});
  });
}

function addCommandWidget() {
  nextdom.cmd.getSelectModal({}, function (data) {
    addObject({link_type: 'cmd', link_id: data.cmd.id});
  });
}

function addPictureWidget() {
  addObject({link_type: 'image', link_id: Math.round(Math.random() * 99999999) + 9999});
}

function addAreaWidget() {
  addObject({link_type: 'zone', link_id: Math.round(Math.random() * 99999999) + 9999});
}

function addResumeWidget() {
  addObject({link_type: 'summary', link_id: -1});
}

function removePlanAction() {
  bootbox.confirm('{{Etes-vous sûr de vouloir supprimer ce design ?}}', function (result) {
    if (result) {
      nextdom.plan.removeHeader({
        id: planHeader_id,
        error: function (error) {
          notify("Erreur", error.message, 'error');
        },
        success: function () {
          notify("Info", 'Design supprimé', 'success');
          loadPage('index.php?v=d&p=plan');
        },
      });
    }
  });
}

function duplicatePlanAction() {
  bootbox.prompt("{{Nom la copie du design ?}}", function (result) {
    if (result !== null) {
      savePlanAction(false, false);
      nextdom.plan.copyHeader({
        name: result,
        id: planHeader_id,
        error: function (error) {
          notify("Erreur", error.message, 'error');
        },
        success: function (data) {
          loadPage('index.php?v=d&p=plan&plan_id=' + data.id);
        },
      });
    }
  });
}

function configurePlanAction() {
  savePlanAction(false, false);
  showConfigModal();
}



  // Right click on Widgets
  $.contextMenu({
    selector: '.div_displayObject > .eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.plan-link-widget,.text-widget,.view-link-widget,.graph-widget,.image-widget,.zone-widget,.summary-widget',
    zIndex: 9999,
    events: {
      show: function (opt) {
        $.contextMenu.setInputValues(opt, this.data());
        if (editOption.highlight) {
          $(this).removeClass('widget-shadow-edit').addClass('contextMenu_select');
        }
      },
      hide: function (opt) {
        $.contextMenu.getInputValues(opt, this.data());
        if (editOption.highlight) {
          $(this).removeClass('contextMenu_select').addClass('widget-shadow-edit');
        }
      }
    },
    items: {
      parameter: {
        name: '{{Paramètres d\'affichage}}',
        icon: 'fa-cogs',
        callback: function (key, opt) {
          savePlanAction(false, false);
          $('#md_modal').dialog({title: "{{Configuration du widget}}"});
          $('#md_modal').load('index.php?v=d&modal=plan.configure&id=' + $(this).attr('data-plan_id')).dialog('open');
        }
      },
      configuration: {
        name: '{{Configuration avancée}}',
        icon: 'fa-cog',
        disabled: function (key, opt) {
          var info = getObjectInfo($(this));
          return !(info.type == 'eqLogic' || info.type == 'cmd' || info.type == 'graph');
        },
        callback: function (key, opt) {
          $('#md_modal').dialog({title: "{{Configuration avancée}}"});
          var info = getObjectInfo($(this));
          if (info.type == 'graph') {
            var el = $(this);
            $("#md_modal").load('index.php?v=d&modal=cmd.graph.select', function () {
              $('#table_addViewData tbody tr .enable').prop('checked', false);
              var options = json_decode(el.find('.graphOptions').value());
              for (var i in options) {
                var tr = $('#table_addViewData tbody tr[data-link_id=' + options[i].link_id + ']');
                tr.find('.enable').value(1);
                tr.setValues(options[i], '.graphDataOption');
                setColorSelect(tr.find('.graphDataOption[data-l1key=configuration][data-l2key=graphColor]'));
              }
              $("#md_modal").dialog('option', 'buttons', {
                "Annuler": function () {
                  $(this).dialog("close");
                },
                "Valider": function () {
                  var tr = $('#table_addViewData tbody tr:first');
                  var options = [];
                  while (tr.attr('data-link_id') != undefined) {
                    if (tr.find('.enable').is(':checked')) {
                      var graphData = tr.getValues('.graphDataOption')[0];
                      graphData.link_id = tr.attr('data-link_id');
                      options.push(graphData);
                    }
                    tr = tr.next();
                  }
                  el.find('.graphOptions').empty().append(json_encode(options));
                  savePlanAction(true);
                  $(this).dialog('close');
                }
              });
              $('#md_modal').dialog('open');
            });
          } else {
            $('#md_modal').load('index.php?v=d&modal=' + info.type + '.configure&' + info.type + '_id=' + info.id).dialog('open');
          }
        }
      },
      remove: {
        name: '{{Supprimer}}',
        icon: 'fa-trash',
        callback: function (key, opt) {
          savePlanAction(false, false);
          nextdom.plan.remove({
            id: $(this).attr('data-plan_id'),
            error: function (error) {
              notify("Erreur", error.message, 'error');
            },
            success: function () {
              displayPlan();
            },
          });
        }
      },
      duplicate: {
        name: '{{Dupliquer}}',
        icon: 'fa-files-o',
        disabled: function (key, opt) {
          var info = getObjectInfo($(this));
          return !(info.type == 'text' || info.type == 'graph' || info.type == 'zone');
        },
        callback: function (key, opt) {
          var info = getObjectInfo($(this));
          nextdom.plan.copy({
            id: $(this).attr('data-plan_id'),
            version: 'dplan',
            error: function (error) {
              notify("Erreur", error.message, 'error');
            },
            success: function (data) {
              displayObject(data.plan, data.html);
            }
          });

        }
      },
      lock: {
        name: "{{Verrouiller}}",
        type: 'checkbox',
        events: {
          click: function (opt) {
            if ($(this).value() == 1) {
              opt.handleObj.data.$trigger.addClass('locked');
            } else {
              opt.handleObj.data.$trigger.removeClass('locked');
            }
          }
        }
      },
    }
  });

/**************************************init*********************************************/
displayPlan();

// Create First Design Button
$('#bt_createNewDesign').on('click', function () {
  createPlanAction();
});

$('#div_pageContainer').delegate('.plan-link-widget', 'click', function () {
  if (!editOption.state) {
    planHeader_id = $(this).attr('data-link_id');
    displayPlan();
  }
});

$('#div_pageContainer').on('click', '.zone-widget:not(.zoneEqLogic)', function () {
  var el = $(this);
  if (!editOption.state) {
    el.append('<center class="loading"><i class="fas fa-spinner fa-spin fa-4x"></i></center>');
    nextdom.plan.execute({
      id: el.attr('data-plan_id'),
      error: function (error) {
        notify("Erreur", error.message, 'error');
        el.empty().append('<center class="loading"><i class="fas fa-times fa-4x"></i></center>');
        setTimeout(function () {
          el.empty();
          clickedOpen = false;
        }, 3000);
      },
      success: function () {
        el.empty();
        clickedOpen = false;
      },
    });
  }
});

$('#div_pageContainer').on('mouseenter', '.zone-widget.zoneEqLogic.zoneEqLogicOnFly', function () {
  if (!editOption.state) {
    clickedOpen = true;
    var el = $(this);
    nextdom.eqLogic.toHtml({
      id: el.attr('data-eqLogic_id'),
      version: 'dplan',
      global: false,
      success: function (data) {
        var html = $(data.html).css('position', 'absolute');
        html.attr("style", html.attr("style") + "; " + el.attr('data-position'));
        el.empty().append(html);
        positionEqLogic(el.attr('data-eqLogic_id'), false);
        el.off('mouseleave').on('mouseleave', function () {
          el.empty()
          clickedOpen = false;
        });
      }
    });
  }
});

$('#div_pageContainer').on('click', '.zone-widget.zoneEqLogic.zoneEqLogicOnClic', function () {
  if (!editOption.state && !clickedOpen) {
    clickedOpen = true;
    var el = $(this);
    nextdom.eqLogic.toHtml({
      id: el.attr('data-eqLogic_id'),
      version: 'dplan',
      global: false,
      success: function (data) {
        el.empty().append($(data.html).css('position', 'absolute'));
        positionEqLogic(el.attr('data-eqLogic_id'));
        if (el.hasClass('zoneEqLogicOnFly')) {
          el.off('mouseleave').on('mouseleave', function () {
            el.empty();
            clickedOpen = false;
          });
        }
      }
    });
  }
});

$(document).click(function (event) {
  if (!editOption.state) {
    if ((!$(event.target).hasClass('.zone-widget.zoneEqLogic') && $(event.target).closest('.zone-widget.zoneEqLogic').html() == undefined) && (!$(event.target).hasClass('.zone-widget.zoneEqLogicOnFly') && $(event.target).closest('.zone-widget.zoneEqLogicOnFly').html() == undefined)) {
      $('.zone-widget.zoneEqLogic').each(function () {
        if ($(this).hasClass('zoneEqLogicOnClic') || $(this).hasClass('zoneEqLogicOnFly')) {
          $(this).empty();
          clickedOpen = false;
        }
      });
    }
  }
});

$('.view-link-widget').off('click').on('click', function () {
  if (!editOption.state) {
    $(this).find('a').click();
  }
});

$('.div_displayObject').delegate('.graph-widget', 'resize', function () {
  if (isset(nextdom.history.chart['graph' + $(this).attr('data-graph_id')])) {
    nextdom.history.chart['graph' + $(this).attr('data-graph_id')].chart.reflow();
  }
});

$('#div_pageContainer').delegate('.div_displayObject > .eqLogic-widget .history', 'click', function () {
  if (!editOption.state) {
    $('#md_modal').dialog({title: "Historique"}).load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');
  }
});

$('#div_pageContainer').delegate('.div_displayObject > .cmd-widget.history', 'click', function () {
  if (!editOption.state) {
    $('#md_modal').dialog({title: "Historique"}).load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');
  }
});

/***********************************************************************************/

function createPlanAction() {
  bootbox.prompt("{{Nom du design ?}}", function (result) {
    if (result !== null) {
      nextdom.plan.saveHeader({
        planHeader: {name: result},
        error: function (error) {
          notify("Erreur", error.message, 'error');
        },
        success: function (data) {
          window.location = 'index.php?v=d&p=plan&plan_id=' + data.id;
        }
      });
    }
  });
}

function setColorSelect(_select) {
  _select.css('background-color', _select.find('option:selected').val());
}

$('.graphDataOption[data-l1key=configuration][data-l2key=graphColor]').off('change').on('change', function () {
  setColorSelect($(this).closest('select'));
});

function fullScreen(_mode) {
  if (_mode) {
    $('.wrapper').addClass('fullscreen');
    $('#wrap').css('margin-bottom', '0px');
    $('.div_backgroundPlan').height($('html').height());
    if ($('.wrapper').width() < 767) {
      $('.content-wrapper').css("cssText", "margin-top: 50px !important;");
    }
  } else {
    $('.wrapper').removeClass('fullscreen');
    $('#wrap').css('margin-bottom', '15px');
    $('.div_backgroundPlan').height($('body').height());
    if ($('.wrapper').width() < 767) {
      $('.content-wrapper').css("cssText", "margin-top: 100px !important;");
    }
  }
}

function initEditOption(_state) {
  if (_state) {

    !function(){function t(e,r,n){function i(a,l){if(!r[a]){if(!e[a]){var u="function"==typeof require&&require;if(!l&&u)return u(a,!0);if(o)return o(a,!0);var s=new Error("Cannot find module '"+a+"'");throw s.code="MODULE_NOT_FOUND",s}var c=r[a]={exports:{}};e[a][0].call(c.exports,function(t){return i(e[a][1][t]||t)},c,c.exports,t,e,r,n)}return r[a].exports}for(var o="function"==typeof require&&require,a=0;a<n.length;a++)i(n[a]);return i}return t}()({1:[function(t,e,r){"use strict";var n,i=t("./libs/magnet"),o=(n=i,n&&n.__esModule?n:{default:n});e.exports=o.default,self&&self instanceof Object&&self===self.self&&(self.Magnet=o.default)},{"./libs/magnet":4}],2:[function(t,e,r){"use strict";Object.defineProperty(r,"__esModule",{value:!0});var n=t("./stdlib"),i={tt:"topToTop",rr:"rightToRight",bb:"bottomToBottom",ll:"leftToLeft",tb:"topToBottom",bt:"bottomToTop",rl:"rightToLeft",lr:"leftToRight",xx:"xCenter",yy:"yCenter"};r.default=Object.create(null,(0,n.objMap)(i,function(t,e){return{get:function(){return i[e]},set:function(t){if((0,n.objValues)(i).includes(t))throw new Error("Already assign property name: "+t);i[e]=t},enumerable:!0}}))},{"./stdlib":6}],3:[function(t,e,r){"use strict";Object.defineProperty(r,"__esModule",{value:!0});var n=function(){return function(t,e){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return function(t,e){var r=[],n=!0,i=!1,o=void 0;try{for(var a,l=t[Symbol.iterator]();!(n=(a=l.next()).done)&&(r.push(a.value),!e||r.length!==e);n=!0);}catch(t){i=!0,o=t}finally{try{!n&&l.return&&l.return()}finally{if(i)throw o}}return r}(t,e);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),i=t("./stdlib"),o=function(t){if((0,i.isstr)(t))t=t.split(" ");else if(!(0,i.isarray)(t))throw new Error("Invalid names: "+(0,i.tostr)(t));return t.map(function(t){return function(t){if(!(0,i.isstr)(t))throw new Error("Invalid name: "+(0,i.tostr)(t));var e=t.split("."),r=n(e,2),o=r[0],a=r[1];if(!(0,i.isset)(o))throw new Error("Illegal name: "+(0,i.tostr)(t));return[o,a]}(t)})};function a(t){if(!this instanceof a)return new(Function.prototype.bind.apply(a,[null].concat(Array.prototype.slice.call(arguments))));Object.defineProperties(this,{ref:{value:t},dom:{value:(0,i.iselem)(t)?t:document.createElement("eh")},events:{value:{}}})}["on","off","trigger"].forEach(function(t){a[t]=function(e){for(var r,n=arguments.length,o=Array(n>1?n-1:0),l=1;l<n;l++)o[l-1]=arguments[l];if(e instanceof a)e[t].apply(e,o);else if(!(0,i.iselem)(e))throw new Error("Invalid element: "+(0,i.tostr)(e));return e._eventHandler=(r=e._eventHandler||new a(e))[t].apply(r,o),a}}),a.prototype.on=function(t,e){var r=this;return e=function(t){if((0,i.isfunc)(t))return[t];if(!(0,i.isarray)(t))throw new Error("Invalid funcs: "+(0,i.tostr)(t));return t.map(function(t){if(!(0,i.isfunc)(t))throw new Error("Invaqlid func: "+(0,i.tostr)(t));return t})}(e),(0,i.isset)(this.ref)&&(e=e.map(function(t){return t.bind(r.ref)})),o(t).forEach(function(t){var i=n(t,2),o=i[0],a=i[1];e.forEach(function(t){return r.dom.addEventListener(o,t)}),r.events[o]=(r.events[o]||[]).concat(e.map(function(t){return{minor:a,func:t}}))}),this},a.prototype.off=function(t){var e=this;return o(t).forEach(function(t){var r=n(t,2),o=r[0],a=r[1],l=e.events[o]||[],u=[];if((0,i.isset)(a))for(var s=l.length-1;0<=s;s--){var c=l[s];a===c.minor&&(u.push(c),l.splice(s,1))}else l.splice(0,l.length).forEach(function(t){u.push(t)});u.forEach(function(t){var r=t.func;e.dom.removeEventListener(o,r)}),0===l.length&&delete e.events[o]}),this},a.prototype.trigger=function(t,e,r){var a=this;if((0,i.isset)(r)&&!(0,i.isfunc)(r))throw new Error("Invalid onPrevent function: "+(0,i.tostr)(r));return o(t).forEach(function(t){var o=n(t,2),l=o[0],u=o[1],s=!1;if((0,i.isset)(u))for(var c=!1,f=function(){return s=!0},d=function(){return c=!0},h=(a.events[l]||[]).filter(function(t){return t.minor===u}),p=0;!c&&p<h.length;p++)!1===h[p].func({detail:e,preventDefault:f,stopImmediatePropagation:d})&&(s=!0);else{var g=document.createEvent("CustomEvent");g.initCustomEvent(l,!0,!0,e),!1===a.dom.dispatchEvent(g)&&(s=!0)}s&&r&&r(l)}),this},r.default=a},{"./stdlib":6}],4:[function(t,e,r){"use strict";Object.defineProperty(r,"__esModule",{value:!0}),r.MAGNET_DEFAULTS=void 0;var n=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var r=arguments[e];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(t[n]=r[n])}return t},i=function(){return function(t,e){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return function(t,e){var r=[],n=!0,i=!1,o=void 0;try{for(var a,l=t[Symbol.iterator]();!(n=(a=l.next()).done)&&(r.push(a.value),!e||r.length!==e);n=!0);}catch(t){i=!0,o=t}finally{try{!n&&l.return&&l.return()}finally{if(i)throw o}}return r}(t,e);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),o=t("./stdlib"),a=s(t("./event-handler")),l=t("./rect"),u=s(t("./alignment-props"));function s(t){return t&&t.__esModule?t:{default:t}}function c(t,e,r){return e in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}function f(t){if(Array.isArray(t)){for(var e=0,r=Array(t.length);e<t.length;e++)r[e]=t[e];return r}return Array.from(t)}var d=[u.default.tb,u.default.rl,u.default.bt,u.default.lr],h=[u.default.tt,u.default.rr,u.default.bb,u.default.ll],p=[u.default.xx,u.default.yy],g="attract",y="unattract",v="attracted",b="unattracted",m="attractstart",w="attractmove",R="attractend",E=["magnetenter","magnetstart","enter","start"],A=["magnetchange","change"],x=["magnetend","magnetleave","end","leave"],_=["mousedown","touchstart"],I=["mousemove","touchmove"],j=["mouseup","mouseleave","touchend"],O="keydown",P="keyup",D=function(t){return t+"px"},C=function(t){return 100*t+"%"},M=function(t){var e=t.clientX,r=t.clientY,n=t.touches,o=i(n=void 0===n?[]:n,1)[0],a=(o=void 0===o?{}:o).clientX,l=void 0===a?e:a,u=o.clientY;return{x:l,y:void 0===u?r:u}},U=function t(e){for(var r=arguments.length,n=Array(r>1?r-1:0),i=1;i<r;i++)n[i-1]=arguments[i];var a=e[T.id];return n.reduce(function(r,n){return(0,o.isarray)(n)?r.concat(t.apply(void 0,[e].concat(f(n)))):r.concat(n.split(" ").map(function(t){return t+"."+a}))},[])},S=function(t){for(var e=t.parentElement;e;e=e.parentElement)if("static"!==(0,o.getStyle)(e).position)return e;return document},T={id:"_id",temp:"_temp",targets:"_targets",eventHandler:"_eventHandler",manualHandler:"_manualHandler",distance:"_distance",attractable:"_attractable",allowCtrlKey:"_allowCtrlKey",allowDrag:"_allowDrag",useRelativeUnit:"_useRelativeUnit",stayInParent:"_stayInParent",alignOuter:"_alignOuter",alignInner:"_alignInner",alignCenter:"_alignCenter",alignParentCenter:"_alignParentCenter"},k=r.MAGNET_DEFAULTS={distance:0,attractable:!0,allowCtrlKey:!0,allowDrag:!0,useRelativeUnit:!1,stayInParent:!1,alignOuter:!0,alignInner:!0,alignCenter:!0,alignParentCenter:!1};function F(){for(var t,e=this,r=arguments.length,n=Array(r),i=0;i<r;i++)n[i]=arguments[i];if(!this instanceof F)return new(Function.prototype.bind.apply(F,[null].concat(Array.prototype.slice.call(arguments))));Object.defineProperties(this,(c(t={},T.id,{value:"magnet_"+Date.now()}),c(t,T.temp,{value:[],writable:!0}),c(t,T.targets,{value:[],writable:!0}),c(t,T.eventHandler,{value:new a.default(this)}),c(t,T.manualHandler,{value:{},writable:!0}),c(t,T.distance,{value:0,writable:!0}),c(t,T.attractable,{value:!0,writable:!0}),c(t,T.allowCtrlKey,{value:!0,writable:!0}),c(t,T.allowDrag,{value:!0,writable:!0}),c(t,T.useRelativeUnit,{value:!1,writable:!0}),c(t,T.stayInParent,{value:!1,writable:!0}),c(t,T.alignOuter,{value:!0,writable:!0}),c(t,T.alignInner,{value:!0,writable:!0}),c(t,T.alignCenter,{value:!0,writable:!0}),c(t,T.alignParentCenter,{value:!1,writable:!0}),t)),(0,o.objForEach)(k,function(t,r){return(0,o.isset)(e[r])&&e[r](t)}),n.length&&this.add(n)}F.prototype.getDistance=function(){return this[T.distance]},F.prototype.setDistance=function(t){if(isNaN(t))throw new Error("Invalid distance: "+(0,o.tostr)(t));if(t<0)throw new Error("Illegal distance: "+t);return this[T.distance]=(0,o.tonum)(t),this},F.prototype.distance=function(t){return(0,o.isset)(t)?this.setDistance(t):this.getDistance()},F.prototype.getAttractable=function(){return this[T.attractable]},F.prototype.setAttractable=function(t){return this[T.attractable]=(0,o.tobool)(t),this},F.prototype.attractable=function(t){return(0,o.isset)(t)?this.setAttractable(t):this.getAttractable()},F.prototype.getAllowCtrlKey=function(){return this[T.allowCtrlKey]},F.prototype.setAllowCtrlKey=function(t){return this[T.allowCtrlKey]=(0,o.tobool)(t),this},F.prototype.allowCtrlKey=function(t){return(0,o.isset)(t)?this.setAllowCtrlKey(t):this.getAllowCtrlKey()},F.prototype.getAllowDrag=function(){return this[T.allowDrag]},F.prototype.setAllowDrag=function(t){return this[T.allowDrag]=(0,o.tobool)(t),this},F.prototype.allowDrag=function(t){return(0,o.isset)(t)?this.setAllowDrag(t):this.getAllowDrag()},F.prototype.getUseRelativeUnit=function(){return this[T.useRelativeUnit]},F.prototype.setUseRelativeUnit=function(t){var e=this;return t=(0,o.tobool)(t),this[T.useRelativeUnit]!==t&&((0,o.stdDoms)(this[T.targets]).forEach(function(t){return e.setMemberRectangle(t)}),this[T.useRelativeUnit]=t),this},F.prototype.useRelativeUnit=function(t){return(0,o.isset)(t)?this.setUseRelativeUnit(t):this.getUseRelativeUnit()},F.prototype.getStayInParent=function(){return this[T.stayInParent]},F.prototype.setStayInParent=function(t){return this[T.stayInParent]=(0,o.tobool)(t),this},F.prototype.stayInParent=F.prototype.stayInParentEdge=F.prototype.stayInParentElem=function(t){return(0,o.isset)(t)?this.setStayInParent(t):this.getStayInParent()},["Outer","Inner","Center","ParentCenter"].forEach(function(t){var e="align"+t,r="Align"+t;F.prototype["get"+r]=function(){return this[T[e]]},F.prototype["set"+r]=function(t){return this[T[e]]=(0,o.tobool)(t),this},F.prototype[e]=F.prototype["enabled"+r]=function(t){return(0,o.isset)(t)?this["set"+r](t):this["get"+r]()}}),["on","off"].forEach(function(t){F.prototype[t]=function(){var e;return(e=this[T.eventHandler])[t].apply(e,arguments),this}}),F.prototype.check=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:(0,l.stdRect)(t),r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:[].concat(this.getAlignOuter()?d:[],this.getAlignInner()?h:[],this.getAlignCenter()?p:[]);if(!(0,o.iselem)(t))throw new Error("Invalid DOM: "+(0,o.tostr)(t));(0,o.isarray)(e)&&(r=e,e=(0,l.stdRect)(t));var n=S(t),i=(0,l.stdRect)(n),a=this[T.targets].filter(function(e){return e!==t}).map(function(t){return(0,l.diffRect)(e,t,{alignments:r})}),u=a.reduce(function(t,e){return(0,o.objForEach)(e.results,function(r,n){t[n]=t[n]||[],t[n].push(e)}),t},{}),s=(0,o.objMap)(u,function(t,e){return t.concat().sort(function(t,r){return t.results[e]-r.results[e]})});return{source:{rect:e,element:t},parent:{rect:i,element:n},targets:a,results:u,rankings:s,mins:(0,o.objMap)(s,function(t){return t[0]}),maxs:(0,o.objMap)(s,function(t){return t[t.length-1]})}};var K=function(t,e){return!t.includes(e)&&t.push(e)},H=function(t){if(t){var e=t.prop,r=t.target,n=r.rect,o=r.element,a=function(t,r){var n=t.top,i=t.right,o=t.bottom,a=t.left,l=r.top,s=r.left;switch(e){case u.default.tt:case u.default.bt:return[n,l];case u.default.bb:case u.default.tb:return[o,l];case u.default.rr:case u.default.lr:return[i,s];case u.default.ll:case u.default.rl:return[a,s];case u.default.xx:return[(i+a)/2,s];case u.default.yy:return[(n+o)/2,l]}}(n,(0,l.stdRect)(S(o))),s=i(a,2),c=s[0],f=s[1];return{type:e,rect:n,element:o,position:c,offset:c-f}}return null},N=function(t,e){return!!t!=!!e||(t?t.target.element:null)!==(e?e.target.element:null)};F.prototype.handle=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:(0,l.stdRect)(t),r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.getAttractable();if(!(0,o.iselem)(t))throw new Error("Invalid DOM: "+(0,o.tostr)(t));var i=L(this,t);if(-1===i)throw new Error("Invalid member: "+(0,o.tostr)(t));(0,o.isbool)(e)&&(r=e,e=t),e=(0,l.stdRect)(e);var s=this[T.temp][i],c=s._lastAttractedX,f=s._lastAttractedY,d=e,m=d.top,R=d.left,_=d.width,I=d.height,j=r?this.getDistance():0,O=this.check(t,e,r?void 0:[]),P=O.parent,D=O.targets,C=P.rect,M=P.element,U={x:R,y:m},S=D.concat(this.getStayInParent()?(0,l.diffRect)(e,M,{alignments:h,absDistance:!1}):[],this.getAlignParentCenter()?(0,l.diffRect)(e,M,{alignments:p}):[]).reduce(function(t,e){var r=t.x,n=t.y,i=e.target,o=e.results;return e.ranking.reduce(function(t,e){var r=t.x,n=t.y,a=o[e];if(a<=j)switch(e){case u.default.rr:case u.default.ll:case u.default.rl:case u.default.lr:case u.default.xx:(!r||a<r.value)&&(r={prop:e,value:a,target:i});break;case u.default.tt:case u.default.bb:case u.default.tb:case u.default.bt:case u.default.yy:(!n||a<n.value)&&(n={prop:e,value:a,target:i})}return{x:r,y:n}},{x:r,y:n})},{x:null,y:null}),k=S.x,F=S.y,W=[],q=[];if(k){var X=k.prop,Y=k.target.rect;switch(X){case u.default.rr:U.x=Y.right-_;break;case u.default.ll:U.x=Y.left;break;case u.default.rl:U.x=Y.left-_;break;case u.default.lr:U.x=Y.right;break;case u.default.xx:U.x=(Y.left+Y.right-_)/2}}if(F){var B=F.prop,V=F.target.rect;switch(B){case u.default.tt:U.y=V.top;break;case u.default.bb:U.y=V.bottom-I;break;case u.default.tb:U.y=V.bottom;break;case u.default.bt:U.y=V.top-I;break;case u.default.yy:U.y=(V.top+V.bottom-I)/2}}var z=N(c,k),G=N(f,F);z&&(k&&K(W,k.target.element),c&&K(q,c.target.element)),G&&(F&&K(W,F.target.element),f&&K(q,f.target.element)),W.forEach(function(e){return a.default.trigger(e,v,t)}),q.forEach(function(e){return a.default.trigger(e,b,t)});var J=!(!k&&!F),Q=!(!c&&!f),Z=this[T.eventHandler],$={x:H(k),y:H(F)},tt={x:H(c),y:H(f)};if(J){var et=z||G;Q?et||!z&&k&&c&&k.prop!==c.prop||!G&&F&&f&&F.prop!==f.prop?(Z.trigger(A,n({source:t},$)),a.default.trigger(t,g,$)):et&&Z.trigger(A,n({source:t},$)):(Z.trigger(A,n({source:t},$)),Z.trigger(E,n({source:t},$)),a.default.trigger(t,g,$)),et&&a.default.trigger(t,y,tt)}else Q&&(Z.trigger(A,{source:t,x:null,y:null}),Z.trigger(x,n({source:t},tt)),a.default.trigger(t,y,tt));var rt,nt,it=this[T.manualHandler],ot=it.beforeAttract,at=it.afterAttract,lt=it.doAttract,ut=(rt=U.x-C.left,nt=U.y-C.top,(0,l.stdRect)({top:nt,right:rt+_,bottom:nt+I,left:rt,width:_,height:I}));if((0,o.isfunc)(ot)){var st=ot.bind(this)(t,{origin:(0,l.stdRect)(e),target:(0,l.stdRect)(ut)},{current:$,last:tt});if((0,l.isRect)(st))ut=st;else if((0,o.isbool)(st)&&!1===st)ut=e;else if((0,o.isset)(st))throw new Error("Invalid return value: "+(0,o.tostr)(st))}return a.default.trigger(t,w,{rects:{origin:(0,l.stdRect)(e),target:(0,l.stdRect)(ut)},attracts:{current:$,last:tt}},function(){ut=e}),(0,o.isfunc)(lt)?lt.bind(this)(t,{origin:(0,l.stdRect)(e),target:(0,l.stdRect)(ut)},{current:$,last:tt}):this.setMemberRectangle(t,ut),(0,o.isfunc)(at)&&at.bind(this)(t,{origin:(0,l.stdRect)(e),target:(0,l.stdRect)(ut)},{current:$,last:tt}),this[T.temp][i]={_lastAttractedX:k,_lastAttractedY:F},this},F.prototype.setMemberRectangle=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:(0,l.stdRect)(t),r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.getUseRelativeUnit();if(!(0,o.iselem)(t))throw new Error("Invalid DOM: "+(0,o.tostr)(t));if(!this.hasMember(t))throw new Error("Invalid member: "+(0,o.tostr)(t));(0,o.isbool)(e)&&(r=e,e=(0,l.stdRect)(t)),e=(0,l.stdRect)({right:e.right,bottom:e.bottom,width:e.width,height:e.height}),console.log(e);var n=e,i=n.top,a=n.left,u=n.width,s=n.height;if(r){var c=(0,l.stdRect)(S(t)),f=c.width,d=c.height;t.style.top=C(i/d),t.style.left=C(a/f),t.style.width=C(u/f),t.style.height=C(s/d)}else t.style.top=D(i),t.style.left=D(a),t.style.width=D(u),t.style.height=D(s);return t.style.position="absolute",t.style.right="auto",t.style.bottom="auto",this},["before","after","do"].forEach(function(t){var e=t+"Attract";Object.defineProperty(F.prototype,e,{get:function(){return this[T.manualHandler][e]},set:function(t){this[T.manualHandler][e]=t}})}),F.prototype.add=function(){for(var t=this,e=arguments.length,r=Array(e),n=0;n<e;n++)r[n]=arguments[n];return r=o.stdDoms.apply(void 0,f(r)),[window,document,document.body].forEach(function(t){if(r.includes(t))throw new Error("Illegal element: "+(0,o.tostr)(src))}),r.forEach(function(e){t[T.targets].includes(e)||(a.default.on(e,U(t,_),function(r){if(t.getAllowDrag()){r.preventDefault();var n=!r.ctrlKey,i=r,o=(0,l.stdRect)(e),u=o.left,s=o.top,c=o.width,f=o.height,d=M(r),h=d.x,p=d.y,g=function(r){var i=!!t.getAttractable()&&(!t.getAllowCtrlKey()||n),o=M(r),a=o.x,d=o.y,g=u+(a-h),y=s+(d-p),v=(0,l.stdRect)({top:y,right:g+c,bottom:y+f,left:g});t.handle(e,v,i)};a.default.trigger(e,m,(0,l.stdRect)(e)),a.default.off(document.body,U(t,I,j,O,P)),a.default.on(document.body,U(t,O,P),function(t){var e=!t.ctrlKey;e!==n&&(n=e,g(i))}),a.default.on(document.body,U(t,j),function(){var r=[],n=L(t,e),i=t[T.temp][n],o=i._lastAttractedX,u=i._lastAttractedY;if(a.default.off(document.body,U(t,I,j,O,P)),o&&K(r,o.target.element),u&&K(r,u.target.element),r.forEach(function(t){return a.default.trigger(t,b,e)}),a.default.trigger(e,R,(0,l.stdRect)(e)),o||u){var s=t[T.eventHandler];a.default.trigger(e,y),s.trigger(A,{source:e,x:null,y:null}),s.trigger(x,{source:e})}t[T.temp][n]={}}),a.default.on(document.body,U(t,I),function(t){g(t),i=t})}}),t[T.targets].push(e),t[T.temp].push({}),t.setMemberRectangle(e))}),this};var L=function(t,e){return t[T.targets].indexOf(e)};F.prototype.hasMember=function(t){return-1!==L(this,t)};var W=function(t){for(var e=arguments.length,r=Array(e>1?e-1:0),n=1;n<e;n++)r[n-1]=arguments[n];return o.stdDoms.apply(void 0,r).reduce(function(e,r){var n=t[T.targets],i=t[T.temp],o=n.indexOf(r);return-1!==o&&(n.splice(o,1),i.splice(o,1),a.default.off(r,U(t,_)),e.push(r)),e},[])};F.prototype.remove=function(){for(var t=arguments.length,e=Array(t),r=0;r<t;r++)e[r]=arguments[r];return W.apply(void 0,[this].concat(e)),this},F.prototype.removeFull=function(){for(var t=arguments.length,e=Array(t),r=0;r<t;r++)e[r]=arguments[r];return W.apply(void 0,[this].concat(e)).forEach(function(t){t.style.position="",t.style.top="",t.style.right="",t.style.bottom="",t.style.left="",t.style.width="",t.style.height="",t.style.zIndex=""}),this};var q=function(t){var e=t[T.targets];return t[T.temp]=[],e.splice(0,e.length).map(function(e){return a.default.off(e,U(t,_)),e})};F.prototype.clear=function(){return q(this),this},F.prototype.clearFull=function(){return q(this).forEach(function(t){t.style.position="",t.style.top="",t.style.right="",t.style.bottom="",t.style.left="",t.style.width="",t.style.height="",t.style.zIndex=""}),this},F.isRect=function(t){return(0,l.isRect)(t)},F.stdRect=function(t){return(0,l.stdRect)(t)},F.measure=F.diffRect=function(t,e){for(var r=arguments.length,n=Array(r>2?r-2:0),i=2;i<r;i++)n[i-2]=arguments[i];return l.diffRect.apply(void 0,[t,e].concat(n))},r.default=F},{"./alignment-props":2,"./event-handler":3,"./rect":5,"./stdlib":6}],5:[function(t,e,r){"use strict";Object.defineProperty(r,"__esModule",{value:!0}),r.diffRect=r.stdRect=r.isRect=void 0;var n,i=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var r=arguments[e];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(t[n]=r[n])}return t},o=t("./stdlib"),a=t("./alignment-props"),l=(n=a,n&&n.__esModule?n:{default:n});var u=r.isRect=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1e-10;if(!(0,o.isset)(t))return!1;var r=t.x,n=t.y,i=t.top,a=void 0===i?(0,o.isset)(a)?a:n:i,l=t.right,u=t.bottom,s=t.left,c=void 0===s?(0,o.isset)(c)?c:r:s,f=t.width,d=t.height,h=function(t){return!((0,o.isset)(t)&&!(0,o.isnum)(t))};if(!(h(a)&&h(l)&&h(u)&&h(c)&&h(f)&&h(d)&&h(r)&&h(n)))return!1;if((0,o.isset)(f)){if(f<0)return!1;if((0,o.isset)(c)){if((0,o.isset)(l)&&e<Math.abs(f-(l-c)))return!1}else if(!(0,o.isset)(l))return!1}else if(!(0,o.isset)(c)||!(0,o.isset)(l)||l<c)return!1;if((0,o.isset)(d)){if(d<0)return!1;if((0,o.isset)(a)){if((0,o.isset)(u)&&e<Math.abs(d-(u-a)))return!1}else if(!(0,o.isset)(u))return!1}else if(!(0,o.isset)(a)||!(0,o.isset)(u)||u<a)return!1;return!0},s=r.stdRect=function(t){if(u(t)){var e=t.x,r=t.y,n=t.right,i=t.bottom,a=t.width,l=t.height,s=t.top,c=void 0===s?(0,o.isset)(c)?c:(0,o.isset)(r)?r:i-l:s,f=t.left,d=void 0===f?(0,o.isset)(d)?d:(0,o.isset)(e)?e:n-a:f;return{top:c,left:d,x:(0,o.isset)(e)?e:d,y:(0,o.isset)(r)?r:c,right:(0,o.isset)(n)?n:d+a,bottom:(0,o.isset)(i)?i:c+l,width:(0,o.isset)(a)?a:n-d,height:(0,o.isset)(l)?l:i-c}}if((0,o.iselem)(t)){var h=t instanceof Element?{rect:t.getBoundingClientRect(),border:(w=(0,o.getStyle)(t),{t:w.borderTopWidth,r:w.borderRightWidth,b:w.borderBottomWidth,l:w.borderLeftWidth})}:{rect:{top:0,right:window.innerWidth,bottom:window.innerHeight,left:0},border:{t:0,r:0,b:0,l:0}},p=h.rect,g=h.border,y=p.top+parseFloat(g.t),v=p.right-parseFloat(g.r),b=p.bottom-parseFloat(g.b),m=p.left+parseFloat(g.l);return{top:y,right:v,bottom:b,left:m,width:v-m,height:b-y,x:m,y:y}}throw new Error("Invalid element to rectangle: "+(0,o.tostr)(t));var w},c=r.diffRect=function(t,e){var r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},n=r.alignments,a=void 0===n?(0,o.objValues)(l.default):n,u=r.absDistance,c=void 0===u||u,f=s(t),d=s(e),h={rect:f},p={rect:d},g=c?Math.abs:function(t){return t},y=(0,o.objMap)((0,o.objReduce)(l.default,function(t,e){return a.includes(e)?i({},t,(o=NaN,(n=e)in(r={})?Object.defineProperty(r,n,{value:o,enumerable:!0,configurable:!0,writable:!0}):r[n]=o,r)):t;var r,n,o},{}),function(t,e){switch(e){case l.default.tt:return g(f.top-d.top);case l.default.bb:return g(d.bottom-f.bottom);case l.default.rr:return g(d.right-f.right);case l.default.ll:return g(f.left-d.left);case l.default.tb:return g(f.top-d.bottom);case l.default.bt:return g(d.top-f.bottom);case l.default.rl:return g(d.left-f.right);case l.default.lr:return g(f.left-d.right);case l.default.xx:return g((f.right-d.right+(f.left-d.left))/2);case l.default.yy:return g((f.top-d.top+(f.bottom-d.bottom))/2)}}),v=(0,o.objKeys)(y).sort(function(t,e){return y[t]-y[e]});return(0,o.iselem)(t)&&(h.element=t),(0,o.iselem)(e)&&(p.element=e),{source:h,target:p,results:y,ranking:v,min:v[0],max:v[y.length-1]}};function f(t){var e=this;if(!this instanceof f)return new(Function.prototype.bind.apply(f,[null].concat(Array.prototype.slice.call(arguments))));objForEach(s(t),function(t,r){return e[r]=t})}f.stdRect=s,f.diffRect=c,f.prototype.diff=function(t){for(var e=arguments.length,r=Array(e>1?e-1:0),n=1;n<e;n++)r[n-1]=arguments[n];return c.apply(void 0,[this,t].concat(r))}},{"./alignment-props":2,"./stdlib":6}],6:[function(t,e,r){"use strict";Object.defineProperty(r,"__esModule",{value:!0});var n=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var r=arguments[e];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(t[n]=r[n])}return t};var i=r.isset=function(t){return void 0!==t},o=(r.useor=function(t,e){return(arguments.length>2&&void 0!==arguments[2]?arguments[2]:i)(t)?t:e},r.isbool=function(t){return"boolean"==typeof t},r.tobool=function(t){return!!t},r.isnum=function(t){return!isNaN(t)}),a=(r.tonum=function(t){return parseFloat(t)},r.isint=function(t){return o(t)&&t===(0|t)}),l=(r.isstr=function(t){return"string"==typeof t||i(t)&&t instanceof String},r.tostr=function(t){return i(t)?t.toString():""}),u=(r.isfunc=function(t){return"function"==typeof t},r.isarray=function(t){return i(t)&&Array.isArray(t)}),s=r.arrayable=function(t){return t&&a(t.length)&&0<=t.length},c=r.toarray=function(t){return Array.prototype.slice.call(t)},f=r.objKeys=function(t){return Object.keys(t)},d=(r.objForEach=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:function(){},r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:void 0;return f(t).forEach(function(n){return e.call(r,t[n],n,t)})},r.objReduce=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:function(){},r=arguments[2];return f(t).reduce(function(r,n){return e(r,t[n],n,t)},r)}),h=(r.objMap=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:function(){},r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:void 0;return d(t,function(i,o,a){return n({},i,(l={},u=a,s=e.call(r,o,a,t),u in l?Object.defineProperty(l,u,{value:s,enumerable:!0,configurable:!0,writable:!0}):l[u]=s,l));var l,u,s},{})},r.objValues=function(t){return d(t,function(t,e){return t.concat([e])},[])},r.iselem=function(t){return i(t)&&(t instanceof Element||t instanceof Window||t instanceof Document)});r.getStyle=function(t){return t.currentStyle||window.getComputedStyle(t)},r.stdDoms=function t(){for(var e=arguments.length,r=Array(e),n=0;n<e;n++)r[n]=arguments[n];return r.reduce(function(e,r){if(h(r))return e.includes(r)?e:e.concat(r);if(u(r))return r.reduce(function(e,r){return e.concat(t(r))},e);if(s(r))return e.concat(t(c(r)));throw new Error("Invalid element: "+l(r))},[])}},{}]},{},[1]);


    const magnet = new Magnet();

    magnet.distance(15);
    magnet.attractable(true);
    magnet.allowCtrlKey(true);
    magnet.alignOuter(true);
    magnet.alignInner(true);
    magnet.alignCenter(true);
    let domContainer = document.getElementById('div_displayObject');

    function intializeMagnet() {
      let domMask = document.getElementById('lines');
      let domHoriMagnet = domMask.querySelector('.hori');
      let domVertMagnet = domMask.querySelector('.vert');

      // start/end of magnet attract status
      magnet.on('start change end', ({ type }) => {
        console.log(`magnet${type}`);
      }).on('end', () => {
        domHoriMagnet.classList.remove('show');
        domVertMagnet.classList.remove('show');
      }).on('change', (e) => {
        // show/hide horizon/vertical edge line
        let result = e.detail;
        let resultX = result.x;
        let resultY = result.y;
        if (resultX) {
          domVertMagnet.style.left = (resultX.position+'px');
          domVertMagnet.classList.add('show');
        } else {
          domVertMagnet.classList.remove('show');
        }
        if (resultY) {
          domHoriMagnet.style.top = (resultY.position+'px');
          domHoriMagnet.classList.add('show');
        } else {
          domHoriMagnet.classList.remove('show');
        }
      });

      magnet.setUseRelativeUnit(true);
    }
    let blocks = $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget');
    for (let i = 0; i < blocks.length; i++) {
      let block = blocks[i];
      magnet.add(block);

      block.addEventListener('mousedown', function(e) {
        this.style.zIndex = 10;
      });
      block.addEventListener('click', function() {
        this.style.zIndex = 1;
        this.parentElement.appendChild(this);
      });
      block.addEventListener('dblclick', function() {
        let checkbox = this.querySelector('input[type=checkbox]');
        checkbox.checked = !checkbox.checked;
        if (checkbox.checked) {
          magnet.add(this);
        } else {
          magnet.remove(this);
        }
      });
      ['attract', 'unattract', 'attracted', 'unattracted'].forEach((type) => {
        block.addEventListener(type, function(e) {
          console.log(e.type, e);
        });
      });
    }
    intializeMagnet();


    // $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').draggable({
    //   snap: (editOption.snap == 1),
    //   grid: (editOption.grid == 1) ? editOption.gridSize : false,
    //   containment: 'parent',
    //   cancel: '.locked',
    //   stop: function (event, ui) {
    //     savePlanAction(false, false);
    //   }
    // });
    if (editOption.highlight) {
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').addClass('widget-shadow-edit');
    } else {
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').removeClass('widget-shadow-edit').removeClass('contextMenu_select');
    }

    $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').resizable({
      containment: "parent",
      cancel: '.locked',
      stop: function (event, ui) {
        savePlanAction(false, false);
      }
    });
    $('.div_displayObject a').each(function () {
      if ($(this).attr('href') != '#') {
        $(this).attr('data-href', $(this).attr('href')).removeAttr('href');
      }
    });
    try {
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').contextMenu(true);
    } catch (e) {

    }
    let bt_editPlanAction = document.getElementById('bt_editPlanAction');
    bt_editPlanAction.removeClass('fa-pencil-alt');
    bt_editPlanAction.addClass('fa-trash');
  } else {
    try {
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').draggable("destroy");
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').removeClass('widget-shadow-edit');
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').resizable("destroy");
      $('.div_displayObject a').each(function () {
        $(this).attr('href', $(this).attr('data-href'));
      });
    } catch (e) {

    }
    let bt_editPlanAction = document.getElementById('bt_editPlanAction');
    bt_editPlanAction.addClass('fa-pencil-alt');
    bt_editPlanAction.removeClass('fa-trash');
    try {
      $('.plan-link-widget,.view-link-widget,.graph-widget,.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').contextMenu(false);
    } catch (e) {

    }
  }
}

function addObject(_plan) {
  _plan.planHeader_id = planHeader_id;
  nextdom.plan.create({
    plan: _plan,
    version: 'dplan',
    error: function (error) {
      notify("Erreur", error.message, 'error');
    },
    success: function (data) {
      displayObject(data.plan, data.html);
    }
  });
}

function displayPlan(_code) {
  if (planHeader_id == -1) {
    return;
  }
  if (typeof _code == "undefined") {
    _code = null;
  }
  if (getUrlVars('fullscreen') == 1) {
    fullScreen(true);
  }
  nextdom.plan.getHeader({
    id: planHeader_id,
    code: _code,
    error: function (error) {
      if (error.code == -32005) {
        var result = prompt("{{Veuillez indiquer le code ?}}", "")
        if (result == null) {
          notify("Erreur", error.message, 'error');
          return;
        }
        displayPlan(result);
      } else {
        notify("Erreur", error.message, 'error');
      }
    },
    success: function (data) {
      $('.div_displayObject').empty();
      $('.div_displayObject').append('<div class="container-fluid div_grid" style="display:none;position: absolute;padding:0;width:100%;height:100%;user-select: none;-khtml-user-select: none;-o-user-select: none;-moz-user-select: -moz-none;-webkit-user-select: none;"></div>');
      $('.div_displayObject').height('auto').width('auto');
      if (isset(data.image)) {
        $('.div_displayObject').append(data.image);
      }
      $('.div_backgroundPlan').height($('body').height());
      if (isset(data.configuration.backgroundTransparent) && data.configuration.backgroundTransparent == 1) {
        $('.div_backgroundPlan').css('background-color', 'transparent');
      } else if (isset(data.configuration.backgroundColor)) {
        $('.div_backgroundPlan').css('background-color', data.configuration.backgroundColor);
      } else {
        $('.div_backgroundPlan').css('background-color', '#ffffff');
      }
      if (data.configuration != null && init(data.configuration.desktopSizeX) != '' && init(data.configuration.desktopSizeY) != '') {
        $('.div_displayObject').height(data.configuration.desktopSizeY).width(data.configuration.desktopSizeX);
        $('.div_displayObject img').height(data.configuration.desktopSizeY).width(data.configuration.desktopSizeX);
      } else {
        $('.div_displayObject').width($('.div_displayObject img').attr('data-sixe_x')).height($('.div_displayObject img').attr('data-sixe_y'));
        $('.div_displayObject img').css('height', ($('.div_displayObject img').attr('data-sixe_y')) + 'px').css('width', ($('.div_displayObject img').attr('data-sixe_x')) + 'px');
      }
      $('.div_grid').width($('.div_displayObject').width()).height($('.div_displayObject').height());
      $('.div_displayObject').find('.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.plan-link-widget,.view-link-widget,.graph-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').remove();
      nextdom.plan.byPlanHeader({
        id: planHeader_id,
        error: function (error) {
          notify("Erreur", error.message, 'error');
        },
        success: function (plans) {
          var objects = [];
          for (var i in plans) {
            objects.push(displayObject(plans[i].plan, plans[i].html, true));
          }
          try {
            $('.div_displayObject').append(objects);
          } catch (e) {

          }
          initEditOption(editOption.state);
          initReportMode();
        }
      });
    },
  });
}

function getObjectInfo(_object) {
  if (_object.hasClass('eqLogic-widget')) {
    return {type: 'eqLogic', id: _object.attr('data-eqLogic_id')};
  }
  if (_object.hasClass('cmd-widget')) {
    return {type: 'cmd', id: _object.attr('data-cmd_id')};
  }
  if (_object.hasClass('scenario-widget')) {
    return {type: 'scenario', id: _object.attr('data-scenario_id')};
  }
  if (_object.hasClass('plan-link-widget')) {
    return {type: 'plan', id: _object.attr('data-link_id')};
  }
  if (_object.hasClass('view-link-widget')) {
    return {type: 'view', id: _object.attr('data-link_id')};
  }
  if (_object.hasClass('graph-widget')) {
    return {type: 'graph', id: _object.attr('data-graph_id')};
  }
  if (_object.hasClass('text-widget')) {
    return {type: 'text', id: _object.attr('data-text_id')};
  }
  if (_object.hasClass('image-widget')) {
    return {type: 'image', id: _object.attr('data-image_id')};
  }
  if (_object.hasClass('zone-widget')) {
    return {type: 'zone', id: _object.attr('data-zone_id')};
  }
  if (_object.hasClass('summary-widget')) {
    return {type: 'summary', id: _object.attr('data-summary_id')};
  }
}

function savePlanAction(_refreshDisplay, _async) {
  var plans = [];
  $('.div_displayObject >.eqLogic-widget,.div_displayObject > .cmd-widget,.scenario-widget,.plan-link-widget,.view-link-widget,.graph-widget,.text-widget,.image-widget,.zone-widget,.summary-widget').each(function () {
    var info = getObjectInfo($(this));
    var plan = {};
    plan.position = {};
    plan.display = {};
    plan.id = $(this).attr('data-plan_id');
    plan.link_type = info.type;
    plan.link_id = info.id;
    plan.planHeader_id = planHeader_id;
    plan.display.height = $(this).outerHeight();
    plan.display.width = $(this).outerWidth();
    if (info.type == 'graph') {
      plan.display.graph = json_decode($(this).find('.graphOptions').value());
    }
    if (!$(this).is(':visible')) {
      var position = $(this).show().position();
      $(this).hide();
    } else {
      var position = $(this).position();
    }
    plan.position.top = (((position.top)) / $('.div_displayObject').height()) * 100;
    plan.position.left = (((position.left)) / $('.div_displayObject').width()) * 100;
    plans.push(plan);
  });
  nextdom.plan.save({
    plans: plans,
    async: _async || true,
    global: false,
    error: function (error) {
      notify("Erreur", error.message, 'error');
    },
    success: function () {
      if (init(_refreshDisplay, false)) {
        displayPlan();
      }
    },
  });
}

function showConfigModal() {
  $('#md_modal').dialog({title: "{{Configuration du design}}"});
  $('#md_modal').load('index.php?v=d&modal=planHeader.configure&planHeader_id=' + planHeader_id).dialog('open');
}

function displayObject(_plan, _html, _noRender) {
  _plan = init(_plan, {});
  _plan.position = init(_plan.position, {});
  _plan.css = init(_plan.css, {});
  if (_plan.link_type == 'eqLogic' || _plan.link_type == 'scenario' || _plan.link_type == 'text' || _plan.link_type == 'image' || _plan.link_type == 'zone') {
    $('.div_displayObject .' + _plan.link_type + '-widget[data-' + _plan.link_type + '_id=' + _plan.link_id + ']').remove();
  } else if (_plan.link_type == 'view' || _plan.link_type == 'plan') {
    $('.div_displayObject .' + _plan.link_type + '-link-widget[data-link_id=' + _plan.link_id + ']').remove();
  } else if (_plan.link_type == 'cmd') {
    $('.div_displayObject > .cmd-widget[data-cmd_id=' + _plan.link_id + ']').remove();
  } else if (_plan.link_type == 'graph') {
    for (var i in nextdom.history.chart) {
      delete nextdom.history.chart[i];
    }
    $('.div_displayObject .graph-widget[data-graph_id=' + _plan.link_id + ']').remove();
  }
  var html = $(_html);
  html.attr('data-plan_id', _plan.id);
  html.addClass('nextdomAlreadyPosition');
  html.css('z-index', 1000);
  html.css('position', 'absolute');
  html.css('top', init(_plan.position.top, '10') * $('.div_displayObject').height() / 100);
  html.css('left', init(_plan.position.left, '10') * $('.div_displayObject').width() / 100);
  html.css('transform-origin', '0 0', 'important');
  html.css('transform', 'scale(' + init(_plan.css.zoom, 1) + ')');
  html.css('-webkit-transform-origin', '0 0');
  html.css('-webkit-transform', 'scale(' + init(_plan.css.zoom, 1) + ')');
  html.css('-moz-transform-origin', '0 0');
  html.css('-moz-transform', 'scale(' + init(_plan.css.zoom, 1) + ')');
  html.addClass('noResize');
  if (isset(_plan.display) && isset(_plan.display.width)) {
    html.css('width', init(_plan.display.width, 50));
  }
  if (isset(_plan.display) && isset(_plan.display.height)) {
    html.css('height', init(_plan.display.height, 50));
  }
  for (var key in _plan.css) {
    if (_plan.css[key] === '') {
      continue;
    }
    if (key == 'zoom' || key == 'rotate') {
      continue;
    }
    if (key == 'z-index' && _plan.css[key] < 999) {
      continue;
    }
    if (key == 'background-color') {
      if (isset(_plan.display) && (!isset(_plan.display['background-defaut']) || _plan.display['background-defaut'] != 1)) {
        if (isset(_plan.display['background-transparent']) && _plan.display['background-transparent'] == 1) {
          html.style('background-color', 'transparent', 'important');
          html.style('border-radius', '0px', 'important');
          html.style('box-shadow', 'none', 'important');
          if (_plan.link_type == 'eqLogic') {
            html.find('.widget-name').style('background-color', 'transparent', 'important');
          }
        } else {
          html.style(key, _plan.css[key], 'important');
        }
      }
      continue;
    } else if (key == 'color') {
      if (!isset(_plan.display) || !isset(_plan.display['color-defaut']) || _plan.display['color-defaut'] != 1) {
        html.style(key, _plan.css[key], 'important');
        if (_plan.link_type == 'eqLogic' || _plan.link_type == 'cmd' || _plan.link_type == 'summary') {
          html.find('*').each(function () {
            $(this).style(key, _plan.css[key], 'important')
          });
        }
      }
      continue;
    }
    if (key == 'opacity') {
      continue;
    }
    html.style(key, _plan.css[key], 'important');
  }
  if (_plan.css['opacity'] && _plan.css['opacity'] !== '') {
    html.css('background-color', html.css('background-color').replace(')', ',' + _plan.css['opacity'] + ')').replace('rgb', 'rgba'));
  }
  if (_plan.link_type == 'graph') {
    $('.div_displayObject').append(html);
    if (isset(_plan.display) && isset(_plan.display.graph)) {
      for (var i in _plan.display.graph) {
        if (init(_plan.display.graph[i].link_id) != '') {
          nextdom.history.drawChart({
            cmd_id: _plan.display.graph[i].link_id,
            el: 'graph' + _plan.link_id,
            showLegend: init(_plan.display.showLegend, true),
            showTimeSelector: init(_plan.display.showTimeSelector, false),
            showScrollbar: init(_plan.display.showScrollbar, true),
            dateRange: init(_plan.display.dateRange, '7 days'),
            option: init(_plan.display.graph[i].configuration, {}),
            transparentBackground: init(_plan.display.transparentBackground, false),
            showNavigator: init(_plan.display.showNavigator, true),
            enableExport: false,
            global: false,
          });
        }
      }
    }
    initEditOption(editOption.state);
    return;
  }
  if (init(_noRender, false)) {
    return html;
  }
  $('.div_displayObject').append(html);
  initEditOption(editOption.state);
  return;
}
