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
 * Class ObjectRight
 * @package NextDom\Enums
 */
class ControllerData extends Enum
{
    const AJAX_TOKEN = 'AJAX_TOKEN';
    const ALERT_MSG = 'ALERT_MSG';
    const CAN_SUDO = 'CAN_SUDO';
    const CONTENT = 'content';
    const CONTENT_MAIN = 'CONTENT_MAIN';
    const CSS_POOL = 'CSS_POOL';
    const IS_ADMIN = 'IS_ADMIN';
    const IS_MOBILE = 'IS_MOBILE';
    const LANGUAGE = 'LANGUAGE';
    const PLUGINS_JS_POOL = 'PLUGINS_JS_POOL';
    const JS_END_POOL = 'JS_END_POOL';
    const JS_POOL = 'JS_POOL';
    const JS_VARS = 'JS_VARS';
    const JS_VARS_RAW = 'JS_VARS_RAW';
    const TITLE = 'TITLE';
}
