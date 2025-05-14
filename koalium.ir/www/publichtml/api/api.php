<?php
require_once 'config.php';
require_once 'utils/Response.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/ContractController.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Route requests
switch($url[0]) {
    case 'register':
        $authController = new AuthController();
        $authController->register();
        break;
        
    case 'contracts':
        $contractController = new ContractController();
        if(isset($url[1])) {
            // Handle specific contract ID
        } else {
            $contractController->getAllContracts();
        }
        break;
        
    default:
        Response::send(404, ['message' => 'Endpoint not found']);
        break;
}
// api.php
// Add after existing routes
case 'user':
    $auth = validateToken();
    $userController = new UserController();
    $userController->getUserData($auth->wallet);
    break;

case 'transactions':
    $auth = validateToken();
    $transactionController = new TransactionController();
    $transactionController->getUserTransactions($auth->wallet);
    break;

function validateToken() {
    $headers = apache_request_headers();
    if(!isset($headers['Authorization'])) {
        Response::send(401, ['message' => 'Unauthorized']);
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $decoded = JwtHandler::validateToken($token);
    
    if(!$decoded) {
        Response::send(401, ['message' => 'Invalid token']);
    }
    
    return $decoded;
}