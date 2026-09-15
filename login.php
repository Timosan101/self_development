<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error_message = '';

$success_message = '';
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $success_message = "ACCOUNT CREATED SUCCESSFULLY! PLEASE LOG IN.";
}

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
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (!empty($email) && !empty($password)) {

            // FETCH ID, FULLNAME, PASSWORD, AND ROLE
            $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");

            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password']) || $password === $user['password']) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $email;
                        $_SESSION['fullname'] = $user['fullname'];
                        $_SESSION['role'] = $user['role'];

                        // REDIRECT BASED ON ROLE
                        if ($user['role'] === 'admin') {
                            header("Location: admin/dashboard.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        $error_message = "INVALID EMAIL OR PASSWORD.";
                    }
                } else {
                    $error_message = "INVALID EMAIL OR PASSWORD.";
                }
                $stmt->close();
            } else {
                $error_message = "DATABASE QUERY ERROR: " . $conn->error;
            }
        } else {
            $error_message = "PLEASE FILL IN ALL FIELDS.";
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
    <title>LOG IN - ATRION</title>
    <link rel="stylesheet" type="text/css" href="logs.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-logo"><?php echo $atrion; ?></div>
    <h2 class="auth-title">LOG IN</h2>

    <?php if (!empty($success_message)): ?>
        <div class="auth-success" style="color: #00FF66; background: rgba(0,255,102,0.1); padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; text-align: center; border: 1px solid rgba(0,255,102,0.3);"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="auth-error" style="color: #FF5555; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; text-align: center; border: 1px solid rgba(255,0,0,0.3);"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="auth-form">
        <div class="form-group">
            <label for="email">EMAIL ADDRESS</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="password">PASSWORD</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="auth-btn">LOG IN</button>
        <a href="index.php" class="back-btn">BACK</a>
    </form>

    <div class="auth-footer">
        DON'T HAVE AN ACCOUNT? <a href="signup.php">SIGN UP</a>
    </div>
</div>

</body>
</html>