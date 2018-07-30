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

 $(function(){
     $('body').on('nextdom_page_load',function(){
         $('.backgroundforNextDom').css('background-image','');
         $('.backgroundforNextDom').css('background-position','center center');
         $('.backgroundforNextDom').css('background-repeat','no-repeat');
         $('.backgroundforNextDom').css('background-size','cover');
         if(typeof nextdomBackgroundImg !== 'undefined' && nextdomBackgroundImg != null){
             $('.backgroundforNextDom').css('background-image','url("'+nextdomBackgroundImg+'")');
             if(typeof nextdomBackgroundPosition !== 'undefined' && nextdomBackgroundPosition != null){
                 $('.backgroundforNextDom').css('background-position',''+nextdomBackgroundPosition+'');
             }
             if(typeof nextdomBackgroundRepeat !== 'undefined' && nextdomBackgroundRepeat != null){
                 $('.backgroundforNextDom').css('background-repeat',''+nextdomBackgroundRepeat+'');
             }
         }else{
             switch (getUrlVars('p')){
                 case 'scenario':
                 $('.backgroundforNextDom').css('background-image','url("core/themes/nextdom/desktop/background/scenario.png")');
                 $('.backgroundforNextDom').css('background-position','bottom right');
                 $('.backgroundforNextDom').css('background-repeat','no-repeat');
                 $('.backgroundforNextDom').css('background-size','auto');
                 break;
                 case 'interact':
                 $('.backgroundforNextDom').css('background-image','url("core/themes/nextdom/desktop/background/interact.png")');
                 $('.backgroundforNextDom').css('background-position','bottom right');
                 $('.backgroundforNextDom').css('background-repeat','no-repeat');
                 $('.backgroundforNextDom').css('background-size','auto');
                 break;
                 case 'object':
                 $('.backgroundforNextDom').css('background-image','url("core/themes/nextdom/desktop/background/object.png")');
                 $('.backgroundforNextDom').css('background-position','bottom right');
                 $('.backgroundforNextDom').css('background-repeat','no-repeat');
                 $('.backgroundforNextDom').css('background-size','auto');
                 break;
                 case 'display':
                 $('.backgroundforNextDom').css('background-image','url("core/themes/nextdom/desktop/background/display.png")');
                 $('.backgroundforNextDom').css('background-position','bottom right');
                 $('.backgroundforNextDom').css('background-repeat','no-repeat');
                 $('.backgroundforNextDom').css('background-size','auto');
                 break;
             }
         }
     });
 });