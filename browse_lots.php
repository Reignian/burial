<?php

    include(__DIR__ . '/includes/header.php');
    require_once __DIR__ . '/website/lots.class.php';
    

    $burialObj = new Reservation();
    $lotarray = $burialObj->displayAvailable_lots();

?>

    <section id="lots">
        <?php
            foreach ($lotarray as $lotarr){

                $max_length = 75;
                $description = $lotarr['description'];

                if (strlen($description) > $max_length) {
                    $description = substr($description, 0, $max_length) . '...';
                }
        ?>
        
            <div class="lotsdisplay">
                <div class="lotimage">
                    <img src="admin/lots/<?= $lotarr['lot_image'] ?>" alt="">
                </div>
                <div class="lotdetails">
                    <ul>
                        <li class="lotname"><?= $lotarr['lot_name'] ?></li>
                        <li class="location"><?= $lotarr['location'] ?></li>
                        <li class="size"><?= $lotarr['size'] ?> sqm.</li>
                        <li class="price">₱ <?= $lotarr['price'] ?></li>
                        <li class="description"><?= $description ?></li>
                    </ul>
                </div>
                <a href="website/add_reservation.php?lot_id=<?= $lotarr['lot_id'] ?>">RESERVE</a>
            </div>

        <?php
            }
        ?>
    </section>
<!-- Add SSE for payment checks -->
<script src="website/js/check-payments.js"></script>
    <?php include('includes/footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>