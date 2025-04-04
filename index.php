<?php
require_once 'api/index.php';

$base_dir = __DIR__;

switch ($_SERVER['REQUEST_URI']) {
    // ...existing code...
    case '/expresso-cafe/api/process-payment':
        error_log("Matched route: /api/process-payment");
        require_once $base_dir . '/stripe/process_payment.php';
        break;
    // ...existing code...
}
?>