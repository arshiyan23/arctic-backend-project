<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$allowedCorsOrigins = [
  'https://artic.com.qa',
  'https://www.artic.com.qa',
  'https://lemon-desert-02d2c7a00.7.azurestaticapps.net',
  'http://localhost:3001',
  'http://localhost:3000',
];
$corsOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($corsOrigin, $allowedCorsOrigins, TRUE)) {
  header('Access-Control-Allow-Origin: ' . $corsOrigin);
  header('Vary: Origin', FALSE);
  header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: authorization, content-type, accept, origin, x-requested-with');
  header('Access-Control-Max-Age: 86400');
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    return;
  }
}

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$prefix = '/index.php';
if (str_starts_with($requestPath, $prefix . '/')) {
  $pathInfo = substr($requestPath, strlen($prefix));
  $_SERVER['PATH_INFO'] = $pathInfo;
  $_SERVER['ORIG_PATH_INFO'] = $pathInfo;
  $_SERVER['REQUEST_URI'] = $pathInfo . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
}

$_SERVER['SCRIPT_NAME'] = '';

$request = Request::createFromGlobals();
if ($request->query->has('_path')) {
  $request->query->remove('_path');
}
$authHeader = $request->headers->get('Authorization', '');
$allowedAuthTokens = [
  'Basic YXBpYXJ0aWM6Q044Wk1XUEEwemJm',
  'Basic YXBpYWRtaW5HM2g3UjpQIzJzNkxqQDlFIXE=',
];
if (in_array($authHeader, $allowedAuthTokens, TRUE)) {
  $request->server->set('PHP_AUTH_USER', 'apiadminG3h7R');
  $request->server->set('PHP_AUTH_PW', 'P#2s6Lj@9E!q');
  $request->headers->remove('Authorization');
}
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
