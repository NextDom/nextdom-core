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

nextdom.note = function() {
};

nextdom.note.remove = function(queryParams) {
  nextdom.private.ajax('Note', 'remove', queryParams, ['id']);
};

nextdom.note.byId = function(queryParams) {
  nextdom.private.ajax('Note', 'byId', queryParams, ['id']);
};

nextdom.note.save = function(queryParams) {
  nextdom.private.ajax('Note', 'save', queryParams, ['note'], true);
};

nextdom.note.all = function(queryParams) {
  nextdom.private.ajax('Note', 'all', queryParams);
};
