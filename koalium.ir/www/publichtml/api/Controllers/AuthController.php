<?php
require_once '../models/User.php';
require_once '../utils/JwtHandler.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if(empty($data['wallet'])) {
            Response::send(400, ['message' => 'Wallet address is required']);
        }

        // Check if wallet exists
        if($this->userModel->findByWallet($data['wallet'])) {
            Response::send(400, ['message' => 'Wallet already registered']);
        }

        // Get client IP
        $ip = $_SERVER['REMOTE_ADDR'];

        // Create user
        $userData = [
            'wallet' => $data['wallet'],
            'reg_ip' => $ip
        ];

        if($this->userModel->create($userData)) {
            $jwt = JwtHandler::generateToken(['wallet' => $data['wallet']]);
            Response::send(201, [
                'message' => 'User registered successfully',
                'token' => $jwt
            ]);
        } else {
            Response::send(500, ['message' => 'Failed to register user']);
        }
    }
}