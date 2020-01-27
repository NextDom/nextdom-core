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
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    $(document).ready(function(){
        $('#ul_noteList').css("overflow-y", "auto");
        $('#ul_noteList').css("padding-right", "5px");
        updateNoteList();
        initTextAreaAutosize();
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#note').delegate('.noteAttr', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        updateNoteList();
    });

    // Note add button
    $('#bt_noteManagerAdd').on('click', function () {
        bootbox.prompt("Nom de la note ?", function (result) {
            if (result != null) {
                nextdom.note.save({
                    note: {name: result},
                    error: function (error) {
                        notify('Core', error.message, 'error');
                    },
                    success: function (notes) {
                        notify('Core', '{{Note créée avec succès}}', 'success');
                        updateNoteList();
                    }
                });
            }
        });
    });

    // Note choose and display
    $('#ul_noteList').on('click', '.li_noteDisplay', function () {
        $('.li_noteDisplay').removeClass('active');
        $(this).addClass('active');
        getNoteDisplay($(this).attr('data-id'));
    });

    // Note save button
    $('#bt_noteManagerSave').on('click', function () {
        var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
        nextdom.note.save({
            note: note,
            error: function (error) {
                notify('Core', error.message, 'error');
            },
            success: function (notes) {
                notify('Core', '{{Note sauvegardée avec succès}}', 'success');
                updateNoteList();
            }
        });
    });

    // Note remove button
    $('#bt_noteManagerRemove').on('click', function () {
        var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
        bootbox.confirm('{{Etês-vous sur de vouloir supprimer la note : }} <span style="font-weight: bold ;">' + note.name + '</span> ?', function (result) {
            if (result) {
                nextdom.note.remove({
                    id: note.id,
                    error: function (error) {
                        notify('Core', error.message, 'error');
                    },
                    success: function (notes) {
                        notify('Core', '{{Note supprimée avec succès}}', 'success');
                        $('#div_noteManagerDisplay .noteAttr').value('');
                        $('#div_noteDisplay').hide();
                        $('#div_noteBtn').hide();
                        updateNoteList();
                    }
                });
            }
        });
    });
}

/**
 * Updating note list
 */
function updateNoteList() {
    nextdom.note.all({
        error: function (error) {
            notify('Core', error.message, 'error');
        },
        success: function (notes) {
            var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
            var ul = '';
            for (var i in notes) {
                ul += '<li class="cursor li_noteDisplay" data-id="' + notes[i].id + '"><a class="label-list">' + notes[i].name + '</a></li>';
            }
            $('#ul_noteList').empty().append(ul);
            if (note.id != '') {
                $('.li_noteDisplay[data-id=' + note.id + ']').addClass('active');
                getNoteDisplay($('.li_noteDisplay[data-id=' + note.id + ']').attr('data-id'));
            }else{
                $('.li_noteDisplay').first().addClass('active');
                getNoteDisplay($('.li_noteDisplay').first().attr('data-id'));
            }
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
        }
    });
}

/**
 * Display note datas
 *
* @param _id note id
 */
function getNoteDisplay(_id) {
    nextdom.note.byId({
        id: _id,
        error: function (error) {
            $('#div_noteDisplay').hide();
            $('#div_noteBtn').hide();
            notify('Core', error.message, 'error');
        },
        success: function (note) {
            $('#div_noteDisplay').show();
            $('#div_noteBtn').show();
            $('#div_noteManagerDisplay .noteAttr').value('');
            $('#div_noteManagerDisplay .noteAttr').attr('disabled', false);
            $('#div_noteManagerDisplay').setValues(note, '.noteAttr');
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
            initTextAreaAutosize();
        }
    });
}
