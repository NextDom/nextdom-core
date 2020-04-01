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

function getNextDomLog(_autoUpdate, _log) {
    $.ajax({
        type: 'POST',
        url: 'src/ajax.php',
        data: {
            target: 'Log',
            action: 'get',
            log: _log
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            setTimeout(function () {
                getNextDomLog(_autoUpdate, _log);
            }, 1000);
        },
        success: function (data) {
            if (data.state !== 'ok') {
                setTimeout(function () {
                    getNextDomLog(_autoUpdate, _log)
                }, 1000);
                return;
            }
            var log = '';
            if($.isArray(data.result)){
                for (var i in data.result.reverse()) {
                    log += data.result[i]+"\n";
                    if(data.result[i].indexOf('Closing with success') !== -1){
                        switchNotify(1);
                        nextdom.user.refresh();
                        notify('Info', '{{L\'opération est réussie}}', 'success');
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('Closing with error') !== -1){
                        switchNotify(1);
                        nextdom.user.refresh();
                        notify('Erreur', '{{L\'opération a échoué}}', 'error');
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('Fatal error') !== -1){
                        switchNotify(1);
                        nextdom.user.refresh();
                        notify('Erreur', '{{L\'opération a échoué}}', 'error');
                        _autoUpdate = 0;
                    }
                }
            }
            $('#pre_backupInfo').text(log);
            if (init(_autoUpdate, 0) === 1) {
                setTimeout(function () {
                    getNextDomLog(_autoUpdate, _log);
                }, 500);
            } else {
                $('#bt_' + _log + 'NextDom').removeClass('disabled');
                $('#bt_' + _log + 'RepoNextDom').removeClass('disabled');
                $('#bt_' + _log + 'NextDom .fa-refresh').hide();
                $('#bt_' + _log + 'RepoNextDom .fa-refresh').hide();
                $('#bt_' + _log + 'NextDom .fa-floppy-o').show();
                $('#bt_' + _log + 'NextDom .fa-window-restore').show();
                $('#bt_' + _log + 'RepoNextDom .fa-window-restore').show();
                $('#bt_' + _log + 'NextDom .fa-cloud-upload-alt').show();
                $('#bt_' + _log + 'NextDom .fa-cloud-dowload-alt').show();
                refreshMessageNumber();
            }
        }
    });
}