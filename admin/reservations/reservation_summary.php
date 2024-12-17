<?php
session_start();
require_once __DIR__ . '/../../database.php';

// Check if user is logged in
if(!isset($_SESSION['account']) || !($_SESSION['account']['is_admin'] || $_SESSION['account']['is_staff'])){
    header('location: ../../sign/login.php');
    exit();
}

// Check if there are reservation details to show
if(!isset($_SESSION['reservation_details'])){
    header('location: ../reservations.php');
    exit();
}

$details = $_SESSION['reservation_details'];
unset($_SESSION['reservation_details']); // Clear after showing
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Summary</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .card {
                border: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2>Sto. Niño Parish Cemetery</h2>
                    <h4>Reservation Summary</h4>
                    <p class="text-muted">Date: <?php echo date('F d, Y'); ?></p>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Account Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Name:</th>
                                <td><?php echo $details['account']['first_name'] . ' ' . $details['account']['middle_name'] . ' ' . $details['account']['last_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo $details['account']['email']; ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo $details['account']['phone_number']; ?></td>
                            </tr>
                            <?php if ($details['account']['username'] && $details['account']['password']): ?>
                            <tr class="table-warning">
                                <th>Username:</th>
                                <td><?php echo $details['account']['username']; ?></td>
                            </tr>
                            <tr class="table-warning">
                                <th>Password:</th>
                                <td><?php echo $details['account']['password']; ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Payment Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Lot Price:</th>
                                <td>₱<?php echo number_format($details['lot']['price'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Down Payment:</th>
                                <td>₱<?php echo number_format($details['payment']['down_payment'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Monthly Payment:</th>
                                <td>₱<?php echo number_format($details['payment']['monthly_payment'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Interest Rate:</th>
                                <td><?php echo $details['payment']['interest_rate']; ?>%</td>
                            </tr>
                            <tr>
                                <th>Payment Duration:</th>
                                <td><?php echo $details['payment']['payment_duration']; ?> months</td>
                            </tr>
                            <tr>
                                <th>Total Balance:</th>
                                <td>₱<?php echo number_format($details['payment']['total_balance'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p><small>Please keep this information secure.</small></p>
                </div>

                <div class="text-center mt-4 no-print">
                    <button class="btn btn-danger me-2" onclick="exportToPDF()">
                        <i class="fas fa-download"></i> Save as PDF
                    </button>
                    <button class="btn btn-primary me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Summary
                    </button>
                    <a href="../reservations.php" class="btn btn-secondary">
                        Back to Reservations
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>

    <script>
    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Add content
        doc.setFontSize(18);
        doc.text('Sto. Niño Parish Cemetery', doc.internal.pageSize.width/2, 20, { align: 'center' });
        doc.setFontSize(16);
        doc.text('Reservation Summary', doc.internal.pageSize.width/2, 30, { align: 'center' });
        doc.setFontSize(12);
        doc.text('Date: <?php echo date('F d, Y'); ?>', doc.internal.pageSize.width/2, 40, { align: 'center' });

        // Account Information
        doc.setFontSize(14);
        doc.text('Account Information', 20, 60);
        doc.setFontSize(12);
        doc.text('Name: <?php echo $details['account']['first_name'] . ' ' . $details['account']['middle_name'] . ' ' . $details['account']['last_name']; ?>', 20, 70);
        doc.text('Email: <?php echo $details['account']['email']; ?>', 20, 80);
        doc.text('Phone: <?php echo $details['account']['phone_number']; ?>', 20, 90);
        
        <?php if (!empty($details['account']['username'])): ?>
        doc.text('Username: <?php echo $details['account']['username']; ?>', 20, 100);
        doc.text('Password: <?php echo $details['account']['password']; ?>', 20, 110);
        let yPos = 130;
        <?php else: ?>
        let yPos = 110;
        <?php endif; ?>

        // Payment Details
        doc.setFontSize(14);
        doc.text('Payment Details', 20, yPos);
        doc.setFontSize(12);
        yPos += 10;
        doc.text('Lot Price: ₱<?php echo number_format($details['lot']['price'], 2); ?>', 20, yPos);
        yPos += 10;
        doc.text('Down Payment: ₱<?php echo number_format($details['payment']['down_payment'], 2); ?>', 20, yPos);
        yPos += 10;
        doc.text('Monthly Payment: ₱<?php echo number_format($details['payment']['monthly_payment'], 2); ?>', 20, yPos);
        yPos += 10;
        doc.text('Interest Rate: <?php echo $details['payment']['interest_rate']; ?>%', 20, yPos);
        yPos += 10;
        doc.text('Payment Duration: <?php echo $details['payment']['payment_duration']; ?> months', 20, yPos);
        yPos += 10;
        doc.text('Total Balance: ₱<?php echo number_format($details['payment']['total_balance'], 2); ?>', 20, yPos);

        // Save the PDF
        doc.save('reservation_summary_<?php echo date('Y-m-d'); ?>.pdf');
    }
    </script>
</body>
</html>
