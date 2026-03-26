<?php

/**
 * Ponto de entrada principal do epiGuard
 * Movido para a raiz para facilitar o acesso, mantendo a organização Clean Architecture internamente.
 */

/**
 * Autoloader manual PSR-4 (Substitui o Composer caso não esteja instalado)
 */
spl_autoload_register(function ($class) {
    $prefix = 'epiGuard\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});


session_start();

// Carregar .env manualmente se existir na pasta config
if (file_exists(__DIR__ . '/config/.env')) {
    $lines = file(__DIR__ . '/config/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

// Carregar configurações
$config = require_once __DIR__ . '/config/app.php';

// Roteamento
$routes = require_once __DIR__ . '/config/routes.php';

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

// Suporte para quando index.php está na URL (caso o mod_rewrite não esteja ativo)
$path = str_replace('/index.php', '', $path);

// Remove query strings
$path = explode('?', $path)[0];

if ($path === '' || $path === '/') $path = '/login';

try {
    if (isset($routes[$path])) {
        [$controllerClass, $method] = $routes[$path];
        $controller = new $controllerClass();
        $controller->$method();
    } else {
        header("HTTP/1.0 404 Not Found");
        require_once __DIR__ . '/src/Presentation/View/auth/login.php'; // Fallback ou página 404
        // echo "<h1>404 - Página não encontrada</h1>";
        // echo "<p>Caminho requisitado: " . htmlspecialchars($path) . "</p>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; font-family: sans-serif; margin: 20px;'>";
    echo "<h3>Erro do Sistema</h3>";
    echo "<p>Mensagem: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " na linha " . $e->getLine() . "</p>";
    echo "</div>";
}
