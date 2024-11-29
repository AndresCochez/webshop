<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id']) && !empty($_SESSION['cart'])) {
    $userId = $_SESSION['user_id'];

    $pdo->beginTransaction();
    try {
        // Maak een nieuwe bestelling aan
        $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        $orderId = $pdo->lastInsertId();

        // Voeg de producten toe aan de bestelling
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $stmt->execute([$orderId, $item['id'], $item['quantity']]);
        }

        // Leeg het winkelmandje
        unset($_SESSION['cart']);
        $pdo->commit();

        // Zet een succesmelding in de sessie
        $_SESSION['order_success'] = "Bestelling succesvol geplaatst!";
        header("Location: user_dashboard.php.php"); // Redirect naar dashboard
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['order_error'] = "Fout bij het plaatsen van de bestelling: " . $e->getMessage();
        header("Location: user_dashboard.php"); // Redirect naar dashboard
        exit();
    }
} else {
    $_SESSION['order_error'] = "Uw winkelmandje is leeg of u bent niet ingelogd.";
    header("Location: user_dashboard.php"); // Redirect naar dashboard
    exit();
}