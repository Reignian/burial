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
        $reservations = $burialObj->getReservationsByAccountId($account_id);
    }
?>

<style>
    .custom-bg {
        background-color: #455a64;
        color:#e0f2f1;
    }
    .lot-card {
        background-color: white;
        border-radius: 15px;
        font-family: sans-serif;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .lot-card.due-today {
        background-color: #fff3cd;  /* Light yellow */
    }
    .lot-card.overdue {
        background-color: #f8d7da;  /* Light red */
    }
    .lot-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .lot-link {
        text-decoration: none;
        color: inherit;
    }
</style>

<div class="container-fluid p-0">
    
    <div>
        <!-- Header -->
        <div class="custom-bg p-3 d-flex justify-content-between align-items-center">
        <h1><?= $_SESSION['account']['first_name'], ' ', $_SESSION['account']['middle_name'], ' ', $_SESSION['account']['last_name']?></h1>
            <div>
                <i class="bi bi-bell me-2"></i>
                <i class="bi bi-gear"></i>
            </div>
        </div>
        <!-- Main Content -->
        <div class="container mt-4">
            <h3 class="mb-4">Reserved Lots</h3>

            <!-- Lot Card -->
            <?php foreach ($reservations as $reservation): 
                $balance = $burialObj->Balance($reservation['reservation_id']);
                if ($balance <= 0) {
                    $status = "<span style='font-weight: bold'>Paid</span>";
                } else {
                    $status = $burialObj->Duedate($reservation['reservation_id']);
                }
                $payment_status = $burialObj->getPaymentStatus($reservation['reservation_id']);
                $card_class = '';
                if ($payment_status === 'due_today') {
                    $card_class = ' due-today';
                } elseif ($payment_status === 'overdue') {
                    $card_class = ' overdue';
                }
            ?>
                <a href="transactions.php?reservation_id=<?= $reservation['reservation_id'] ?>" class="lot-link">
                    <div class="lot-card p-0 mb-4<?= $card_class ?>">
                        <div class="row align-items-center">
                            <div class="col-3 pl-3">
                                <img src="admin/lots/<?= $reservation['lot_image'] ?>" alt="Lot Image" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-8 m-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?= $reservation['lot_name'] ?></h5>
                                        <p class="mb-1">Location: <?= $reservation['location'] ?></p>
                                        <p class="mb-1">Size: <?= $reservation['size'] ?> m²</p>
                                        <p class="mb-1">Price: ₱ <?= $reservation['price'] ?></p>
                                    </div>
                                    <div>
                                        <p class="mb-1">Payment Plan: <?= $reservation['plan'] ?></p>
                                        <p class="mb-1">Monthly Payment:  <?= '₱ ' . number_format($reservation['monthly_payment'], 2) ?></p>
                                        <p class="mb-1">Due: <?= $status ?></p>
                                        <p class="mb-1">Date: <?= date('F d, Y', strtotime($reservation['reservation_date'])) ?></p>
                                    </div>
                                    
                                    <div>
                                        <p class="mb-1">Remaining Balance:</p>
                                        <h5 class="fw-bold"><?= '₱ ' . number_format($balance, 2) ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

            <?php endforeach; ?>



        </div>
    </div>

    <!-- <section id="account-profile">
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
    </section> -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
