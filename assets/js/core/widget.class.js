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

nextdom.widget = function () {
};

nextdom.widget.remove = function (queryParams) {
  nextdom.private.ajax('Widget', 'remove', queryParams, ['id']);
};

nextdom.widget.get = function (queryParams) {
  nextdom.private.ajax('Widget', 'byId', queryParams, ['id']);
};

nextdom.widget.loadConfig = function (queryParams) {
  nextdom.private.ajax('Widget', 'loadConfig', queryParams, ['template']);
};

nextdom.widget.getThemeImg = function (_light, _dark) {
  if (_light !== '' && _dark === '') {
    return _light;
  }
  if (_light === '' && _dark !== '') {
    return _dark;
  }
  if ($('body')[0].hasAttribute('data-theme')) {
    if ($('body').attr('data-theme').endsWith('Light'))
      return _light;
  }
  return _dark;
};

nextdom.widget.getPreview = function (queryParams) {
  nextdom.private.ajax('Widget', 'getPreview', queryParams, ['id']);
};

nextdom.widget.save = function (queryParams) {
  nextdom.private.ajax('Widget', 'save', queryParams, ['widget'], true);
};

//Modal de Remplacement bouton dans le menu de configuration
nextdom.widget.replacement = function(queryParams) {
  nextdom.private.ajax('Widget', 'replacement', queryParams, ['version','replace','by']);
};

nextdom.widgets = nextdom.widget;