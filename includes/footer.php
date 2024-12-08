<?php
    require_once(__DIR__ . '/../database.php');

    try {
        $database = new Database();
        $conn = $database->connect();
        
        $sql = "SELECT * FROM contact";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
    }
?>



<footer class="contact-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2 class="section-title mb-4">Contact Us</h2>
                <ul class="contact-info">
                    <li><i class="fas fa-phone"></i> <?= htmlspecialchars($result['phone']) ?></li>
                    <li><i class="fas fa-envelope"></i> <?= htmlspecialchars($result['email']) ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($result['address']) ?></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>