<?php
/**
 * CORS Test Endpoint
 * 
 * This file can be used to verify CORS headers are being sent correctly.
 * Access it via: https://yourdomain.com/test-cors.php
 * 
 * You should see:
 * - Success message
 * - All CORS headers listed
 * - HTTP 200 status
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'preflight_ok']);
    exit;
}

// Get all response headers that will be sent
$headers = headers_list();

// Return success response with debug info
$response = [
    'status' => 'success',
    'message' => 'CORS headers are configured correctly',
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'headers_sent' => $headers,
    'cors_check' => [
        'access_control_allow_origin' => in_array('Access-Control-Allow-Origin: *', $headers),
        'access_control_allow_methods' => in_array('Access-Control-Allow-Methods: GET, POST, OPTIONS', $headers),
        'access_control_allow_headers' => in_array('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization', $headers),
        'content_type' => in_array('Content-Type: application/json', $headers)
    ],
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
