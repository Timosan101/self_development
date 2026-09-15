<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$feedback_query = "SELECT f.id, f.feedback_text, f.created_at, u.fullname, u.email 
                   FROM feedback f 
                   JOIN users u ON f.user_id = u.id 
                   ORDER BY f.created_at DESC";
$result = $conn->query($feedback_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FEEDBACK VIEWER - ADMIN ATRION</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <div class="header-container">
        <h1>ADMIN FEEDBACK VIEWER</h1>
    </div>

    <a href="dashboard.php" class="back-link">&larr; BACK TO DASHBOARD</a>
    <br><br>


    <div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>USERNAME</th>
                <th>EMAIL</th>
                <th>FEEDBACK MESSAGE</th>
                <th>DATE SUBMITTED</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                        $fullname_parts = explode(' ', trim($row['fullname']));
                        $first_name = $fullname_parts[0];
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($first_name)); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['feedback_text'])); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">NO FEEDBACK SUBMITTED YET.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </table>
    </div>


</body>
</html>