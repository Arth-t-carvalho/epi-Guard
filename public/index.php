<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// Carregar configurações
$config = require_once __DIR__ . '/../config/app.php';

// Roteamento simples para demonstração (Clean Architecture)
$routes = require_once __DIR__ . '/../config/routes.php';

$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Detectar o basePath automaticamente
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('\\', '/', dirname($scriptName));
if ($basePath === '/') {
    $basePath = '';
}

define('BASE_PATH', $basePath);

// Remover o basePath da URI para obter o caminho da rota
$path = parse_url($uri, PHP_URL_PATH);
if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Remove query strings
$path = explode('?', $path)[0];

if ($path === '' || $path === '/') $path = '/login';

if (isset($routes[$path])) {
    [$controllerClass, $method] = $routes[$path];
    $controller = new $controllerClass();
    $controller->$method();
} else {
    header("HTTP/1.0 404 Not Found");
    echo "404 - Page not found: " . htmlspecialchars($path);
}
