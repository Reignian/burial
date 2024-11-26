<?php
    include(__DIR__ . '/includes/header.php');
    require_once __DIR__ . '/website/lots.class.php';

    $burialObj = new Reservation();

    if (!isset($_SESSION['account']) || !$_SESSION['account']['is_customer']) {
        header('location: login.php');
        exit;
    }

    if (!isset($_SESSION['account']['account_id'])) {
        echo "No account ID found in session";
        exit;
    } else {
        $account_id = $_SESSION['account']['account_id'];
    }

    $account_id = $_SESSION['account']['account_id'];

    if ($account_id) {
        $account = $burialObj->fetchAccountRecord($account_id);
        $reservations = $burialObj->getReservationsByAccountId($account_id);
    }
?>

<div class="container">
    <section id="account-profile">
        <h1>Account Profile</h1>
        
        <?php if ($account): ?>
            <h2>Account Details</h2>
            <p>Name: <?= $account['last_name'] ?>, <?= $account['first_name'] ?> <?= $account['middle_name'] ?></p>
            <p>Email: <?= $account['email'] ?></p>
            <p>Phone Number: <?= $account['phone_number'] ?></p>

            <h3>Reservations:</h3>
            <?php if ($reservations): ?>
                <ul>
                <?php foreach ($reservations as $reservation): ?>
                    <li>
                        Lot: <?= $reservation['lot_name'] ?><br>
                        Reservation Date: <?= $reservation['reservation_date'] ?><br>
                        Payment Plan: <?= $reservation['plan'] ?><br>
                        Balance: <?= $reservation['balance'] ?><br>
                        Status: <?= $reservation['request'] ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>You have no reservations at the moment.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Unable to fetch account details. Please try again later.</p>
        <?php endif; ?>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
