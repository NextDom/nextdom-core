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
    $('#bt_clearMessage').on('click', function (event) {
        nextdom.message.clear({
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                $('#table_message .menu').empty();
                refreshMessageNumber();
            }
        });
    });

    $('#table_message').delegate('.removeMessage', 'click', function () {
        var messageRow = $(this).closest('li');
        nextdom.message.remove({
            id: messageRow.attr('data-message_id'),
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                messageRow.remove();
                refreshMessageNumber();
                $('#bt_messageModal').click();
            }
        });
    });
}

initEvents();
