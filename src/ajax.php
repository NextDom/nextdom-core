<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom;

use NextDom\Ajax\BaseAjax;
use NextDom\Helpers\Utils;

require_once('core.php');

$target = Utils::initStr('target');
$targetClass = '\\NextDom\\Ajax\\' . $target . 'Ajax';
if (class_exists($targetClass)) {
    /** @var BaseAjax $ajaxClass */
    $ajaxClass = new $targetClass();
    $ajaxClass->process();
}