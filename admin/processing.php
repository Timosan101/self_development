<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// HANDLE PRODUCT DELETION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if ($stmt = $conn->prepare("DELETE FROM products WHERE id = ?")) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manageproducts.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $remove_image = isset($_POST['remove_image']) ? 1 : 0;

    $image_path = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['product_image']['name']);
        $upload_dir = '../images/fourth/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_path = 'images/fourth/' . $file_name;
        }
    }

    if ($product_id === 0) {
        if (!$image_path) {
            $image_path = 'images/fourth/shirts.jpg';
        }
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $category, $price, $stock, $image_path);
    } else {
        if ($remove_image) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, image='' WHERE id=?");
            $stmt->bind_param("ssdii", $name, $category, $price, $stock, $product_id);
        } elseif ($image_path) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, image=? WHERE id=?");
            $stmt->bind_param("ssdssi", $name, $category, $price, $stock, $image_path, $product_id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=? WHERE id=?");
            $stmt->bind_param("ssdii", $name, $category, $price, $stock, $product_id);
        }
    }

    if (!$stmt->execute()) {
        die("EXECUTION ERROR: " . $stmt->error);
    }
    $stmt->close();
}

header("Location: manageproducts.php");
exit();
?>