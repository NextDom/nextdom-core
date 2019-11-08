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
 * Common configuration keys
 * @package NextDom\Enums
 */
class CmdConfiguration extends Enum
{
    const ACTION_CODE_ACCESS = 'actionCodeAccess';
    const ACTION_CHECK_CMD = 'actionCheckCmd';
    const CALCUL_VALUE_OFFSET = 'calculValueOffset';
    const CMD_CACHE_ATTR = 'cmdCacheAttr';
    const DENY_VALUES = 'denyValues';
    const HISTORIZE_ROUND = 'historizeRound';
    const LAST_CMD_VALUE = 'lastCmdValue';
    const LIST_VALUE = 'listValue';
    const MIN_VALUE = 'minValue';
    const MIN_VALUE_REPLACE = 'minValueReplace';
    const MAX_VALUE = 'maxValue';
    const MAX_VALUE_REPLACE = 'maxValueReplace';
    const NEVER_FAIL = 'nerverFail';
    const NEXTDOM_CHECK_CMD_OPERATOR = 'nextdomCheckCmdOperator';
    const NEXTDOM_CHECK_CMD_TEST = 'nextdomCheckCmdTest';
    const NEXTDOM_CHECK_CMD_TIME = 'nextdomCheckCmdTime';
    const NEXTDOM_POST_EXEC_CMD = 'nextdomPostExecCmd';
    const NEXTDOM_PRE_EXEC_CMD = 'nextdomPreExecCmd';
    const NEXTDOM_PUSH_URL = 'nextdomPushUrl';
    const REPEAT_EVENT_MGMT = 'repeatEventManagement';
    const RETURN_STATE_VALUE = 'returnStateValue';
    const RETURN_STATE_TIME = 'returnStateTime';
    const TIMELIME_ENABLE = 'timeline::enable';
    const UPDATE_CMD_ID = 'updateCmdId';
}
