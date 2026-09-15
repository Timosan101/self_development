<?php
session_start();

// 1. INCLUDE DATABASE CONNECTION
require_once __DIR__ . '/db_connect.php';

// 2. CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. PROCESS PURCHASE WHEN FORM IS SUBMITTED
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // CHECK STOCK BEFORE PROCESSING
    $check_stmt = $conn->prepare("SELECT stock, price FROM products WHERE id = ?");
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $prod = $check_stmt->get_result()->fetch_assoc();

    if ($prod && $prod['stock'] >= $quantity && $quantity > 0) {
        // DEDUCT STOCK
        $update_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update_stmt->bind_param("ii", $quantity, $product_id);
        $update_stmt->execute();

        // INSERT INTO ORDERS TABLE SAFELY WITH PREPARE ERROR CATCHING
        $delivery_date = date('Y-m-d', strtotime('+3 days'));
        $order_query = "INSERT INTO orders (user_id, product_id, quantity, status, order_date, delivery_date) VALUES (?, ?, ?, 'PENDING', NOW(), ?)";
        
        if ($order_stmt = $conn->prepare($order_query)) {
            $order_stmt->bind_param("iiis", $user_id, $product_id, $quantity, $delivery_date);
            $order_stmt->execute();
            $message = "<script>alert('PURCHASE SUCCESSFUL!'); window.location.href='apparels.php';</script>";
        } else {
            // DUMP ERROR IF ORDERS TABLE OR COLUMNS ARE MISSING IN DATABASE
            die("DATABASE ERROR: " . $conn->error);
        }
    } else {
        $message = "<script>alert('INSUFFICIENT STOCK AVAILABLE!');</script>";
    }
}

// 4. EXTRACT FIRST NAME FOR LOGGED-IN USERS
$user_firstname = '';
if (isset($_SESSION['fullname'])) {
    $name_parts = explode(' ', trim($_SESSION['fullname']));
    $user_firstname = strtoupper($name_parts[0]);
}

// 5. FETCH PRODUCTS FROM DATABASE
$products_by_category = [];
if ($result = $conn->query("SELECT * FROM products ORDER BY id DESC")) {
    while ($row = $result->fetch_assoc()) {
        $cat = strtoupper(trim($row['category']));
        $products_by_category[$cat][] = $row;
    }
}

$atrion = '<span class="A">A</span><span class="TRION">TRION</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apparel - ATRION</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" type="text/css" href="apparels.css">
</head>
<body>

<?php echo $message; ?>

<header class="header">
    <div class="LOGO">
        <a href="index.php" style="text-decoration: none;"><?php echo $atrion; ?></a>
    </div>

    <nav class="top-navigation">
        <span class="welcome-text">
            <?php echo !empty($user_firstname) ? "WELCOME, " . htmlspecialchars($user_firstname) . "!" : "WELCOME!"; ?>
        </span>
        <a href="logout.php" class="login">LOG OUT</a>
    </nav>

    <nav class="main-navigation">
        <a href="index.php#first-page">HOME</a>
       
    
        <div class="nav-links">

    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <!-- IF LOGGED IN AS ADMIN, SHOW DASHBOARD LINK -->
            <a href="admin/dashboard.php">ADMIN DASHBOARD</a>
        <?php else: ?>
            <!-- IF LOGGED IN AS CLIENT, SHOW PROFILE LINK -->
            <a href="clients/profile.php">PROFILE</a>
        <?php endif; ?>

    <?php else: ?>
        <a href="login.php">LOG IN</a>
    <?php endif; ?>
</div>


    </nav>
</header>

<section class="apparel-hero-section">
    <div class="apparel-header-container">
        <h1 class="main-heading">ATRION COLLECTION</h1>
    </div>

    <div class="category-filters">
        <span class="filter-btn">ALL ITEMS</span>
    </div>

    <?php if (!empty($products_by_category)): ?>
        <?php foreach ($products_by_category as $category_name => $items): ?>
            <div id="<?php echo strtolower($category_name); ?>-section" class="apparel-category-block">
                <h2 class="category-heading"><?php echo htmlspecialchars($category_name); ?></h2>
                <div class="apparel-grid">
                    
                    <?php foreach ($items as $product): ?>
                        <div class="apparel-card" id="card-<?php echo $product['id']; ?>">
                            <div class="apparel-img-box">
                                <img src="<?php echo htmlspecialchars(!empty($product['image']) ? $product['image'] : 'images/fourth/kuan.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="apparel-info">
                                <span class="item-cat"><?php echo htmlspecialchars($category_name); ?></span>
                                <h3><?php echo strtoupper(htmlspecialchars($product['name'])); ?></h3>
                                
                                <div class="price-stock-row">
                                    <span class="item-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="stock-badge <?php echo ($product['stock'] <= 3) ? 'stock-low' : 'stock-in'; ?>">
                                        STOCKS: <?php echo $product['stock']; ?>
                                    </span>
                                </div>

                                <!-- PURCHASE FORM -->
                                <form method="POST" action="apparels.php" class="purchase-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    
                                    <!-- EXPANDABLE QUANTITY & TOTAL ROW -->
                                    <div class="checkout-controls" id="controls-<?php echo $product['id']; ?>">
                                        <label>QTY:</label>
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                                               class="qty-input" 
                                               onchange="updateTotal(this, <?php echo $product['price']; ?>, <?php echo $product['id']; ?>)"
                                               onkeyup="updateTotal(this, <?php echo $product['price']; ?>, <?php echo $product['id']; ?>)">
                                    </div>

                                    <div class="action-row">
                                        <span class="total-display" id="total-<?php echo $product['id']; ?>">
                                            TOTAL: $<?php echo number_format($product['price'], 2); ?>
                                        </span>
                                        
                                        <button type="button" class="shoppie-sm buy-toggle-btn" onclick="activateCard(<?php echo $product['id']; ?>)">BUY NOW</button>
                                        <button type="submit" name="buy_now" class="shoppie-sm confirm-btn">CONFIRM</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<!-- JAVASCRIPT FOR ENLARGING CARD AND COMPUTING TOTAL -->
<script>
function activateCard(id) {
    // RESET PREVIOUS CARDS
    document.querySelectorAll('.apparel-card').forEach(card => card.classList.remove('enlarged-card'));
    document.querySelectorAll('.checkout-controls').forEach(ctrl => ctrl.style.display = 'none');
    document.querySelectorAll('.total-display').forEach(tot => tot.style.display = 'none');
    document.querySelectorAll('.buy-toggle-btn').forEach(btn => btn.style.display = 'inline-block');
    document.querySelectorAll('.confirm-btn').forEach(btn => btn.style.display = 'none');

    // ACTIVATE CURRENT CARD
    const card = document.getElementById('card-' + id);
    card.classList.add('enlarged-card');
    
    document.getElementById('controls-' + id).style.display = 'flex';
    document.getElementById('total-' + id).style.display = 'inline-block';
    
    const buyBtn = card.querySelector('.buy-toggle-btn');
    const confirmBtn = card.querySelector('.confirm-btn');
    
    buyBtn.style.display = 'none';
    confirmBtn.style.display = 'inline-block';
}

function updateTotal(input, price, id) {
    let qty = parseInt(input.value) || 1;
    let total = qty * price;
    document.getElementById('total-' + id).innerText = 'TOTAL: $' + total.toFixed(2);
}
</script>

</body>
</html>