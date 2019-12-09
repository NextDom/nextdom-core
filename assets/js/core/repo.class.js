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


nextdom.repo = function () {
};

nextdom.repo.install = function (queryParams) {
  var paramsRequired = ['id', 'repo'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'install');
    ajaxParams.data['repo'] = queryParams.repo;
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['version'] = queryParams.version || 'stable';
    $.ajax(ajaxParams);
  }
};

nextdom.repo.remove = function (queryParams) {
  var paramsRequired = ['id', 'repo'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'remove');
    ajaxParams.data['repo'] = queryParams.repo;
    ajaxParams.data['id'] = queryParams.id;
    $.ajax(ajaxParams);
  }
};

nextdom.repo.setRating = function (queryParams) {
  var paramsRequired = ['id', 'rating', 'repo'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'setRating');
    ajaxParams.data['repo'] = queryParams.repo;
    ajaxParams.data['id'] = queryParams.id;
    ajaxParams.data['rating'] = queryParams.rating;
    $.ajax(ajaxParams);
  }
};

nextdom.repo.test = function (queryParams) {
  var paramsRequired = ['repo'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'test');
    ajaxParams.data['repo'] = queryParams.repo;
    $.ajax(ajaxParams);
  }
};


nextdom.repo.backupList = function (queryParams) {
  var paramsRequired = ['repo'];
  var paramsSpecifics = {
    global: queryParams.global || true,
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Repo', 'backupList');
    ajaxParams.data['repo'] = queryParams.repo;
    $.ajax(ajaxParams);
  }
};