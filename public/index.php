<?php

session_start();

// Autoloader PSR-4 Fallback
spl_autoload_register(function ($class) {
    $prefix = 'epiGuard\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Carregar .env manualmente se existir na pasta config
if (file_exists(__DIR__ . '/../config/.env')) {
    $lines = file(__DIR__ . '/../config/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

// Global helpers (e.g., i18n translation)
require_once __DIR__ . '/../src/Application/helpers.php';

// Carregar configurações
$config = require_once __DIR__ . '/../config/app.php';

// Roteamento (Clean Architecture)
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

define('CURRENT_ROUTE', $path);

if (isset($routes[$path])) {
    [$controllerClass, $method] = $routes[$path];
    $controller = new $controllerClass();
    $controller->$method();
} else {
    header("HTTP/1.0 404 Not Found");
    echo "404 - Page not found: " . htmlspecialchars($path);
}
