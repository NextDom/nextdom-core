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

$(function() {
    $('.colorpick_inline').colorpicker({
        container: true,
        inline: true
    });
    $('.colorpick').colorpicker();
    $(":input").inputmask();
    $('[data-toggle="tooltip"]').tooltip();
    $(".slimScrollDiv").css("overflow","");
    $(".sidebar").css("overflow","");
});
if ($('[role="dialog"] .fab').length == 0) {
    $('.fab').on('mouseleave',function() {
        $('.blurPanel').removeClass('blur');
    });

    $('.fab').on('mouseenter',function() {
        $('.blurPanel').addClass('blur');
    });
} else {
    $('.fab').css('display', 'none');
}

window.onscroll = function() {
    var goOnTopButton = document.getElementById("bt_goOnTop");
    if (goOnTopButton !== undefined && goOnTopButton !== null) {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            goOnTopButton.style.display = "block";
        } else {
            goOnTopButton.style.display = "none";
        }
    }
};

$('#bt_goOnTop').click(function() {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
});
