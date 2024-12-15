<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/generate_report/report.class.php');

    $reportObj = new Report_class();

    // Get filter parameters
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'revenue';

    // Process Excel export
    if(isset($_POST['export_excel'])) {
        require '../vendor/autoload.php';
        
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set column headers
        $headers = [];
        if ($reportType == 'revenue') {
            $headers = ['Payment Date', 'Client Name', 'Lot Details', 'Payment Plan', 'Amount Paid'];
            
            // Add summary at the top
            $sheet->setCellValue('A1', 'Total Revenue:');
            $sheet->setCellValue('B1', '₱' . number_format($summary['total_revenue'], 2));
            $sheet->setCellValue('A2', 'Outstanding Balance:');
            $sheet->setCellValue('B2', '₱' . number_format($summary['total_balance'], 2));
            $sheet->setCellValue('A3', 'Fully Paid:');
            $sheet->setCellValue('B3', $summary['fully_paid']);
            $sheet->setCellValue('A4', 'Pending Payment:');
            $sheet->setCellValue('B4', $summary['pending_payment']);
            
            // Style the summary section
            $sheet->getStyle('A1:B4')->getFont()->setBold(true);
            
            // Add a blank row after summary
            $startRow = 6;
        } elseif ($reportType == 'lot_status') {
            $headers = ['Lot Details', 'Status', 'Reserved By', 'Reservation Date', 'Balance'];
            $startRow = 1;
        } elseif ($reportType == 'payment_status') {
            $headers = ['Client Name', 'Lot Details', 'Reservation Date', 'Payment Plan', 'Monthly Payment', 'Balance', 'Status'];
            $startRow = 1;
        } elseif ($reportType == 'penalty') {
            $headers = ['Client Name', 'Lot Details', 'Reservation Date', 'Payment Plan', 'Number of Penalties', 'Total Penalty Amount', 'Penalty Dates'];
            $startRow = 1;
        }
        
        // Set headers
        foreach(range(0, count($headers) - 1) as $i) {
            $sheet->setCellValueByColumnAndRow($i + 1, $startRow, $headers[$i]);
        }
        $sheet->getStyle($startRow)->getFont()->setBold(true);
        
        // Add data
        $row = $startRow + 1;
        foreach($reportData as $item) {
            if ($reportType == 'revenue') {
                $sheet->setCellValue('A'.$row, date('M d, Y', strtotime($item['payment_date'])));
                $sheet->setCellValue('B'.$row, $item['client_name']);
                $sheet->setCellValue('C'.$row, $item['lot_details']);
                $sheet->setCellValue('D'.$row, $item['payment_plan']);
                $sheet->setCellValue('E'.$row, '₱' . number_format($item['amount_paid'], 2));
            } elseif ($reportType == 'lot_status') {
                $sheet->setCellValue('A'.$row, $item['lot_details']);
                $sheet->setCellValue('B'.$row, $item['status']);
                $sheet->setCellValue('C'.$row, $item['reserved_by'] ?? 'N/A');
                $sheet->setCellValue('D'.$row, $item['reservation_date'] ? date('M d, Y', strtotime($item['reservation_date'])) : 'N/A');
                $sheet->setCellValue('E'.$row, $item['balance'] ? '₱' . number_format($item['balance'], 2) : 'N/A');
            } elseif ($reportType == 'payment_status') {
                $sheet->setCellValue('A'.$row, $item['client_name']);
                $sheet->setCellValue('B'.$row, $item['lot_details']);
                $sheet->setCellValue('C'.$row, date('M d, Y', strtotime($item['reservation_date'])));
                $sheet->setCellValue('D'.$row, $item['payment_plan']);
                $sheet->setCellValue('E'.$row, '₱' . number_format($item['monthly_payment'], 2));
                $sheet->setCellValue('F'.$row, '₱' . number_format($item['balance'], 2));
                $sheet->setCellValue('G'.$row, $item['payment_status']);
            } elseif ($reportType == 'penalty') {
                $sheet->setCellValue('A'.$row, $item['client_name']);
                $sheet->setCellValue('B'.$row, $item['lot_details']);
                $sheet->setCellValue('C'.$row, date('M d, Y', strtotime($item['reservation_date'])));
                $sheet->setCellValue('D'.$row, $item['payment_plan']);
                $sheet->setCellValue('E'.$row, $item['penalty_count']);
                $sheet->setCellValue('F'.$row, '₱' . number_format($item['total_penalty_amount'], 2));
                $sheet->setCellValue('G'.$row, $item['penalty_dates']);
            }
            $row++;
        }
        
        // Auto-size columns
        foreach(range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create Excel file
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="report.xlsx"');
        header('Cache-Control: max-age=0');
        
        ob_end_clean(); // Clean any output buffering
        $writer->save('php://output');
        exit;
    }

    // Get report data based on type
    $reportData = [];
    $summary = [];
    switch ($reportType) {
        case 'revenue':
            $reportData = $reportObj->generateRevenueReport($startDate, $endDate);
            $summary = $reportObj->getReportSummary($startDate, $endDate);
            break;
        case 'lot_status':
            $reportData = $reportObj->generateLotStatusReport();
            break;
        case 'payment_status':
            $reportData = $reportObj->generatePaymentStatusReport();
            break;
        case 'penalty':
            $reportData = $reportObj->generatePenaltyReport();
            break;
    }
?>

<div class="dashboard-container">
    <div class="content-grid">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">GENERATE REPORTS</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <div class="filter-section">
            <form method="GET" action="">
                <div class="filters-container">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="form-select" onchange="this.form.submit()">
                                <option value="revenue" <?= $reportType == 'revenue' ? 'selected' : '' ?>>Revenue Report</option>
                                <option value="lot_status" <?= $reportType == 'lot_status' ? 'selected' : '' ?>>Lot Status Report</option>
                                <option value="payment_status" <?= $reportType == 'payment_status' ? 'selected' : '' ?>>Payment Status Report</option>
                                <option value="penalty" <?= $reportType == 'penalty' ? 'selected' : '' ?>>Penalty Report</option>
                            </select>
                        </div>
                        <?php if ($reportType == 'revenue'): ?>
                            <div class="date-filters">
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
                                    <button type="submit" class="filter-btn">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
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
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="export_excel" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export to Excel
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
        "dom": '<"row mb-2"<"col-md-6"f><"col-md-6 text-end"l>>rtip'
    });
});
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

<script>
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const reportType = '<?= $reportType ?>';
    
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