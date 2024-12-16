<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once __DIR__ . '/reservations/reservations.class.php';
    
    $burialObj = new Reservation_class();
    $payment_plans = $burialObj->getPaymentPlans();

    $resarray = $burialObj->showALL_reservation();

    // Handle reservation cancellation with reason
    if (isset($_POST['cancel_reservation']) && isset($_POST['reservation_id']) && isset($_POST['cancellation_reason'])) {
        $reservationID = $_POST['reservation_id'];
        $cancellationReason = trim($_POST['cancellation_reason']);
        
        if (empty($cancellationReason)) {
            echo "<script>alert('Please provide a reason for cancellation.');</script>";
        } else {
            $reservation = new Reservation_class();
            if ($reservation->cancelReservation($reservationID, $cancellationReason)) {
                echo "<script>
                    alert('Reservation has been cancelled successfully.');
                    window.location.href = '" . $_SERVER['PHP_SELF'] . "';
                </script>";
            } else {
                echo "<script>
                    alert('Failed to cancel the reservation. Please try again.');
                    window.location.href = '" . $_SERVER['PHP_SELF'] . "';
                </script>";
            }
        }
        exit();
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
                                                <a href="reservations/transfer_reservation.php?reservation_id=<?= $resarr['reservation_id'] ?>&name=<?= urlencode($accountname) ?>&lot=<?= urlencode($account_lot) ?>" 
                                                   class="btn btn-sm btn-info d-flex align-items-center text-white">
                                                    <i class="fas fa-exchange-alt me-1"></i> Transfer
                                                </a>
                                            
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info d-flex align-items-center text-white"
                                                        onclick="showReservationDetails(<?= $resarr['reservation_id'] ?>)">
                                                    <i class="fas fa-info-circle me-1"></i> Details
                                                </button>
                                            <?php endif; ?>   
                                            
                                            <?php if (!$hasPayments): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning d-flex align-items-center"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?= $resarr['reservation_id'] ?>">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="editModal<?= $resarr['reservation_id'] ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Reservation</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="reservations/process_edit.php" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="reservation_id" value="<?= $resarr['reservation_id'] ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="reservation_date<?= $resarr['reservation_id'] ?>" class="form-label">Reservation Date</label>
                                                                        <input type="date" 
                                                                               class="form-control" 
                                                                               id="reservation_date<?= $resarr['reservation_id'] ?>" 
                                                                               name="reservation_date"
                                                                               value="<?= date('Y-m-d', strtotime($resarr['reservation_date'])) ?>"
                                                                               required>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="payment_plan<?= $resarr['reservation_id'] ?>" class="form-label">Payment Plan</label>
                                                                        <select class="form-select" 
                                                                                id="payment_plan<?= $resarr['reservation_id'] ?>" 
                                                                                name="payment_plan_id" 
                                                                                required>
                                                                            <?php foreach ($payment_plans as $plan): ?>
                                                                                <option value="<?= $plan['payment_plan_id'] ?>" 
                                                                                        <?= $resarr['payment_plan_id'] == $plan['payment_plan_id'] ? 'selected' : '' ?>>
                                                                                    <?= $plan['plan'] ?> 
                                                                                    <?php if ($plan['duration'] > 0): ?>
                                                                                        (<?= $plan['down_payment'] ?>% DP, 
                                                                                        <?= $plan['interest_rate'] ?>% interest)
                                                                                    <?php endif; ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($resarr['request'] !== 'Cancelled'): ?>
                                                <form method="post" style="display: inline;" 
                                                      onsubmit="return showCancellationModal(<?= $resarr['reservation_id'] ?>, '<?= addslashes($accountname) ?>', '<?= date('M d, Y', strtotime($resarr['reservation_date'])) ?>', <?= $balance ?>);">
                                                    <input type="hidden" name="reservation_id" value="<?= $resarr['reservation_id'] ?>">
                                                    <?php if($_SESSION['account']['is_admin']): ?>
                                                    <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center">
                                                        <i class="fas fa-times-circle me-1"></i> Cancel
                                                    </button>
                                                    <?php endif; ?>
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

function showCancellationModal(reservationId, accountName, reservationDate, balance) {
    const modal = new bootstrap.Modal(document.getElementById('cancellationModal'));
    document.getElementById('modalReservationId').value = reservationId;
    document.getElementById('cancellationConfirmText').innerHTML = `Are you sure you want to cancel the reservation for ${accountName} on ${reservationDate}?` + 
        (balance > 0 ? `<br><br><strong>Note:</strong> This reservation has a remaining balance of ₱${balance.toLocaleString()}.` : '');
    modal.show();
    return false;
}

function showReservationDetails(reservationId) {
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('reservationDetailsModal'));
    modal.show();
    
    // Use relative path from current location
    fetch(`./reservations/get_reservation_details.php?reservation_id=${reservationId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                // Customer Information
                document.getElementById('customerName').textContent = 
                    `${data.first_name} ${data.middle_name ? data.middle_name + ' ' : ''}${data.last_name}`.trim();
                document.getElementById('customerEmail').textContent = data.email || 'N/A';
                document.getElementById('customerPhone').textContent = data.phone_number || 'N/A';

                // Lot Information
                document.getElementById('lotName').textContent = data.lot_name || 'N/A';
                document.getElementById('lotLocation').textContent = data.location || 'N/A';
                document.getElementById('lotSize').textContent = data.size ? data.size + ' sq. m.' : 'N/A';
                document.getElementById('lotPrice').textContent = 
                    '₱' + parseFloat(data.price).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                // Payment Information
                document.getElementById('paymentPlan').textContent = 
                    `${data.plan || 'N/A'} (${data.months || 0} months)`;
                document.getElementById('monthlyPayment').textContent = 
                    '₱' + parseFloat(data.monthly_payment).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('balance').textContent = 
                    '₱' + parseFloat(data.balance).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('paymentStatus').textContent = data.payment_status || 'N/A';

                // Reservation Information
                document.getElementById('reservationDate').textContent = 
                    new Date(data.reservation_date).toLocaleDateString('en-PH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                // Payment History
                const paymentHistoryBody = document.getElementById('paymentHistoryBody');
                paymentHistoryBody.innerHTML = ''; // Clear existing rows
                
                if (data.payments && data.payments.length > 0) {
                    data.payments.forEach(payment => {
                        const row = document.createElement('tr');
                        const paymentDate = new Date(payment.payment_date).toLocaleDateString('en-PH', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        row.innerHTML = `
                            <td>${paymentDate}</td>
                            <td>₱${parseFloat(payment.amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        `;
                        paymentHistoryBody.appendChild(row);
                    });
                } else {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="2" class="text-center">No payment history available</td>';
                    paymentHistoryBody.appendChild(row);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading the reservation details');
            modal.hide();
        });
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

<!-- Add this modal at the end of the file, before the closing body tag -->
<div class="modal fade" id="cancellationModal" tabindex="-1" aria-labelledby="cancellationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancellationModalLabel">Cancel Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancellationForm" method="post">
                <div class="modal-body">
                    <p id="cancellationConfirmText"></p>
                    <div class="mb-3">
                        <label for="cancellationReason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancellationReason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="reservation_id" id="modalReservationId">
                    <input type="hidden" name="cancel_reservation" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reservation Details Modal -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-labelledby="reservationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationDetailsModalLabel">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Customer Information</h5>
                        <p><strong>Name:</strong> <span id="customerName"></span></p>
                        <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="customerPhone"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Lot Information</h5>
                        <p><strong>Lot Name:</strong> <span id="lotName"></span></p>
                        <p><strong>Location:</strong> <span id="lotLocation"></span></p>
                        <p><strong>Size:</strong> <span id="lotSize"></span></p>
                        <p><strong>Price:</strong> <span id="lotPrice"></span></p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Payment Information</h5>
                        <p><strong>Monthly Payment:</strong> <span id="monthlyPayment"></span></p>
                        <p><strong>Balance:</strong> <span id="balance"></span></p>
                        <p><strong>Status:</strong> <span id="paymentStatus"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Reservation Information</h5>
                        <p><strong>Reservation Date:</strong> <span id="reservationDate"></span></p>
                        <p><strong>Payment Plan:</strong> <span id="paymentPlan"></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-muted mb-3">Payment History</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentHistoryBody" style="text-align: center;">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>