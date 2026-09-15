<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_GET['user_id'])) {
    exit('NO CLIENT SPECIFIED.');
}

$user_id = intval($_GET['user_id']);

$active_query = "SELECT o.id, o.quantity, o.status, o.order_date, o.delivery_date, 
                        p.name AS product_name, p.category, p.price 
                 FROM orders o 
                 JOIN products p ON o.product_id = p.id 
                 WHERE o.user_id = ? AND o.status != 'DELIVERED' AND o.status != 'CANCELLED' 
                 ORDER BY o.order_date DESC";

$stmt = $conn->prepare($active_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_orders = $stmt->get_result();

$history_query = "SELECT o.id, o.quantity, o.status, o.order_date, o.delivery_date, 
                         p.name AS product_name, p.category, p.price 
                  FROM orders o 
                  JOIN products p ON o.product_id = p.id 
                  WHERE o.user_id = ? AND (o.status = 'DELIVERED' OR o.status = 'CANCELLED') 
                  ORDER BY o.order_date DESC";

$stmt_h = $conn->prepare($history_query);
$stmt_h->bind_param("i", $user_id);
$stmt_h->execute();
$history_orders = $stmt_h->get_result();
?>

<h3>ACTIVE ORDERS</h3>
<table class="modal-table">
    <thead>
        <tr>
            <th>ITEM</th>
            <th>CATEGORY</th>
            <th>QTY</th>
            <th>UNIT PRICE</th>
            <th>TOTAL PRICE</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $active_grand_total = 0;
        if ($active_orders && $active_orders->num_rows > 0): 
            while ($row = $active_orders->fetch_assoc()):
                $total = $row['price'] * $row['quantity'];
                if ($row['status'] !== 'CANCEL_REQUESTED') {
                    $active_grand_total += $total;
                }
        ?>
            <tr>
                <td><?php echo strtoupper(htmlspecialchars($row['product_name'])); ?></td>
                <td><?php echo strtoupper(htmlspecialchars($row['category'])); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>$<?php echo number_format($row['price'], 2); ?></td>
                <td>$<?php echo number_format($total, 2); ?></td>
                <td><strong><?php echo strtoupper(htmlspecialchars($row['status'])); ?></strong></td>
                <td>
                    <?php if ($row['status'] === 'PENDING'): ?>
                        <form method="POST" action="clientsmanager.php" style="display:inline;">
                            <input type="hidden" name="action_order_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="new_status" value="CONFIRMED">
                            <button type="submit" style="background: green; color: white; border: none; padding: 4px 8px; cursor: pointer;">CONFIRM</button>
                        </form>
                        <form method="POST" action="clientsmanager.php" style="display:inline;">
                            <input type="hidden" name="action_order_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="new_status" value="CANCELLED">
                            <button type="submit" style="background: red; color: white; border: none; padding: 4px 8px; cursor: pointer;" onclick="return confirm('ARE YOU SURE YOU WANT TO DENY THIS ORDER?');">DENY</button>
                        </form>
                    <?php elseif ($row['status'] === 'CANCEL_REQUESTED'): ?>
                        <form method="POST" action="clientsmanager.php" style="display:inline;">
                            <input type="hidden" name="action_order_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="new_status" value="CANCELLED">
                            <button type="submit" style="background: orange; color: white; border: none; padding: 4px 8px; cursor: pointer;" onclick="return confirm('APPROVE CANCELLATION FOR THIS ORDER?');">APPROVE CANCEL</button>
                        </form>
                    <?php else: ?>
                        <span>NONE</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">NO ACTIVE ORDERS.</td></tr>
        <?php endif; ?>
    </tbody>
    <?php if ($active_orders && $active_orders->num_rows > 0): ?>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; background: rgba(25, 181, 229, 0.15);">
                OVERALL ACTIVE TOTAL: $<?php echo number_format($active_grand_total, 2); ?>
            </td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>

<br>

<h3>PURCHASE HISTORY</h3>
<table class="modal-table">
    <thead>
        <tr>
            <th>ITEM</th>
            <th>CATEGORY</th>
            <th>QTY</th>
            <th>TOTAL PRICE</th>
            <th>PURCHASE DATE</th>
            <th>STATUS / DELIVERED ON</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $history_grand_total = 0;
        if ($history_orders && $history_orders->num_rows > 0): 
            while ($h = $history_orders->fetch_assoc()):
                $h_total = $h['price'] * $h['quantity'];
                $history_grand_total += $h_total;
        ?>
            <tr>
                <td><?php echo strtoupper(htmlspecialchars($h['product_name'])); ?></td>
                <td><?php echo strtoupper(htmlspecialchars($h['category'])); ?></td>
                <td><?php echo $h['quantity']; ?></td>
                <td>$<?php echo number_format($h_total, 2); ?></td>
                <td><?php echo $h['order_date']; ?></td>
                <td><?php echo strtoupper(htmlspecialchars($h['status'])); ?> - <?php echo $h['delivery_date']; ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">NO COMPLETED ORDER HISTORY.</td></tr>
        <?php endif; ?>
    </tbody>
    <?php if ($history_orders && $history_orders->num_rows > 0): ?>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: center; font-weight: bold; background: rgba(0, 182, 82, 0.15);">
                OVERALL HISTORY TOTAL: $<?php echo number_format($history_grand_total, 2); ?>
            </td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>