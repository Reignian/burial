<?php

    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/payments/payments.class.php');

    $burialObj = new Payments_class();

    $payarray = $burialObj->showALL_payments();
?>

<!-- Add DataTables CSS after Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">PAYMENTS</h1>
        <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="paymentsTable" class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Lot</th>
                        <th>Amount</th>
                        <th>Remaining Balance</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($payarray as $payarr){
                            $payment_name = $burialObj->payer($payarr['payment_id']);
                            $payment_lot = $burialObj->payerlot($payarr['payment_id']);
                            $remaining_balance = $burialObj->balhistory($payarr['payment_id']);
                    ?>
                    <tr>
                        <td><?= $payment_name ?></td>
                        <td><?= $payment_lot ?></td>
                        <td>₱<?= number_format($payarr['amount_paid'], 2) ?></td>
                        <td>₱<?= number_format($remaining_balance, 2) ?></td>
                        <td><?= date('M d, Y', strtotime($payarr['payment_date'])) ?></td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-danger deleteBtn d-flex align-items-center" 
                                        data-payment_id="<?= $payarr['payment_id'] ?>">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#paymentsTable').DataTable({
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

    let deleteButtons = document.querySelectorAll('.deleteBtn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', handleDeleteClick);
    });
});

function handleDeleteClick(e) {
    e.preventDefault();
    
    let paymentId = this.dataset.payment_id;
    if (confirm('Are you sure you want to delete this payment record?')) {
        fetch('payments/delete_payment.php?id=' + paymentId, { method: 'GET' })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    window.location.reload();
                } else {
                    alert('Failed to delete the payment record.');
                }
            });
    }
}
</script>

<style>
/* Import all styles from reservations.php and lots.php */

/* Table Styling */
.table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    width: 100% !important;
    margin-bottom: 0 !important;
    border: none !important;
}

/* Header Styling */
.table thead th {
    background-color: #00838f !important;
    color: white !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.5px !important;
    padding: 1rem !important;
    text-align: center !important;
    border: none !important;
    border-bottom: 2px solid #4dd0e1 !important;
}

/* Body Styling */
.table tbody td {
    padding: 1rem !important;
    vertical-align: middle !important;
    border: none !important;
    border-bottom: 1px solid #e9ecef !important;
    color: #495057 !important;
    font-size: 0.95rem !important;
    text-align: center !important;
}

/* Zebra Striping */
.table tbody tr:nth-of-type(odd) {
    background-color: #f5fcfd !important;
}

.table tbody tr:hover {
    background-color: #e0f7fa !important;
}

/* Card Styling */
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

/* DataTables Styling */
.dataTables_filter {
    text-align: left !important;
    margin-bottom: 0 !important;
}

.dataTables_filter input {
    border: 1px solid #b2ebf2 !important;
    border-radius: 6px !important;
    padding: 0.5rem 1rem !important;
    width: 400px !important;
    font-size: 0.875rem !important;
}

.dataTables_length {
    text-align: right !important;
    margin-bottom: 0 !important;
    padding-right: 1rem !important;
}

.dataTables_length select {
    border: 1px solid #b2ebf2 !important;
    border-radius: 6px !important;
    padding: 0.5rem 2rem 0.5rem 1rem !important;
    font-size: 0.875rem !important;
}

/* Button Styling */
.btn-sm {
    padding: 0.3rem 0.6rem !important;
    font-size: 0.75rem !important;
    border-radius: 4px !important;
    font-weight: 500 !important;
    letter-spacing: 0.3px !important;
}

.btn-primary {
    background-color: #00838f !important;
    border-color: #00838f !important;
}

.btn-primary:hover {
    background-color: #0097a7 !important;
    border-color: #0097a7 !important;
}

/* Title Styling */
.display-5 {
    font-size: 2.5rem !important;
    margin-bottom: 0.5rem !important;
    letter-spacing: 1px !important;
    color: #00838f !important;
}

.border-bottom {
    margin-bottom: 2rem !important;
    border-color: #4dd0e1 !important;
}

/* Pagination Styling */
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

/* Action Column */
.d-flex.gap-1.justify-content-center {
    display: flex !important;
    justify-content: center !important;
    gap: 0.3rem !important;
}

/* Add Plan Button Container */
.add-plan-button {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
}

.add-plan-button .btn {
    margin-left: 0.5rem !important;
}

/* Add these styles to center align table cells */
.table tbody td {
    text-align: center !important;
}

.table td:nth-child(1) {
    text-align: left !important;
}

/* Keep right alignment only for amount and balance columns */
.table td:nth-child(3),
.table td:nth-child(4) {
    text-align: center !important;
}

/* Keep date column centered */
.table td:nth-child(5) {
    text-align: center !important;
}
</style>

</body>
</html>
