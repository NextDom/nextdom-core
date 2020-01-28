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

nextdom.message = function() {};

nextdom.message.cache = Array();

nextdom.message.all = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'Message', 'all');
  ajaxParams.data['plugin'] = queryParams.plugin || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.message.remove = function(queryParams) {
  nextdom.private.ajax('Message', 'removeMessage', queryParams, ['id']);
};

nextdom.message.clear = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'Message', 'clearMessage');
  ajaxParams.data['plugin'] = queryParams.plugin || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.message.number = function(queryParams) {
  nextdom.private.ajax('Message', 'nbMessage', queryParams, false, false, false);
};