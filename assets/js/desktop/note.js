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
            }
        }
    });
}

$('#bt_noteManagerAdd').on('click', function () {
    var name = prompt("Nom de la note ?");
    if (name != null) {
        nextdom.note.save({
            note: {name: name},
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

$('#ul_noteList').on('click', '.li_noteDisplay', function () {
    $('.li_noteDisplay').removeClass('active');
    $(this).addClass('active');
    nextdom.note.byId({
        id: $(this).attr('data-id'),
        error: function (error) {
            notify('Core', error.message, 'error');
        },
        success: function (note) {
            $('#div_noteManagerDisplay .noteAttr').value('');
            $('#div_noteManagerDisplay .noteAttr').attr('disabled', false);
            $('#div_noteManagerDisplay').setValues(note, '.noteAttr');
            taAutosize();
        }
    });
});

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

$('#bt_noteManagerRemove').on('click', function () {
    var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
    var r = confirm('{{Etês-vous sur de vouloir supprimer la note : }}' + note.name + ' ?');
    if (r == true) {
        nextdom.note.remove({
            id: note.id,
            error: function (error) {
                notify('Core', error.message, 'error');
            },
            success: function (notes) {
                notify('Core', '{{Note supprimée avec succès}}', 'success');
                $('#div_noteManagerDisplay .noteAttr').value('');
                updateNoteList();
            }
        });
    }
});

updateNoteList();
taAutosize();
