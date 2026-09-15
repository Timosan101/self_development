<?php
session_start();

// 1. INCLUDE DATABASE CONNECTION
require_once __DIR__ . '/../db_connect.php';

// 2. CHECK ADMIN AUTHENTICATION
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 3. HANDLE PRODUCT DELETION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if ($stmt = $conn->prepare("DELETE FROM products WHERE id = ?")) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manageproducts.php");
    exit();
}

// 4. HANDLE PRODUCT CREATION & EDITION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!empty($name) && !empty($category) && $price >= 0 && $stock >= 0) {
        
        // HANDLE FILE UPLOAD IF PROVIDED
        $image_path = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['product_image']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['product_image']['name']);
            $upload_dir = '../images/fourth/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $image_path = 'images/fourth/' . $file_name;
            }

        }

        if ($product_id > 0) {
            // UPDATE EXISTING PRODUCT
            if ($image_path) {
                if ($stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ?, image = ? WHERE id = ?")) {
                    $stmt->bind_param("ssdisi", $name, $category, $price, $stock, $image_path, $product_id);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                if ($stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?")) {
                    $stmt->bind_param("ssdii", $name, $category, $price, $stock, $product_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } else {
            // INSERT NEW PRODUCT
            if (!$image_path) {
                $image_path = 'images/fourth/shirts.jpg';
            }
            if ($stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)")) {
                $stmt->bind_param("ssdis", $name, $category, $price, $stock, $image_path);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    header("Location: manageproducts.php");
    exit();
}

// 5. FETCH ALL PRODUCTS
$products = false;
if ($result = $conn->query("SELECT id, name, category, price, stock, image FROM products ORDER BY id DESC")) {
    $products = $result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MANAGE PRODUCTS - ATRION</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>

    <div class="header-container">
        <h1>PRODUCT INVENTORY MANAGER</h1>
        <button onclick="openForm()" class="btn-add">+ ADD NEW PRODUCT</button>
    </div>

    <a href="dashboard.php" class="back-link">&larr; BACK TO DASHBOARD</a>
    <br><br>

    <!-- ADD / EDIT PRODUCT FORM CONTAINER -->
    <div id="productFormCard" class="card form-card">
        <h3 id="formTitle">ADD NEW PRODUCT</h3>
        <form method="POST" action="processing.php" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id" value="0">

            <label for="name">PRODUCT NAME:</label>
            <input type="text" name="name" id="name" required class="form-input">

            <label for="category">CATEGORY:</label>
            <input type="text" name="category" id="category" required class="form-input">

            <label for="price">PRICE ($):</label>
            <input type="number" step="0.01" name="price" id="price" required class="form-input">

            <label for="stock">STOCK QUANTITY:</label>
            <input type="number" name="stock" id="stock" required class="form-input">

            <label for="product_image">PRODUCT IMAGE:</label>
            <div id="imagePreviewContainer" style="margin-bottom: 10px;"></div>
            <input type="file" name="product_image" id="product_image" accept="image/*" class="form-input">


            <div id="currentImageDisplay" style="margin-bottom: 10px; display: none;">
                <span class="label">CURRENT IMAGE:</span><br>
                <img id="existingImageTag" src="" alt="Product Image" width="80"><br>
                <label style="display: inline-block; margin-top: 5px;">
                    <input type="checkbox" name="remove_image" value="1"> REMOVE CURRENT IMAGE
                </label>
            </div>



            <div class="form-actions">
                <button type="submit" class="btn-save">SAVE PRODUCT</button>
                <button type="button" onclick="closeForm()" class="btn-cancel">CANCEL</button>
            </div>
        </form>
    </div>

    <!-- PRODUCTS DATA TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>PRODUCT NAME</th>
                <th>CATEGORY</th>
                <th>PRICE ($)</th>
                <th>STOCK</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products && $products->num_rows > 0): ?>
                <?php 
                $display_id = $products->num_rows; 
                ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $display_id--; ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($product['name'])); ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($product['category'])); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <a href="javascript:void(0);" 
                               onclick='editProduct(<?php echo json_encode($product); ?>)'>EDIT</a> | 
                            <a href="processing.php?action=delete&id=<?php echo $product['id']; ?>" 
                               onclick="return confirm('ARE YOU SURE YOU WANT TO REMOVE THIS PRODUCT?');" 
                               class="logout-link">REMOVE</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-data">NO PRODUCTS FOUND IN INVENTORY.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function openForm() {
            document.getElementById('productFormCard').style.display = 'block';
            document.getElementById('formTitle').innerText = 'ADD NEW PRODUCT';
            document.getElementById('product_id').value = '0';
            document.getElementById('name').value = '';
            document.getElementById('category').value = '';
            document.getElementById('price').value = '';
            document.getElementById('stock').value = '';
            document.getElementById('product_image').value = '';
            document.getElementById('imagePreviewContainer').innerHTML = '';
        }

        function editProduct(product) {
            document.getElementById('productFormCard').style.display = 'block';
            document.getElementById('formTitle').innerText = 'EDIT PRODUCT #' + product.id;
            document.getElementById('product_id').value = product.id;
            document.getElementById('name').value = product.name;
            document.getElementById('category').value = product.category;
            document.getElementById('price').value = product.price;
            document.getElementById('stock').value = product.stock;
            document.getElementById('product_image').value = '';
            
            let previewHtml = '';
            if (product.image) {
                previewHtml = '<small style="color: #ccc;">CURRENT IMAGE:</small><br><img src="../' + product.image + '" alt="Product Image" style="max-height: 80px; margin-top: 5px; border-radius: 4px;">';
            }
            document.getElementById('imagePreviewContainer').innerHTML = previewHtml;

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeForm() {
            document.getElementById('productFormCard').style.display = 'none';
        }
    </script>


<script>
function openForm() {
    document.getElementById('productFormCard').style.display = 'block';
    document.getElementById('formTitle').innerText = 'ADD NEW PRODUCT';
    document.getElementById('product_id').value = '0';
    document.getElementById('name').value = '';
    document.getElementById('category').value = '';
    document.getElementById('price').value = '';
    document.getElementById('stock').value = '';
    document.getElementById('product_image').value = '';
    document.getElementById('imagePreviewContainer').innerHTML = '';
    document.getElementById('currentImageDisplay').style.display = 'none';
    document.getElementById('existingImageTag').src = '';
}

function editProduct(product) {
    document.getElementById('productFormCard').style.display = 'block';
    document.getElementById('formTitle').innerText = 'EDIT PRODUCT #' + product.id;
    document.getElementById('product_id').value = product.id;
    document.getElementById('name').value = product.name;
    document.getElementById('category').value = product.category;
    document.getElementById('price').value = product.price;
    document.getElementById('stock').value = product.stock;
    document.getElementById('product_image').value = '';

    const imgDisplay = document.getElementById('currentImageDisplay');
    const imgTag = document.getElementById('existingImageTag');
    
    if (product.image && product.image.trim() !== '') {
        imgTag.src = '../' + product.image;
        imgDisplay.style.display = 'block';
    } else {
        imgDisplay.style.display = 'none';
        imgTag.src = '';
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeForm() {
    document.getElementById('productFormCard').style.display = 'none';
}
</script>



</body>
</html>