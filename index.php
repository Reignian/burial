<?php
    include('includes/header.php');
?>
<link rel="stylesheet" href="assets/landing.css">

<div class="container-fluid px-0">
    <!-- Hero Section with Slideshow -->
    <section id="hero-slideshow" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#hero-slideshow" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#hero-slideshow" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#hero-slideshow" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/images/slide1.jpg" class="d-block w-100" alt="Memorial Park">
                <div class="carousel-caption">
                    <h2>Welcome to Sto. Niño Parish Cemetery</h2>
                    <p>A peaceful resting place for your loved ones</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/slide2.jpg" class="d-block w-100" alt="Garden View">
                <div class="carousel-caption">
                    <h2>Serene Environment</h2>
                    <p>Beautiful landscapes and peaceful surroundings</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/slide3.jpg" class="d-block w-100" alt="Services">
                <div class="carousel-caption">
                    <h2>Professional Services</h2>
                    <p>Dedicated to providing respectful memorial services</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#hero-slideshow" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#hero-slideshow" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </section>
</div>

<!-- Products Section -->
<section id="products" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Available Memorial Lots</h2>
        <div class="owl-carousel product-carousel">
            <div class="item">
                <div class="card">
                    <img src="assets/images/slide2.jpg" class="card-img-top" alt="Lawn Lot">
                    <div class="card-body">
                        <h5 class="card-title">Lawn Lot</h5>
                        <p class="card-text">Peaceful garden setting with well-maintained grounds.</p>
                        <a href="browse_lots.php" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="card">
                    <img src="assets/images/slide2.jpg" class="card-img-top" alt="Garden Lot">
                    <div class="card-body">
                        <h5 class="card-title">Garden Lot</h5>
                        <p class="card-text">Beautiful garden lots with scenic views.</p>
                        <a href="browse_lots.php" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="card">
                    <img src="assets/images/slide2.jpg" class="card-img-top" alt="Family Estate">
                    <div class="card-body">
                        <h5 class="card-title">Family Estate</h5>
                        <p class="card-text">Spacious family estates for generations to come.</p>
                        <a href="browse_lots.php" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features">
    <div class="container-fluid px-0">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Us</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="fas fa-heart"></i>
                        <h4>Compassionate Care</h4>
                        <p>We provide caring and understanding service during difficult times.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="fas fa-clock"></i>
                        <h4>24/7 Support</h4>
                        <p>Available round the clock to assist you with your needs.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h4>Professional Service</h4>
                        <p>Experienced staff dedicated to serving you with respect.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>

<!-- Required JS -->
<script>
    $(document).ready(function(){
        $('.product-carousel').owlCarousel({
            loop: true,
            margin: 20,
            nav: true,
            dots: true,
            autoplay: true,
            autoplayTimeout: 5000,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 2
                },
                992: {
                    items: 3
                }
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
