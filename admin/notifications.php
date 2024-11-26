<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/notifications/notifications.class.php');

    $burialObj = new Notifications_class();

    $requestarray = $burialObj->showrequest();


?>

<div class="container">

    <section id="notifications">
        <h1>Notifications</h1>

            <?php
                $i = 1;
                foreach ($requestarray as $reqarr){
                    $accountname = $burialObj->account($reqarr['reservation_id']);
                    $account_lot = $burialObj->account_lot($reqarr['reservation_id']);

                    $bgColor = $reqarr['request'] === 'Pending' ? '#b2dfdb' : 
                               ($reqarr['request'] === 'Confirmed' ? '#c8e6c9' : '#ffcdd2');
                    
                    $statusMessage = $reqarr['request'] === 'Pending' ? 'New reservation request' : 
                                     ($reqarr['request'] === 'Confirmed' ? 'Reservation confirmed' : 'Reservation cancelled');
            ?>

            <a href="notifications/reservation_request.php?reservation_id=<?= $reqarr['reservation_id'] ?>" >
                <div id="notifs" style="background-color: <?= $bgColor ?>">
                    <h5><?= $statusMessage ?></h5>
                    <div>
                        <strong><?= $accountname ?></strong> has requested a reservation for <strong><?= $account_lot ?></strong>
                    </div>
                </div>
            </a>


            <?php
                }
            ?>


    </section>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
