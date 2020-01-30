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


// ?
jQuery.fn.findAtDepth = function (selector, maxDepth) {
  var depths = [], i;

  if (maxDepth > 0) {
    for (i = 1; i <= maxDepth; i++) {
      depths.push('> ' + new Array(i).join('* > ') + selector);
    }

    selector = depths.join(', ');
  }
  return this.find(selector);
};

/**
 * Floating notification
 *
 * @param _title title of the notification
 * @param _text text of the notification
 * @param _class_name equivalent to the color of notification (success, warning, error, nextdom color)
 */
function notify(_title, _text, _class_name) {
  if (typeof(notify_status)!= 'undefined' && isset(notify_status) && notify_status == 1) {
    var _backgroundColor = '';
    var _icon = '';

    if (_title == '') {
      _title = 'Core';
    }
    if (_text == '') {
      _text = 'Erreur inconnue';
    }
    if (_class_name == 'success') {
      _backgroundColor = '#00a65a';
      _icon = 'far fa-check-circle fa-3x';
    } else if (_class_name == 'warning') {
      _backgroundColor = '#f39c12';
      _icon = 'fas fa-exclamation-triangle fa-3x';
    } else if (_class_name == 'error') {
      _backgroundColor = '#dd4b39';
      _icon = 'fas fa-times fa-3x';
    } else {
      _backgroundColor = '#33B8CC';
      _icon = 'fas fa-info fa-3x';
    }

    iziToast.show({
      id: null,
      class: '',
      title: _title,
      titleColor: 'white',
      titleSize: '1.5em',
      titleLineHeight: '30px',
      message: _text,
      messageColor: 'white',
      messageSize: '',
      messageLineHeight: '',
      theme: 'dark', // dark
      iconText: '',
      backgroundColor: _backgroundColor,
      icon: _icon,
      iconColor: 'white',
      iconUrl: null,
      image: '',
      imageWidth: 50,
      maxWidth: jQuery(window).width() - 500,
      zindex: null,
      layout: 2,
      balloon: false,
      close: true,
      closeOnEscape: false,
      closeOnClick: false,
      displayMode: 0, // once, replace
      position: notify_position, // bottomRight, bottomLeft, topRight, topLeft, topCenter, bottomCenter, center
      target: '',
      targetFirst: true,
      timeout: notify_timeout * 1000,
      rtl: false,
      animateInside: true,
      drag: true,
      pauseOnHover: true,
      resetOnHover: false,
      progressBar: true,
      progressBarColor: '',
      progressBarEasing: 'linear',
      overlay: false,
      overlayClose: false,
      overlayColor: 'rgba(0, 0, 0, 0.6)',
      transitionIn: 'fadeInUp',
      transitionOut: 'fadeOut',
      transitionInMobile: 'fadeInUp',
      transitionOutMobile: 'fadeOutDown',
      buttons: {},
      inputs: {},
      onOpening: function () {
      },
      onOpened: function () {
      },
      onClosing: function () {
      },
      onClosed: function () {
      }
    });
  }
}

/**
 * Opening a icone selector modal to choose one
 *
 * @param callbackFunc Callback function who receive the icon code
 */
function chooseIcon(callbackFunc) {
  var chooseIconModal = $('#mod_selectIcon');
  if (chooseIconModal.length === 0) {
    $('#div_pageContainer').append('<div id="mod_selectIcon" title="{{Choisissez votre icône}}" ></div>');
    chooseIconModal = $('#mod_selectIcon');
    // Init choose icon modal
    chooseIconModal.dialog({
      closeText: '',
      autoOpen: false,
      modal: true,
      height: (jQuery(window).height() - 150),
      width: getModalWidth(),
      open: function () {
        $('body').css({overflow: 'hidden'});
        $(this).dialog('option', 'position', {my: 'center', at: 'center', of: window});
      },
      beforeClose: function (event, ui) {
        $('body').css({overflow: 'inherit'});
      }
    });
    // Populate modal
    jQuery.ajaxSetup({async: false});
    chooseIconModal.load('index.php?v=d&modal=icon.selector');
    jQuery.ajaxSetup({async: true});
  }
  chooseIconModal.dialog('option', 'buttons', {
    'Annuler': function () {
      $(this).dialog('close');
    },
    'Valider': function () {
      var selectedIcon = $('.iconSelected .iconSel').html();
      if (typeof (selectedIcon) === 'undefined') {
        selectedIcon = '';
      }
      selectedIcon = selectedIcon.replace(/"/g, "'");
      callbackFunc(selectedIcon);
      $(this).dialog('close');
    }
  });
  chooseIconModal.dialog('open');
}

/**
 * Blocking sleep loop for a while
 *
 * @param milliseconds number of milliseconds you want to sleep execution
 */
function sleep(milliseconds) {
  var startLoop = new Date().getTime();
  do {
    // Waiting
  } while ((new Date().getTime() - startLoop) < milliseconds);
}

/**
 * Create a unique cmd ID
 *
 * @param _prefix ID prefix
 */
function uniqId(_prefix) {
  if (typeof(_prefix)== 'undefined') {
    _prefix = 'jee-uniq';
  }
  do {
    var result = _prefix + '-' + uniqId_count + '-' + Math.random().toString(36).substring(8);
    uniqId_count++;
  } while ($('#' + result).length !== 0);
  return result;
}

/**
 * Control size of widget, and assign class category for filtering
 *
 * @param _id EqLogic ID, if null>ALL
 * @param _preResize TRUE if pre-resizing
 * @param _scenario TRUE if it's a scenario widget
 */
function positionEqLogic(_id, _preResize, _scenario) {
  if (_id != undefined) {
    var eqLogic = $('.eqLogic-widget[data-eqlogic_id=' + _id + ']');
    var widget = (_scenario) ? $('.scenario-widget[data-scenario_id=' + _id + ']') : $('.eqLogic-widget[data-eqlogic_id=' + _id + ']');
    widget.css('margin', '0px').css('padding', '0px');
    eqLogic.trigger('resize');
    eqLogic.addClass(eqLogic.attr('data-category'));
    eqLogic.css('border-radius', widget_radius + 'px');
  } else {
    $('.eqLogic-widget:not(.nextdomAlreadyPosition)').css('margin', '0px').css('padding', '0px');
    $('.eqLogic-widget:not(.nextdomAlreadyPosition)').each(function () {
      if ($(this).width() == 0) {
        $(this).width('100px');
      }
      if ($(this).height() == 0) {
        $(this).height('100px');
      }
      $(this).trigger('resize');
      $(this).addClass($(this).attr('data-category'));
    });
    $('.eqLogic-widget:not(.nextdomAlreadyPosition)').css('border-radius', widget_radius + 'px');
    $('.eqLogic-widget').addClass('nextdomAlreadyPosition');
  }
}

/**
 * Remove a Equipement context
 */
function removeContextualFunction() {
  printEqLogic = undefined
}

/**
 * Convert a text on a link
 *
 * @param inputText text to convert
 */
function linkify(inputText) {
  var replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
  var replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');
  var replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
  var replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');
  var replacePattern3 = /(\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6})/gim;
  var replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');
  return replacedText
}

/**
 * Reset à config param to his default value in default.config.ini
 *
 * @param keyElt Elt or button who handle the reset and contain the config key
 */
function resetConfigParamKey(keyElt) {
  lockModify = true;
  var paramKey = keyElt.attr('data-l1key');
  var defaultValue = '';
  var arrayKey = paramKey.split('::');
  arrayKey.pop();
  var paramSubKey = arrayKey.join('::');
  nextdom.config.remove({
    configuration: paramKey,
    error: function (error) {
      notify('Core', error.message, 'error');
    },
    success: function (dataRemove) {
      nextdom.config.load({
        configuration: paramKey,
        error: function (error) {
          notify('Core', error.message, 'error');
        },
        success: function (dataLoad) {
          if (isset(dataLoad) && dataLoad != '') {
            // Direct slider
            keyElt.siblings('.slider').value(dataLoad);
            // Or associate fields
            $('.configKey[data-l1key="' + paramKey + '"]').value(dataLoad);
            lockModify = false;
          } else {
            nextdom.config.load({
              configuration: paramSubKey,
              error: function (error) {
                notify('Core', error.message, 'error');
              },
              success: function (dataSubLoad) {
                if (isset(dataSubLoad) && dataSubLoad != '') {
                  defaultValue = dataSubLoad;
                } else {
                  defaultValue = 0;
                }
                // Direct slider
                keyElt.siblings('.slider').value(defaultValue);
                // Or associate fields
                $('.configKey[data-l1key="' + paramKey + '"]').value(dataSubLoad);
                lockModify = false;
              }
            });
          }
        }
      });
    }
  });
}

/**
 * Calcul a score password from 0=none, 2/4=low, 8/16=Middle, 32/64=High, 128/256=VeryHigh
 *
 * @param password password value
 * @param progressbar progressbar component with role="progressbar"
 * @param spanLevel span id to write level
 */
function passwordScore(password, progressbar = null, spanLevel = null) {
  var passwordToScore = password.toString();
  var score = passwordToScore.match(/\d/) ? 15 : 0;
  score += passwordToScore.match(/(.*\d.*\d.*\d)/) ? 15 : 0;
  score += passwordToScore.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/) ? 15 : 0;
  score += passwordToScore.match(/\W/) ? 15 : 0;
  score += passwordToScore.match(/(.*\W.*\W)/) ? 15 : 0;
  score += passwordToScore.length >= 3 ? 10 : 0;
  score += passwordToScore.length >= 10 ? 15 : 0;
  if (progressbar !== null) {
    progressbar.width(score + '%');
    progressbar.removeClass('progress-bar-green').removeClass('progress-bar-yellow').removeClass('progress-bar-red');
    if (score > 0 && score <= 25) {
      progressbar.addClass('progress-bar-red');
    } else if (score >= 25 && score <= 70) {
      progressbar.addClass('progress-bar-yellow');
    } else if (score > 70) {
      progressbar.addClass('progress-bar-green');
    }
  }
  var textLevel = '';
  if (spanLevel !== null) {
    if (score === 0) {
      textLevel = '{{Sécurité Trés Faible}}';
    } else if (score > 0 && score <= 25) {
      textLevel = '{{Sécurité Faible}}';
    } else if (score > 25 && score <= 70) {
      textLevel = '{{Sécurité Moyenne}}';
    } else if (score > 70 && score < 100) {
      textLevel = '{{Sécurité Forte}}';
    } else if (score === 100) {
      textLevel = '{{Sécurité Trés Forte}}';
    }
    spanLevel.html('<i class="fas fa-shield-alt"></i>' + textLevel)
  }
  return score;
}

/**
 * Decode HTML entities in string like &eacute;
 * @param message Decoded message
 */
function decodeHtmlEntities(message) {
  var temporaryTextArea = document.createElement('textarea');
  temporaryTextArea.innerHTML = message;
  return temporaryTextArea.value;
}
