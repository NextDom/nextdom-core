<?php

use NextDom\Rest\Authenticator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

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
}
catch (Exception $e) {
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
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $parameterName = $parameter->getName();
                if (isset($route[$parameterName])) {
                    $callParameters[] = $route[$parameterName];
                }
            }
        }
        $result = call_user_func_array($route['_controller'], $callParameters);
        $response->setData($result);
    }
    catch (ResourceNotFoundException $resourceNotFoundException) {
        $response->setStatusCode(404);
        $response->setData(['error' => $resourceNotFoundException->getMessage()]);
    }
    catch (\TypeError $e) {
        $response->setStatusCode(400);
        $response->setData(['error' => $e->getMessage()]);
    }
    catch (\Throwable $t) {
        $response->setStatusCode(400);
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
                    $response->setStatusCode(400);
                    $response->setData('Bad credentials');
                }
            } catch (Exception $e) {
                $response->setStatusCode(400);
                $response->setData('Get user error ' . $e->getMessage());
            }
        }
    }
    else {
        $response->setStatusCode(403);
        $response->setData('Forbidden access');
    }
    /*
     * Réalisé par Vuejs
    else {
        // Show login page
        $response = new Response(file_get_contents(NEXTDOM_ROOT . '/views/mobile/login.html'));
    }
    */
}

$response->send();