<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../src/Presentation/Controller/Api/ExportApiController.php';

use epiGuard\Presentation\Controller\Api\ExportApiController;

try {
    $controller = new ExportApiController();
    
    // Captura o output do controller (ele dá echo em json)
    ob_start();
    $controller->insights();
    $output = ob_get_clean();
    
    echo "API Response:\n";
    echo $output;
    echo "\n\nDecoded JSON:\n";
    $data = json_decode($output, true);
    if ($data === null) {
        echo "Error: Invalid JSON returned by controller.\n";
    } else {
        print_r($data);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
