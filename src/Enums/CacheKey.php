<?php
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
 */

namespace NextDom\Enums;

/**
 * Class CacheKey
 * @package NextDom\Enums
 */
class CacheKey extends Enum
{
    const ALERT_LEVEL = 'alertLevel';
    const CACHE_ENGINE = 'cache::engine';
    const COLLECT_DATE = 'collectDate';
    const EQLOGIC_CACHE_ATTR = 'eqLogicCacheAttr';
    const EQLOGIC_STATUS_ATTR = 'eqLogicStatusAttr';
    const EVENT = 'event';
    const SCENARIO_CACHE_ATTR = 'scenarioCacheAttr';
    const VALUE = 'value';
    const VALUE_DATE = 'valueDate';
    const WIDGET_HTML = 'widgetHtml';
}
