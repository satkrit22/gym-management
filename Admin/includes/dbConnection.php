<?php
$host = 'localhost';
$dbname = 'gym_db';
$username = 'root';
$password = ''; // Adjust if you have a password set for MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection Failed: " . $e->getMessage());
}
?>
