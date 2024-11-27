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
        echo "Bestelling succesvol geplaatst!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Fout bij het plaatsen van de bestelling: " . $e->getMessage();
    }
} else {
    echo "Uw winkelmandje is leeg of u bent niet ingelogd.";
}
?>