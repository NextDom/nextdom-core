<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Model\Entity;

use NextDom\Helpers\Utils;

/**
 * Class JsonRPC
 * @package NextDom\Model\Entity
 */
class JsonRPC
{
    protected $jsonrpc;
    protected $method;
    protected $params;
    protected $id = 99999;
    protected $startTime;
    protected $applicationName;
    protected $additionnalParams = [];

    /**
     *
     * @param string $_jsonrpc
     */
    public function __construct($_jsonrpc)
    {
        $this->startTime = Utils::getMicrotime();
        $this->applicationName = 'Unknown';
        $jsonrpc = json_decode($_jsonrpc, true);
        $this->jsonrpc = $jsonrpc['jsonrpc'];
        $this->method = ($jsonrpc['method'] != '') ? $jsonrpc['method'] : 'none';
        if (isset($jsonrpc['params'])) {
            if (is_array($jsonrpc['params'])) {
                $this->params = $jsonrpc['params'];
            } else {
                $this->params = json_decode($jsonrpc['params'], true);
            }
        }
        if (isset($jsonrpc['id'])) {
            $this->id = $jsonrpc['id'];
        }
    }

    /**
     * @param $_code
     * @param $_message
     */
    public function makeError($_code, $_message)
    {
        $return = [
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'error' => [
                'code' => $_code,
                'message' => $_message,
            ],
        ];
        $return = array_merge($return, $this->getAdditionnalParams());
        if (Utils::init('callback') != '') {
            echo Utils::init('callback') . '(' . json_encode($return) . ')';
        } else {
            echo json_encode($return);
        }
        exit;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getAdditionnalParams($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->additionnalParams, $_key, $_default);
    }

    /*     * ********Getteur Setteur******************* */

    /**
     * @param $_key
     * @param $_value
     */
    public function setAdditionnalParams($_key, $_value)
    {
        if (in_array($_key, ['result', 'jsonrpc', 'id'])) {
            return;
        }
        $this->additionnalParams = Utils::setJsonAttr($this->additionnalParams, $_key, $_value);
    }

    /**
     * @param string $_result
     */
    public function makeSuccess($_result = 'ok')
    {
        $return = [
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'result' => $_result,
        ];
        $return = array_merge($return, $this->getAdditionnalParams());
        if (Utils::init('callback') != '') {
            echo Utils::init('callback') . '(' . json_encode($return) . ')';
        } else {
            echo json_encode($return);
        }
        exit;
    }

    /**
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * @param $applicationName
     * @return $this
     */
    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJsonrpc()
    {
        return $this->jsonrpc;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
