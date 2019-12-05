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

use NextDom\Rest\Authenticator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Load core
require_once(__DIR__ . '/../src/core.php');
// Read context and request
$context = new RequestContext();
$request = Request::createFromGlobals();
$context->fromRequest($request);

// Use request matcher for method check (base match use only path)
$requestMatcher = new RequestMatcher($context->getBaseUrl(), null, $context->getMethod());

// Load route file
$routeFileLocator = new FileLocator(NEXTDOM_ROOT . '/src/Rest');
$router = new Router(
    new YamlFileLoader($routeFileLocator),
    'rest.yml',
    ['cache_dir' => NEXTDOM_DATA . '/cache/routes', 'matcher_class' => $requestMatcher],
    $context
);

// Check user authentication
try {
    $authenticator = Authenticator::init($request);
    if ($authenticator->supportAuthentication()) {
        $authenticator->checkSendedToken();
    }
    else {
        $authenticator->checkApiKey();
    }
}
catch (Exception $e) {
    echo $e->getMessage();
}

// API answers
if ($authenticator->isAuthenticated()) {
    // Prepare response
    $response = new JsonResponse();
    try {
        $route = $router->matchRequest($request);
        // Call the controller with data from route file
        $method = new ReflectionMethod($route['_controller']);
        // Find all necessary parameters
        $parameters = $method->getParameters();
        $callParameters = [];
        // Inject $request if necessary
        if (count($parameters) > 0 && $parameters[0]->getName() === "request") {
            $callParameters[] = $request;
        }
        // Link all parameters from URL for method calls
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $parameterName = $parameter->getName();
                if (isset($route[$parameterName])) {
                    $callParameters[] = $route[$parameterName];
                }
            }
        }
        // Call the method with params
        $result = call_user_func_array($route['_controller'], $callParameters);
        $response->setData($result);
    }
    catch (ResourceNotFoundException $resourceNotFoundException) {
        // Bad route
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setData(['error' => $resourceNotFoundException->getMessage()]);
    }
    catch (MethodNotAllowedException $methodNotAllowedException) {
        // Bad method
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setData(['error' => 'Method not allowed.']);
    }
    catch (\TypeError $e) {
        // Bad arguments
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        $response->setData(['error' => $e->getMessage()]);
    }
    catch (\Throwable $t) {
        // Others errors
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        $response->setData(['error' => $t->getMessage()]);
    }
}
// Connection case
else {
    $response = new JsonResponse();
    // User try connection
    if (strpos($request->getPathInfo(), '/connect') === 0) {
        if (isset($_GET['login']) && isset($_GET['password'])) {
            try {
                $user = $authenticator->checkCredentials($_GET['login'], $_GET['password']);
                if (is_object($user)) {
                    $token = $authenticator->createTokenForUser($user);
                    $response->setData(['token' => $token]);
                }
                else {
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    $response->setData('Bad credentials');
                }
            } catch (Exception $e) {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                $response->setData('Get user error ' . $e->getMessage());
            }
        }
    }
    else {
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $response->setData('Forbidden access');
    }
}

// Send answer with correct header
$response->send();
