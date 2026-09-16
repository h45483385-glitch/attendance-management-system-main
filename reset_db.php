<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$stmt = $pdo->query("SHOW VARIABLES LIKE 'datadir'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$datadir = $row['Value'];

$laravelDir = $datadir . 'laravel';
echo "Data dir: $laravelDir\n";

if (is_dir($laravelDir)) {
    $files = glob($laravelDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "Deleted $file\n";
        }
    }
    rmdir($laravelDir);
    echo "Deleted directory $laravelDir\n";
}

$pdo->exec('DROP DATABASE IF EXISTS laravel');
$pdo->exec('CREATE DATABASE laravel');
echo "Database recreated successfully.\n";
