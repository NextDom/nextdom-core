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

var currentPlugin = null;
var filterHiddenSrc = [];
var filterCategory = '';
var filterInstalled = false;
var filterNotInstalled = false;
var currentSearchValue = '';
var iconDownloadQueue = [];
var pluginsUpdateNeededList = [];

// Point d'entrée du script
$(document).ready(function () {
    initEvents();
    $("img.lazy").lazyload({
      threshold : 400
    });
    refresh(false);
});

/**
 * Initialise les évènements
 */
function initEvents() {
    if (github != '1') {
        // TODO: Bloquer la page
        notify('Erreur', 'Les dépôts GitHub sont désactivés', 'error');
    }
    $('#market-filter-src button').click(function () {
        var source = $(this).data('source');
        if (isActive($(this))) {
            filterHiddenSrc.push(source);
            setActive($(this), false);
        }
        else {
            var itemIndex = -1;
            for (var index = 0; index < filterHiddenSrc.length; ++index) {
                if (filterHiddenSrc[index] == source) {
                    itemIndex = index;
                }
            }
            if (itemIndex > -1) {
                filterHiddenSrc.splice(itemIndex, 1);
            }
            setActive($(this), true);
        }
        updateFilteredList();
    });
    $('#market-filter-category').change(function () {
        var selectedCategory = $("#market-filter-category option:selected").val();
        if (selectedCategory !== 'all') {
            filterCategory = selectedCategory;
        }
        else {
            filterCategory = '';
        }
        updateFilteredList();
    });
    $('#market-filter-installed').click(function () {
        if (isActive($(this))) {
            filterInstalled = true;
            setActive($(this), false);
            if (filterNotInstalled) {
                setActive($('#market-filter-notinstalled'), true);
                filterNotInstalled = false;
            }
        }
        else {
            filterInstalled = false;
            setActive($(this), true);
        }
        updateFilteredList();
    });
    $('#market-filter-notinstalled').click(function () {
        if (isActive($(this))) {
            filterNotInstalled = true;
            setActive($(this), false);
            if (filterInstalled) {
                setActive($('#market-filter-installed'), true);
                filterInstalled = false;
            }
        }
        else {
            filterNotInstalled = false;
            setActive($(this), true);
        }
        updateFilteredList();
    });
    $('#refresh-markets').click(function () {
        refresh(true);
    });
    $('#mass-update').click(function () {
        if (pluginsUpdateNeededList.length > 0) {
            currentPlugin = pluginsUpdateNeededList[0];
            $('#market-modal-title').text(updateStr);
            var contentHtml = '<p>' + updateAllStr + '</p><ul>';
            for (var pluginIndex = 0; pluginIndex < pluginsUpdateNeededList.length; ++pluginIndex) {
                contentHtml += '<li>' + pluginsUpdateNeededList[pluginIndex]['name'] + '</li>';
            }
            contentHtml += '</ul>';
            $('#market-modal-content').html(contentHtml);
            $('#market-modal-valid-text').text(updateStr);
            $('#market-modal').modal('show');
            $('#market-modal-valid').click(function () {
                updatePlugin(currentPlugin['installedBranchData']['id'], true);
            });
            return false;
        }
    });
}

/**
 * Test si un bouton est actif
 *
 * @param button Bouton à tester
 *
 * @returns {boolean} True si le bouton est actif
 */
function isActive(button) {
    var result = false;
    if (button.hasClass('btn-primary')) {
        result = true;
    }
    return result;
}

/**
 * Change l'état d'activation d'un bouton
 *
 * @param button Bouton à changer
 * @param activate Etat à changer
 */
function setActive(button, activate) {
    if (activate) {
        button.removeClass('btn-action');
        button.addClass('btn-primary');
    }
    else {
        button.removeClass('btn-primary');
        button.addClass('btn-action');
    }
}

/**
 * Met à jour la liste des éléments affichés
 */
function updateFilteredList() {
    currentSearchValue = $('#generalSearch').val().toLowerCase();
    $('#market-div>div').each(function () {
        var hide = false;
        var dataSource = $(this).data('source');
        var dataCategory = $(this).data('category');
        var dataInstalled = $(this).data('installed');
        if (filterHiddenSrc.indexOf(dataSource) !== -1) {
            hide = true;
        }
        if (filterCategory !== '' && filterCategory !== dataCategory) {
            hide = true;
        }
        if (filterInstalled && dataInstalled === true) {
            hide = true;
        }
        if (filterNotInstalled && dataInstalled === false) {
            hide = true;
        }
        if (!hide && currentSearchValue.length > 1) {
            var title = $(this).find('h4').text().toLowerCase();
            var description = $(this).find('.media-body').text().toLowerCase();
            if (title.indexOf(currentSearchValue) === -1 && description.indexOf(currentSearchValue) === -1) {
                hide = true;
            }
        }
        if (hide) {
            $(this).slideUp();
        }
        else {
            $(this).slideDown();
        }
    });
}

/**
 * Rafraichit les éléments affichés
 */
function refresh(force) {
    pluginsUpdateNeededList = [];
    var params = 'list';
    if (force) {
        params += '-force';
    }
    $('#mass-update').hide();
    var ajaxData = {
        action: 'refresh',
        params: params,
        data: sourcesList
    };
    ajaxQuery('core/ajax/nextdom_market.ajax.php', ajaxData, function () {
        refreshItems();
    });
}

/**
 * Rafraichit un elément
 */
function refreshItems() {
    var ajaxData = {
        action: 'get',
        params: 'list',
        data: sourcesList
    };
    ajaxQuery('core/ajax/nextdom_market.ajax.php', ajaxData, function (result) {
        showItems(result);
        updateFilteredList();
        if (pluginsUpdateNeededList.length > 0) {
            $('#mass-update').show();
            $('#mass-update .badge').text(pluginsUpdateNeededList.length);
        }
    });
}

/**
 * Affiche les élements
 *
 * @param items Liste des éléments
 */
function showItems(items) {
    var container = $('#market-div');
    container.empty();
    for (var index = 0; index < items.length; ++index) {
        var itemHtmlObj = $(getItemHtml(items[index]));
        if (items[index]['iconPath'] === false) {
            iconDownloadQueue.push([items[index], itemHtmlObj]);
        }
        container.append(itemHtmlObj);
    }
    startIconsDownload();
    $('.media').click(function () {
        showPluginModal($(this).data('plugin'), $(this).find('img').attr('src'));
        return false;
    });
    $('.update-marker').click(function () {
        $('#market-modal-title').text(updateStr);
        $('#market-modal-content').text(updateThisStr);
        $('#market-modal-valid-text').text(updateStr);
        $('#market-modal').modal('show');
        currentPlugin = $(this).parent().data('plugin');
        $('#market-modal-valid').click(function () {
            updatePlugin(currentPlugin['installedBranchData']['id'], false);
        });
        return false;
    });
    $('[data-toggle="tooltip"]').tooltip();
}

function startIconsDownload() {
    // Lance 3 téléchargement simultanéments
    for (var i = 0; i < 3; ++i) {
        iconDownload();
    }
}

function iconDownload() {
    var content = iconDownloadQueue.shift();
    if (typeof content !== 'undefined') {
        var itemData = content[0];
        var itemObj = content[1];
        $.post({
            url: 'core/ajax/nextdom_market.ajax.php',
            global: false,
            data: {
                action: 'get',
                params: 'icon',
                data: {sourceName: itemData['sourceName'], fullName: itemData['fullName']}
            },
            dataType: 'json',
            success: function (iconData, status) {
                // Test si l'appel a échoué
                if (iconData.state !== 'ok' || status !== 'success') {
                    notify("Erreur", iconData.result, 'error');
                }
                else {
                    var img = new Image();
                    img.src = iconData['result'];
                    itemObj.find('img').attr('src', iconData['result']);
                    if (iconDownloadQueue.length > 0) {
                        iconDownload();
                    }
                }
            },
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            }
        });
    }
}

/**
 * Obtenir le code HTML d'un élément
 *
 * @param item Informations de l'élément à créer
 *
 * @returns {string} Code HTML
 */
function getItemHtml(item) {
    // Préparation des données
    var title = item['name'];
    if (title !== null) {
        title = title.replace(/([a-z])([A-Z][a-z])/g, '\$1 \$2');
    }
    var pluginData = JSON.stringify(item);
    pluginData = pluginData.replace(/"/g, '&quot;');
    var descriptionPar = '';
    if (item['description'] == null) {
        item['description'] = '';
    }
    if (item['description'].length > 170) {
        descriptionPar = '<p class="truncate">' + item['description'].substr(0, 170) + '...</p>';
    }
    else {
        descriptionPar = '<p>' + item['description'] + '</p>';
    }

    var iconPath = item['iconPath'];
    if (item['iconPath'] === false) {
        iconPath = 'public/img/wait_icon.png';
    }
    // Préparation du code
    var result = '' +
        '<div class="media-container col-xs-12 col-sm-6 col-md-4" data-source="' + item['sourceName'] + '" data-category="' + item['category'] + '" data-installed="' + item['installed'] + '">' +
        '<div class="media" data-plugin="' + pluginData + '">';
    if (item['installed']) {
        result += '<div class="installed-marker"><i data-toggle="tooltip" data-placement="bottom" title="' + installedPluginStr + '" class="fas fa-check"></i></div>';
    }
    if (item['installedBranchData'] !== false && item['installedBranchData']['needUpdate'] == true) {
        result += '<div class="update-marker"><i data-toggle="tooltip" data-placement="bottom" title="' + updateAvailableStr + '" class="fas fa-download"></i></div>';
        pluginsUpdateNeededList.push(item);
    }
    result += '' +
        '<h4 class="media-title">' + title + '</h4>' +
        '<div class="media-content">' +
        '<div class="media-left">' +
        '<img class="lazy" src="' + iconPath + '"/>' +
        '</div>' +
        '<div class="media-body">' +
        descriptionPar +
        '</div>' +
        '</div>' +
        '<button>' + moreInformationsStr + '</button>' +
        '<div class="gitid">' + item['sourceName'] + '</div>' +
        '</div>' +
        '</div>';
    return result;
}

/**
 * Affiche la fenêtre d'un plugin
 *
 * @param pluginData Données du plugin
 */
function showPluginModal(pluginData, iconPath) {
    var modal = $('#md_modal');
    modal.dialog({title: pluginData['name']});
    modal.load('index.php?v=d&modal=plugin.market').dialog('open');
    currentPlugin = pluginData;
    currentPlugin['iconPath'] = iconPath;
}

/**
 * Lance l'installation du plugin
 */
function updatePlugin(id, massUpdate) {
    var data = {
        action: 'update',
        id: id
    };
    ajaxQuery('core/ajax/update.ajax.php', data, function () {
        var data = {
            action: 'refresh',
            params: 'branch-hash',
            data: [currentPlugin['sourceName'], currentPlugin['fullName']]
        }
        // Met à jour les branches
        ajaxQuery('core/ajax/nextdom_market.ajax.php', data, function () {
            if (massUpdate && pluginsUpdateNeededList.length > 1) {
                pluginsUpdateNeededList.splice(0, 1);
                currentPlugin = pluginsUpdateNeededList[0];
                updatePlugin(currentPlugin['installedBranchData']['id'], true);
            }
            else {
                reloadWithMessage(0);
            }
        });
    });
}

/**
 * Lancer une requête Ajax
 *
 * @param data Données de la requête
 */
function ajaxQuery(url, data, callbackFunc) {
    $.post({
        url: url,
        data: data,
        dataType: 'json',
        success: function (data, status) {
            // Test si l'appel a échoué
            if (data.state !== 'ok' || status !== 'success') {
                notify("Erreur", data.result, 'error');
            }
            else {
                if (typeof callbackFunc !== "undefined") {
                    callbackFunc(data.result);
                }
            }
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}

/**
 * Recharge une page avec un message à afficher
 *
 * @param messageId Identifiant du message
 */
function reloadWithMessage(messageId) {
    var urlMessageParamIndex = window.location.href.indexOf('message=');
    if (urlMessageParamIndex === -1) {
        window.location.href = window.location.href + "&message=" + messageId;
    }
    else {
        window.location.href = window.location.href.substr(0, urlMessageParamIndex) + "message=" + messageId;
    }

}
