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


nextdom.dataStore = function () {
};


nextdom.dataStore.save = function (queryParams) {
  var paramsRequired = ['id', 'value', 'type', 'key', 'link_id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'DataStore', 'save');
    ajaxParams.async = queryParams.async || true;
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['value'] = queryParams.value;
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['key'] = queryParams.key;
    ajaxParams.data['link_id'] = queryParams.link_id;
    $.ajax(ajaxParams);
  }
};

nextdom.dataStore.all = function (queryParams) {
  var paramsRequired = ['type', 'usedBy'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'DataStore', 'all');
    ajaxParams.data['type'] = queryParams.type;
    ajaxParams.data['usedBy'] = queryParams.usedBy;
    $.ajax(ajaxParams);
  }
};

nextdom.dataStore.getSelectModal = function (_options, callback) {
  var dataStoreModal = $("#mod_insertDataStoreValue");
  if (!isset(_options)) {
    _options = {};
  }
  if (dataStoreModal.length !== 0) {
    dataStoreModal.remove();
  }
  $('body').append('<div id="mod_insertDataStoreValue" title="{{Sélectionner une variable}}" ></div>');
  dataStoreModal.dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    height: 250,
    width: 800
  });
  jQuery.ajaxSetup({async: false});
  dataStoreModal.load('index.php?v=d&modal=dataStore.human.insert');
  jQuery.ajaxSetup({async: true});
  mod_insertDataStore.setOptions(_options);
  dataStoreModal.dialog('option', 'buttons', {
    "Annuler": function () {
      $(this).dialog("close");
    },
    "Valider": function () {
      var retour = {};
      retour.human = mod_insertDataStore.getValue();
      retour.id = mod_insertDataStore.getId();
      if ($.trim(retour) !== '') {
        callback(retour);
      }
      $(this).dialog('close');
    }
  });
  dataStoreModal.dialog('open');
};


nextdom.dataStore.remove = function (queryParams) {
  var paramsRequired = ['id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'DataStore', 'remove');
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};