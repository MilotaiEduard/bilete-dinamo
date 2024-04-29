<?php
require_once __DIR__ . '/vendor/autoload.php';

// Încarcă variabilele de mediu
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Accesează variabilele de mediu
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

// Crearea conexiunii
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificarea conexiunii
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Connected successfully";
?>
