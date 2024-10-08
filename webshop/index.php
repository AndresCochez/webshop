<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welkom</title>
</head>
<body>
    <div class="container">
        <h1>Welkom op de shop!</h1>
        <p>Je bent succesvol ingelogd.</p>
        <a href="logout.php">Uitloggen</a>
    </div>
</body>
</html>