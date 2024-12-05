<?php
$host = 'localhost';
$db = 'webshop';
$user = 'root';
$pass = '';

try {
    // Gebruik veilige DSN-opbouw en verbind met PDO
    $dsn = "mysql:host=" . htmlspecialchars($host, ENT_QUOTES, 'UTF-8') . ";dbname=" . htmlspecialchars($db, ENT_QUOTES, 'UTF-8') . ";charset=utf8mb4";
    $pdo = new PDO($dsn, htmlspecialchars($user, ENT_QUOTES, 'UTF-8'), htmlspecialchars($pass, ENT_QUOTES, 'UTF-8'));

    // Zet PDO-foutmodus op exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Schakel emulatie van prepared statements uit voor extra veiligheid
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Gebruik een generieke foutmelding om gevoelige informatie niet prijs te geven
    die("Error: Unable to connect to the database. Please try again later.");
}
?>