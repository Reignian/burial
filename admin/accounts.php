<?php
    require_once (__DIR__ . '/accounts/accounts.class.php');
    $burialObj = new Accounts_class();
    $cusarray = $burialObj->showALL_account();
    include(__DIR__ . '/nav/navigation.php');
?>

<!-- Add DataTables CSS after Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <section id="accounts">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">ACCOUNTS</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="accountsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i = 1;
                            foreach ($cusarray as $cusarr){
                        ?>
                        <tr>
                            <td class="text-center"><?= $i ?></td>
                            <td><?= $cusarr['last_name'] ?>, <?= $cusarr['first_name'] ?></td>
                            <td><?= $cusarr['email'] ?></td>
                            <td class="text-center"><?= $cusarr['phone_number'] ?></td>
                            <td class="text-center">
                                <span class="status-badge <?= strtolower($cusarr['status']) ?>">
                                    <?= $cusarr['status'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" 
                                            onclick="askReason('ban', <?= $cusarr['account_id'] ?>, '<?= $cusarr['status'] == 'Banned' ? 'unban' : 'ban' ?>')"
                                            class="btn btn-sm <?= $cusarr['status'] == 'Banned' ? 'btn-success' : 'btn-danger' ?> d-flex align-items-center">
                                        <i class="fas <?= $cusarr['status'] == 'Banned' ? 'fa-user-check' : 'fa-user-slash' ?> me-1"></i>
                                        <?= $cusarr['status'] == 'Banned' ? 'Unban' : 'Ban' ?>
                                    </button>
                                    <?php if($_SESSION['account']['is_admin']): ?>
                                    <button type="button"
                                            onclick="askReason('delete', <?= $cusarr['account_id'] ?>)"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center">
                                        <i class="fas fa-trash-alt me-1"></i>
                                        Delete
                                    </button>
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

<!-- Reason Modal -->
<div class="modal fade" id="reasonModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reasonModalLabel">Enter Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reasonForm" method="POST">
                    <input type="hidden" id="actionType" name="action_type">
                    <input type="hidden" id="accountId" name="account_id">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Please provide a reason:</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                    <div id="deleteWarning" class="alert alert-warning d-none">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Warning: This action cannot be undone. Are you sure you want to proceed?
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReason()">Proceed</button>
            </div>
        </div>
    </div>
</div>

<!-- Add DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#accountsTable').DataTable({
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

let currentModal;

function askReason(action, accountId, actionSubtype = '') {
    const modal = new bootstrap.Modal(document.getElementById('reasonModal'), {
        backdrop: 'static',
        keyboard: false
    });
    const form = document.getElementById('reasonForm');
    const actionTypeInput = document.getElementById('actionType');
    const accountIdInput = document.getElementById('accountId');
    const modalTitle = document.getElementById('reasonModalLabel');
    const deleteWarning = document.getElementById('deleteWarning');
    const submitButton = document.querySelector('#reasonModal .btn-primary');

    // Set the form action based on the action type
    if (action === 'ban') {
        form.action = 'accounts/toggle_ban.php';
        modalTitle.textContent = `Enter Reason to ${actionSubtype.charAt(0).toUpperCase() + actionSubtype.slice(1)} Account`;
        deleteWarning.classList.add('d-none');
        submitButton.textContent = 'Proceed';
    } else {
        form.action = 'accounts/delete_account.php';
        modalTitle.textContent = 'Enter Reason to Delete Account';
        deleteWarning.classList.remove('d-none');
        submitButton.textContent = 'Delete Account';
        submitButton.classList.remove('btn-primary');
        submitButton.classList.add('btn-danger');
    }

    actionTypeInput.value = action;
    accountIdInput.value = accountId;
    currentModal = modal;
    modal.show();
}

function submitReason() {
    const form = document.getElementById('reasonForm');
    const reason = document.getElementById('reason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason.');
        return;
    }

    // Submit the form
    form.submit();
}

// Reset modal state when it's closed
document.getElementById('reasonModal').addEventListener('hidden.bs.modal', function () {
    const submitButton = document.querySelector('#reasonModal .btn-primary');
    submitButton.classList.remove('btn-danger');
    submitButton.classList.add('btn-primary');
    submitButton.textContent = 'Proceed';
    document.getElementById('reason').value = '';
});
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
}

/* Zebra Striping */
.table tbody tr:nth-of-type(odd) {
    background-color: #f5fcfd !important;
}

.table tbody tr:hover {
    background-color: #e0f7fa !important;
}

/* Status Badge Styling */
.status-badge {
    padding: 0.35rem 0.75rem !important;
    border-radius: 6px !important;
    font-size: 0.85rem !important;
    font-weight: 500 !important;
    display: inline-block !important;
    text-align: center !important;
    min-width: 100px !important;
}

.status-badge.active {
    background-color: #e0f2f1 !important;
    color: #00695c !important;
}

.status-badge.banned {
    background-color: #ffebee !important;
    color: #c62828 !important;
}

/* DataTables Styling */
.dataTables_filter {
    text-align: left !important;
    margin-bottom: 0.5rem !important;
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
    margin-bottom: 0.5rem !important;
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
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.3rem !important;
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
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
