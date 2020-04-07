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

class DisplayCardPageA {
    name;
    modifyWithoutSave;

    constructor(name) {
        this.name = name;
        this.loadInformationsA();
        this.initEventsA();
    }

    /**
     * Load informations in all forms of the page
     */
    loadInformationsA() {
        this.loadFromUrl();
        setTimeout(function () {
            $('.displayCardList').packery();
        }, 100);
        this.loadInformations();
        $(document).ready(function () {
            this.modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
        });
    }
    loadInformations() {/* nothing */
    }

    /**
     * Init events on the profils page
     */
    initEventsA() {
        var $this = this;

        // Add new button
        $("#bt_add").on('click', function () {
            bootbox.prompt("{{Quel est le nom de votre nouvel élément ?}}", function (result) {
                if (result !== null) {
                    var objectPage = {
                        name: result
                    };
                    $this.save(objectPage);
                }
            });
        });
        // Save button
        $("#bt_save").on('click', function () {
            var objectPage = $('#div_conf').getValues('.attr')[0];
            $this.save(objectPage);
            $('#bt_ThumbnailDisplay').show();
        });
        // widget duplicate button
        $('#bt_duplicate').on('click', function () {
            bootbox.prompt("Quel est le nom de votre nouvel élément ?", function (result) {
                if (result !== null) {
                    var objectPage = $('#div_conf').getValues('.attr')[0];
                    objectPage.name = result;
                    objectPage.id = '';
                    $this.save(objectPage);
                }
            });
        });

// Widget delete button
        $("#bt_remove").on('click', function () {
            bootbox.confirm('{{Etes-vous sûr de vouloir supprimer l’élément suivant}} <span style="font-weight: bold ;">' + $('.displayCard.active .name').text() + '</span> ?', function (result) {
                if (result) {
                    $this.remove($('.displayCard.active').attr('data-id'));
                }
            });
        });
        // Panel collasping
        $('#bt_Collapse').off('click').on('click', function () {
            $('#accordion .panel-collapse').each(function () {
                if (!$(this).hasClass("in")) {
                    //$(this).css({'height': ''});
                    $(this).addClass("in");
                }
            });
            $('#bt_Collapse').hide();
            $('#bt_Uncollapse').show();
        });

        // Panel uncollasping
        $('#bt_Uncollapse').off('click').on('click', function () {
            $('#accordion .panel-collapse').each(function () {
                if ($(this).hasClass("in")) {
                    $(this).removeClass("in");
                }
            });
            $('#bt_Uncollapse').hide();
            $('#bt_Collapse').show();
        });

        // Packering panel size change
        $('.accordion-toggle').off('click').on('click', function () {
            setTimeout(function () {
                $('.displayCardList').packery();
            }, 100);
        });
        // displayCard events
        $(".displayCard").off('click').on('click', function (event) {
            if (event.ctrlKey) {
                var url = '/index.php?v=d&p=' + $this.name + '&id=' + $(this).attr('data-id');
                window.open(url).focus();
            } else {
                $this.displayA($(this).attr('data-id'));
            }
        });
        $('.displayCard').off('mouseup').on('mouseup', function (event) {
            if (event.which === 2) {
                event.preventDefault();
                var id = $(this).attr('data-id');
                $('.displayCard[data-id="' + id + '"]').trigger(jQuery.Event('click', {ctrlKey: true}));
            }
        });

        // Param changed : page leaving lock by msgbox
        $('#div_conf').delegate('.attr', 'change', function () {
            //if (!lockModify) {
            this.modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
            //}
        });
        // Cancel modifications
        $('.bt_cancelModifs').on('click', function () {
            $this.loadFromUrl();
        });
        // widget go back list button
        $('#bt_ThumbnailDisplay').on('click', function () {
            loadPage('index.php?v=d&p=' + $this.name);
        });

        // Icon choose button
        $('#bt_chooseIcon').on('click', function () {
            var icon = false;
            var color = false;
            if ($('div[data-l2key="icon"] > i').length) {
                color = '';
                var class_icon = $('div[data-l2key="icon"] > i').attr('class');
                class_icon = class_icon.replace(' ', '.').split(' ');
                icon = '.' + class_icon[1];
                if (class_icon[2]) {
                    color = class_icon[2];
                }
            }
            chooseIcon(function (_icon) {
                $('.attr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
            }, {icon: icon, color: color});
        });

        // Icon delete on double click
        $('.attr[data-l1key=display][data-l2key=icon]').on('dblclick', function () {
            $('.attr[data-l1key=display][data-l2key=icon]').value('');
        });

        this.initEvents();
    }
    initEvents() {/* nothing */
    }

    clean() {/* nothing */
    }

    /**
     * Load with the URL data
     */
    loadFromUrl() {
        var idFromUrl = getUrlVars('id');
        if (idFromUrl) {
            if ($('.displayCard[data-id=' + idFromUrl + ']').length !== 0) {
                this.displayA(idFromUrl);
            } else {
                notify("Erreur", 'Id ' + idFromUrl + ' not found', 'error');
            }
        }
    }

    /**
     * Display
     * @param id id
     */
    displayA(id) {
        var $this = this;
        $('#div_conf').show();
        $('#thumbnailDisplay').hide();
        $('.displayCard').removeClass('active');
        $('.displayCard[data-id=' + id + ']').addClass('active');
        this.display(id);
        addOrUpdateUrl('id', id);
        if (document.location.toString().split('#')[1] === '' || document.location.toString().split('#')[1] === undefined) {
            $('.nav-tabs a[href="#generaltab"]').click();
        }
        setTimeout(function () {
            $(".bt_cancelModifs").hide();
            $this.modifyWithoutSave = false;
        }, 300);
    }

    /**
     * Display
     * @param id id
     */
    display(id) {
        throw new Error('You must implement this function : display');
    }

    save(objectPage) {
        throw new Error('You must implement this function : save');
    }

    remove(id) {
        throw new Error('You must implement this function : remove');
    }
}