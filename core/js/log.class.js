
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


 nextdom.log = function () {
 };

 nextdom.log.currentAutoupdate = [];

 nextdom.log.get = function (_params) {
     var paramsRequired = ['log'];
     var paramsSpecifics = {
         global: _params.global || true,
     };
     try {
         nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
     } catch (e) {
         (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
         return;
     }
     var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
     var paramsAJAX = nextdom.private.getParamsAJAX(params);
     paramsAJAX.url = 'core/ajax/log.ajax.php';
     paramsAJAX.data = {
         action: 'get',
         log: _params.log
     };
     $.ajax(paramsAJAX);
 }

 nextdom.log.remove = function (_params) {
     var paramsRequired = ['log'];
     var paramsSpecifics = {
         global: _params.global || true,
     };
     try {
         nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
     } catch (e) {
         (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
         return;
     }
     var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
     var paramsAJAX = nextdom.private.getParamsAJAX(params);
     paramsAJAX.url = 'core/ajax/log.ajax.php';
     paramsAJAX.data = {
         action: 'remove',
         log: _params.log
     };
     $.ajax(paramsAJAX);
 }

 nextdom.log.clear = function (_params) {
     var paramsRequired = ['log'];
     var paramsSpecifics = {
         global: _params.global || true,
     };
     try {
         nextdom.private.checkParamsRequired(_params || {}, paramsRequired);
     } catch (e) {
         (_params.error || paramsSpecifics.error || nextdom.private.default_params.error)(e);
         return;
     }
     var params = $.extend({}, nextdom.private.default_params, paramsSpecifics, _params || {});
     var paramsAJAX = nextdom.private.getParamsAJAX(params);
     paramsAJAX.url = 'core/ajax/log.ajax.php';
     paramsAJAX.data = {
         action: 'clear',
         log: _params.log
     };
     $.ajax(paramsAJAX);
 }

 nextdom.log.autoupdate = function (_params) {
     if(!isset(_params.callNumber)){
         _params.callNumber = 0;
     }
     if(!isset(_params.log)){
         console.log('[nextdom.log.autoupdate] No logfile');
         return;
     }
     if(!isset(_params.display)){
         console.log('[nextdom.log.autoupdate] No display');
         return;
     }
     if (!_params['display'].is(':visible')) {
         return;
     }
     if(_params.callNumber > 0 && isset(_params['control']) && _params['control'].attr('data-state') != 1){
         return;
     }
     if(_params.callNumber > 0 && isset(nextdom.log.currentAutoupdate[_params.display.uniqueId().attr('id')]) && nextdom.log.currentAutoupdate[_params.display.uniqueId().attr('id')].log != _params.log){
         return;
     }
     if(_params.callNumber == 0){
         if(isset(_params.default_search)){
             _params['search'].value(_params.default_search);
         }else{
             _params['search'].value('');
         }
         _params.display.scrollTop(_params.display.height() + 200000);
         if(_params['control'].attr('data-state') == 0){
             _params['control'].attr('data-state',1);
         }
         _params['control'].off('click').on('click',function(){
             if($(this).attr('data-state') == 1){
                 $(this).attr('data-state',0);
                 $(this).removeClass('btn-warning').addClass('btn-success');
                 $(this).html('<i class="fa fa-play">&nbsp;&nbsp;</i>{{Reprise}}');
             }else{
                 $(this).removeClass('btn-success').addClass('btn-warning');
                 $(this).html('<i class="fa fa-pause">&nbsp;&nbsp;</i>{{Pause}}');
                 $(this).attr('data-state',1);
                 _params.display.scrollTop(_params.display.height() + 200000);
                 nextdom.log.autoupdate(_params);
             }
         });

         _params['search'].off('keypress').on('keypress',function(){
             if(_params['control'].attr('data-state') == 0){
                 _params['control'].trigger('click');
             }
         });
     }
     _params.callNumber++;
     nextdom.log.currentAutoupdate[_params.display.uniqueId().attr('id')] = {log : _params.log};

     if(_params.callNumber > 0 && (_params.display.scrollTop() + _params.display.innerHeight() + 1) < _params.display[0].scrollHeight){
         if(_params['control'].attr('data-state') == 1){
             _params['control'].trigger('click');
         }
         return;
     }
     nextdom.log.get({
         log : _params.log,
         slaveId : _params.slaveId,
         global : (_params.callNumber == 1),
         success : function(result){
             var log = '';
             var regex = /<br\s*[\/]?>/gi;
             if($.isArray(result)){
                 for (var i in result.reverse()) {
                     if(!isset(_params['search']) || _params['search'].value() == '' || result[i].toLowerCase().indexOf(_params['search'].value().toLowerCase()) != -1){
                         log += $.trim(result[i])+"\n";
                     }
                 }
             }
             _params.display.text(log);
             _params.display.scrollTop(_params.display.height() + 200000);
             setTimeout(function() {
                 nextdom.log.autoupdate(_params)
             }, 1000);
         },
         error : function(){
             setTimeout(function() {
                 nextdom.log.autoupdate(_params)
             }, 1000);
         },
     });
 }
