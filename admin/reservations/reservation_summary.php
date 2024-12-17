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
    <title>Reservation Summary - Sto. Nino Parish Cemetery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root {
            --primary-color: #006064;
            --primary-light: #428e92;
            --primary-dark: #00363a;
            --accent-color: #ffd54f;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .main-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }
        
        .page-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .summary-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .summary-section h5 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
            font-weight: 600;
        }
        
        .summary-section p {
            margin-bottom: 0.75rem;
            color: #333;
        }
        
        .summary-section strong {
            color: var(--primary-dark);
        }
        
        .alert-credentials {
            background-color: #fff3e0;
            border-left: 4px solid var(--accent-color);
            border-radius: 4px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }
        
        .btn-danger:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
        }
        
        .action-buttons {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .action-buttons .btn {
            margin: 0 0.5rem;
            min-width: 140px;
        }
        
        @media print {
            body {
                background: none;
                padding: 0;
            }
            
            .main-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .summary-section {
                box-shadow: none;
                padding: 1rem 0;
                margin-bottom: 1rem;
            }
            
            .summary-section h5 {
                color: #000;
                border-bottom-color: #000;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="page-header">
                <h2>Sto. Nino Parish Cemetery</h2>
                <p class="text-muted mb-0">Reservation Summary</p>
                <p class="text-muted">Date: <?php echo date('F d, Y'); ?></p>
            </div>

            <!-- Account Information -->
            <div class="summary-section">
                <h5><i class="fas fa-user me-2"></i>Account Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo $details['account']['first_name'] . ' ' . 
                            ($details['account']['middle_name'] ? $details['account']['middle_name'] . ' ' : '') . 
                            $details['account']['last_name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $details['account']['email']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Phone:</strong> <?php echo $details['account']['phone_number']; ?></p>
                        <?php if (!empty($details['account']['username'])): ?>
                            <p><strong>Username:</strong> <?php echo $details['account']['username']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="summary-section">
                <h5><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Lot Price:</strong> PHP <?php echo number_format($details['lot']['price'], 2); ?></p>
                        <p><strong>Down Payment:</strong> PHP <?php echo number_format($details['payment']['down_payment'], 2); ?></p>
                        <p><strong>Interest Rate:</strong> <?php echo $details['payment']['interest_rate']; ?>%</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Monthly Payment:</strong> PHP <?php echo number_format($details['payment']['monthly_payment'], 2); ?></p>
                        <p><strong>Payment Duration:</strong> <?php echo $details['payment']['payment_duration']; ?> months</p>
                        <p><strong>Total Balance:</strong> PHP <?php echo number_format($details['payment']['total_balance'], 2); ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($details['account']['username'])): ?>
            <!-- Account Credentials -->
            <div class="summary-section">
                <h5><i class="fas fa-key me-2"></i>Account Credentials</h5>
                <div class="alert alert-credentials">
                    <p class="mb-2"><strong>Important:</strong> Please keep these credentials secure and change your password upon first login.</p>
                    <p class="mb-1"><strong>Username:</strong> <?php echo $details['account']['username']; ?></p>
                    <p class="mb-0"><strong>Password:</strong> <?php echo $details['account']['password']; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="action-buttons text-center no-print">
                <button class="btn btn-danger" onclick="exportToPDF()">
                    <i class="fas fa-download me-2"></i>Save as PDF
                </button>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Summary
                </button>
                <a href="../reservations.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Reservations
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.jsPDF = window.jspdf.jsPDF;
        
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Set font size and style
            doc.setFontSize(20);
            doc.text('Sto. Nino Parish Cemetery', 105, 20, { align: 'center' });
            doc.setFontSize(16);
            doc.text('Reservation Summary', 105, 30, { align: 'center' });
            doc.setFontSize(12);
            doc.text('Date: ' + new Date().toLocaleDateString(), 105, 40, { align: 'center' });
            
            // Account Information
            doc.setFontSize(14);
            doc.text('Account Information', 20, 60);
            doc.setFontSize(12);
            doc.text('Name: <?php echo addslashes($details['account']['first_name'] . ' ' . 
                ($details['account']['middle_name'] ? $details['account']['middle_name'] . ' ' : '') . 
                $details['account']['last_name']); ?>', 20, 70);
            doc.text('Email: <?php echo addslashes($details['account']['email']); ?>', 20, 80);
            doc.text('Phone: <?php echo addslashes($details['account']['phone_number']); ?>', 20, 90);
            
            // Payment Details
            doc.setFontSize(14);
            doc.text('Payment Details', 20, 110);
            doc.setFontSize(12);
            doc.text('Lot Price: PHP <?php echo number_format($details['lot']['price'], 2); ?>', 20, 120);
            doc.text('Down Payment: PHP <?php echo number_format($details['payment']['down_payment'], 2); ?>', 20, 130);
            doc.text('Monthly Payment: PHP <?php echo number_format($details['payment']['monthly_payment'], 2); ?>', 20, 140);
            doc.text('Payment Duration: <?php echo $details['payment']['payment_duration']; ?> months', 20, 150);
            doc.text('Interest Rate: <?php echo $details['payment']['interest_rate']; ?>%', 20, 160);
            doc.text('Total Balance: PHP <?php echo number_format($details['payment']['total_balance'], 2); ?>', 20, 170);
            
            <?php if (!empty($details['account']['username'])): ?>
            // Account Credentials
            doc.setFontSize(14);
            doc.text('Account Credentials', 20, 190);
            doc.setFontSize(12);
            doc.text('Username: <?php echo addslashes($details['account']['username']); ?>', 20, 200);
            doc.text('Password: <?php echo addslashes($details['account']['password']); ?>', 20, 210);
            doc.text('Please change your password upon first login for security.', 20, 220);
            <?php endif; ?>
            
            // Save the PDF
            doc.save('reservation-summary.pdf');
        }
    </script>
</body>
</html>
