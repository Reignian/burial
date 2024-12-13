<?php
    if (isset($_POST['toggle_ban'])) {
        session_start();
        require_once __DIR__ . '/staffs/staffs.class.php';
        $staffObj = new Staffs_class();
        $account_id = $_POST['account_id'];
        $staffObj->toggleBanStatus($account_id);
        $_SESSION['success_message'] = 'Staff ban status updated successfully!';
        header("Location: staff.php");
        exit();
    }

    include(__DIR__ . '/nav/navigation.php');
    require_once __DIR__ . '/staffs/staffs.class.php';

    $staffObj = new Staffs_class();
    $staffArray = $staffObj->showALL_staff();
?>

<!-- Add DataTables CSS after Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <section id="staff">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">STAFFS</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table id="staffTable" class="table">
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
                            foreach ($staffArray as $staff){
                        ?>
                        <tr>
                            <td class="text-center"><?= $i ?></td>
                            <td><?= $staff['last_name'] ?>, <?= $staff['first_name'] ?></td>
                            <td><?= $staff['email'] ?></td>
                            <td class="text-center"><?= $staff['phone_number'] ?></td>
                            <td class="text-center">
                                <span class="status-badge <?= strtolower($staff['status']) ?>">
                                    <?= $staff['status'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="account_id" value="<?= $staff['account_id'] ?>">
                                    <button type="submit" name="toggle_ban" 
                                            class="btn btn-sm <?= $staff['status'] == 'Banned' ? 'btn-success' : 'btn-danger' ?> d-flex align-items-center mx-auto">
                                        <i class="fas <?= $staff['status'] == 'Banned' ? 'fa-user-check' : 'fa-user-slash' ?> me-1"></i>
                                        <?= $staff['status'] == 'Banned' ? 'Unban' : 'Ban' ?>
                                    </button>
                                </form>
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

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addStaffModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Staff Member
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="staffs/staff_register.php" method="post" id="staffForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="text" name="first_name" id="first_name" required placeholder=" " class="form-control">
                                <label for="first_name">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="text" name="middle_name" id="middle_name" placeholder=" " class="form-control">
                                <label for="middle_name">Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="text" name="last_name" id="last_name" require placeholder=" " class="form-control">
                                <label for="last_name">Last Name</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" id="username" required placeholder=" " class="form-control">
                                <label for="username">Username</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" required placeholder=" " class="form-control">
                                <label for="email">Email</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" required placeholder=" " class="form-control">
                                <label for="password">Password</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" required placeholder=" " class="form-control">
                                <label for="confirm_password">Confirm Password</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone_number" id="phone_number" required placeholder=" " class="form-control">
                                <label for="phone_number">Phone Number</label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" name="register_staff">
                            <i class="fas fa-user-plus me-2"></i>Add Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#staffTable').DataTable({
        dom: '<"row mb-3"<"col-md-6"f><"col-md-4 text-end"l><"col-md-2 text-end add-staff-button">>rtip',
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

    $('.add-staff-button').html(`
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="fas fa-plus-circle me-1"></i> Add Staff
        </button>
    `);

    // Form validation
    $('#staffForm').submit(function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        const phoneNumber = $('#phone_number').val();
        const email = $('#email').val();

        if (password.length < 8) {
            alert('Password must be at least 8 characters long.');
            e.preventDefault();
            return false;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            e.preventDefault();
            return false;
        }

        if (!phoneNumber.match(/^(09|\+639)\d{9}$/)) {
            alert('Invalid phone number format. Use 09XXXXXXXXX or +639XXXXXXXXX format.');
            e.preventDefault();
            return false;
        }

        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            alert('Invalid email format.');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>

.display-5 {
    font-size: 2.5rem !important;
    margin-bottom: 0.5rem !important;
    letter-spacing: 1px !important;
    color: #00838f !important;
}

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

/* Modal Form Styling */
.modal .input-group {
    position: relative;
    margin-bottom: 1.5rem;
}

.modal .input-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #b2ebf2;
    border-radius: 6px;
    font-size: 0.9rem;
    background-color: white;
}

.modal .input-group label {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    background-color: white;
    padding: 0 5px;
    color: #455a64;
    transition: all 0.2s ease-in-out;
    pointer-events: none;
}

.modal .input-group input:focus + label,
.modal .input-group input:not(:placeholder-shown) + label {
    top: 0;
    font-size: 0.8rem;
    color: #006064;
}

.modal .input-group input:focus {
    border-color: #006064;
    box-shadow: 0 0 0 0.2rem rgba(0, 96, 100, 0.25);
    outline: none;
}

.btn-primary {
    background-color: #006064;
    border-color: #006064;
}

.btn-primary:hover {
    background-color: #00838f;
    border-color: #00838f;
}

/* Alert Styling */
.alert {
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #e0f2f1;
    border-color: #00695c;
    color: #00695c;
}

.alert-danger {
    background-color: #ffebee;
    border-color: #c62828;
    color: #c62828;
}

/* Enhanced Modal Styling */
.modal-lg {
    max-width: 800px;
}

.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background-color: #006064 !important;
    border-radius: 15px 15px 0 0;
    padding: 1.5rem;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.modal .input-group {
    position: relative;
    margin-bottom: 1.5rem;
}

.modal .input-group input {
    height: 45px;
    padding-left: 1rem;
    border: 1px solid #b2ebf2;
    border-radius: 8px;
    font-size: 0.95rem;
    background-color: white;
    transition: all 0.3s ease;
}

.modal .input-group-text {
    background-color: #e0f7fa;
    border: 1px solid #b2ebf2;
    color: #006064;
    border-radius: 8px 0 0 8px;
}

.modal .input-group input:focus {
    border-color: #006064;
    box-shadow: 0 0 0 0.2rem rgba(0, 96, 100, 0.25);
}

.modal .input-group label {
    position: absolute;
    left: 2.5rem;
    top: 50%;
    transform: translateY(-50%);
    background-color: white;
    padding: 0 0.5rem;
    color: #455a64;
    transition: all 0.2s ease-in-out;
    margin-bottom: 0;
}

.modal .input-group input:focus + label,
.modal .input-group input:not(:placeholder-shown) + label {
    top: 0;
    left: 1rem;
    font-size: 0.8rem;
    color: #006064;
    z-index: 10;
}

.alert-info {
    background-color: #e1f5fe;
    border-color: #006064;
    color: #006064;
}

.alert-info ul {
    padding-left: 1.5rem;
}

.btn {
    padding: 0.6rem 1.5rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #006064;
    border-color: #006064;
}

.btn-primary:hover {
    background-color: #00838f;
    border-color: #00838f;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #eceff1;
    border-color: #eceff1;
    color: #455a64;
}

.btn-secondary:hover {
    background-color: #cfd8dc;
    border-color: #cfd8dc;
    color: #263238;
}

/* Fix for input groups with icons */
.input-group > .form-control:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.input-group > .input-group-text + input + label {
    left: 3.5rem;
}

.input-group > .input-group-text + input:focus + label,
.input-group > .input-group-text + input:not(:placeholder-shown) + label {
    left: 2rem;
}
</style>
