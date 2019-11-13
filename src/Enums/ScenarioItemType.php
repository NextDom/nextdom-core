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
 * Class ScenarioExpressionType
 * @package NextDom\Enums
 */
class ScenarioItemType extends Enum
{
    const ACTION = 'action';
    const CODE = 'code';
    const CONDITION = 'condition';
    const DO = 'do';
    const ELEMENT = 'element';
    const ELSE = 'else';
    const FOR = 'for';
    const IF = 'if';
    const IN = 'in';
    const THEN = 'then';
}
