<?php
session_start();

// 1. INCLUDE THE DATABASE CONNECTION FIRST
require_once __DIR__ . '/../db_connect.php';

// 2. CHECK LOGGED-IN STATUS AND ENFORCE STRICT ROLE ROUTING
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// IF A CLIENT TRIES TO ACCESS THE ADMIN DASHBOARD, REDIRECT THEM TO CLIENT PROFILE
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../clients/profile.php");
    exit();
}

// 3. FETCH METRICS SAFELY
$product_count = 0;
if ($result = $conn->query("SELECT COUNT(*) as total FROM products")) {
    $product_count = $result->fetch_assoc()['total'];
}

$user_count = 0;
if ($result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'client'")) {
    $user_count = $result->fetch_assoc()['total'];
}

$total_feedback = 0;
if ($result = $conn->query("SELECT COUNT(*) as total FROM feedback")) {
    $total_feedback = $result->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN DASHBOARD - ATRION</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>

    <div class="header-container">
        <h1>ATRION ADMIN DASHBOARD</h1>
        <a href="../logout.php" onclick="return confirm('ARE YOU SURE YOU WANT TO LOG OUT?');" class="logout-link">LOG OUT</a>
    </div>

    <p>WELCOME, <?php echo strtoupper(htmlspecialchars($_SESSION['fullname'] ?? 'ADMIN')); ?></p>
    <hr>

    <div class="dashboard-grid">

        <div class="card">
            <h3>RATINGS & COMMENTS</h3>
            <h2><?php echo $total_feedback; ?></h2>
            <a href="feedbackviewer.php">VIEW RATINGS & COMMENTS</a>
        </div>

        <div class="card">
            <h3>TOTAL PRODUCTS</h3>
            <h2><?php echo $product_count; ?></h2>
            <a href="manageproducts.php">MANAGE PRODUCTS</a>
        </div>

        <div class="card">
            <h3>TOTAL CLIENTS</h3>
            <h2><?php echo $user_count; ?></h2>
            <a href="clientsmanager.php">VIEW CLIENTS</a>
        </div>

        <div class="card">
                <h3>HOMEPAGE IMAGES</h3>
                    <h2>MANAGE</h2>
                        <a href="manageimages.php">CHANGE IMAGES</a>
        </div>


    </div>

    <br><br>
    <a href="../apparels.php" class="back-link">BACK TO SITE</a>

</body>
</html>