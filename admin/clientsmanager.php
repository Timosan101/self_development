<?php
session_start();

// 1. INCLUDE DATABASE CONNECTION
require_once __DIR__ . '/../db_connect.php';

// 2. CHECK ADMIN AUTHENTICATION
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2.1 HANDLE ORDER STATUS ACTIONS (CONFIRM, DENY, APPROVE CANCELLATION)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_order_id'], $_POST['new_status'])) {
    $order_id = intval($_POST['action_order_id']);
    $new_status = trim($_POST['new_status']);
    
    $allowed_statuses = ['CONFIRMED', 'CANCELLED'];
    if (in_array($new_status, $allowed_statuses)) {
        if ($stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?")) {
            $stmt->bind_param("si", $new_status, $order_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: clientsmanager.php");
    exit();
}

// 3. HANDLE CLIENT REMOVAL
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    
    if ($stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'client'")) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: clientsmanager.php");
    exit();
}

// 4. FETCH CLIENT ACCOUNTS
$clients = false;
if ($stmt = $conn->prepare("SELECT id, fullname, email, created_at FROM users WHERE role = ? ORDER BY id DESC")) {
    $role_type = 'client';
    $stmt->bind_param("s", $role_type);
    $stmt->execute();
    $clients = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLIENT MANAGER - ATRION</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>

    <h1>CLIENT MANAGER</h1>
    <a href="dashboard.php" class="back-link">&larr; BACK TO DASHBOARD</a>
    <br><br>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>FULL NAME</th>
                <th>EMAIL ADDRESS</th>
                <th>REGISTERED DATE</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($clients && $clients->num_rows > 0): ?>
                <?php 
                $display_id = $clients->num_rows; 
                ?>
                <?php while ($client = $clients->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $display_id--; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($client['fullname'])); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo $client['created_at']; ?></td>
                        <td>
                            <button type="button" class="view-orders-btn" onclick="openOrderModal(<?php echo $client['id']; ?>, '<?php echo addslashes(strtoupper(htmlspecialchars($client['fullname']))); ?>')">
                                VIEW ORDERS
                            </button>
                            <a href="clientsmanager.php?action=delete&id=<?php echo $client['id']; ?>" 
                               onclick="return confirm('ARE YOU SURE YOU WANT TO REMOVE THIS CLIENT ACCOUNT?');" 
                               class="logout-link">REMOVE</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">NO CLIENT ACCOUNTS FOUND.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- CLIENT ORDERS MODAL -->
    <div id="ordersModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="closeOrderModal()">&times;</span>
            <h2 id="modalClientName">CLIENT ORDERS</h2>
            <div id="modalOrderDetails">
                <p>LOADING ORDERS...</p>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT FOR FETCHING ORDERS VIA AJAX -->
    <script>
    function openOrderModal(userId, clientName) {
        document.getElementById('modalClientName').innerText = clientName + " - ORDER RECORDS";
        document.getElementById('ordersModal').classList.add('active');
        
        fetch('fetch_client_orders.php?user_id=' + userId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modalOrderDetails').innerHTML = data;
            })
            .catch(err => {
                document.getElementById('modalOrderDetails').innerHTML = '<p>ERROR LOADING ORDERS.</p>';
            });
    }

    function closeOrderModal() {
        document.getElementById('ordersModal').classList.remove('active');
    }
    </script>

</body>
</html>