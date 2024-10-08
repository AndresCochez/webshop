<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Webshop!</h1>
            <p>Je bent succesvol ingelogd.</p>
            <div class="logout-btn-container">
                <a href="logout.php"><button class="logout-btn">Uitloggen</button></a>
            </div>
        </div>
    </div>
</body>
</html>