<?php
    include(__DIR__ . '/nav/navigation.php');
    require_once __DIR__ . '/staffs/staffs.class.php';

    $staffs = new Staffs_class();
    $logs = $staffs->getStaffLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            padding: 2rem;
            background-color: #f8f9fa;
            min-height: calc(100vh - 60px);
        }
        .page-header {
            background: linear-gradient(135deg, #006064 0%, #00838f 100%);
            padding: 2rem;
            border-radius: 10px;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .log-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        .table thead th {
            background-color: #006064;
            color: white;
            font-weight: 500;
            border: none;
            pointer-events: none; /* Disable sorting */
        }
        .table tbody tr {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: #f1f8f9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .date-column {
            white-space: nowrap;
            color: #666;
        }
        .staff-column {
            font-weight: 500;
            color: #006064;
        }
        .action-column {
            color: #333;
        }
        .details-column {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dataTables_filter {
            margin-bottom: 1rem;
        }
        .dataTables_filter input {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.4rem 0.8rem;
            margin-left: 0.5rem;
        }
        .dataTables_info {
            color: #666;
        }
        .paginate_button {
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            color: #006064;
        }
        .paginate_button.current {
            background-color: #006064;
            color: white;
            border-color: #006064;
        }
        .modal-header {
            background: linear-gradient(135deg, #006064 0%, #00838f 100%);
            color: white;
            border-bottom: none;
        }
        .modal-body {
            padding: 2rem;
        }
        .log-detail-item {
            margin-bottom: 1.5rem;
        }
        .log-detail-label {
            font-weight: 600;
            color: #006064;
            margin-bottom: 0.5rem;
        }
        .log-detail-value {
            color: #333;
            line-height: 1.6;
        }
        .modal-footer {
            border-top: none;
            padding: 1rem 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="page-header">
            <h1 class="h2 mb-0"><i class="fas fa-history me-2"></i>Staff Activity Logs</h1>
            <p class="mb-0 mt-2 text-light">Track and monitor all staff activities</p>
        </div>

        <div class="log-card">
            <div class="table-responsive">
                <table id="logsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-alt me-2"></i>Date/Time</th>
                            <th><i class="fas fa-user me-2"></i>Staff</th>
                            <th><i class="fas fa-tasks me-2"></i>Action</th>
                            <th><i class="fas fa-info-circle me-2"></i>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($logs): ?>
                            <?php foreach($logs as $log): ?>
                                <tr data-log-id="<?php echo $log['log_id']; ?>" 
                                    data-date="<?php echo htmlspecialchars($log['log_date']); ?>"
                                    data-staff="<?php echo htmlspecialchars($log['staff_name']); ?>"
                                    data-action="<?php echo htmlspecialchars($log['action']); ?>"
                                    data-details="<?php echo htmlspecialchars($log['details']); ?>">
                                    <td class="date-column">
                                        <?php 
                                            $date = new DateTime($log['log_date']);
                                            echo $date->format('M d, Y h:i A');
                                        ?>
                                    </td>
                                    <td class="staff-column">
                                        <i class="fas fa-user-circle me-2"></i>
                                        <?php echo htmlspecialchars($log['staff_name']); ?>
                                    </td>
                                    <td class="action-column">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </td>
                                    <td class="details-column" title="<?php echo htmlspecialchars($log['details']); ?>">
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No logs found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Log Details Modal -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailsModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Log Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="log-detail-item">
                        <div class="log-detail-label">
                            <i class="fas fa-calendar-alt me-2"></i>Date/Time
                        </div>
                        <div class="log-detail-value" id="modalDate"></div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">
                            <i class="fas fa-user me-2"></i>Staff Member
                        </div>
                        <div class="log-detail-value" id="modalStaff"></div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">
                            <i class="fas fa-tasks me-2"></i>Action
                        </div>
                        <div class="log-detail-value" id="modalAction"></div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">
                            <i class="fas fa-info-circle me-2"></i>Details
                        </div>
                        <div class="log-detail-value" id="modalDetails"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#logsTable').DataTable({
                ordering: false, // Disable sorting
                pageLength: 25,
                language: {
                    search: "<i class='fas fa-search'></i> Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries available",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "<i class='fas fa-angle-double-left'></i>",
                        last: "<i class='fas fa-angle-double-right'></i>",
                        next: "<i class='fas fa-angle-right'></i>",
                        previous: "<i class='fas fa-angle-left'></i>"
                    }
                }
            });

            // Initialize modal
            const logModal = new bootstrap.Modal(document.getElementById('logDetailsModal'));

            // Handle row click
            $('#logsTable tbody').on('click', 'tr', function() {
                if (!$(this).find('td.dataTables_empty').length) { // Check if not empty row
                    const data = $(this).data();
                    const date = new Date(data.date);
                    
                    // Format date
                    const formattedDate = date.toLocaleString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric',
                        second: 'numeric',
                        hour12: true
                    });

                    // Update modal content
                    $('#modalDate').text(formattedDate);
                    $('#modalStaff').text(data.staff);
                    $('#modalAction').text(data.action);
                    $('#modalDetails').text(data.details);

                    // Show modal
                    logModal.show();
                }
            });
        });
    </script>
</body>
</html>