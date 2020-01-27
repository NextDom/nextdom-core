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
    nextdom.config.load({
        configuration: $('#cache').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#cache').setValues(data, '.configKey');
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
        }
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#cache').delegate('.configKey', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadInformations();
    });

    // Save button
    $("#bt_savecache").on('click', function (event) {
        nextdom.config.save({
            configuration: $('#cache').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#cache').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#cache').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // Cache engine change
    $('#cache').delegate('.configKey[data-l1key="cache::engine"]', 'change', function () {
       $('.cacheEngine').hide();
       $('.cacheEngine.'+$(this).value()).show();
    });

    // Cache clean button
    $("#bt_cleanCache").on('click', function (event) {
        cleanCache();
    });

    // Cache flush button
    $("#bt_flushCache").on('click', function (event) {
        flushCache();
    });
}

/**
 * Flush cache
 */
function flushCache() {
    nextdom.cache.flush({
        error: function (error) {
            notify('Erreur', data.result, 'error');
        },
        success: function (data) {
            updateCacheStats();
            notify('Info', '{{Cache vidé}}', 'success');
        }
    });
}

/**
 * Clean cache
 */
function cleanCache() {
    nextdom.cache.clean({
        error: function (error) {
            notify('Erreur', data.result, 'error');
        },
        success: function (data) {
            updateCacheStats();
            notify('Info', '{{Cache nettoyé}}', 'success');
        }
    });
}

/**
 * Update cache statistics
 */
function updateCacheStats(){
    nextdom.cache.stats({
        error: function (error) {
            notify('Erreur', data.result, 'error');
        },
        success: function (data) {
            $('#span_cacheObject').html(data.count);
        }
    });
}
