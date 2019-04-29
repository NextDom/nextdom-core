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

var modalContainer = $('#md_modal');

function initEvents() {
    $('#sel_plugin').on('change', function () {
        var pluginId = $('#sel_plugin').value();
        modalContainer.dialog({title: '{{Message NextDom}} ' + pluginId});
        modalContainer.load('index.php?v=d&modal=message&plugin_id=' + pluginId).dialog('open');
    });

    $('#bt_clearMessage').on('click', function (event) {
        nextdom.message.clear({
            plugin: $('#sel_plugin').value(),
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                $('#table_message tbody').remove();
                refreshMessageNumber();
            }
        });
    });

    $('#bt_refreshMessage').on('click', function () {
        modalContainer.dialog({title: '{{Message NextDom}}'});
        modalContainer.load('index.php?v=d&modal=message').dialog('open');
    });

    $('#table_message').delegate('.removeMessage', 'click', function () {
        var messageRow = $(this).closest('tr');
        nextdom.message.remove({
            id: messageRow.attr('data-message_id'),
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                messageRow.remove();
                $('#table_message').trigger('update');
                refreshMessageNumber();
            }
        });
    });
}

initEvents();