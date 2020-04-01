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
// Page init
initEvents();

/**
 * Init events on the profils page
 */
function initEvents() {
    // Report type selection
    $('.li_type').on('click',function(){
       $('.li_type').removeClass('active');
       $(this).addClass('active');
       $('.reportType').hide();
       $('.reportType.'+$(this).attr('data-type')).show();
    });

    // Design selection
    $('.li_reportType').on('click',function(){
       $('.li_reportType').removeClass('active');
       $(this).addClass('active');
       getReportList($(this).attr('data-type'),$(this).attr('data-id'))
    });

    // Report selection
    $('#ul_report').on('click','.li_report',function(){
        $('.li_report').removeClass('active');
        $(this).addClass('active');
        getReport($(this).attr('data-type'),$(this).attr('data-id'),$(this).attr('data-report'))
    });

    // Report download button
    $('#bt_download').on('click',function(){
        var type = $('#div_reportForm .reportAttr[data-l1key=type]').value();
        var id = $('#div_reportForm .reportAttr[data-l1key=id]').value();
        var filename = $('#div_reportForm .reportAttr[data-l1key=filename]').value();
        var extension = $('#div_reportForm .reportAttr[data-l1key=extension]').value();
        window.open('src/Api/downloadFile.php?pathfile=data/report/' + type+'/'+id+'/'+filename+'.'+extension, "_blank", null);
    });

    // Remove report button
    $('#bt_remove').on('click',function(){
        var report = $('#div_reportForm .reportAttr[data-l1key=filename]').value()+'.'+$('#div_reportForm .reportAttr[data-l1key=extension]').value();
        nextdom.report.remove({
            type: $('#div_reportForm .reportAttr[data-l1key=type]').value(),
            id: $('#div_reportForm .reportAttr[data-l1key=id]').value(),
            report: report,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                $('#div_reportForm').hide();
                $('#div_reportBtn').hide();
                $('.li_report[data-report="'+report+'"]').remove();
                $('.li_reportType.active .number').text($('.li_reportType.active .number').text() - 1);
            }
        });
    });

    // Remove all report button
    $('#bt_removeAll').on('click',function(){
        nextdom.report.removeAll({
            type: $('#div_reportForm .reportAttr[data-l1key=type]').value(),
            id: $('#div_reportForm .reportAttr[data-l1key=id]').value(),
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
                $('#div_reportForm').hide();
                $('#div_reportBtn').hide();
                $('.li_report').remove();
                $('.li_reportType.active .number').text('0');
            }
        });
    });
}

/**
 * Find report list from type
 *
 * @param _type Report type to find
 * @param _id Report id to find
 */
function getReportList(_type, _id){
   nextdom.report.list({
       type: _type,
       id: _id,
       error: function (error) {
           notify('Erreur', error.message, 'error');
       },
       success: function (data) {
           $('#ul_report .li_report').remove();
           var ul = '';
           for(var i in data){
               ul += '<li class="cursor li_report" data-type='+_type+' data-id='+_id+' data-report="'+i+'"><a>' +i+ '</a></li>';
           }
           $('#ul_report').append(ul);
       }
   });
}

/**
 * Get report data
 *
 * @param _type Report type to get data
 * @param _id Report id to get data
 * @param _report Report object to get data
 */
function getReport(_type, _id, _report){
   nextdom.report.get({
       type: _type,
       id: _id,
       report: _report,
       error: function (error) {
           notify('Erreur', error.message, 'error');
       },
       success: function (data) {
           $('#div_reportForm').show();
           $('#div_reportBtn').show();
           $('#div_reportForm').setValues(data, '.reportAttr');
           $('#div_imgreport').empty();
           var type = $('#div_reportForm .reportAttr[data-l1key=type]').value();
           var id = $('#div_reportForm .reportAttr[data-l1key=id]').value();
           var filename = $('#div_reportForm .reportAttr[data-l1key=filename]').value();
           var extension = $('#div_reportForm .reportAttr[data-l1key=extension]').value();
           if(extension != 'pdf'){
               $('#div_imgreport').append('<img class="img-responsive" src="src/Api/downloadFile.php?pathfile=data/report/' + type+'/'+id+'/'+filename+'.'+extension+'" />');
           }else{
               $('#div_imgreport').append('{{Aucun aperçu possible en pdf}}');
           }
       }
   });
}
