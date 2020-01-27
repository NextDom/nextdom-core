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

function refreshDependancyInfo(){
    var nok = false;
    nextdom.plugin.getDependancyInfo({
        id : plugin_id,
        success: function (data) {
            switch(data.state) {
                case 'ok':
                    $('.dependancyState').empty().append('<span class="label label-success label-sticker">{{ OK }}</span>');
                    break;
                case 'nok':
                    nok = true;
                    $("#div_plugin_dependancy").closest('.box').removeClass('box-success box-info').addClass('box-danger');
                    $('.dependancyState').empty().append('<span class="label label-danger label-sticker">{{ NOK }}</span>');
                    break;
                case 'in_progress':
                    nok = true;
                    $("#div_plugin_dependancy").closest('.box').removeClass('box-success box-danger').addClass('box-info');
                    var html = '<span class="label label-primary label-sticker"><i class="fas fa-spinner fa-spin spacing-right"></i>{{ Installation en cours }}';
                    if(isset(data.progression) && data.progression !== ''){
                        html += ' - '+data.progression+' %';
                    }
                    if(isset(data.duration) && data.duration != -1){
                        html += ' - '+data.duration+' min';
                    }
                    html += '</span>';
                    $('.dependancyState').empty().append(html);
                break;
                default:
                    $('.dependancyState').empty().append('<span class="label label-warning label-sticker-sm">'+data.state+'</span>');
            }
            $('.td_lastLaunchDependancy').empty().append(data.last_launch);
            if(!nok){
                $("#div_plugin_dependancy").closest('.box').removeClass('box-danger box-info').addClass('box-success');
            }
            if(nok){
                setTimeout(refreshDependancyInfo, 5000);
            }
        }
    });
}
refreshDependancyInfo();

$('.launchInstallPluginDependancy').on('click',function(){
    nextdom.plugin.dependancyInstall({
        id : plugin_id,
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $("#div_plugin_dependancy").load('index.php?v=d&modal=plugin.dependancy&plugin_id='+plugin_id);
        }
    });
});
