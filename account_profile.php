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
        $reservations = $burialObj->getReservationsByAccountId($account_id);
    }
?>

<style>
    .custom-bg {
        background-color: #455a64;
        color:#e0f2f1;
    }
    .lot-card {
        background-color: white;
        border-radius: 15px;
        font-family: sans-serif;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .lot-card.due-today {
        background-color: #fff3cd;  /* Light yellow */
    }
    .lot-card.overdue {
        background-color: #f8d7da;  /* Light red */
    }
    .lot-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .lot-link {
        text-decoration: none;
        color: inherit;
    }
</style>

<div class="container-fluid p-0">
    
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <script>
        <?php if (isset($_SESSION['success'])): ?>
            alert("<?= addslashes($_SESSION['success']) ?>");
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            alert("<?= addslashes($_SESSION['error']) ?>");
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
    <?php endif; ?>

    <div>
        <!-- Header -->
        <div class="custom-bg p-3 d-flex justify-content-between align-items-center">
        <h1><?= $_SESSION['account']['first_name'], ' ', $_SESSION['account']['middle_name'], ' ', $_SESSION['account']['last_name']?></h1>
            <div>
                <a href=""> 
                    <i class="bi bi-bell me-2" style="color: #e0f2f1" ></i>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editAccountModal" style="color: #e0f2f1">
                    <i class="bi bi-gear"></i>
                </a>
            </div>
        </div>
        <!-- Main Content -->
        <div class="container mt-4">
            <h3 class="mb-4">Reserved Lots</h3>

            <?php if (empty($reservations)): ?>
                <div class="alert" style="border: 1px solid #006064;" role="alert">
                    You currently don't have any purchased burial lots. Visit our 
                    <a href="browse_lots.php" class="alert-link">
                    lots page</a>
                     and purchased one.
                </div>
            <?php else: ?>
            <!-- Lot Card -->
            <?php foreach ($reservations as $reservation): 
                $balance = $burialObj->Balance($reservation['reservation_id']);
                if ($balance <= 0) {
                    $status = "<span style='font-weight: bold'>Paid</span>";
                } else {
                    $status = $burialObj->Duedate($reservation['reservation_id']);
                }
                $payment_status = $burialObj->getPaymentStatus($reservation['reservation_id']);
                $card_class = '';
                if ($payment_status === 'due_today') {
                    $card_class = ' due-today';
                } elseif ($payment_status === 'overdue') {
                    $card_class = ' overdue';
                }
            ?>
                <a href="transactions.php?reservation_id=<?= $reservation['reservation_id'] ?>" class="lot-link">
                    <div class="lot-card p-0 mb-4<?= $card_class ?>">
                        <div class="row align-items-center">
                            <div class="col-3 pl-3">
                                <img src="admin/lots/<?= $reservation['lot_image'] ?>" alt="Lot Image" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-8 m-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?= $reservation['lot_name'] ?></h5>
                                        <p class="mb-1">Location: <?= $reservation['location'] ?></p>
                                        <p class="mb-1">Size: <?= $reservation['size'] ?> m²</p>
                                        <p class="mb-1">Price: ₱ <?= $reservation['price'] ?></p>
                                    </div>
                                    <div>
                                        <p class="mb-1">Payment Plan: <?= $reservation['plan'] ?></p>
                                        <p class="mb-1">Monthly Payment:  <?= '₱ ' . number_format($reservation['monthly_payment'], 2) ?></p>
                                        <p class="mb-1">Due: <?= $status ?></p>
                                        <p class="mb-1">Date: <?= date('F d, Y', strtotime($reservation['reservation_date'])) ?></p>
                                    </div>
                                    
                                    <div>
                                        <p class="mb-1">Remaining Balance:</p>
                                        <h5 class="fw-bold"><?= '₱ ' . number_format($balance, 2) ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

            <?php endforeach; ?>
            <?php endif; ?>



        </div>
    </div>

</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccountModalLabel">Edit Account Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAccountForm" method="POST" action="update_account.php">
                <div class="modal-body">
                    <input type="hidden" name="account_id" value="<?= $_SESSION['account']['account_id'] ?>">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= $_SESSION['account']['first_name'] ?>">
                        <div class="text-danger" id="first_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= $_SESSION['account']['middle_name'] ?>">
                        <div class="text-danger" id="middle_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= $_SESSION['account']['last_name'] ?>">
                        <div class="text-danger" id="last_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= $_SESSION['account']['username'] ?>" >
                        <div class="text-danger" id="username_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $_SESSION['account']['email'] ?>" >
                        <div class="text-danger" id="email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= $_SESSION['account']['phone_number'] ?>" >
                        <div class="text-danger" id="phone_number_error"></div>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary" onclick="openChangePasswordModal()">Change Password</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #006064;">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm" method="POST" action="update_account.php">
                <div class="modal-body">
                    <input type="hidden" name="change_password" value="1">
                    <input type="hidden" name="account_id" value="<?= $_SESSION['account']['account_id'] ?>">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <div class="text-danger" id="current_password_error">
                            <?php if (isset($_SESSION['current_password_error'])): ?>
                                <?= $_SESSION['current_password_error'] ?>
                                <?php unset($_SESSION['current_password_error']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <div class="text-danger" id="new_password_error">
                            <?php if (isset($_SESSION['new_password_error'])): ?>
                                <?= $_SESSION['new_password_error'] ?>
                                <?php unset($_SESSION['new_password_error']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
                        <div class="text-danger" id="confirm_new_password_error">
                            <?php if (isset($_SESSION['confirm_new_password_error'])): ?>
                                <?= $_SESSION['confirm_new_password_error'] ?>
                                <?php unset($_SESSION['confirm_new_password_error']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #006064;">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openChangePasswordModal() {
    const editAccountModal = bootstrap.Modal.getInstance(document.getElementById('editAccountModal'));
    editAccountModal.hide();
    const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'), {
        backdrop: 'static',
        keyboard: false
    });
    changePasswordModal.show();
}

document.getElementById('editAccountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    
    // Clear previous error messages
    document.querySelectorAll('.text-danger').forEach(el => el.innerHTML = '');
    
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const phoneNumber = document.getElementById('phone_number').value.trim();
    
    // Validate first name
    if (!firstName) {
        document.getElementById('first_name_error').innerHTML = 'First name is required';
        isValid = false;
    }
    
    // Validate last name
    if (!lastName) {
        document.getElementById('last_name_error').innerHTML = 'Last name is required';
        isValid = false;
    }
    
    // Validate username
    if (!username) {
        document.getElementById('username_error').innerHTML = 'Username is required';
        isValid = false;
    }
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        document.getElementById('email_error').innerHTML = 'Email is required';
        isValid = false;
    } else if (!emailRegex.test(email)) {
        document.getElementById('email_error').innerHTML = 'Invalid email format';
        isValid = false;
    }
    
    // Validate phone number
    const phoneRegex = /^(09|\+639)\d{9}$/;
    if (!phoneNumber) {
        document.getElementById('phone_number_error').innerHTML = 'Phone number is required';
        isValid = false;
    } else if (!phoneRegex.test(phoneNumber)) {
        document.getElementById('phone_number_error').innerHTML = 'Invalid phone number format. Use 09XXXXXXXXX or +639XXXXXXXXX';
        isValid = false;
    }
    
    if (isValid) {
        // Submit form via AJAX
        const formData = new FormData(this);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_account.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Check if the response indicates a successful update
                if (xhr.responseText.includes('Account updated successfully')) {
                    alert('Account updated successfully');
                    location.reload(); // Reload to reflect updated information
                } else {
                    // Show error message from server
                    alert(xhr.responseText);
                }
            } else {
                alert('An error occurred while updating account');
            }
        };
        xhr.send(formData);
    }
});

document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    
    // Clear previous error messages
    document.querySelectorAll('.text-danger').forEach(el => el.innerHTML = '');
    
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmNewPassword = document.getElementById('confirm_new_password').value;
    
    // Check if current password is empty
    if (!currentPassword) {
        document.getElementById('current_password_error').innerHTML = 'Current password is required.';
        isValid = false;
    }
    
    // Check if new password is empty or too short
    if (!newPassword) {
        document.getElementById('new_password_error').innerHTML = 'New password is required.';
        isValid = false;
    } else if (newPassword.length < 8) {
        document.getElementById('new_password_error').innerHTML = 'Password must be at least 8 characters long.';
        isValid = false;
    }
    
    // Check if confirm password is empty
    if (!confirmNewPassword) {
        document.getElementById('confirm_new_password_error').innerHTML = 'Please confirm your new password.';
        isValid = false;
    }
    
    // Check if new password and confirm password match
    if (newPassword !== confirmNewPassword) {
        document.getElementById('confirm_new_password_error').innerHTML = 'Passwords do not match.';
        isValid = false;
    }
    
    if (isValid) {
        // Check if current password is correct using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_current_password.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.responseText !== 'valid') {
                document.getElementById('current_password_error').innerHTML = 'Current password is incorrect.';
            } else {
                // Submit form via AJAX to prevent modal from closing
                const formData = new FormData(document.getElementById('changePasswordForm'));
                const ajaxXhr = new XMLHttpRequest();
                ajaxXhr.open('POST', 'update_account.php', true);
                ajaxXhr.onload = function() {
                    if (ajaxXhr.status === 200) {
                        // Check if the response indicates a successful password change
                        if (ajaxXhr.responseText.includes('Password changed successfully')) {
                            // Show success message and optionally close modal
                            alert('Password changed successfully');
                            const changePasswordModal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                            changePasswordModal.hide();
                        } else {
                            // Show error message from server
                            alert(ajaxXhr.responseText);
                        }
                    } else {
                        alert('An error occurred while changing password');
                    }
                };
                ajaxXhr.send(formData);
            }
        };
        xhr.send('current_password=' + encodeURIComponent(currentPassword));
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
