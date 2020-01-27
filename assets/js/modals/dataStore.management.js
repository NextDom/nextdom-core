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
*/

function initEvents() {
    // Remove data button
    $('#table_dataStore').delegate('.bt_removeDataStore', 'click', function() {
        var tr = $(this).closest('tr');
        bootbox.confirm('Etes-vous sûr de vouloir supprimer la variable <span class="text-bold">' + tr.find('.key').value() + '</span> ?', function(result) {
            if (result) {
                nextdom.dataStore.remove({
                   id: tr.attr('data-dataStore_id'),
                   error: function (error) {
                       notify('Core', error.message, 'error');
                   },
                success: function (data) {
                    notify('Info', '{{Dépôt de données supprimé}}', 'success');
                    refreshDataStoreMangementTable();
                }
            });
            }
        });
    });

    // Save data button
    $('#table_dataStore').delegate('.bt_saveDataStore', 'click', function() {
      var tr = $(this).closest('tr');
      nextdom.dataStore.save({
          id: tr.attr('data-dataStore_id'),
          value: tr.find('.value').value(),
          type: dataStore_type,
          key: tr.find('.key').value(),
          link_id: dataStore_link_id,
          error: function (error) {
              notify('Core', error.message, 'error');
          },
          success: function (data) {
              notify('Info', '{{Dépôt de données sauvegardé}}', 'success');
              refreshDataStoreMangementTable();
          }
       });
    });

    // Link data button
    $('#table_dataStore').delegate('.bt_graphDataStore', 'click', function() {
        var tr = $(this).closest('tr');
        $('#md_modal2').dialog({title: "{{ Graphique de lien(s) }}"});
        $("#md_modal2").load('index.php?v=d&modal=graph.link&filter_type=dataStore&filter_id='+tr.attr('data-dataStore_id')).dialog('open');
    });

    // Add data button
    $('#bt_dataStoreManagementAdd').on('click', function() {
        var tr = '<tr data-dataStore_id="">';
        tr += '<td>';
        tr += '<input class="form-control input-sm key" value="" />';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="form-control input-sm value" value="" />';
        tr += '</td>';
        tr += '<td>';
        tr += '</td>';
        tr += '<td style="width:127px">';
        tr += '<a class="btn btn-success pull-right btn-sm bt_saveDataStore"><i class="fas fa-save no-spacing"></i></a>';
        tr += '<a class="btn btn-danger pull-right btn-sm bt_removeDataStore"><i class="fas fa-trash no-spacing"></i></a>';
        tr += '<a class="btn btn-default pull-right btn-sm bt_graphDataStore"><i class="fas fa-object-group no-spacing"></i></a>';
        tr += '</td>';
        tr += '</tr>';
        $('#table_dataStore tbody').append(tr);
        $("#table_dataStore").trigger("update");
    });
}

function refreshDataStoreMangementTable() {
    nextdom.dataStore.all({
      type: dataStore_type,
      usedBy : 1,
      error: function (error) {
         notify('Core', error.message, 'error');
      },
      success: function (data) {
        $('#table_dataStore tbody').empty();
        var tr = '';
        for (var i in data) {
          tr += '<tr data-dataStore_id="' + data[i].id + '">';
          tr += '<td>';
          tr += '<span style="display : none;">' + data[i].key + '</span><input class="form-control input-sm key" value="' + data[i].key + '" disabled />';
          tr += '</td>';
          tr += '<td>';
          tr += '<span style="display : none;">' + data[i].value + '</span><input class="form-control input-sm value" value="' + data[i].value + '" />';
          tr += '</td>';
          tr += '<td>';
          for(var j in data[i].usedBy.scenario){
              tr += '<span class="label label-primary">'+data[i].usedBy.scenario[j]+'</span> ';
          }
          for(var j in data[i].usedBy.eqLogic){
              tr += '<span class="label label-primary">'+data[i].usedBy.eqLogic[j]+'</span> ';
          }
          for(var j in data[i].usedBy.cmd){
              tr += '<span class="label label-primary">'+data[i].usedBy.cmd[j]+'</span> ';
          }
          for(var j in data[i].usedBy.interactDef){
              tr += '<span class="label label-primary">'+data[i].usedBy.interactDef[j]+'</span> ';
          }
          tr += '</td>';
          tr += '<td style="width:127px">';
          tr += '<a class="btn btn-success pull-right btn-sm bt_saveDataStore"><i class="fas fa-save no-spacing"></i></a>';
          tr += '<a class="btn btn-danger pull-right btn-sm bt_removeDataStore"><i class="fas fa-trash no-spacing"></i></a>';
          tr += '<a class="btn btn-default pull-right btn-sm bt_graphDataStore"><i class="fas fa-object-group no-spacing"></i></a>';
          tr += '</td>';
          tr += '</tr>';
        }
        $('#table_dataStore tbody').append(tr);
        $("#table_dataStore").trigger("update");
      }
  });
}

initEvents();
initTableSorter();
refreshDataStoreMangementTable();
