<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'esmartis_editor');
define('DB_PASS', 'koala551364');
define('DB_NAME', 'esmartis_inv_db');

// JWT Secret
define('JWT_SECRET', '');
define('JWT_ALGO', 'HS256');

// CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');