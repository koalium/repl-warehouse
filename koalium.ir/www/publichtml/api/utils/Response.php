<?php
class Response {
    public static function send($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}