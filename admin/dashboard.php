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

    $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $baseUrl .= $_SERVER['HTTP_HOST'];
    $baseUrl .= dirname($_SERVER['PHP_SELF']);

    // Add this debugging code at the top after the require statements
    echo "<!-- Current script path: " . $_SERVER['SCRIPT_NAME'] . " -->\n";
    echo "<!-- PHP Self: " . $_SERVER['PHP_SELF'] . " -->\n";
    echo "<!-- Document Root: " . $_SERVER['DOCUMENT_ROOT'] . " -->\n";
?>

<div class="container-fluid mt-4">
    <form class="mb-4" method="GET" action="">
        <div class="row">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter Revenue</button>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Lots</h5>
                    <p class="card-text display-4"><?php echo $totalLots; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Available Lots</h5>
                    <p class="card-text display-4"><?php echo $totalAvailableLots; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Reservations</h5>
                    <p class="card-text display-4"><?php echo $totalReservations; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue<?php echo ($startDate || $endDate) ? ' (Filtered)' : ''; ?></h5>
                    <p class="card-text display-4">₱<?php echo number_format($totalRevenue, 2); ?></p>
                    <?php if ($startDate || $endDate): ?>
                        <small>
                            <?php 
                            echo $startDate ? "From: " . date('M d, Y', strtotime($startDate)) : ""; 
                            echo $startDate && $endDate ? " - " : "";
                            echo $endDate ? "To: " . date('M d, Y', strtotime($endDate)) : "";
                            ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments This Month</h5>
                    <p class="card-text display-4"><?php echo $pendingPayments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Late Payments</h5>
                    <p class="card-text display-4"><?php echo $latePayments; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Lot Status Distribution</h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="lotStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Confirmed Reservations</h5>
                    <table id="recentReservationsTable" class="table table-striped table-bordered">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('lotStatusChart').getContext('2d');
    var lotStatusChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Available', 'Reserved', 'On Request'],
            datasets: [{
                data: [<?php echo $totalAvailableLots; ?>, <?php echo $totalReservations; ?>, <?php echo $totalLots - $totalAvailableLots - $totalReservations; ?>],
                backgroundColor: ['#28a745', '#007bff', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    $('#recentReservationsTable').DataTable({
        ajax: {
            url: 'get_recent_reservations.php',
            dataSrc: function(json) {
                if (json.error) {
                    console.error('Server error:', json.message);
                    return [];
                }
                return json || [];
            },
            error: function (xhr, error, thrown) {
                console.error('Ajax error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    thrown: thrown
                });
            }
        },
        columns: [
            { data: 'lot_name' },
            { data: 'reserved_by' },
            { 
                data: 'reservation_date',
                render: function(data) {
                    return data || '';
                }
            }
        ],
        order: [],
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        ordering: false,
        searching: false,
        info: false,
        language: {
            emptyTable: "No recent reservations found",
            zeroRecords: "No matching reservations found"
        },
        processing: true
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>