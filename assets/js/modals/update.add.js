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

$('.updateAttr[data-l1key=source]').on('change',function(){
    $('.repoSource').hide();
    $('.repoSource.repo_'+$(this).value()).show();
});

$('#bt_uploadPlugin').fileupload({
    dataType: 'json',
    replaceFileInput: false,
    done: function (e, data) {
        if (data.result.state != 'ok') {
            notify("Update", data.result.result, 'error');
            return;
        }
        $('.updateAttr[data-l1key=configuration][data-l2key='+$('#bt_uploadPlugin').attr('data-key')+']').value(data.result.result);
    }
});


$('#bt_repoAddSaveUpdate').on('click',function(){
    var source = $('.updateAttr[data-l1key=source]').value();
    var update =  $('.repoSource.repo_'+source).getValues('.updateAttr')[0];
    update.source = source;
    jeedom.update.save({
        update : update,
        error: function (error) {
            notify("{{ Plugin }}", error.message, 'error');
        },
        success: function () {
            notify("{{ Plugin }}", '{{ Ajout réussi }}', 'success');
        }
    });
});

$('#bt_repoAddClose').on('click', function () {
    $('#md_modal').dialog('close');
});
