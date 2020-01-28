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

nextdom.nextdom_market = function() {};

nextdom.nextdom_market.get = function(queryParams) {
  var paramsRequired = ['data', 'params'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDomMarket', 'get');
    ajaxParams.data['data'] = json_encode(queryParams.data);
    ajaxParams.data['params'] = queryParams.params;
    nextdom.private.ajaxCall(ajaxParams);
  }
};

nextdom.nextdom_market.refresh = function(queryParams) {
  var paramsRequired = ['data', 'params'];
  if (nextdom.private.isValidQuery(queryParams, paramsRequired)) {
    var params = $.extend({}, nextdom.private.default_params, queryParams || {});
    var ajaxParams = nextdom.private.getParamsAJAX(params, 'NextDomMarket', 'get');
    ajaxParams.data['data'] = json_encode(queryParams.data);
    ajaxParams.data['params'] = queryParams.params;
    nextdom.private.ajaxCall(ajaxParams);
  }
};