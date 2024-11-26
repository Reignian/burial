<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once __DIR__ . '/dashboard/dashboard.class.php';

    $class = new Dashboard_class();

    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    $totalLots = $class->getTotalLots();
    $totalAvailableLots = $class->getTotalAvailableLots();
    $totalReservations = $class->getTotalReservations();
    $totalRevenue = $class->getTotalRevenue($startDate, $endDate);
    $pendingPayments = $class->getPendingPaymentsThisMonth();
    $latePayments = $class->getLatePayments();
?>

<div class="dashboard-container">
    <!-- Date Filter Section -->
    <div class="filter-section">
        <form method="GET" action="">
            <div class="date-filters">
                <div class="date-input">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="date-input">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Stats Cards Section -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-details">
                <h3>Total Lots</h3>
                <p class="stat-number"><?php echo $totalLots; ?></p>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3>Available Lots</h3>
                <p class="stat-number"><?php echo $totalAvailableLots; ?></p>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-details">
                <h3>Total Reservations</h3>
                <p class="stat-number"><?php echo $totalReservations; ?></p>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-peso-sign"></i>
            </div>
            <div class="stat-details">
                <h3>Total Revenue<?php echo ($startDate || $endDate) ? ' (Filtered)' : ''; ?></h3>
                <p class="stat-number">₱<?php echo number_format($totalRevenue, 2); ?></p>
                <?php if ($startDate || $endDate): ?>
                    <small class="date-range">
                        <?php 
                        echo $startDate ? "From: " . date('M d, Y', strtotime($startDate)) : ""; 
                        echo $startDate && $endDate ? " - " : "";
                        echo $endDate ? "To: " . date('M d, Y', strtotime($endDate)) : "";
                        ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3>Pending Payments</h3>
                <p class="stat-number"><?php echo $pendingPayments; ?></p>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-details">
                <h3>Late Payments</h3>
                <p class="stat-number"><?php echo $latePayments; ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-card">
            <h3>Lot Status Distribution</h3>
            <div class="chart-container">
                <canvas id="lotStatusChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>Recent Reservations</h3>
            <div class="dashboard-recent-reservations">
                <table id="recentReservationsTable" class="table">
                    <thead>
                        <tr>
                            <th>Lot Name</th>
                            <th>Reserved By</th>
                            <th>Reservation Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lot Status Chart
    var ctx = document.getElementById('lotStatusChart').getContext('2d');
    var lotStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Reserved', 'On Request'],
            datasets: [{
                data: [
                    <?php echo $totalAvailableLots; ?>, 
                    <?php echo $totalReservations; ?>, 
                    <?php echo $totalLots - $totalAvailableLots - $totalReservations; ?>
                ],
                backgroundColor: ['#28a745', '#007bff', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Simplified DataTable
    $('#recentReservationsTable').DataTable({
        ajax: {
            url: 'dashboard/get_recent_reservations.php',
            dataSrc: ''
        },
        columns: [
            { data: 'lot_name' },
            { data: 'reserved_by' },
            { data: 'reservation_date' }
        ],
        pageLength: 5,
        searching: false,    // Remove search
        lengthChange: false, // Remove show entries dropdown
        paging: false,       // Remove pagination
        info: false         // Remove showing X of Y entries text
    });
});
</script>