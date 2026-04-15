<?php
session_start();
$page_title = 'About Us - FurniHome';
$page_banner = true;
$banner_title = 'About FurniHome';
$banner_subtitle = 'Discover Our Journey in Quality Furniture';
include '_head.php';
?>

<main class="about-container">
    <h2 class="about-heading">About Us</h2>

    <div class="about-section">
        <div class="about-left">
            <video class="furniture-video" autoplay loop muted>
                <source src="/assets/video.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <div class="about-right">
            <h3 class="why-choose-heading">Why Choose Us</h3>
            <p class="about-paragraph">
                FurniHome is a company dedicated to helping people create comfortable, stylish, and welcoming living spaces. We believe that a home should reflect your personality and provide a place where you can relax and feel at ease.
            </p>
            <p class="about-paragraph">
                The right furniture does more than just fill a room, it adds warmth, function, and character to your space. That is why we focus on offering furniture that combines good design, comfort, and durability at affordable prices. Whether you are furnishing a new home, upgrading your current space, or simply looking for one special piece, we aim to provide options that suit different tastes and lifestyles.
            </p>
            <p class="about-paragraph">
                We carefully select our products to ensure quality and long-term value, so our customers can enjoy furniture that looks good and lasts. Most importantly, we are committed to delivering a smooth and pleasant shopping experience, because customer satisfaction is always our priority. At Furniture, our goal is to make it easier for everyone to create a home they truly love.
            </p>
            <a href="/index.php" class="cta-button">Learn more</a>
        </div>
    </div>
</main>

<?php include '_foot.php'; ?>

