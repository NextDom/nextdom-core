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

$('.viewAttr[data-l1key=display][data-l2key=icon]').on('dblclick',function(){
    $('.viewAttr[data-l1key=display][data-l2key=icon]').value('');
});

$('#bt_chooseIcon').on('click', function () {
    chooseIcon(function (_icon) {
        $('.viewAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
    });
});

$('#bt_uploadImage').fileupload({
    replaceFileInput: false,
    url: 'src/ajax.php?target=View&action=uploadImage&id=' + view.id+'&nextdom_token='+NEXTDOM_AJAX_TOKEN,
    dataType: 'json',
    done: function (e, data) {
        if (data.result.state != 'ok') {
            notify('Core',data.result.result,'error');
            return;
        }
        notify('Core',"{{ Image ajoutée }}","success");
    }
});

$('#bt_removeBackgroundImage').off('click').on('click', function () {
  nextdom.view.removeImage({
    view: view.id,
    error: function (error) {
        notify('Core',error.message,'error');
    },
    success: function () {
        notify('Core',"{{ Image supprimée }}","success");
    },
});
});

$('#bt_saveConfigureView').on('click', function () {
    var view =  $('#div_viewConfigure').getValues('.viewAttr')[0];
    nextdom.view.save({
        id : view.id,
        view: view,
        error: function (error) {
            notify('Core',error.message,'error');
        },
        success: function () {
            notify('Core',"{{ Vue sauvegardé }}","success");
        },
    });
});

if (isset(id) && id != '') {
 $('#div_viewConfigure').setValues(view, '.viewAttr');
}
