<?php
session_start();

if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_POST['cancel_logout'])) {
    header("Location: index.php");
    exit();
}

$atrion = '<span class="A">A</span><span class="TRION">TRION</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGOUT CONFIRMATION - ATRION</title>
    <link rel="stylesheet" type="text/css" href="logout.css">
</head>
<body class="logout-page">

<div class="logout-container">
    <div class="logout-logo"><?php echo $atrion; ?></div>
    <h2 class="logout-title">LOG OUT</h2>
    <p class="logout-message">ARE YOU SURE YOU WANT TO LOG OUT OF YOUR ACCOUNT?</p>

    <form method="POST" action="logout.php" class="logout-actions">
        <button type="submit" name="confirm_logout" class="btn-confirm">YES, LOG OUT</button>
        <button type="submit" name="cancel_logout" class="btn-cancel">CANCEL</button>
    </form>
</div>

</body>
</html>