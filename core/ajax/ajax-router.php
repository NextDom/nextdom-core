<?php

namespace NextDom;

use NextDom\Enums\AjaxActionsEnum;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;

require_once __DIR__ . '/../../core/php/core.inc.php';

\include_file('core', 'authentification', 'php');
Status::initConnectState();
$action = Utils::init('ajax-action', false);
if (\config::byKey('developer::mode', 'core', '0') === '1') {
    error_log('AJAX QUERY '.$action);
    $params = '';
    foreach ($_POST as $param => $value) {
        if (is_array($value)) {
            $params .= $param.' => Array ';
        }
        elseif (is_object($value)) {
            $params .= $param.' => Object ';
        }
        else {
            $params .= $param.' => '.$value.' ';
        }
    }
    error_log('PARAMETERS '.$params);
}
if ($action) {
    if (in_array($action, AjaxActionsEnum::getConstants())) {
        require_once($action.'.ajax.php');
    } else {
        throw new \Exception(__('core.error-ajax'));
    }
}
