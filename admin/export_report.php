<?php
session_start();

// Check if we have the required session data
if (!isset($_SESSION['export_data']) || !isset($_SESSION['export_type'])) {
    die('No data to export');
}

$reportData = $_SESSION['export_data'];
$reportType = $_SESSION['export_type'];
$summary = $_SESSION['export_summary'] ?? null;

// Clean any output buffers
ob_clean();

// Set headers for Excel download with proper encoding
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.xls"');
header('Cache-Control: no-cache');
header('Pragma: no-cache');

// Add BOM for UTF-8
echo chr(0xEF) . chr(0xBB) . chr(0xBF);
?>
<table border="1">
    <?php if ($reportType === 'revenue' && $summary): ?>
        <tr><td>Total Revenue:</td><td>P<?= number_format($summary['total_revenue'], 2) ?></td></tr>
        <tr><td>Outstanding Balance:</td><td>P<?= number_format($summary['total_balance'], 2) ?></td></tr>
        <tr><td>Fully Paid:</td><td><?= $summary['fully_paid'] ?></td></tr>
        <tr><td>Pending Payment:</td><td><?= $summary['pending_payment'] ?></td></tr>
        <tr><td colspan="2">&nbsp;</td></tr>
    <?php endif; ?>
    
    <tr>
        <?php switch ($reportType):
            case 'revenue': ?>
                <th>Payment Date</th>
                <th>Client Name</th>
                <th>Lot Details</th>
                <th>Payment Plan</th>
                <th>Amount Paid</th>
                <?php break;
            case 'lot_status': ?>
                <th>Lot Details</th>
                <th>Status</th>
                <th>Reserved By</th>
                <th>Reservation Date</th>
                <th>Balance</th>
                <?php break;
            case 'payment_status': ?>
                <th>Client Name</th>
                <th>Lot Details</th>
                <th>Reservation Date</th>
                <th>Payment Plan</th>
                <th>Monthly Payment</th>
                <th>Balance</th>
                <th>Status</th>
                <?php break;
            case 'penalty': ?>
                <th>Client Name</th>
                <th>Lot Details</th>
                <th>Reservation Date</th>
                <th>Payment Plan</th>
                <th>Number of Penalties</th>
                <th>Total Penalty Amount</th>
                <th>Penalty Dates</th>
                <?php break;
        endswitch; ?>
    </tr>

    <?php foreach ($reportData as $item): ?>
        <tr>
            <?php switch ($reportType):
                case 'revenue': ?>
                    <td><?= date('M d, Y', strtotime($item['payment_date'])) ?></td>
                    <td><?= htmlspecialchars($item['client_name']) ?></td>
                    <td><?= htmlspecialchars($item['lot_details']) ?></td>
                    <td><?= htmlspecialchars($item['payment_plan']) ?></td>
                    <td>P<?= number_format($item['amount_paid'], 2) ?></td>
                    <?php break;
                case 'lot_status': ?>
                    <td><?= htmlspecialchars($item['lot_details']) ?></td>
                    <td><?= htmlspecialchars($item['status']) ?></td>
                    <td><?= htmlspecialchars($item['reserved_by'] ?? 'N/A') ?></td>
                    <td><?= $item['reservation_date'] ? date('M d, Y', strtotime($item['reservation_date'])) : 'N/A' ?></td>
                    <td><?= $item['balance'] ? 'P' . number_format($item['balance'], 2) : 'N/A' ?></td>
                    <?php break;
                case 'payment_status': ?>
                    <td><?= htmlspecialchars($item['client_name']) ?></td>
                    <td><?= htmlspecialchars($item['lot_details']) ?></td>
                    <td><?= date('M d, Y', strtotime($item['reservation_date'])) ?></td>
                    <td><?= htmlspecialchars($item['payment_plan']) ?></td>
                    <td>P<?= number_format($item['monthly_payment'], 2) ?></td>
                    <td>P<?= number_format($item['balance'], 2) ?></td>
                    <td><?= htmlspecialchars($item['payment_status']) ?></td>
                    <?php break;
                case 'penalty': ?>
                    <td><?= htmlspecialchars($item['client_name']) ?></td>
                    <td><?= htmlspecialchars($item['lot_details']) ?></td>
                    <td><?= date('M d, Y', strtotime($item['reservation_date'])) ?></td>
                    <td><?= htmlspecialchars($item['payment_plan']) ?></td>
                    <td><?= htmlspecialchars($item['penalty_count']) ?></td>
                    <td>P<?= number_format($item['total_penalty_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($item['penalty_dates']) ?></td>
                    <?php break;
            endswitch; ?>
        </tr>
    <?php endforeach; ?>
</table>
<?php
// Clean up session variables
unset($_SESSION['export_data']);
unset($_SESSION['export_type']);
unset($_SESSION['export_summary']);
unset($_SESSION['start_date']);
unset($_SESSION['end_date']);
?>
