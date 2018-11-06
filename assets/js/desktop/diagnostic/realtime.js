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

nextdom.log.autoupdate({
    log : realtime_name,
    default_search : log_default_search,
    display : $('#pre_realtimelog'),
    search : $('#in_realtimeLogSearch'),
    control : $('#bt_eventRealtimeStopStart'),
});

$("#bt_logrealtimeclearLog").on('click', function(event) {
    nextdom.log.clear({
        log : realtime_name,
    });
});

$("#bt_logrealtimeremoveLog").on('click', function(event) {
    nextdom.log.remove({
        log : realtime_name,
    });
});
$('#bt_logrealtimedownloadLog').click(function() {
    window.open('core/php/downloadFile.php?pathfile=log/' + realtime_name, "_blank", null);
});
