<?php
// Mocking session for testing
session_start();
$_SESSION['active_filial_id'] = 1;

require_once 'src/Presentation/Controller/Api/ExportApiController.php';
require_once 'src/Infrastructure/Database/Connection.php';

// Prevent header errors during testing
ob_start();
$controller = new \Facchini\Presentation\Controller\Api\ExportApiController();
$controller->insights();
$output = ob_get_clean();

header('Content-Type: text/plain');
echo "--- RAW OUTPUT START ---\n";
echo $output;
echo "\n--- RAW OUTPUT END ---\n";

$json = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "JSON Status: " . ($json['status'] ?? 'unknown') . "\n";
    if (($json['status'] ?? '') === 'error') {
        echo "Error Message: " . ($json['message'] ?? 'none') . "\n";
    }
}
