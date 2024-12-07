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

    // Get specific reservation if ID is provided
    $selected_reservation = null;
    $payments = [];
    if (isset($_GET['reservation_id'])) {
        $reservation_id = $_GET['reservation_id'];
        foreach ($reservations as $reservation) {
            if ($reservation['reservation_id'] == $reservation_id) {
                $selected_reservation = $reservation;
                // Fetch payments only for the selected reservation
                $payments = $burialObj->getPayments($reservation_id);
                $balance = $burialObj->Balance($reservation_id);
                $next_payment = $burialObj->getNextPaymentSchedule($reservation_id);
                break;
            }
        }
    }

    if ($selected_reservation) {
        // If a specific reservation is selected, only show that one
        $reservations = [$selected_reservation];
    }

?>
<style>
        .custom-bg {
            background-color: #455a64;
            color:#e0f2f1;
        }
        .lot-info {
            background-color: white;
            font-family: sans-serif;
        }
        .payment-record {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: white;
        }
        .payment-record {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .receipt-icon {
            color: #455a64;
        }
        .payment-history {
            flex: 1;
            min-width: 300px;
            margin-left: 20px;
        }
        #payment-history-container {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }
        .lot-card {
            width: 425px;
            min-width: 425px;
        }
        @media (max-width: 992px) {
            .lot-card {
                width: 100%;
                min-width: 100%;
            }
            .payment-history {
                width: 100%;
                margin-left: 0;
                margin-top: 20px;
            }
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
            <div class="container mt-4" id="payment-history-container">
                <!-- Lot Card -->
                <div class="card border-0 lot-card">
                    <!-- Image Section -->
                    <img src="admin/lots/<?= $reservation['lot_image'] ?>" class="card-img-top" alt="Lot Image" style="height: 200px; object-fit: cover;">
                    
                    <!-- Lot Information -->
                    <div class="lot-info p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="mb-1"><?= $reservation['lot_name'] ?></h2>
                                <h6 class="mb-1 text-muted"><?= $reservation['location'] ?></h6>
                                <h6 class="mb-1 text-muted"><?= $reservation['size'] ?> m²</h6>
                            </div>
                        </div>
                    </div>
                    <div class="lot-info p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1">Payment Plan: </p>
                                <p class="mb-1">Monthly Payment:</p>
                                <p class="mb-1">Payment Schedule:</p>
                                <p class="mb-1">Remaining Balance:</p>
                            </div>
                            <div class="text-end">
                                <p class="mb-1"><?= $reservation['plan'] ?></p>
                                <p class="mb-1"><?= '₱ ' . number_format($reservation['monthly_payment'], 2) ?></p>
                                <p class="mb-1"><?= $next_payment ?></p>
                                <p class="mb-1 fw-bold"><?= '₱ ' . number_format($balance, 2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Records -->
                <div class="payment-history">
                    <h4 class="mb-3">Payment Records</h4>
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info">No payment records found.</div>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <div class="payment-record p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span>Amount Paid:</span>
                                        <h4 class="fw-bold">₱ <?= number_format($payment['amount_paid'], 2) ?></h4>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= date('M d, Y', strtotime($payment['payment_date'])) ?></span>
                                        <i class="bi bi-receipt receipt-icon"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>



        </div>
    </div>

    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
