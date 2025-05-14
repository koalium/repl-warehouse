<?php
require_once '../models/User.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }

    public function getUserData($wallet) {
        $user = $this->userModel->findByWallet($wallet);
        if(!$user) {
            Response::send(404, ['message' => 'User not found']);
        }
        
        $data = [
            'wallet' => $user->wallet,
            'registration_date' => $user->time,
            'total_invested' => $user->total_invested,
            'total_profit' => $user->total_profit
        ];
        
        Response::send(200, $data);
    }
}
// In UserController.php
public function getUserData($wallet) {
    // Validate wallet format first
    if(!preg_match('/^T[a-zA-Z0-9]{33}$/', $wallet)) {
        Response::send(400, ['message' => 'Invalid wallet format']);
    }

    $user = $this->userModel->findByWallet($wallet);
    if(!$user) {
        Response::send(404, ['message' => 'User not found']);
    }
    
    $data = [
        'wallet' => $user->wallet,
        'registration_date' => $user->time,
        'total_invested' => $user->total_invested,
        'total_profit' => $user->total_profit
    ];
    
    Response::send(200, $data);
}