<?php
session_start();

if (isset($_POST['add_to_cart'])) {
    $productId = intval($_POST['product_id']);
    $productTitle = $_POST['product_title'];
    $productPrice = floatval($_POST['product_price']);

    // Controleer of het product al in het winkelmandje zit
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $productId) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }

    // Als het product niet in het winkelmandje zit, voeg het toe
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $productId,
            'title' => $productTitle,
            'price' => $productPrice,
            'quantity' => 1
        ];
    }
}

// Stuur gebruiker terug naar de productdetailpagina
header("Location: product_details.php?id=" . $productId);
exit();
?>