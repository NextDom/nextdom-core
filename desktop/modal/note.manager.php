<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div style="display: none;" id="div_noteManagementAlert"></div>
<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4" style="overflow-y:auto;overflow-x:hidden;">
        <fieldset>
            <legend class="legend-title">{{Liste des notes}}</legend>
                <div class="bs-sidebar">
                    <ul class="nav nav-list bs-sidenav list-group" id="ul_noteList">

                    </ul>
                </div>
            </legend>
        </fieldset>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8" style="border-left: solid 1px #EEE;overflow-y:hidden;overflow-x:hidden;">
        <fieldset>
            <div class="box-tools pull-right padding-bottom">
                <a class="btn btn-action" id="bt_noteManagerAdd"><i class="fas fa-plus">&nbsp;&nbsp;</i>{{Ajouter}}</a>
                <a class="btn btn-success" id="bt_noteManagerSave"><i class="fas fa-save">&nbsp;&nbsp;</i>{{Sauvegarder}}</a>
                <a class="btn btn-danger" id="bt_noteManagerRemove"><i class="fas fa-trash">&nbsp;&nbsp;</i>{{Supprimer}}</a>
            </div>
            <div id="div_noteManagerDisplay">
                <input class="noteAttr form-control" data-l1key="id" style="display:none;" disabled/>
                <input class="noteAttr form-control label-list" data-l1key="name" disabled/>
                <textarea class="noteAttr form-control ta_autosize" data-l1key="text" disabled></textarea>
            </div>
        </fieldset>
    </div>
</div>

<script type="text/javascript">
    function updateNoteList(){
        nextdom.note.all({
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function (notes) {
                var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
                var ul = '';
                for(var i in notes){
                    ul += '<li class="cursor li_noteDisplay" data-id="'+notes[i].id+'"><a>'+notes[i].name+'</a></li>';
                }
                $('#ul_noteList').empty().append(ul);
                if(note.id != ''){
                    $('.li_noteDisplay[data-id='+note.id+']').addClass('active');
                }
            }
        });
    }

    $('#bt_noteManagerAdd').on('click',function(){
        var name = prompt("Nom de la note ?");
        if (name != null) {
            nextdom.note.save({
                note : {name : name},
                error: function (error) {
                    notify('Core',error.message,'error');
                },
                success: function (notes) {
                    notify('Core','{{Note créée avec succès}}','success');
                    updateNoteList();
                }
            });
        }
    });

    $('#ul_noteList').on('click','.li_noteDisplay',function(){
        $('.li_noteDisplay').removeClass('active');
        $(this).addClass('active');
        nextdom.note.byId({
            id : $(this).attr('data-id'),
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function (note) {
                $('#div_noteManagerDisplay .noteAttr').value('');
                $('#div_noteManagerDisplay .noteAttr').attr('disabled',false);
                $('#div_noteManagerDisplay').setValues(note, '.noteAttr');
                taAutosize();
            }
        });
    });

    $('#bt_noteManagerSave').on('click',function(){
        var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
        nextdom.note.save({
            note : note,
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function (notes) {
                notify('Core','{{Note sauvegardée avec succès}}','success');
                updateNoteList();
            }
        });
    });

    $('#bt_noteManagerRemove').on('click',function(){
        var note = $('#div_noteManagerDisplay').getValues('.noteAttr')[0];
        var r = confirm('{{Etês vous sur de vouloir supprimer la note : }}'+note.name+' ?');
        if (r == true) {
            nextdom.note.remove({
                id : note.id,
                error: function (error) {
                    notify('Core',error.message,'error');
                },
                success: function (notes) {
                    notify('Core','{{Note supprimée avec succès}}','success');
                    $('#div_noteManagerDisplay .noteAttr').value('');
                    updateNoteList();
                }
            });
        }
    });

    updateNoteList();
    taAutosize();
</script>
