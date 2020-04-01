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

// Page init
initEvents();

/**
 * Init events on the profils page
 */
function initEvents() {
    // Welcome page button
    $('#btn_welcomeModal').on('click', function () {
        loadModal('modal', '{{Bienvenue dans NextDom}}', 'welcome');
    });

    // Restart button
    $('#bt_rebootSystemAdmin').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir redémarrer le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=reboot';
            }
        });
    });

    // Shutdown button
    $('#bt_haltSystemAdmin').on('click', function () {
        $.hideAlert();
        bootbox.confirm('{{Etes-vous sûr de vouloir arrêter le système ?}}', function (result) {
            if (result) {
                window.location.href = 'index.php?v=d&p=shutdown';
            }
        });
    });
}
