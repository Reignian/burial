<?php

    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/generate_report/report.class.php');

    $reportObj = new Report_class();

    // Get filter parameters
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'revenue';

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
    }
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Generate Reports</h2>

    <!-- Report Type Selection and Filters -->
    <form method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select name="report_type" class="form-select" onchange="this.form.submit()">
                    <option value="revenue" <?= $reportType == 'revenue' ? 'selected' : '' ?>>Revenue Report</option>
                    <option value="lot_status" <?= $reportType == 'lot_status' ? 'selected' : '' ?>>Lot Status Report</option>
                    <option value="payment_status" <?= $reportType == 'payment_status' ? 'selected' : '' ?>>Payment Status Report</option>
                </select>
            </div>
            <?php if ($reportType == 'revenue'): ?>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Apply Filters</button>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($reportType == 'revenue' && !empty($summary)): ?>
    <!-- Summary Cards for Revenue Report -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text h3">₱<?= number_format($summary['total_revenue'], 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Outstanding Balance</h5>
                    <p class="card-text h3">₱<?= number_format($summary['total_balance'], 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Fully Paid</h5>
                    <p class="card-text h3"><?= $summary['fully_paid'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Payment</h5>
                    <p class="card-text h3"><?= $summary['pending_payment'] ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Report Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="reportTable">
                    <thead>
                        <?php if ($reportType == 'revenue'): ?>
                            <tr>
                                <th>Date</th>
                                <th>Client Name</th>
                                <th>Lot</th>
                                <th>Payment Plan</th>
                                <th>Amount Paid</th>
                            </tr>
                        <?php elseif ($reportType == 'lot_status'): ?>
                            <tr>
                                <th>Lot Name</th>
                                <th>Status</th>
                                <th>Reserved By</th>
                                <th>Reservation Date</th>
                                <th>Balance</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>Client Name</th>
                                <th>Lot</th>
                                <th>Reservation Date</th>
                                <th>Payment Plan</th>
                                <th>Monthly Payment</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <?php if ($reportType == 'revenue'): ?>
                                    <td><?= date('M d, Y', strtotime($row['payment_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><?= htmlspecialchars($row['lot_name']) ?></td>
                                    <td><?= htmlspecialchars($row['payment_plan']) ?></td>
                                    <td>₱<?= number_format($row['amount_paid'], 2) ?></td>
                                <?php elseif ($reportType == 'lot_status'): ?>
                                    <td><?= htmlspecialchars($row['lot_name']) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= $row['reserved_by'] ? htmlspecialchars($row['reserved_by']) : 'N/A' ?></td>
                                    <td><?= $row['reservation_date'] ? date('M d, Y', strtotime($row['reservation_date'])) : 'N/A' ?></td>
                                    <td><?= $row['balance'] ? '₱' . number_format($row['balance'], 2) : 'N/A' ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><?= htmlspecialchars($row['lot_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['reservation_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['payment_plan']) ?></td>
                                    <td>₱<?= number_format($row['monthly_payment'], 2) ?></td>
                                    <td>₱<?= number_format($row['balance'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['payment_status']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="mt-3">
        <button class="btn btn-success" onclick="exportToExcel()">Export to Excel</button>
        <button class="btn btn-danger" onclick="exportToPDF()">Export to PDF</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>

<script>
function exportToExcel() {
    const table = document.getElementById('reportTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Report" });
    const reportType = '<?= $reportType ?>';
    XLSX.writeFile(wb, `${reportType}_report.xlsx`);
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const reportType = '<?= $reportType ?>';
    
    doc.autoTable({ 
        html: '#reportTable',
        startY: 20,
        headStyles: { fillColor: [0, 123, 255] },
        theme: 'grid'
    });
    
    doc.save(`${reportType}_report.pdf`);
}

// Initialize DataTables
$(document).ready(function() {
    $('#reportTable').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
        "dom": 'Bfrtip',
        "buttons": ['copy', 'csv', 'excel', 'pdf', 'print']
    });
});
</script>

</body>
</html>