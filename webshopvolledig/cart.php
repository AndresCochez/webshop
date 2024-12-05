<?php
session_start();

if (isset($_POST['add_to_cart'])) {
    // Zorg ervoor dat invoer veilig wordt verwerkt
    $productId = intval($_POST['product_id']);
    $productTitle = htmlspecialchars($_POST['product_title'], ENT_QUOTES, 'UTF-8');
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

// Stuur gebruiker veilig terug naar de productdetailpagina
header("Location: product_details.php?id=" . urlencode($productId));
exit();
?>