<?php
require_once('notifications.class.php');

$burialObj = new Notifications_class();
$account_id = $lot_id = $reservation_date = $payment_plan_id = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['reservation_id'])) {
        $recordID = $_GET['reservation_id'];
        $record = $burialObj->fetchReservationRecord($recordID);
        if (!empty($record)) {
            $account_id = $record['account_id'];
            $lot_id = $record['lot_id'];
            $reservation_date = $record['reservation_date'];
            $payment_plan_id = $record['payment_plan_id'];
            $request_status = $record['request'];
        } else {
            echo 'No reservation found';
            exit;
        }
    } else {
        echo 'No reservation id found';
        exit;
    }
}

$lot = $burialObj->fetchLotRecord($lot_id);
$account = $burialObj->fetchAccountRecord($account_id);
$payment_plan = $burialObj->fetchPayment_planRecord($payment_plan_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --deep-teal: #006064;
            --light-teal: #e0f2f1;
            --medium-teal: #b2dfdb;
            --blue-grey: #455a64;
            --dark-blue-grey: #263238;
        }

        body {
            background-color: var(--light-teal);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .left-panel {
            background: var(--deep-teal);
            color: white;
            padding: 2.5rem;
            height: 100%;
        }

        .right-panel {
            padding: 2.5rem;
            background: white;
        }

        .lot-image {
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .details-group {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .detail-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .account-details {
            background: var(--light-teal);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.2s;
            width: 48%;
            font-weight: 600;
        }

        .btn-confirm {
            background-color: var(--deep-teal);
            border: 2px solid var(--deep-teal);
            color: white;
        }

        .btn-cancel {
            background-color: transparent;
            border: 2px solid var(--deep-teal);
            color: var(--deep-teal);
        }

        .btn-confirm:hover, .btn-cancel:hover {
            background-color: var(--blue-grey);
            border-color: var(--blue-grey);
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            font-weight: 600;
        }

        .alert-success {
            background-color: #c8e6c9;
            color: #1b5e20;
        }

        .alert-danger {
            background-color: #ffcdd2;
            color: #b71c1c;
        }

        .form-control, .form-select {
            border: 2px solid var(--medium-teal);
            padding: 0.75rem;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .form-control:disabled, .form-select:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="row g-0">
                <div class="col-md-6">
                    <div class="left-panel">
                        <h2 class="fs-2 fw-bold mb-4">Lot Details</h2>
                        
                        <img src="../lots/<?= $lot['lot_image'] ?>" alt="Lot Image" class="lot-image">
                        
                        <div class="details-group">
                            <div class="detail-item">
                                <h3 class="fs-2 fw-bold"><?= $lot['lot_name']?></h3>
                            </div>
                            <div class="detail-item fs-5">
                                <strong>Location:</strong> <?= $lot['location']?>
                            </div>
                            <div class="detail-item fs-5">
                                <strong>Size:</strong> <?= $lot['size']?> sqm lot
                            </div>
                            <div class="detail-item fs-5">
                                <strong>Price:</strong> ₱<?= number_format($lot['price'], 2) ?>
                            </div>
                            <div class="detail-item">
                                <?= $lot['description']?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="right-panel">
                        <h2 class="fs-2 fw-bold mb-4">Reservation Details</h2>
                        <div class="account-details mb-4">
                            <h3 class="fs-5 fw-bold mb-3">Account Information</h3>
                            <div class="mb-2">
                                <strong>Name:</strong> <?= $account['first_name']?> <?= $account['middle_name']?> <?= $account['last_name']?>
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong> <?= $account['email']?>
                            </div>
                            <div>
                                <strong>Phone:</strong> <?= $account['phone_number']?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reservation Date</label>
                            <input type="date" class="form-control fw-bold" value="<?= $reservation_date ?>" disabled>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Payment Plan</label>
                            <input type="text" class="form-control fw-bold" value="<?= $payment_plan['plan'] ?>" disabled>
                        </div>

                        <div class="button-group d-flex justify-content-between">
                            <?php if ($request_status === 'Pending'): ?>
                                <a href="#" class="btn btn-confirm confirmBtn" data-id="<?= $recordID ?>">Confirm</a>
                                <a href="#" class="btn btn-cancel cancelBtn" data-id="<?= $recordID ?>">Cancel</a>
                            <?php elseif ($request_status === 'Confirmed'): ?>
                                <div class="alert alert-success w-100">This reservation has been confirmed.</div>
                            <?php elseif ($request_status === 'Cancelled'): ?>
                                <div class="alert alert-danger w-100">This reservation has been cancelled.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../admin.js"></script>
</body>
</html>
