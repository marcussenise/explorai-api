<?php
// index.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/Controllers/UserController.php';

header('Content-Type: application/json');

$requestUri = $_SERVER['REQUEST_URI'];
$requestData = json_decode(file_get_contents('php://input'), true) ?? [];

$userController = new UserController();
$authMiddleware = new AuthMiddleware();

switch ($requestUri) {
    // PÚBLICAS 
    
    case '/api/auth/register':
        $userController->register($requestData);
        break;
        
    case '/api/auth/forgot-password':
        $userController->forgotPassword($requestData); 
        break;
        
    case '/api/public/data':
        echo json_encode(['statusCode' => 200, 'payload' => ['message' => 'Isso é público']]);
        break;

    // PROTEGIDAS 

    case '/api/auth/logout':
        (new AuthMiddleware())->handle($requestData);
        (new UserController())->logout($requestData);
        break;
        
    case '/api/user/profile':
        $authMiddleware->handle($requestData); 
        $userController->getProfile($requestData);
        break;

    case '/api/user/delete':
        $authMiddleware->handle($requestData);
        $userController->deleteAccount($requestData);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Rota não encontrada']);
        break;
}