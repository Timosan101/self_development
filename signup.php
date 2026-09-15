<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "atrion";

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        $error_message = "DATABASE CONNECTION ERROR: " . $conn->connect_error;
    } else {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error_message = "PASSWORDS DO NOT MATCH.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error_message = "EMAIL IS ALREADY REGISTERED.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
                    
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("sss", $fullname, $email, $hashed_password);
                        if ($insert_stmt->execute()) {
                            // REDIRECT TO LOGIN PAGE UPON SUCCESSFUL REGISTRATION
                            header("Location: login.php?registered=success");
                            exit();
                        } else {
                            $error_message = "REGISTRATION FAILED. PLEASE TRY AGAIN.";
                        }
                        $insert_stmt->close();
                    } else {
                        $error_message = "DATABASE INSERT ERROR: " . $conn->error;
                    }
                }
                $stmt->close();
            } else {
                $error_message = "DATABASE QUERY ERROR: " . $conn->error;
            }
        }
        $conn->close();
    }
}

$atrion = '<span class="A">A</span><span class="TRION">TRION</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ATRION</title>
    <link rel="stylesheet" type="text/css" href="logs.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-logo"><?php echo $atrion; ?></div>
    <h2 class="auth-title">CREATE ACCOUNT</h2>

    <?php if (!empty($error_message)): ?>
        <div class="auth-error" style="color: #FF5555; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; text-align: center;"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="signup.php" class="auth-form">
        <div class="form-group">
            <label for="fullname">FULL NAME</label>
            <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
            <label for="email">EMAIL ADDRESS</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="password">PASSWORD</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">CONFIRM PASSWORD</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>

        <!-- MAIN ACTION BUTTON -->
        <button type="submit" class="auth-btn">SIGN UP</button>

        <!-- STYLED BACK BUTTON -->
        <a href="index.php" class="back-btn">BACK</a>
    </form>

    <div class="auth-footer">
        ALREADY HAVE AN ACCOUNT? <a href="login.php">LOG IN</a>
    </div>
</div>

</body>
</html>