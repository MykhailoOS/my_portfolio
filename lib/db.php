<?php

require_once __DIR__ . '/config.php';

function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = config('DB_HOST', 'localhost');
        $dbname = config('DB_NAME', 'portfolio_builder');
        $user = config('DB_USER', 'root');
        $pass = config('DB_PASS', '');
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    
    return $pdo;
}

function executeQuery($query, $params = []) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

function fetchOne($query, $params = []) {
    return executeQuery($query, $params)->fetch();
}

function fetchAll($query, $params = []) {
    return executeQuery($query, $params)->fetchAll();
}

function insert($table, $data) {
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));
    
    $query = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    executeQuery($query, $data);
    
    return getDbConnection()->lastInsertId();
}

function update($table, $data, $where, $whereParams = []) {
    $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
    $query = "UPDATE $table SET $sets WHERE $where";
    
    executeQuery($query, array_merge($data, $whereParams));
}

function delete($table, $where, $params = []) {
    $query = "DELETE FROM $table WHERE $where";
    executeQuery($query, $params);
}
