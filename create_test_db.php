<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS hydronew_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✅ Database 'hydronew_test' created successfully!" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Error creating database: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
