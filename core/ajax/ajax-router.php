<?php

namespace NextDom;

use NextDom\Enums\AjaxActionsEnum;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;

require_once __DIR__ . '/../../core/php/core.inc.php';

\include_file('core', 'authentification', 'php');
Status::initConnectState();
$action = Utils::init('ajax-action', false);
if ($action) {
    if (in_array($action, AjaxActionsEnum::getConstants())) {
        require_once($action.'.ajax.php');
    } else {
        throw new \Exception(__('core.error-ajax'));
    }
}
