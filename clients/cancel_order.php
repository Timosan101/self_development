<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT status, product_id, quantity FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        if ($order['status'] === 'PENDING') {
            $update = $conn->prepare("UPDATE orders SET status = 'CANCELLED' WHERE id = ?");
            $update->bind_param("i", $order_id);
            $update->execute();
            
            $restock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $restock->bind_param("ii", $order['quantity'], $order['product_id']);
            $restock->execute();
        } elseif ($order['status'] === 'CONFIRMED') {
            $update = $conn->prepare("UPDATE orders SET status = 'CANCEL_REQUESTED' WHERE id = ?");
            $update->bind_param("i", $order_id);
            $update->execute();
        }
    }
}

header("Location: profile.php");
exit();
?>