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

namespace NextDom\Rest;

use NextDom\Managers\UserManager;
use NextDom\Model\Entity\User;
use ReallySimpleJWT\Token;
use Symfony\Component\HttpFoundation\Request;

// user access 10 hours
define('TOKEN_EXPIRATION_TIME', 3600 * 10);

class Authenticator
{
    /**
     * @var Authenticator Instance
     */
    private static $instance;

    /**
     * @var Request Content of the request
     */
    private $request;

    /**
     * @var bool If query is authenticated
     */
    private $authenticated = false;

    /**
     * @var User Connected user
     */
    private $connectedUser = null;

    /**
     * @var string Secret key for token
     */
    private $secret;

    /**
     * Private authenticator constructor with request for singleton
     *
     * @param Request $request
     */
    private function __construct(Request $request)
    {
        global $CONFIG;
        $this->request = $request;
        $this->secret = $CONFIG['secretKey'];
    }

    /**
     * @param Request $request
     * @return Authenticator
     */
    public static function init(Request $request): Authenticator
    {
        self::$instance = new self($request);
        return self::$instance;
    }

    /**
     * Get instance of the singleton
     *
     * @return Authenticator Instance of the singleton
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Test if query use authentication
     *
     * @return bool True if query use authentication
     */
    public function supportAuthentication(): bool
    {
        if ($this->request->headers->has('X-AUTH-TOKEN')) {
            return true;
        }
        return false;
    }

    /**
     * Authentication state of the request
     *
     * @return bool True if user send token
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    /**
     * Check user credentials
     * @param string $login User login
     * @param string $password User passowrd
     *
     * @return User|false User object or false
     * @throws \Exception
     */
    public function checkCredentials(string $login, string $password)
    {
        $user = UserManager::connect($login, $password);
        return $user;
    }

    /**
     * Create user token for next request
     *
     * @param User $user
     *
     * @return string User token
     */
    public function createTokenForUser(User $user): string
    {
        $token = Token::getToken($user->getId(), $this->secret, time() + TOKEN_EXPIRATION_TIME, 'localhost');
        // Save token in database
        $user->setOptions('token', $token);
        $user->save();

        return $token;
    }

    /**
     * Check if sended token is valid
     *
     * @return bool True if the token is valid
     *
     * @throws \Exception
     */
    public function checkSendedToken(): bool
    {
        if ($this->secret === null) {
            $this->authenticated = false;
        } elseif (Token::validate($this->request->headers->get('X-AUTH-TOKEN'), $this->secret)) {
            $this->connectedUser = $this->getUserFromToken();
            if (is_object($this->connectedUser)) {
                $this->authenticated = true;
            }
        }

        return $this->authenticated;
    }

    /**
     * Get the user from the token
     *
     * @return User|null User
     * @throws \Exception
     */
    private
    function getUserFromToken()
    {
        $user = null;
        $payload = Token::getPayload($this->request->headers->get('X-AUTH-TOKEN'));
        if (!empty($payload)) {
            $payloadData = json_decode($payload, true);
            if (isset($payloadData['user_id'])) {
                $user = UserManager::byId($payloadData['user_id']);
            }
        }
        return $user;
    }

    /**
     * Get current connected user
     *
     * @return User Connected user
     */
    public
    function getConnectedUser()
    {
        return $this->connectedUser;
    }
}