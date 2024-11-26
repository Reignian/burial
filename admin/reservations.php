<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once __DIR__ . '/reservations/reservations.class.php';
    
    $burialObj = new Reservation_class();

    $resarray = $burialObj->showALL_reservation();

    // Handle reservation cancellation
    if (isset($_POST['cancel_reservation'])) {
        $reservationID = $_POST['reservation_id'];
        $result = $burialObj->cancelReservation($reservationID);
        if ($result === true) {
            echo "<script>alert('Reservation cancelled successfully.');</script>";
        } else {
            echo "<script>alert('" . addslashes($result) . "');</script>";
        }
        // Refresh the page to update the reservation list
        echo "<script>window.location.href = 'reservations.php';</script>";
    }
?>

<!-- Add DataTables CSS after Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <section id="reservations">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">RESERVATIONS</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="reservationsTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Lot</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Payment Plan</th>
                            <th>Monthly Payment</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i = 1;
                            foreach ($resarray as $resarr) {
                                $accountname = $burialObj->account($resarr['reservation_id']);
                                $account_lot = $burialObj->account_lot($resarr['reservation_id']);
                                $balance = $burialObj->Balance($resarr['reservation_id']);
                                $paymentplan = $burialObj->pp($resarr['reservation_id']);
                                
                                // Determine row class based on due status or if paid
                                $rowClass = $burialObj->getRowClass($resarr['reservation_id']);

                                // Format the balance with peso sign and thousands separator
                                $formatted_balance = '₱' . number_format($balance, 2);
                                $formatted_monthly = '₱' . number_format($resarr['monthly_payment'], 2);

                                // Determine status and payment due
                                if ($balance <= 0) {
                                    $status = "<span style='font-weight: bold'>Paid</span>";
                                } else {
                                    $status = $burialObj->Duedate($resarr['reservation_id']);
                                }

                                // Add this code block for the edit button
                                $hasPayments = $burialObj->hasPayments($resarr['reservation_id']);
                        ?>
                                <tr class="<?= $rowClass ?>">
                                    <td><?= $i ?></td>
                                    <td><?= $account_lot ?></td>
                                    <td><?= $accountname ?></td>
                                    <td><?= date('M d, Y', strtotime($resarr['reservation_date'])) ?></td>
                                    <td><?= $paymentplan ?></td>
                                    <td><?= $formatted_monthly ?></td>
                                    <td><?= $formatted_balance ?></td>
                                    <td><?= $status ?></td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?php if ($balance > 0): ?>
                                                <a href="reservations/add_payment.php?reservation_id=<?= $resarr['reservation_id'] ?>&name=<?= urlencode($accountname) ?>&lot=<?= urlencode($account_lot) ?>" 
                                                   class="btn btn-sm btn-primary d-flex align-items-center">
                                                    <i class="fas fa-plus-circle me-1"></i> Payment
                                                </a>
                                            <?php else: ?>
                                                <a href="" class="btn btn-sm btn-info d-flex align-items-center">
                                                    <i class="fas fa-info-circle me-1"></i> Details
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!$hasPayments): ?>
                                                <a href="reservations/edit_reservation.php?id=<?= $resarr['reservation_id'] ?>" 
                                                   class="btn btn-sm btn-warning d-flex align-items-center">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($resarr['request'] !== 'Cancelled'): ?>
                                                <form method="post" style="display: inline;" 
                                                      onsubmit="return confirmCancellation(<?= $resarr['reservation_id'] ?>, '<?= addslashes($accountname) ?>', '<?= date('M d, Y', strtotime($resarr['reservation_date'])) ?>', <?= $balance ?>);">
                                                    <input type="hidden" name="reservation_id" value="<?= $resarr['reservation_id'] ?>">
                                                    <button type="submit" name="cancel_reservation" class="btn btn-sm btn-danger d-flex align-items-center">
                                                        <i class="fas fa-times-circle me-1"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                                $i++;
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Add DataTables JS before the closing body tag -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#reservationsTable').DataTable({
        dom: '<"row mb-3"<"col-md-6"f><"col-md-6 text-end"l>>rtip',
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        ordering: false,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            lengthMenu: "Show _MENU_ entries"
        }
    });
});

function confirmCancellation(reservationId, accountName, reservationDate, balance) {
    var warning = "";
    var today = new Date();
    var reservationDateObj = new Date(reservationDate);
    var daysPassed = Math.floor((today - reservationDateObj) / (1000 * 60 * 60 * 24));

    if (balance > 0) {
        warning += "Warning: This reservation has existing payments.\n";
    }
    if (daysPassed > 1) {
        warning += "Warning: This reservation is older than 24 hours.\n";
    }

    var message = "Are you sure you want to cancel the reservation for " + accountName + "?\n\n";
    if (warning) {
        message += warning + "\n";
    }
    message += "This action cannot be undone.";

    return confirm(message);
}
</script>

<style>
/* Modern Table Styling */
.table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    width: 100% !important;
    margin-bottom: 0 !important;
    border: none !important;
}

/* Header Styling */
.table thead th {
    background-color: #00838f !important; /* Slightly lighter teal */
    color: white !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.5px !important;
    padding: 1rem !important;
    text-align: center !important;
    border: none !important;
    border-bottom: 2px solid #4dd0e1 !important; /* Light teal border */
}

/* Body Styling */
.table tbody td {
    padding: 1rem !important;
    vertical-align: middle !important;
    border: none !important;
    border-bottom: 1px solid #e9ecef !important;
    color: #495057 !important;
    font-size: 0.95rem !important;
}

/* Zebra Striping */
.table tbody tr:nth-of-type(odd) {
    background-color: #f5fcfd !important; /* Very light blue-teal */
}

.table tbody tr:hover {
    background-color: #e0f7fa !important; /* Light cyan */
}

/* DataTables Styling */

.dataTables_filter {
    text-align: left !important;
    margin-bottom: 0.5rem !important;
}

.dataTables_filter input {
    border: 1px solid #b2ebf2 !important; /* Very light teal */
    border-radius: 6px !important;
    padding: 0.5rem 1rem !important;
    width: 400px !important;
    font-size: 0.875rem !important;
}

.dataTables_filter input:focus {
    border-color: #00acc1 !important; /* Medium teal */
    box-shadow: 0 0 0 0.2rem rgba(0, 172, 193, 0.25) !important;
    outline: none !important;
}

.dataTables_length {
    text-align: right !important;
    margin-bottom: 0.5rem !important;
}

.dataTables_length select {
    border: 1px solid #b2ebf2 !important;
    border-radius: 6px !important;
    padding: 0.5rem 2rem 0.5rem 1rem !important;
    font-size: 0.875rem !important;
}

.dataTables_length select:focus {
    border-color: #00acc1 !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 172, 193, 0.25) !important;
    outline: none !important;
}

.dataTables_info {
    font-size: 0.75rem !important;
    color: #6c757d !important;
    padding-top: 0.5rem !important;
}

/* Updated Pagination Styling */
.dataTables_paginate {
    padding-top: 0.5rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 0.25rem !important;
}

.paginate_button {
    padding: 0.2rem 0.4rem !important;
    margin: 0 !important;
    font-size: 0.75rem !important;
    line-height: 1 !important;
}

.paginate_button.previous,
.paginate_button.next,
.paginate_button.current,
.paginate_button:hover:not(.current):not(.disabled),
.paginate_button.disabled {
    background: none !important;
    border: none !important;
    color: inherit !important;
}

/* Updated Action Buttons Styling */
.btn-sm {
    padding: 0.3rem 0.6rem !important;
    font-size: 0.75rem !important;
    border-radius: 4px !important;
    font-weight: 500 !important;
    letter-spacing: 0.3px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.3rem !important;
}

/* Primary button - Add Payment */
.btn-primary {
    background-color: #0097a7 !important; /* Medium-light teal */
    border-color: #0097a7 !important;
    color: white !important;
}

.btn-primary:hover {
    background-color: #00acc1 !important; /* Lighter teal */
    border-color: #00acc1 !important;
}

/* Warning button - Edit */
.btn-warning {
    background-color: #ffa000 !important;
    border-color: #ffa000 !important;
    color: white !important;
}

.btn-warning:hover {
    background-color: #ffb74d !important;
    border-color: #ffb74d !important;
}

/* Danger button - Cancel */
.btn-danger {
    background-color: #d32f2f !important;
    border-color: #d32f2f !important;
    color: white !important;
}

.btn-danger:hover {
    background-color: #ef5350 !important;
    border-color: #ef5350 !important;
}

/* Info button - Details */
.btn-info {
    background-color: #00acc1 !important; /* Changed to match theme */
    border-color: #00acc1 !important;
    color: white !important;
}

.btn-info:hover {
    background-color: #26c6da !important; /* Lighter version */
    border-color: #26c6da !important;
}

/* Button icons */
.btn i {
    font-size: 0.75rem !important;
}

.d-flex.gap-1 {
    gap: 0.3rem !important;
}

/* Status Styling */
td span {
    padding: 0.35rem 0.75rem !important;
    border-radius: 6px !important;
    font-size: 0.85rem !important;
    font-weight: 500 !important;
}

/* Container Styling */
.card {
    border: none !important;
    border-radius: 12px !important;
    background: white !important;
    box-shadow: none !important;
    overflow-x: hidden !important;
}

.card:hover {
    transform: none !important;
    box-shadow: none !important;
}

.card-body {
    padding: 0 !important;
    overflow-x: hidden !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dataTables_filter input {
        width: 100% !important;
    }
    
    .dataTables_length,
    .dataTables_filter {
        text-align: left !important;
        margin-bottom: 1rem !important;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.75rem !important;
    }
}

/* Currency and Number Alignment */
.table td:nth-child(6),
.table td:nth-child(7) {
    text-align: right !important;
}

/* Center specific columns */
.table td:first-child,
.table td:nth-child(4),
.table td:nth-child(5),
.table td:nth-child(8) {
    text-align: center !important;
}

/* Status Colors */
span[style*="color: green"] {
    background-color: #e0f2f1 !important; /* Very light teal */
    color: #00695c !important; /* Dark teal */
}

span[style*="color: red"] {
    color: #d32f2f !important;
}

span[style*="color: orange"] {
    background-color: #fff3e0 !important;
    color: #f57c00 !important; /* Brighter orange */
}

/* Button Shadows */
.btn {
    box-shadow: none !important;
}

.btn:hover {
    transform: none !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

/* Adjust DataTables wrapper padding */
.dataTables_wrapper {
    padding: 0.5rem 0 !important;
}

/* Adjust filter and length menu spacing */
.dataTables_filter {
    text-align: left !important;
    margin-bottom: 0.5rem !important;
}

.dataTables_length {
    text-align: right !important;
    margin-bottom: 0.5rem !important;
}

/* Remove horizontal scrollbar */
.card {
    border: none !important;
    border-radius: 12px !important;
    background: white !important;
    box-shadow: none !important;
    overflow-x: hidden !important;
}

.card-body {
    padding: 0 !important;
    overflow-x: hidden !important;
}

.table-responsive {
    overflow-x: hidden !important;
}

/* Adjust container padding */
.container-fluid {
    padding-right: 1rem !important;
    padding-left: 1rem !important;
}

/* Restore original button colors */
.btn-primary {
    background-color: #018488 !important;
    border-color: #018488 !important;
    color: white !important;
}

.btn-warning {
    background-color: #ffa000 !important;
    border-color: #ffa000 !important;
    color: white !important;
}

.btn-danger {
    background-color: #d32f2f !important;
    border-color: #d32f2f !important;
    color: white !important;
}

.btn-info {
    background-color: #0288d1 !important;
    border-color: #0288d1 !important;
    color: white !important;
}

/* Title Styling */
.display-5 {
    font-size: 2.5rem !important;
    margin-bottom: 0.5rem !important;
    letter-spacing: 1px !important;
    color: #00838f !important; /* Slightly lighter teal */
}

.border-bottom {
    margin-bottom: 2rem !important;
    border-color: #4dd0e1 !important; /* Light teal */
}

</style>
</body>
</html>