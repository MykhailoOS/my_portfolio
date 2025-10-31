<?php

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($message, $status = 400) {
    jsonResponse(['error' => $message], $status);
}

function validateCsrf() {
    session_start();
    
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        errorResponse('Invalid CSRF token', 403);
    }
}

function generateCsrf() {
    session_start();
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function sanitizeHtml($html) {
    $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>';
    return strip_tags($html, $allowedTags);
}

function validateImage($file) {
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $maxSize = 5 * 1024 * 1024;
    
    if (!in_array($file['type'], $allowedMimes)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File too large (max 5MB)'];
    }
    
    if ($file['type'] === 'image/svg+xml') {
        $content = file_get_contents($file['tmp_name']);
        if (preg_match('/<script/i', $content) || preg_match('/on\w+\s*=/i', $content)) {
            return ['valid' => false, 'error' => 'SVG contains forbidden content'];
        }
    }
    
    return ['valid' => true];
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'project-' . bin2hex(random_bytes(4));
}

function ensureDir($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function loadI18n($lang) {
    $file = __DIR__ . "/../i18n/ui.{$lang}.json";
    
    if (!file_exists($file)) {
        $file = __DIR__ . "/../i18n/ui.en.json";
    }
    
    return json_decode(file_get_contents($file), true);
}

function getTranslation($data, $lang, $key, $fallback = '') {
    if (is_array($data) && isset($data[$lang][$key])) {
        return $data[$lang][$key];
    }
    
    if (is_array($data) && isset($data['en'][$key])) {
        return $data['en'][$key];
    }
    
    return $fallback;
}
