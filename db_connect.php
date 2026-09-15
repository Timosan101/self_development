<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "atrion";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("DATABASE CONNECTION FAILED: " . $conn->connect_error);
}
?>