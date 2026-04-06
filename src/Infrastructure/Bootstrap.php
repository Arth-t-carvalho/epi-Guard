<?php
declare(strict_types=1);

/**
 * Arquivo de Bootstrap para inicialização do sistema
 * Gerencia a detecção de caminhos (BASE_PATH e BASE_DIR)
 */

if (!defined('BASE_DIR')) {
    // Definir o diretório raiz físico de forma absoluta e robusta
    // Buscamos o diretório pai de 'src/Infrastructure' (ou o nível correspondente de onde o bootstrap reside)
    define('BASE_DIR', str_replace('\\', '/', realpath(__DIR__ . '/../../')));
}

if (!defined('BASE_PATH')) {
    // Detectar o basePath automaticamente do SCRIPT_NAME da requisição atual
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    
    // O basePath é o diretório que contém o index.php (seja na raiz ou em /public/)
    // Mas nos interessa o basePath do PROJETO.
    
    // Estratégia: Pegar a parte comum entre SCRIPT_NAME e o diretório físico BASE_DIR
    // Se o index.php estiver na raiz, dirname(SCRIPT_NAME) é o basePath.
    $basePath = str_replace('\\', '/', dirname($scriptName));
    
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }
    
    define('BASE_PATH', $basePath);
}
