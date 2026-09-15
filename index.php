<?php

session_start();
require_once 'db_connect.php';

// FETCH HOMEPAGE IMAGES DYNAMICALLY FROM THE DATABASE WITH A FALLBACK TO KANI.PNG
$homepage_images = [];
$img_result = $conn->query("SELECT section_key, image_path FROM homepage_images");
if ($img_result) {
    while ($row = $img_result->fetch_assoc()) {
        // ASSIGN DATABASE PATH IF AVAILABLE, OTHERWISE USE DEFAULT KANI.PNG FALLBACK
        $homepage_images[$row['section_key']] = !empty($row['image_path']) ? $row['image_path'] : 'images/kani.png';
    }
}

// ENSURE EVERY KEY HAS A DEFAULT FALLBACK EVEN IF NOT FOUND IN DATABASE
$sections = ['bestseller_1', 'bestseller_2', 'bestseller_3', 'bestseller_4', 'category_shirts', 'category_paddles', 'category_sneakers', 'comment_1', 'comment_2', 'comment_3', 'comment_4'];
foreach ($sections as $sec) {
    if (!isset($homepage_images[$sec])) {
        $homepage_images[$sec] = 'images/kani.png';
    }
}

// DYNAMIC IMAGE PATH VARIABLES FOR DIRECT USE IN YOUR INDEX.PHP IMAGE TAGS
$bestseller_1_img = !empty($homepage_images['bestseller_1']) ? $homepage_images['bestseller_1'] : 'images/kani.png';
$bestseller_2_img = !empty($homepage_images['bestseller_2']) ? $homepage_images['bestseller_2'] : 'images/kani.png';
$bestseller_3_img = !empty($homepage_images['bestseller_3']) ? $homepage_images['bestseller_3'] : 'images/kani.png';
$bestseller_4_img = !empty($homepage_images['bestseller_4']) ? $homepage_images['bestseller_4'] : 'images/kani.png';
$category_shirts_img = !empty($homepage_images['category_shirts']) ? $homepage_images['category_shirts'] : 'images/kani.png';
$category_paddles_img = !empty($homepage_images['category_paddles']) ? $homepage_images['category_paddles'] : 'images/kani.png';
$category_sneakers_img = !empty($homepage_images['category_sneakers']) ? $homepage_images['category_sneakers'] : 'images/kani.png';

$user_firstname = '';
if (isset($_SESSION['fullname'])) {
    // THIS EXTRACTS ONLY THE FIRST NAME FROM "JOHN DOE" -> "JOHN"
    $name_parts = explode(' ', trim($_SESSION['fullname']));
    $user_firstname = strtoupper($name_parts[0]);
}


// CHECK IF THE USER IS LOGGED IN
$isLoggedIn = isset($_SESSION['user_id']); 

// HELPER FUNCTION FOR INTERACTIVE LINKS
function getInteractiveLink($destination, $isLoggedIn) {
    return $isLoggedIn ? $destination : 'login.php';
}

$atrion='<span class="A">A</span><span class="TRION">TRION</span>';
$tag="UPGRADE YOUR GAME EXPERIENCE...";
$desc="Worldwide’s first and authentic online pickleball market and events management center.";
$lux='<span class="kani">Luxury</span> at its finest..';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>atrion.com</title>
</head>

<body>

<header class="header">

    <div class="LOGO">
        <?php echo $atrion; ?>
    </div>


    <nav class="top-navigation">
    <?php if ($isLoggedIn): ?>
        <span class="welcome-text">
    <?php echo !empty($user_firstname) ? "WELCOME, " . htmlspecialchars($user_firstname) . "!" : "WELCOME!"; ?>
</span>
        <a href="logout.php" class="login">LOG OUT</a>
    <?php else: ?>
        <a href="signup.php">Sign Up</a>
        <a href="login.php" class="login">Log In</a>
    <?php endif; ?>
</nav>


    <nav class="main-navigation">
        <a href="#first-page">HOME</a>
        <a href="apparels.php">APPARELS</a>
        <a href="#fourth-page">FITS</a>
        <a href="#fifth-page">CONTACT US</a>
        <a href="#third-page">ABOUT US</a>
    </nav>

</header>

<!-- ************************   FIRST PAGE STARTS HERE ****************************-->
<section class="first-page" id="first-page">

    <div class="gradient">
    <img src="images/first/kanisya.png" alt="SPORTS LUXURY">
    </div>

    <div class="content">
    <p class="tagline"> <?php echo $tag; ?> </p>
        <h1 class="bold">ASCEND<br>SPORTS<br><span class="luxury">LUXURY</span></h1>
        <p class="description">
            <?php echo $desc; ?>
        </p>

        <p class="lux"><?php echo $lux; ?> </p>


        <div class="group">
    <a href="<?php echo getInteractiveLink('apparels.php', $isLoggedIn); ?>" class="shoppie">SHOP NOW!</a>
    </div>

    </div>


    <!-- ATHLETE -->

    <div class="image">
        <img src="images/first/trans backgrround.png" alt="ATRION LUXURY">
    </div>

    <!-- ENDING SA FIRST -->

    <div class="back">
        <img src="images/first/ending/cover.png">
    </div>


    <!-- FEATURES HERE -->

    <div class="features">

        <div class="authentic">
        <img src="images/first/ending/authentic.png" alt="authentic">
        <div class="text">
            <span>100%</span>
            <span>AUTHENTIC</span>
        </div>
        </div>


        <div class="worldwide">
        <img src="images/first/ending/shipping.png" alt="worldwide shipping">
        <div class="text">
            <span>WORLDWIDE</span>
            <span>SHIPPING</span>
        </div>
        </div>


        <div class="warranty">
        <img src="images/first/ending/warranty.png" alt="1 year warranty">
        <div class="text">
            <span>1 YEAR</span>
            <span>WARRANTY</span>
        </div>
        </div>


        <div class="payment">
        <img src="images/first/ending/secured.png" alt="secured payment">
        <div class="text">
            <span>SECURE</span>
            <span>PAYMENT</span>
        </div>
        </div>

    </div>

</section>


<!-- ************************   SECOND PAGE STARTS HERE ****************************-->
<section class="second-page">

    <div class="back-sliding-gradient">
        <img src="images/second/kilid.png" alt="ATRION BESTSELLERS">
    </div> 

    <div class="pikol">
        <img src="images/second/paddle.png" alt="ATRION BESTSELLERS">
    </div>

    <!-- RIGHT SIDE: HEADINGS & PRODUCT CARDS -->
    <div class="bestseller-right">
        
        <div class="bestseller-header">
            <span class="sub-heading">choose your game !</span>
            <h2 class="main-heading">BESTSELLER ?</h2>
        </div>

        <div class="products-grid">
    


    <!-- PRODUCT CARDS GRID (4 ITEMS) -->

    <!-- 1ST PLACE PRODUCT -->
    <a href="<?php echo getInteractiveLink('apparels.php?id=1', $isLoggedIn); ?>" class="product-card card-1st">
        <span class="rank">1ST</span>
            <div class="card-image">
                <img src="<?php echo $bestseller_1_img; ?>" alt="PERSEUS PRO IV">
            </div>
    </a>

    <!-- 2ND PLACE PRODUCT -->
    <a href="<?php echo getInteractiveLink('apparels.php?id=2', $isLoggedIn); ?>" class="product-card card-2nd">
        <span class="rank">2ND</span>
                <div class="card-image">
                    <img src="<?php echo $bestseller_2_img; ?>" alt="SELKIRK">
            </div>
    </a>

    <!-- 3RD PLACE PRODUCT -->
    <a href="<?php echo getInteractiveLink('apparels.php?id=3', $isLoggedIn); ?>" class="product-card card-3rd">
        <span class="rank">3RD</span>
                <div class="card-image">
                    <img src="<?php echo $bestseller_3_img; ?>" alt="LUXX PRO">
            </div>
    </a>

    <!-- 4TH PLACE PRODUCT -->
    <a href="<?php echo getInteractiveLink('apparels.php?id=4', $isLoggedIn); ?>" class="product-card card-4th">
        <span class="rank">4TH</span>
                <div class="card-image">
                    <img src="<?php echo $bestseller_4_img; ?>" alt="FOURTH PADDLE">
            </div>
    </a>
    </div>
    </div>
</section>


<!-- ************************   THIRD PAGE STARTS HERE ****************************-->

<section class="third-page" id="third-page">
    <!-- BLUE BANNER BACKGROUND STRIP -->
    <div class="banner-bg">
        <img src="images/third/3rd.png" alt="BANNER BACKGROUND">
    </div>

    <!-- MAIN CONTAINER -->
    <div class="third-content">

        <!-- LEFT SIDE: CARD WITH TEXT AND BUTTON -->
        <div class="vip-card">
            <span class="sub-title">BUNDLE EXPERIENCE</span>
            <h2>Join Our VIP's<br>best FREEBIES<br>& SERVICES !</h2>
            <a href="<?php echo getInteractiveLink('download-app.php', $isLoggedIn); ?>" class="app-btn">GET THE APP</a>
        </div>

        <!-- RIGHT SIDE: BIG SPLIT BALL GRAPHIC AND OVERLAY TEXT -->
        <div class="ball-container">
            <img src="images/third/bigball.png" alt="VIP BUNDLEBALL" class="big-ball-img">
            <div class="overlay-text">
                <span class="yellow-text-sm">VIP</span>
                <span class="yellow-text-lg">BUNDLEBALL!</span>
            </div>
        </div>

    </div>

    <!-- FOOTER CAPTION -->
    <p class="bottom-caption">
        Experience the BEST EVER pickle ball bundle <br> pack with your friends now !
    </p>
</section>



<!-- ************************   FOURTH PAGE STARTS HERE ****************************-->

<section class="fourth-page" id="fourth-page">

    <!-- HANDSHAKE BACKGROUND IMAGE -->

    <div class="handshake-bg">
        <img src="images/fourth/SPORTSMANSHIP.jpg" alt="Sportsmanship Background">
    </div>

    <!-- TOP SECTION: MODEL CARD & MAIN HEADING -->
    <div class="fourth-top">
        
        <!-- FEMALE MODEL CARD -->
        <div class="model-card">
            <img src="images/fourth/bayenisya.png" alt="Female Model" class="model-img">
            <div class="model-info">
                <span>female</span>
                <span>26</span>
                <span>5'11</span>
                <span>MVP: 16X</span>
            </div>
        </div>

        <!-- RIGHT SIDE HEADINGS -->
        <div class="fourth-heading">
            <h2>Check Our<br>High Quality <span class="cyan-text">Luxury</span><br>Clothing & Equipments !</h2>
            <p>Worldwide's best and luxurious equipment a company has to offer </p>
        </div>

    </div>

<!-- BOTTOM SECTION: 3 PRODUCT CATEGORIES -->
<div class="fourth-bottom">

    <!-- CATEGORY 1: SHIRTS -->
    <div class="category-item">
        <span class="cat-title">SHIRTS</span>
        <a href="<?php echo getInteractiveLink('apparels.php#shirts-section', $isLoggedIn); ?>" class="cat-card">
            <img src="<?php echo $category_shirts_img; ?>" alt="Apparel Shirts">
        </a>
    </div>

    <!-- CATEGORY 2: PADDLES -->
    <div class="category-item">
        <span class="cat-title">PADDLES</span>
        <a href="<?php echo getInteractiveLink('apparels.php#paddles-section', $isLoggedIn); ?>" class="cat-card">
            <img src="<?php echo $category_paddles_img; ?>" alt="Paddle Equipment">
        </a>
    </div>

    <!-- CATEGORY 3: SNEAKERS -->
    <div class="category-item">
        <span class="cat-title">SNEAKERS</span>
        <a href="<?php echo getInteractiveLink('apparels.php#sneakers-section', $isLoggedIn); ?>" class="cat-card">
            <img src="<?php echo $category_sneakers_img; ?>" alt="Pickleball Shoes">
        </a>
    </div>

</div>
    </div>
</section>



<!-- ************************   FIFTH PAGE STARTS HERE ****************************-->

<section class="fifth-page" id="fifth-page">
    <!-- COURT BACKGROUND IMAGE -->
    <div class="fifth-bg">
        <img src="images/fifth/court.jpg" alt="Court Background">
    </div>

    <!-- MAIN FEEDBACK CONTAINER -->
    <div class="fifth-container">

        <!-- TOP PART: FEEDBACK TEXT & LADY MODEL CARD -->
        <div class="fifth-top">
            
            <div class="feedback-text">
                <h2>FEEDBACK</h2>
                <p>Where concerns and demands meets with your awaited answer.</p>
                <p>Your feedback is seen, answered and valued.<br>Feel free to vent out anything.</p>
                <p class="tagline">For the goodness of all luxuriness.</p>
            </div>

            <div class="lady-card">
                <span class="lady-caption">Mary jane's response: <br><br><br>The BEST experience<br>EVER !</span>
                <img src="images/fifth/janiii.png" alt="Model Lady" class="lady-img">
            </div>

        </div>

        <!-- MIDDLE PART: 4 GALLERY IMAGES -->
    <div class="fifth-gallery">
    <?php 
        $feedback_page = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin/feedbackviewer.php' : 'clients/feedbackclients.php';
    ?>

    <a href="<?php echo $feedback_page; ?>" class="gallery-card">
        <img src="<?php echo !empty($homepage_images['comment_1']) ? $homepage_images['comment_1'] : 'images/kani.png'; ?>" alt="Comment 1">
    </a>

    <a href="<?php echo $feedback_page; ?>" class="gallery-card">
        <img src="<?php echo !empty($homepage_images['comment_2']) ? $homepage_images['comment_2'] : 'images/kani.png'; ?>" alt="Comment 2">
    </a>

    <a href="<?php echo $feedback_page; ?>" class="gallery-card">
        <img src="<?php echo !empty($homepage_images['comment_3']) ? $homepage_images['comment_3'] : 'images/kani.png'; ?>" alt="Comment 3">
    </a>

    <a href="<?php echo $feedback_page; ?>" class="gallery-card">
        <img src="<?php echo !empty($homepage_images['comment_4']) ? $homepage_images['comment_4'] : 'images/kani.png'; ?>" alt="Comment 4">
    </a>
</div>
        <!-- FOOTER PART: BRAND, CONTACTS & SPONSORS -->
        <div class="fifth-footer">
    
    <div class="footer-left">
        <div class="footer-logo">
            <?php echo $atrion; ?>
        </div>
        <div class="contact-info">
            <p>
                <img src="images/logo/gmail.png" alt="Gmail" class="social-icon">
                atrion.luxury@gmail.com
            </p>
            <p>
                <a href="https://www.instagram.com/augustus.him?stkn=YzA0dHV1YWZydnJ0" target="_blank">
                    <img src="images/logo/IG.png" alt="Instagram" class="social-icon">
                    atrion_sports&luxury101
                </a>
            </p>
            <p>
                <a href="https://www.facebook.com/share/14rgd7JpyiT/" target="_blank">
                    <img src="images/logo/fb.png" alt="Facebook" class="social-icon">
                    atrion luxury
                </a>
            </p>
        </div>
    </div>


    <div class="footer-right">
    <span class="sponsors-title">SPONSORS</span>
    <div class="sponsors-grid">
        <div class="sponsor-item">
            <img src="images/logo/BRAND/B.png" alt="Balenciaga">
            <span>BALENCIAGA</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/V.png" alt="Versace">
            <span>VERSACE</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/N.png" alt="Nike">
            <span>NIKE</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/polo.png" alt="Polo">
            <span>POLO</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/LV.png" alt="Louis Vuitton">
            <span>LOUIS VUITTON</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/A.png" alt="Adidas">
            <span>ADIDAS</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/lacoste.png" alt="Lacoste">
            <span>LACOSTE</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/P.png" alt="Puma">
            <span>PUMA</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/G.png" alt="Gucci">
            <span>GUCCI</span>
        </div>
        <div class="sponsor-item">
            <img src="images/logo/BRAND/UA.png" alt="Under Armour">
            <span>UNDER ARMOUR</span>
        </div>
    </div>
    </div>

        </div>

        <!-- COPYRIGHT BOTTOM -->
        <div class="copyright">
            © Atrion 2026. All rights reserved
        </div>

    </div>
</section>


<script src="script.js"></script>
</body>
</html>