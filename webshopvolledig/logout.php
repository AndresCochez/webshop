<?php
session_start();

// Verwijder alle sessievariabelen
$_SESSION = [];

// Verwijder de sessiecookie als die bestaat
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Vernietig de sessie
session_destroy();

// Omleiden naar de startpagina
header("Location: index.php");
exit();
?>