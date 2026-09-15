<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../db_connect.php';



$user_id = $_SESSION['user_id'];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_feedback'])) {
        $feedback_text = trim($_POST['feedback_text']);
        $rating = intval($_POST['rating']);

        if (!empty($feedback_text) && $rating >= 1 && $rating <= 5) {
            if ($stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, feedback_text, created_at) VALUES (?, ?, ?, NOW())")) {
                $stmt->bind_param("iis", $user_id, $rating, $feedback_text);
                if (!$stmt->execute()) {
                    die("EXECUTE FAILED: " . $stmt->error);
                }
                $stmt->close();
                header("Location: feedbackclients.php");
                exit();
            } else {
                die("PREPARE FAILED: " . $conn->error);
            }
        }
    } elseif (isset($_POST['delete_feedback'])) {
        $feedback_id = intval($_POST['feedback_id']);
        if ($stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?")) {
            $stmt->bind_param("ii", $feedback_id, $user_id);
            $stmt->execute();
            $stmt->close();
            header("Location: feedbackclients.php");
            exit();
        }
    }
}

$feedback_query = "SELECT f.id, f.user_id, f.rating, f.feedback_text, f.created_at, u.fullname 
                   FROM feedback f 
                   JOIN users u ON f.user_id = u.id 
                   ORDER BY f.created_at DESC";
$feedback_result = $conn->query($feedback_query);
if (!$feedback_result) {
    die("QUERY FAILED: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLIENT FEEDBACK PORTAL - ATRION</title>
    <link rel="stylesheet" href="../feedbacks.css">
</head>
<body>
    <div class="main-header">
        <h1>COMMUNITY FEEDBACK</h1>
    </div>

    <?php if (!empty($success_message)): ?>
        <p class="success-alert"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <div class="feedback-container">
        <div class="feedback-left">
            <div class="glass-card">
                <form method="POST" action="feedbackclients.php">
                    <h3>RATE YOUR SATISFACTION</h3>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 stars">&#9733;</label>
                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">&#9733;</label>
                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">&#9733;</label>
                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">&#9733;</label>
                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">&#9733;</label>
                    </div>

                    <h3 class="feedback-title-margin">YOUR FEEDBACK</h3>
                    <textarea name="feedback_text" rows="4" required class="form-input feedback-textarea" placeholder="WRITE YOUR FEEDBACK HERE..."></textarea>

                    <div class="form-actions">
                        <button type="submit" name="submit_feedback" class="btn-save">SEND FEEDBACK</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="feedback-right">
            
            <div class="community-list-wrapper">
                <?php if ($feedback_result && $feedback_result->num_rows > 0): ?>
                    <?php while ($row = $feedback_result->fetch_assoc()): ?>
                        <?php 
                            $fullname_parts = explode(' ', trim($row['fullname']));
                            $first_name = $fullname_parts[0];
                        ?>
                        <div class="glass-card community-card">
                            <div class="community-header-row">
                                <strong><?php echo strtoupper(htmlspecialchars($first_name)); ?></strong>
                                <span class="star-display">
                                    <?php echo str_repeat('&#9733;', intval($row['rating'])) . str_repeat('&#9734;', 5 - intval($row['rating'])); ?>
                                </span>
                            </div>
                            <p class="community-feedback-text"><?php echo nl2br(htmlspecialchars($row['feedback_text'])); ?></p>
                            <div class="feedback-footer-row">
                                <div class="feedback-meta"><?php echo $row['created_at']; ?></div>
                                <?php if (intval($row['user_id']) === intval($user_id)): ?>
                                    <form method="POST" action="feedbackclients.php" style="display:inline;">
                                        <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_feedback" class="btn-delete" onclick="return confirm('ARE YOU SURE YOU WANT TO DELETE THIS FEEDBACK?');">DELETE</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="glass-card no-feedback-card">
                        NO COMMUNITY FEEDBACK AVAILABLE YET.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="back-button-container">
        <a href="../index.php" class="styled-back-btn">&larr; BACK TO HOME</a>
    </div>
</body>
</html>