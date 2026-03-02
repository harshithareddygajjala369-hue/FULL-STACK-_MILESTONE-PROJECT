<?php
$host = 'localhost';
$dbname = 'membership_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Set charset to utf8mb4
    $pdo->exec("SET NAMES utf8mb4");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include functions
require_once __DIR__ . '/functions.php';

// Set timezone
date_default_timezone_set('UTC');
?>