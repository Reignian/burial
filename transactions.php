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
            cursor: pointer;
            transition: color 0.2s;
        }
        .receipt-icon:hover {
            color: #263238;
        }
        .modal-header {
            background-color: #455a64;
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        .modal-header .btn-close {
            color: white;
            filter: brightness(0) invert(1);
        }
        .modal-body {
            padding: 2rem;
            font-family: sans-serif;
        }
        .payment-details {
            margin-bottom: 0;
        }
        .payment-details dt {
            color: #455a64;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .payment-details dd {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        .modal-divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 1.5rem 0;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .receipt-header h6 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .modal-footer {
            border-top: none;
            padding: 1.5rem;
        }
        .btn-print {
            background-color: #455a64;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        .btn-print:hover {
            background-color: #263238;
            color: white;
        }
        .lot-card {
            width: 425px;
            min-width: 425px;
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
                                        <span class="me-2"><?= date('F d, Y', strtotime($payment['payment_date'])) ?></span>
                                        <i class="bi bi-receipt receipt-icon" data-bs-toggle="modal" data-bs-target="#paymentModal<?= $payment['payment_id'] ?>"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details Modal -->
                            <div class="modal fade" id="paymentModal<?= $payment['payment_id'] ?>" tabindex="-1" aria-labelledby="paymentModalLabel<?= $payment['payment_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="paymentModalLabel<?= $payment['payment_id'] ?>">Payment Receipt</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="receipt-header">
                                                <h6>OFFICIAL RECEIPT</h6>
                                                <h2>#<?= str_pad($payment['payment_id'], 6, '0', STR_PAD_LEFT) ?></h2>
                                            </div>

                                            <dl class="row payment-details">
                                                <dt class="col-sm-4">Name</dt>
                                                <dd class="col-sm-8">
                                                    <?= $_SESSION['account']['first_name'] . ' ' . 
                                                        $_SESSION['account']['middle_name'] . ' ' . 
                                                        $_SESSION['account']['last_name'] ?>
                                                </dd>

                                                <div class="modal-divider"></div>

                                                <dt class="col-sm-4">Amount Paid</dt>
                                                <dd class="col-sm-8 fw-bold">₱ <?= number_format($payment['amount_paid'], 2) ?></dd>

                                                <dt class="col-sm-4">Payment Date</dt>
                                                <dd class="col-sm-8"><?= date('F d, Y', strtotime($payment['payment_date'])) ?></dd>

                                                <div class="modal-divider"></div>

                                                <dt class="col-sm-4">Lot Details</dt>
                                                <dd class="col-sm-8">
                                                    <p class="mb-1 fw-bold"><?= $reservation['lot_name'] ?></p>
                                                    <p class="mb-1 text-muted"><?= $reservation['location'] ?></p>
                                                    <p class="mb-0 text-muted"><?= $reservation['size'] ?> m²</p>
                                                </dd>

                                                <dt class="col-sm-4">Payment Plan</dt>
                                                <dd class="col-sm-8"><?= $reservation['plan'] ?></dd>
                                            </dl>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-print" onclick="window.print()">
                                                <i class="bi bi-printer me-2"></i>Print Receipt
                                            </button>
                                        </div>
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
