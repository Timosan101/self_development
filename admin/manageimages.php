<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// HANDLE AJAX BACKGROUND SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_submit'])) {
    $section_key = $_POST['section_key'];
    $response = ['status' => 'error', 'message' => ''];
    
    if (isset($_POST['remove_image'])) {
        $stmt = $conn->prepare("DELETE FROM homepage_images WHERE section_key = ?");
        $stmt->bind_param("s", $section_key);
        $stmt->execute();
        $stmt->close();
        $response = ['status' => 'success', 'message' => 'IMAGE REMOVED SUCCESSFULLY!', 'image_url' => '../images/kani.png', 'has_image' => false];
    } elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = basename($_FILES["image_file"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;
        
        if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
            $db_path = "images/" . basename($target_file);
            $stmt = $conn->prepare("INSERT INTO homepage_images (section_key, image_path) VALUES (?, ?) ON DUPLICATE KEY UPDATE image_path = ?");
            $stmt->bind_param("sss", $section_key, $db_path, $db_path);
            $stmt->execute();
            $stmt->close();
            $response = ['status' => 'success', 'message' => 'IMAGE UPDATED SUCCESSFULLY!', 'image_url' => '../' . $db_path, 'has_image' => true];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$images = [];
$result = $conn->query("SELECT * FROM homepage_images");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $images[$row['section_key']] = $row['image_path'];
    }
}

function renderSingleForm($section_key, $label, $current_image, $default_img = 'images/kani.png') {
    $img_src = !empty($current_image) ? "../" . $current_image : "../" . $default_img;
    echo "<div class='image-manager-card' id='card-$section_key'>";
    
    // NOTIFICATION CONTAINER HIDDEN BY DEFAULT, SHOWN VIA JS ON SUCCESS
    echo "<div class='notification-box' style='display:none; background: #d4edda; color: #155724; padding: 8px; margin-bottom: 10px; border-radius: 4px; font-size: 12px; font-weight: bold; border: 1px solid #c3e6cb;'></div>";

    echo "<h4>$label</h4>";
    echo "<img src='$img_src' alt='$label' id='img-$section_key'><br>";
    echo "<form class='ajax-image-form' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='section_key' value='$section_key'>";
    echo "<input type='hidden' name='ajax_submit' value='1'>";
    // REMOVED 'required' SO CLICKING REMOVE DOES NOT TRIGGER HTML5 FILE VALIDATION
    echo "<input type='file' name='image_file'><br>";
    echo "<button type='submit'>UPDATE</button>";
    
    $remove_display = !empty($current_image) ? 'inline-block' : 'none';
    echo "<button type='submit' name='remove_image' value='1' class='remove-btn' style='display:$remove_display;'>REMOVE</button>";
    echo "</form>";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MANAGE HOMEPAGE IMAGES - ADMIN</title>
    <link rel="stylesheet" href="../design.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="header-container">
        <h1>MANAGE HOMEPAGE IMAGES</h1>
    </div>
    <a href="dashboard.php" class="back-link">&larr; BACK TO DASHBOARD</a>
    <br><br>

    <div class="image-manager-section">
        <h2>BESTSELLER SECTION</h2>
        <div class="image-manager-grid">
            <?php 
                renderSingleForm('bestseller_1', '1st', $images['bestseller_1'] ?? '');
                renderSingleForm('bestseller_2', '2nd', $images['bestseller_2'] ?? '');
                renderSingleForm('bestseller_3', '3rd', $images['bestseller_3'] ?? '');
                renderSingleForm('bestseller_4', '4th', $images['bestseller_4'] ?? '');
            ?>
        </div>
    </div>

    <div class="image-manager-section">
        <h2>CATEGORIES SECTION</h2>
        <div class="image-manager-grid">
            <?php 
                renderSingleForm('category_shirts', 'Shirts', $images['category_shirts'] ?? '');
                renderSingleForm('category_paddles', 'Paddles', $images['category_paddles'] ?? '');
                renderSingleForm('category_sneakers', 'Sneakers', $images['category_sneakers'] ?? '');
            ?>
        </div>
    </div>

    <div class="image-manager-section">
        <h2>COMMENTS SECTION IMAGES</h2>
        <div class="image-manager-grid">
            <?php 
                renderSingleForm('comment_1', 'Comment 1', $images['comment_1'] ?? '');
                renderSingleForm('comment_2', 'Comment 2', $images['comment_2'] ?? '');
                renderSingleForm('comment_3', 'Comment 3', $images['comment_3'] ?? '');
                renderSingleForm('comment_4', 'Comment 4', $images['comment_4'] ?? '');
            ?>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".ajax-image-form").forEach(form => {
        form.addEventListener("submit", function(e) {
            e.preventDefault(); // STOP FULL PAGE RELOAD
            
            const card = form.closest(".image-manager-card");
            const sectionKey = form.querySelector("input[name='section_key']").value;
            const notificationBox = card.querySelector(".notification-box");
            const imgElement = document.getElementById("img-" + sectionKey);
            const removeBtn = form.querySelector(".remove-btn");
            const fileInput = form.querySelector("input[name='image_file']");
            
            // CHECK IF THE CLICKED SUBMIT BUTTON WAS 'REMOVE'
            const submitter = e.submitter;
            const isRemove = submitter && submitter.name === "remove_image";
            
            // IF UPDATING, ENSURE A FILE IS CHOSEN MANUALLY BEFORE SENDING
            if (!isRemove && fileInput.files.length === 0) {
                alert("PLEASE SELECT A FILE TO UPDATE.");
                return;
            }
            
            const formData = new FormData(form);
            if (isRemove) {
                formData.append("remove_image", "1");
            }

            fetch("manageimages.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // UPDATE IMAGE SOURCE AND TOGGLE REMOVE BUTTON DYNAMICALLY
                    imgElement.src = data.image_url + "?t=" + new Date().getTime(); // Prevent caching
                    if (data.has_image) {
                        removeBtn.style.display = "inline-block";
                    } else {
                        removeBtn.style.display = "none";
                    }
                    
                    // SHOW GREEN NOTIFICATION BOX INSTANTLY WITHOUT SCROLLING
                    notificationBox.textContent = data.message;
                    notificationBox.style.display = "block";
                    
                    // HIDE NOTIFICATION AFTER 3 SECONDS
                    setTimeout(() => {
                        notificationBox.style.display = "none";
                    }, 3000);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});
</script>
</body>
</html>