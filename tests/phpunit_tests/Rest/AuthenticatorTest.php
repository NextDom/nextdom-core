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

use NextDom\Helpers\Api;
use NextDom\Managers\UserManager;
use NextDom\Rest\Authenticator;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/../../../src/core.php');

class AuthenticatorTest extends PHPUnit_Framework_TestCase
{
    private $containerLogin = 'admin';

    private $containerPassword = 'nextdom-test';

    public function testRequestAuthentificationPresent()
    {
        $request = new Request();
        $request->headers->set('X-AUTH-TOKEN', 1);
        $authenticator = Authenticator::init($request);
        $this->assertTrue($authenticator->supportAuthentication());
    }

    public function testRequestAuthentificationMissing()
    {
        $request = new Request();
        $authenticator = Authenticator::init($request);
        $this->assertFalse($authenticator->supportAuthentication());
    }

    public function testCheckCredentialsWithGoodUserAndPassword()
    {
        $authenticator = Authenticator::init(new Request());
        $result = $authenticator->checkCredentials($this->containerLogin, $this->containerPassword);
        $this->assertNotNull($result);
    }

    public function testCheckCredentialsWithBadUserAndPassword()
    {
        $authenticator = Authenticator::init(new Request());
        $this->assertFalse($authenticator->checkCredentials('bad_user', 'bad_password'));
    }

    public function testCreateToken()
    {
        $authenticator = Authenticator::init(new Request());
        $admin = UserManager::byLogin('admin');
        $authenticator->createTokenForUser($admin);
        $token = $admin->getOptions('token');
        $this->assertEquals(172, strlen($token));
        $this->assertEquals(2, substr_count($token, '.'));
    }

    public function testCheckSendedTokenGood()
    {
        $authenticator = Authenticator::init(new Request());
        $admin = UserManager::byLogin('admin');
        $authenticator->createTokenForUser($admin);

        $testRequest = new Request();
        $testRequest->headers->set('X-AUTH-TOKEN', $admin->getOptions('token'));
        $testAuthenticator = Authenticator::init($testRequest);
        $this->assertTrue($testAuthenticator->checkSendedToken());
    }

    public function testCheckSendedTokenBad()
    {
        $authenticator = Authenticator::init(new Request());
        $admin = UserManager::byLogin('admin');
        $authenticator->createTokenForUser($admin);

        $testRequest = new Request();
        $testRequest->headers->set('X-AUTH-TOKEN', 'eyJhbGciOiJIUzI2NiIaInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiMSIsImlzctI7ImxvY2FsaG9zdCIsImV4cCI6MTU2MjY0ODMwMSwic3ViIjoiIiwiYXVkIjoiIn0.meigjfAPBB7zhAu-u7lA0YQUHMMrF-Q9o3S8DTlpVZ8');
        $testAuthenticator = Authenticator::init($testRequest);
        $this->assertFalse($testAuthenticator->checkSendedToken());
    }

    public function testCheckSendedTokenMalformed()
    {
        $authenticator = Authenticator::init(new Request());
        $admin = UserManager::byLogin('admin');
        $authenticator->createTokenForUser($admin);

        $testRequest = new Request();
        $testRequest->headers->set('X-AUTH-TOKEN', 'A really bad token');
        $testAuthenticator = Authenticator::init($testRequest);
        $this->assertFalse($testAuthenticator->checkSendedToken());
    }

    public function testCheckApiKeyWithGoodKey() {
        $goodKey = Api::getApiKey('core');
        $testRequest = new Request();
        $testRequest->query->add(['apikey' => $goodKey]);
        $authenticator = Authenticator::init($testRequest);

        $this->assertTrue($authenticator->checkApiKey());
    }

    public function testCheckApiKeyWithBadKey() {
        $testRequest = new Request();
        $testRequest->query->add(['apikey' => 'This is a bad key']);
        $authenticator = Authenticator::init($testRequest);

        $this->assertFalse($authenticator->checkApiKey());
    }
}
