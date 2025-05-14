<?php
require_once 'config.php';
//use Firebase\JWT\JWT;

class JwtHandler {
    public static function generateToken($payload) {
        $issuedAt = time();
        $expire = $issuedAt + (60 * 60); // 1 hour
        
        $tokenPayload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'data' => $payload
        ];
        
        return JWT::encode($tokenPayload, JWT_SECRET, JWT_ALGO);
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, JWT_SECRET, [JWT_ALGO]);
            return $decoded->data;
        } catch(Exception $e) {
            return false;
        }
    }
}