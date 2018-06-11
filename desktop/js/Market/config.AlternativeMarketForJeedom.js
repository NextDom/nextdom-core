// Point d'entrée du script
$(document).ready(function () {
    var gitsListUl = $('#gitid-list');
    var showedGitHubSource = false;
    if (sourcesList.length > 0) {
        for (var sourceIndex = 0; sourceIndex < sourcesList.length; ++sourceIndex) {
            if (sourcesList[sourceIndex]['type'] === 'github') {
                var sourceData = sourcesList[sourceIndex]['data'];
                var item = getListItem(sourceData);
                gitsListUl.append(item);
                showedGitHubSource = true;
            }
        }
    }
    if (!showedGitHubSource) {
        $('#github-list-container').hide();
    }
    $('#add-git').click(addGitId);
    $('#sources-list-save').click(saveSourcesChoices);
});

/**
 * Obtenir le code d'un élément de la liste
 *
 * @param itemData Données de l'élément
 *
 * @returns {jQuery|HTMLElement} Code de l'élément
 */
function getListItem(itemData) {
    var item = $('<li class="list-group-item">' + itemData + '</li>');
    var deleteButton = $('<button class="badge btn btn-danger" data-gitid="' + itemData + '">Supprimer</button>');

    deleteButton.click(function () {
        removeGitId($(this).data('gitid'));

    });
    item.append(deleteButton);
    return item;
}

/**
 * Ajouter un utilisateur à la liste
 *
 * @param gitId Identifiant GitHub à ajouter
 */
function addGitId(gitId) {
    if (typeof gitId === 'undefined' || typeof gitId !== 'string') {
        gitId = $('#git-id').val();
    }
    if (gitId !== '') {
        var addGitData = {action: 'source', params: 'add', data: {type: 'github', id: gitId}};
        ajaxQuery(addGitData, function () {
            var gitsListUl = $('#gitid-list');
            gitsListUl.append(getListItem(gitId));
            $('#github-list-container').show();
        });
    }
}

/**
 * Supprimer un utilisateur de la liste
 *
 * @param gitId Nom de l'utilisateur
 */
function removeGitId(gitId) {
    if (gitId !== '') {
        var removeGitData = {action: 'source', params: 'remove', data: {type: 'github', id: gitId}};
        ajaxQuery(removeGitData, function () {
            $('#config-modal ul li').each(function () {
                if ($(this).text().indexOf(gitId) !== -1) {
                    $(this).remove();
                }
            });
        });
    }
}

function saveSourcesChoices() {
    var inputsList = $('#sources-list input').toArray();
    var result = [];
    for (var i = 0; i < inputsList.length; ++i) {
        var item = $(inputsList[i]);
        var itemId = item.attr('id').substr(13);
        var enable = 0;

        if (item.is(':checked')) {
            enable = 1;
        }
        result.push({id: itemId, enable: enable});
    }
    ajaxQuery({action: 'save', params: 'sources', data: result});
}

/**
 * Lancer une requête Ajax
 *
 * @param data Données de la requête
 */
function ajaxQuery(queryData, callbackFunc) {
    $.post({
        url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
        data: queryData,
        dataType: 'json',
        success: function (data, status) {
            // Test si l'appel a échoué
            if (data.state !== 'ok' || status !== 'success') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
            }
            else {
                if (typeof callbackFunc !== "undefined") {
                    callbackFunc();
                }
            }
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}

