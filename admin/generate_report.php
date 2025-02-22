<?php
// Include navigation first since it handles session and authentication
include(__DIR__ . '/nav/navigation.php');
require_once (__DIR__ . '/generate_report/report.class.php');
require_once (__DIR__ . '/staffs/staffs.class.php');

$reportObj = new Report_class();
$staffObj = new Staffs_class();

// Get filter parameters with validation
$startDate = filter_input(INPUT_GET, 'start_date') ?: null;
$endDate = filter_input(INPUT_GET, 'end_date') ?: null;
$reportType = filter_input(INPUT_GET, 'report_type') ?: 'revenue';
$generateReport = filter_input(INPUT_GET, 'generate') === 'true';

// Validate date range if provided
if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
    echo "<div class='alert alert-danger'>Start date cannot be later than end date.</div>";
    $startDate = $endDate = null;
}

// Get report data based on type
$reportData = [];
$summary = [];
$logDetails = '';
switch ($reportType) {
    case 'revenue':
        $reportData = $reportObj->generateRevenueReport($startDate, $endDate);
        $summary = $reportObj->getReportSummary($startDate, $endDate);
        $logDetails = "Generated Revenue Report" . ($startDate && $endDate ? " from $startDate to $endDate" : "");
        break;
    case 'lot_status':
        $reportData = $reportObj->generateLotStatusReport();
        $logDetails = "Generated Lot Status Report";
        break;
    case 'payment_status':
        $reportData = $reportObj->generatePaymentStatusReport();
        $logDetails = "Generated Payment Status Report";
        break;
    case 'penalty':
        $reportData = $reportObj->generatePenaltyReport();
        $logDetails = "Generated Penalty Report";
        break;
}

// Log report generation only when explicitly requested
if (!empty($reportData) && $generateReport) {
    $staffObj->addStaffLog($_SESSION['account']['account_id'], "Generate Report", $logDetails);
}

// Log PDF export if requested
if (isset($_POST['log_pdf_export'])) {
    $staffObj->addStaffLog($_SESSION['account']['account_id'], "Export Report", "Exported $reportType report to PDF" . ($startDate && $endDate ? " for period $startDate to $endDate" : ""));
    exit;
}

// Process Excel export
if (isset($_POST['export_excel']) && !isset($_SESSION['export_logged'])) {
    // Log export action only once
    $staffObj->addStaffLog($_SESSION['account']['account_id'], "Export Report", "Exported $reportType report to Excel" . ($startDate && $endDate ? " for period $startDate to $endDate" : ""));
    $_SESSION['export_logged'] = true;
    
    // Store parameters in session
    $_SESSION['export_data'] = $reportData;
    $_SESSION['export_summary'] = $summary;
    $_SESSION['export_type'] = $reportType;
    $_SESSION['start_date'] = $startDate;
    $_SESSION['end_date'] = $endDate;
?>
    <form id="returnForm" action="generate_report.php" method="GET">
        <input type="hidden" name="report_type" value="<?= htmlspecialchars($reportType) ?>">
        <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
        <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
    </form>
    <script>
        // First redirect to export
        window.location.href = 'export_report.php';
        // Then submit the return form after a delay
        setTimeout(function() {
            document.getElementById('returnForm').submit();
        }, 1000);
    </script>
<?php
    exit;
}

// Clear export logged flag if it exists and we're not exporting
if (!isset($_POST['export_excel']) && isset($_SESSION['export_logged'])) {
    unset($_SESSION['export_logged']);
}
?>

<div class="dashboard-container">
    <div class="content-grid">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">GENERATE REPORTS</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <div class="filter-section">
            <form method="GET" action="" id="reportForm">
                <div class="filters-container">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="form-select" onchange="handleReportTypeChange()">
                                <option value="revenue" <?= $reportType == 'revenue' ? 'selected' : '' ?>>Revenue Report</option>
                                <option value="lot_status" <?= $reportType == 'lot_status' ? 'selected' : '' ?>>Lot Status Report</option>
                                <option value="payment_status" <?= $reportType == 'payment_status' ? 'selected' : '' ?>>Payment Status Report</option>
                                <option value="penalty" <?= $reportType == 'penalty' ? 'selected' : '' ?>>Penalty Report</option>
                            </select>
                        </div>
                        <div id="dateFields" class="date-filters" style="display: <?= $reportType == 'revenue' ? 'flex' : 'none' ?>;">
                            <div class="filter-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $startDate ?>">
                            </div>
                            <div class="filter-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $endDate ?>">
                            </div>
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="filter-btn" name="generate" value="true">
                                    <i class="fas fa-filter"></i> Generate Report
                                </button>
                            </div>
                        </div>
                        <div id="nonRevenueButton" class="filter-group" style="display: <?= $reportType != 'revenue' ? 'block' : 'none' ?>;">
                            <label>&nbsp;</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($reportType == 'revenue'): ?>
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Revenue</h3>
                    <p class="stat-number">₱<?= number_format($summary['total_revenue'], 2) ?></p>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-details">
                    <h3>Outstanding Balance</h3>
                    <p class="stat-number">₱<?= number_format($summary['total_balance'], 2) ?></p>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3>Fully Paid</h3>
                    <p class="stat-number"><?= $summary['fully_paid'] ?></p>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3>Pending Payment</h3>
                    <p class="stat-number"><?= $summary['pending_payment'] ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="chart-card">
            <div class="table-header d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0"><i class="fas fa-table"></i> <?= ucfirst($reportType) ?> Details</h3>
                <div class="table-actions">
                    <button class="btn btn-danger me-2" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="export_excel" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="reportTable">
                    <thead>
                        <?php if ($reportType == 'revenue'): ?>
                        <tr>
                            <th>Payment Date</th>
                            <th>Client Name</th>
                            <th>Lot Details</th>
                            <th>Payment Plan</th>
                            <th>Amount Paid</th>
                        </tr>
                        <?php elseif ($reportType == 'lot_status'): ?>
                        <tr>
                            <th>Lot Details</th>
                            <th>Status</th>
                            <th>Reserved By</th>
                            <th>Reservation Date</th>
                            <th>Balance</th>
                        </tr>
                        <?php elseif ($reportType == 'payment_status'): ?>
                        <tr>
                            <th>Client Name</th>
                            <th>Lot Details</th>
                            <th>Reservation Date</th>
                            <th>Payment Plan</th>
                            <th>Monthly Payment</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                        <?php elseif ($reportType == 'penalty'): ?>
                        <tr>
                            <th>Client Name</th>
                            <th>Lot Details</th>
                            <th>Reservation Date</th>
                            <th>Payment Plan</th>
                            <th>Number of Penalties</th>
                            <th>Total Penalty Amount</th>
                            <th>Penalty Dates</th>
                        </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $item): ?>
                            <?php if ($reportType == 'revenue'): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($item['payment_date'])) ?></td>
                                <td><?= $item['client_name'] ?></td>
                                <td><?= $item['lot_details'] ?></td>
                                <td><?= $item['payment_plan'] ?></td>
                                <td>₱<?= number_format($item['amount_paid'], 2) ?></td>
                            </tr>
                            <?php elseif ($reportType == 'lot_status'): ?>
                            <tr>
                                <td><?= $item['lot_details'] ?></td>
                                <td><?= $item['status'] ?></td>
                                <td><?= $item['reserved_by'] ?? 'N/A' ?></td>
                                <td><?= $item['reservation_date'] ? date('M d, Y', strtotime($item['reservation_date'])) : 'N/A' ?></td>
                                <td><?= $item['balance'] ? '₱' . number_format($item['balance'], 2) : 'N/A' ?></td>
                            </tr>
                            <?php elseif ($reportType == 'payment_status'): ?>
                            <tr>
                                <td><?= $item['client_name'] ?></td>
                                <td><?= $item['lot_details'] ?></td>
                                <td><?= date('M d, Y', strtotime($item['reservation_date'])) ?></td>
                                <td><?= $item['payment_plan'] ?></td>
                                <td>₱<?= number_format($item['monthly_payment'], 2) ?></td>
                                <td>₱<?= number_format($item['balance'], 2) ?></td>
                                <td><?= $item['payment_status'] ?></td>
                            </tr>
                            <?php elseif ($reportType == 'penalty'): ?>
                            <tr>
                                <td><?= $item['client_name'] ?></td>
                                <td><?= $item['lot_details'] ?></td>
                                <td><?= date('M d, Y', strtotime($item['reservation_date'])) ?></td>
                                <td><?= $item['payment_plan'] ?></td>
                                <td><?= $item['penalty_count'] ?></td>
                                <td>₱<?= number_format($item['total_penalty_amount'], 2) ?></td>
                                <td><?= $item['penalty_dates'] ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.content-grid {
    padding: 1rem;
    width: 100%;
}

@media (min-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr) !important;
    }
}

.display-5 {
    font-size: 2.5rem !important;
    margin-bottom: 0.5rem !important;
    letter-spacing: 1px !important;
    color: #00838f !important;
}

.filter-section {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    margin-bottom: 1rem;
}

.stats-grid {
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.stat-card {
    padding: 0.875rem;
    border-radius: 8px;
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
    font-weight: 500 !important;
    text-transform: uppercase !important;
    font-size: 0.75rem !important;
    letter-spacing: 0.5px !important;
    padding: 0.75rem !important;
    text-align: center !important;
    border: none !important;
    border-bottom: 2px solid #4dd0e1 !important;
}

/* Body Styling */
.table tbody td {
    padding: 0.625rem 0.75rem !important;
    vertical-align: middle !important;
    border: none !important;
    border-bottom: 1px solid #e9ecef !important;
    color: #495057 !important;
    font-size: 0.875rem !important;
    text-align: center !important;
}

/* DataTables Styling */
.dataTables_wrapper .dataTables_filter {
    text-align: left !important;
    margin-bottom: 0.5rem !important;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #b2ebf2 !important;
    border-radius: 4px !important;
    padding: 0.375rem 0.75rem !important;
    width: 250px !important;
    font-size: 0.875rem !important;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #b2ebf2 !important;
    border-radius: 4px !important;
    padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
}

.dataTables_wrapper .dataTables_paginate {
    padding-top: 0.5rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 0.25rem !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.25rem 0.5rem !important;
    margin: 0 !important;
    font-size: 0.75rem !important;
    line-height: 1 !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 4px !important;
    color: #374151 !important;
}

/* Responsive Table */
@media screen and (max-width: 768px) {
    .content-grid {
        padding: 0.75rem;
    }

    .filter-section {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .table-responsive {
        margin: 0 -0.75rem;
        width: calc(100% + 1.5rem);
        border-radius: 0;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 100% !important;
    }

    .table td, 
    .table th {
        padding: 0.5rem !important;
        font-size: 0.8125rem !important;
    }
}
</style>

<script>
function handleReportTypeChange() {
    const reportType = document.getElementById('report_type').value;
    const dateFields = document.getElementById('dateFields');
    const nonRevenueButton = document.getElementById('nonRevenueButton');
    
    // Toggle visibility of date fields and buttons
    dateFields.style.display = reportType === 'revenue' ? 'flex' : 'none';
    nonRevenueButton.style.display = reportType === 'revenue' ? 'none' : 'block';
    
    // Clear date fields if not revenue report
    if (reportType !== 'revenue') {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
    }
    
    // Submit the form with the generate parameter
    const form = document.getElementById('reportForm');
    const generateInput = document.createElement('input');
    generateInput.type = 'hidden';
    generateInput.name = 'generate';
    generateInput.value = 'true';
    form.appendChild(generateInput);
    form.submit();
}

$(document).ready(function() {
    $('#reportTable').DataTable({
        "pageLength": 25,
        "ordering": false,
        "responsive": true,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No entries available",
            "infoFiltered": "(filtered from _MAX_ total entries)"
        },
    });
});

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const reportType = '<?= $reportType ?>';
    
    // Log the PDF export
    fetch('generate_report.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'log_pdf_export=1'
    });
    
    doc.autoTable({ 
        html: '#reportTable',
        startY: 20,
        headStyles: { fillColor: [0, 150, 136] },
        theme: 'grid',
        styles: { fontSize: 8 }
    });
    
    doc.save(`${reportType}_report.pdf`);
}
</script>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>