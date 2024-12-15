<?php
    include('includes/header.php');
    require_once __DIR__ . '/website/website.class.php';

    $burialObj = new CMS();
    $array1 = $burialObj->Pubmat1();
    $array2 = $burialObj->Pubmat2();
    
?>

<link rel="stylesheet" href="assets/landing.css">

<div class="container-fluid px-0">
    <!-- Hero Section with Slideshow -->
    <section id="hero-slideshow" class="carousel slide" data-bs-ride="carousel">
    <?php $first = true; ?>
    
    <div class="carousel-indicators">
        <?php foreach ($array1 as $key => $arr): ?>
            <button type="button" data-bs-target="#hero-slideshow" data-bs-slide-to="<?= $key ?>" 
                <?= $first ? 'class="active"' : '' ?>></button>
            <?php if ($first) $first = false; ?>
        <?php endforeach; ?>
    </div>

    <div class="carousel-inner">
        <?php $first = true; ?>
        <?php foreach ($array1 as $arr): ?>
            <div class="carousel-item <?= $first ? 'active' : '' ?>">
                <img src="<?= htmlspecialchars($arr['image']) ?>" class="d-block w-100" alt="Sto. Nino Cemetery Pubmat">
                <div class="carousel-caption">
                    <h2><?= htmlspecialchars($arr['heading']) ?></h2>
                    <p><?= nl2br(htmlspecialchars($arr['text'])) ?></p>
                </div>
            </div>
            <?php if ($first) $first = false; ?>
        <?php endforeach; ?>
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
        <h2 class="text-center mb-5 fw-bold" style="font-family: 'Georgia'">Available Memorial Lots</h2>
        <div class="owl-carousel product-carousel">

        <?php foreach ($array2 as $arr) { ?>

            <div class="item">
                <div class="card">
                    <img src="<?= htmlspecialchars($arr['image']) ?>" class="card-img-top" alt="Lawn Lot">
                    <div class="card-body">
                        <h5 class="card-title fw-bold" style="color: #006064" ><?= htmlspecialchars($arr['heading']) ?></h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($arr['text'])) ?></p>
                        <a href="browse_lots.php" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
            
        <?php } ?>

        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features">
    <div class="container-fluid px-0">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" style="font-family: 'Georgia'" >Why Choose Us</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="fas fa-heart"></i>
                        <h4>Compassionate Care</h4>
                        <p><?= nl2br(htmlspecialchars("We provide caring and understanding service during difficult times.")) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="fas fa-clock"></i>
                        <h4>24/7 Support</h4>
                        <p><?= nl2br(htmlspecialchars("Available round the clock to assist you with your needs.")) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h4>Professional Service</h4>
                        <p><?= nl2br(htmlspecialchars("Experienced staff dedicated to serving you with respect.")) ?></p>
                    </div>
               </div>
            </div>
        </div>
    </div>
</section>

<!-- Add SSE for payment checks -->
<script src="website/js/check-payments.js"></script>
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

<style>
    .owl-nav{
        display: none!important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
