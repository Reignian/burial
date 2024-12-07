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

<style>
        .custom-bg {
            background-color: #455a64;
            color:#e0f2f1;
        }
        .lot-info {
            background-color: #7FFFD4;
        }
        .payment-record {
            border: 1px solid #7FFFD4;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .receipt-icon {
            color: #7FFFD4;
        }

        #payment-history-container {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
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
            <h3 class="mb-4">Payment History</h3>

            <div class="container mt-4" id="payment-history-container">
                <!-- Lot Card -->
                <div class="card border-0" style="min-width: 425px;">
                    <!-- Image Section -->
                    <img src="/placeholder.svg" class="card-img-top" alt="Lot Image" style="height: 200px; object-fit: cover;">
                    
                    <!-- Lot Information -->
                    <div class="lot-info p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">Lot 1</h5>
                                <p class="mb-1">Block 2</p>
                            </div>
                            <div class="text-end">
                                <p class="mb-1">25 m</p>
                                <p class="mb-0">₱ 1000</p>
                            </div>
                        </div>
                    </div>
                    <div class="lot-info p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">Payment Plan: </h5>
                                <p class="mb-1">Monthly Payment</p>
                                <p class="mb-1">Payment Schedule:</p>
                                <p class="mb-1">Remaining Balance:</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Records -->
                <div class="payment-history mt-4" style="max-width: 300px;">
                    <!-- Repeated Payment Records -->
                    <div class="payment-record p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Paid: ₱ 100</span>
                            <div class="d-flex align-items-center">
                                <span class="me-2">Dec. 08, 2024</span>
                                <i class="bi bi-receipt receipt-icon"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            



        </div>
    </div>

    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
