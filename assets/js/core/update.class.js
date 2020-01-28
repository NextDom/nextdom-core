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

nextdom.update = function() {
};

nextdom.update.doAll = function(queryParams) {
  var params = $.extend({}, nextdom.private.default_params, queryParams || {});
  var ajaxParams = nextdom.private.getParamsAJAX(params, 'Update', 'updateAll');
  ajaxParams.data['options'] = json_encode(queryParams.options) || '';
  nextdom.private.ajaxCall(ajaxParams);
};

nextdom.update.do = function(queryParams) {
  nextdom.private.ajax('Update', 'update', queryParams, ['id']);
};

nextdom.update.remove = function(queryParams) {
  nextdom.private.ajax('Update', 'remove', queryParams, ['id']);
};

nextdom.update.update = function(queryParams) {
  nextdom.private.ajax('Update', 'update', queryParams, ['id']);
};

nextdom.update.checkAll = function(queryParams) {
  nextdom.private.ajax('Update', 'checkAllUpdate', queryParams);
};

nextdom.update.check = function(queryParams) {
  nextdom.private.ajax('Update', 'checkUpdate', queryParams, ['id']);
};

nextdom.update.get = function(queryParams) {
  nextdom.private.ajax('Update', 'all', queryParams);
};

nextdom.update.install = function(queryParams) {
  nextdom.private.ajax('Update', 'save', queryParams, ['update'], true, true);
};

nextdom.update.save = function(queryParams) {
  console.log(queryParams);
  nextdom.private.ajax('Update', 'save', queryParams, ['update'], true);
};

nextdom.update.saves = function(queryParams) {
  nextdom.private.ajax('Update', 'saves', queryParams, ['updates'], true);
};

nextdom.update.number = function(queryParams) {
  nextdom.private.ajax('Update', 'nbUpdate', queryParams, false, false, false);
};