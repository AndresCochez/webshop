<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id']) && !empty($_SESSION['cart'])) {
    $userId = intval($_SESSION['user_id']); // Zorg ervoor dat user_id een integer is

    $pdo->beginTransaction();
    try {
        // Maak een nieuwe bestelling aan
        $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        $orderId = $pdo->lastInsertId();

        // Voeg de producten toe aan de bestelling
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $productId = intval($item['id']); // Zorg dat product_id een integer is
            $quantity = intval($item['quantity']); // Zorg dat quantity een integer is
            $stmt->execute([$orderId, $productId, $quantity]);
        }

        // Leeg het winkelmandje
        unset($_SESSION['cart']);
        $pdo->commit();

        // Zet een succesmelding in de sessie
        $_SESSION['order_success'] = "Bestelling succesvol geplaatst!";
        header("Location: user_dashboard.php"); // Redirect naar dashboard
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Ontsmet de foutmelding voordat deze wordt opgeslagen in de sessie
        $_SESSION['order_error'] = "Fout bij het plaatsen van de bestelling: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: user_dashboard.php"); // Redirect naar dashboard
        exit();
    }
} else {
    $_SESSION['order_error'] = "Uw winkelmandje is leeg of u bent niet ingelogd.";
    header("Location: user_dashboard.php"); // Redirect naar dashboard
    exit();
}