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

nextdom.cache = function() {};

nextdom.cache.clean = function(queryParams) {
  nextdom.private.ajax('Cache', 'clean', queryParams);
};

nextdom.cache.flush = function(queryParams) {
  nextdom.private.ajax('Cache', 'flush', queryParams);
};

nextdom.cache.stats = function(queryParams) {
  nextdom.private.ajax('Cache', 'stats', queryParams);
};