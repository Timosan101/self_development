<?php
session_start();

// 1. INCLUDE DATABASE CONNECTION
require_once __DIR__ . '/../db_connect.php';

// 2. CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = null;

// 3. FETCH USER PROFILE DETAILS
if ($stmt = $conn->prepare("SELECT fullname, email, created_at FROM users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// 4. FETCH ACTIVE ORDERS (STATUS: PENDING / PROCESSING / SHIPPED / CONFIRMED / CANCEL_REQUESTED)
$active_orders = [];
$active_query = "SELECT o.id, o.quantity, o.status, o.order_date, o.delivery_date, 
                        p.name AS product_name, p.category, p.price, o.product_id 
                 FROM orders o 
                 JOIN products p ON o.product_id = p.id 
                 WHERE o.user_id = ? AND o.status != 'DELIVERED' AND o.status != 'CANCELLED' 
                 ORDER BY o.order_date DESC";

if ($stmt = $conn->prepare($active_query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $active_orders = $stmt->get_result();
}

// 5. FETCH HISTORY ORDERS (STATUS: DELIVERED OR CANCELLED)
$history_orders = [];
$history_query = "SELECT o.id, o.quantity, o.status, o.order_date, o.delivery_date, 
                         p.name AS product_name, p.category, p.price 
                  FROM orders o 
                  JOIN products p ON o.product_id = p.id 
                  WHERE o.user_id = ? AND (o.status = 'DELIVERED' OR o.status = 'CANCELLED') 
                  ORDER BY o.order_date DESC";

if ($stmt = $conn->prepare($history_query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $history_orders = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY PROFILE - ATRION</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>

    <div class="header-container">
        <h1>CLIENT PROFILE</h1>
        <a href="../logout.php" onclick="return confirm('ARE YOU SURE YOU WANT TO LOG OUT?');" class="logout-link">LOG OUT</a>
    </div>

    <a href="../apparels.php" class="back-link">&larr; BACK TO HOME</a>
    <br><br>

    <!-- ACCOUNT DETAILS CARD -->
    <?php if ($user): ?>
        <div class="card profile-card">
            <h3>ACCOUNT DETAILS</h3>
            <p><strong>FULL NAME:</strong> <?php echo strtoupper(htmlspecialchars($user['fullname'])); ?></p>
            <p><strong>EMAIL ADDRESS:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>ACCOUNT CREATED:</strong> <?php echo $user['created_at']; ?></p>
        </div>
    <?php else: ?>
        <p>ACCOUNT DETAILS NOT FOUND.</p>
    <?php endif; ?>

    <hr>

    <!-- CURRENT / ACTIVE ORDERS -->
    <h2>ACTIVE ORDERS</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ORDER ID</th>
                <th>PRODUCT</th>
                <th>CATEGORY</th>
                <th>QTY</th>
                <th>UNIT PRICE</th>
                <th>TOTAL PRICE</th>
                <th>STATUS</th>
                <th>ESTIMATED DELIVERY</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($active_orders && $active_orders->num_rows > 0): ?>
                <?php 
                $grand_total = 0; // 1. INITIALIZE GRAND TOTAL COUNTER
                while ($order = $active_orders->fetch_assoc()): 
                    $total_price = $order['price'] * $order['quantity']; 
                    if ($order['status'] !== 'CANCEL_REQUESTED') {
                        $grand_total += $total_price; // 2. ADD TO GRAND TOTAL IF NOT CANCELLING
                    }
                ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($order['product_name'])); ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($order['category'])); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>$<?php echo number_format($order['price'], 2); ?></td>
                        <td>$<?php echo number_format($total_price, 2); ?></td>
                        <td><strong class="status-badge"><?php echo strtoupper(htmlspecialchars($order['status'])); ?></strong></td>
                        <td><?php echo $order['delivery_date'] ?? 'PENDING DISPATCH'; ?></td>
                        <td>
                            <?php if ($order['status'] === 'PENDING'): ?>
                                <form method="POST" action="cancel_order.php" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="cancel_order" onclick="return confirm('ARE YOU SURE YOU WANT TO CANCEL THIS ORDER?');" style="background: red; color: white; border: none; padding: 4px 8px; cursor: pointer;">CANCEL</button>
                                </form>
                            <?php elseif ($order['status'] === 'CONFIRMED'): ?>
                                <form method="POST" action="cancel_order.php" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="request_cancellation" onclick="return confirm('DO YOU WANT TO REQUEST CANCELLATION FROM THE ADMIN?');" style="background: orange; color: white; border: none; padding: 4px 8px; cursor: pointer;">REQUEST CANCELLATION</button>
                                </form>
                            <?php elseif ($order['status'] === 'CANCEL_REQUESTED'): ?>
                                <span style="color: orange; font-weight: bold;">CANCELLATION PENDING</span>
                            <?php else: ?>
                                NONE
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="no-data">NO ACTIVE ORDERS AT THE MOMENT.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if ($active_orders && $active_orders->num_rows > 0): ?>
        <tfoot>
            <tr>
                <td colspan="9" style="text-align: center; font-weight: bold; padding: 15px; background: rgba(25, 181, 229, 0.1);">
                    OVERALL TOTAL PRICE: $<?php echo number_format($grand_total, 2); ?>
                </td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <br><br>

    <!-- ORDER HISTORY -->
    <h2>ORDER HISTORY</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ORDER ID</th>
                <th>PRODUCT</th>
                <th>CATEGORY</th>
                <th>QTY</th>
                <th>UNIT PRICE</th>
                <th>TOTAL PRICE</th>
                <th>PURCHASE DATE</th>
                <th>STATUS / DELIVERED ON</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($history_orders && $history_orders->num_rows > 0): ?>
                <?php while ($past = $history_orders->fetch_assoc()): ?>
                    <?php $total_price = $past['price'] * $past['quantity']; ?>
                    <tr>
                        <td>#<?php echo $past['id']; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($past['product_name'])); ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($past['category'])); ?></td>
                        <td><?php echo $past['quantity']; ?></td>
                        <td>$<?php echo number_format($past['price'], 2); ?></td>
                        <td>$<?php echo number_format($total_price, 2); ?></td>
                        <td><?php echo $past['order_date']; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($past['status'])); ?><?php echo $past['delivery_date'] ? ' - ' . $past['delivery_date'] : ''; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="no-data">NO PAST PURCHASE HISTORY FOUND.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>