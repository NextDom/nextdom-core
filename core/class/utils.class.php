<?php

require_once __DIR__ . '/../php/core.inc.php';

class utils {
    public static function o2a($_object, $_noToArray = false) {
        return \NextDom\Helpers\Utils::o2a($_object, $_noToArray);
    }

    public static function a2o(&$_object, $_data) {
        \NextDom\Helpers\Utils::a2o($_object, $_data);
    }

    public static function processJsonObject($_class, $_ajaxList, $_dbList = null) {
        \NextDom\Helpers\Utils::processJsonObject($_class, $_ajaxList, $_dbList);
    }

    public static function setJsonAttr($_attr, $_key, $_value = null) {
        return \NextDom\Helpers\Utils::setJsonAttr($_attr, $_key, $_value);
    }

    public static function getJsonAttr(&$_attr, $_key = '', $_default = '') {
        return \NextDom\Helpers\Utils::getJsonAttr($_attr, $_key, $_default);
    }
}