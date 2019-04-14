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

require('vendor/autoload.php');

define('TEST_URL', 'http://127.0.0.1:8765/core/ajax/');
define('ADMIN_ACCOUNT', 'admin');
define('USER_ACCOUNT', 'user');
define('PASSWORD', 'nextdom-test');

class AjaxBase extends PHPUnit_Framework_TestCase
{
    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    private $ajaxToken;

    public function setUp()
    {
        $this->client = new GuzzleHttp\Client(['cookies' => true]);
        $this->getAjaxTokenFromBody();
    }

    protected function connectAsAdmin()
    {
        $this->login(ADMIN_ACCOUNT, PASSWORD);
    }

    protected function connectAsUser()
    {
        $this->login(USER_ACCOUNT, PASSWORD);
    }

    private function login($username, $password)
    {
        $result = $this->getAjaxQueryResult('user', ['action' => 'login', 'username' => $username, 'password' => $password]);
        if ($result->getBody() === '{"state":"ok","result":""}') {
            return true;
        }
        return false;
    }

    protected function getAjaxQueryResult($ajaxFile, $params)
    {
        return $this->getUrlResult(TEST_URL . $ajaxFile . '.ajax.php?' . http_build_query($params));

    }

    protected function getAjaxQueryWithTokenResult($ajaxFile, $params)
    {
        $params = array_merge($params, ['nextdom_token' => $this->ajaxToken]);
        return $this->getUrlResult(TEST_URL . $ajaxFile . '.ajax.php?' . http_build_query($params));
    }

    private function getUrlResult($url)
    {
        $res = '';
        try {

            $res = $this->client->request('GET', $url);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $res = $e->getResponse();
        }
        return $res;
    }

    private function getAjaxTokenFromBody()
    {
        $res = $this->client->request('GET', 'http://127.0.0.1:8765/');
        preg_match('/NEXTDOM_AJAX_TOKEN = \'(.*?)\'/', $res->getBody(), $matchResult);
        if (count($matchResult) > 1) {
            $this->ajaxToken = $matchResult[1];
        }
    }

    protected function resetAjaxToken()
    {
        $this->ajaxToken = '';
    }
}