<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/payments/payments.class.php');

    $burialObj = new Payments_class();
    $paymentPlans = $burialObj->showPaymentPlans();
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">PAYMENT PLANS</h1>
        <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="paymentPlansTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Duration (Months)</th>
                        <th>Down Payment</th>
                        <th>Interest Rate</th>
                        <?php if($_SESSION['account']['is_admin']): ?>
                        <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentPlans as $plan): ?>
                    <tr>
                        <td><?= $plan['plan'] ?></td>
                        <td><?= $plan['duration'] ?></td>
                        <td><?= $plan['down_payment'] ?>%</td>
                        <td><?= $plan['interest_rate'] ?>%</td>
                        <?php if($_SESSION['account']['is_admin']): ?>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="payments/edit_payment_plan.php?id=<?= $plan['payment_plan_id'] ?>" 
                                   class="btn btn-sm btn-warning d-flex align-items-center">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-danger deletePlanBtn d-flex align-items-center" 
                                        data-plan_id="<?= $plan['payment_plan_id'] ?>">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
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
    $('#paymentPlansTable').DataTable({
        dom: '<"row mb-3"<"col-md-6"f><"col-md-4 text-end"l><"col-md-2 text-end add-plan-button">>rtip',
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

    <?php if($_SESSION['account']['is_admin']): ?>
    $('.add-plan-button').html(`
        <button type="button" class="btn btn-primary" onclick="window.location.href='payments/add_payment_plan.php'">
            <i class="fas fa-plus-circle me-1"></i> Add Plan
        </button>
    `);
    <?php endif; ?>

    let deletePlanButtons = document.querySelectorAll('.deletePlanBtn');
    deletePlanButtons.forEach(button => {
        button.addEventListener('click', handleDeletePlanClick);
    });
});

function handleDeletePlanClick(e) {
    e.preventDefault();
    
    let planId = this.dataset.plan_id;
    if (confirm('Are you sure you want to delete this payment plan?')) {
        fetch('payments/soft_delete_payment_plan.php?id=' + planId, { method: 'GET' })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    window.location.reload();
                } else {
                    alert('Failed to delete the payment plan.');
                }
            });
    }
}
</script>

<style>
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

/* Add Plan Button Container */
.add-plan-button {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
}

.add-plan-button .btn {
    margin-left: 0.5rem !important;
}

/* Action Column */
.d-flex.gap-1.justify-content-center {
    display: flex !important;
    justify-content: center !important;
    gap: 0.3rem !important;
}

/* Percentage Columns */
.table td:nth-child(3),
.table td:nth-child(4) {
    text-align: center !important;
}

/* Duration Column */
.table td:nth-child(2) {
    text-align: center !important;
}
</style>