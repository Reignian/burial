<?php
    include('includes/header.php');


?>

    <h2><?= 'Welcome ' . $_SESSION['account']['first_name'] ?></h2>

    <section id="home">

    </section>


<!-- Footer with Newsletter -->
<footer>
    <p>&copy; 2024 Sto. Niño Parish Cemetery</p>
    
    <div class="newsletter">
        <h3>Subscribe to Our Newsletter</h3>
        <form action="subscribe.php" method="POST">
            <input type="email" placeholder="Enter your email" name="email" required>
            <button type="submit" class="button">Subscribe</button>
        </form>
    </div>

    <p>
        <a href="about.php">About</a> |
        <a href="support.php">Support</a> |
        <a href="trademark.php">Trademark</a>
    </p>
</footer>

<script>
    let slideIndex = 0;
    showSlides();

    function showSlides() {
        let i;
        let slides = document.getElementsByClassName("slides");
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";  
        }
        slideIndex++;
        if (slideIndex > slides.length) {slideIndex = 1}    
        slides[slideIndex-1].style.display = "block";  
        setTimeout(showSlides, 4000); // Change image every 4 seconds
    }

    function changeSlide(n) {
        let i;
        let slides = document.getElementsByClassName("slides");
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";  
        }
        slideIndex += n;
        if (slideIndex > slides.length) {slideIndex = 1}    
        if (slideIndex < 1) {slideIndex = slides.length}  
        slides[slideIndex-1].style.display = "block";
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

