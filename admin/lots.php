<?php

    include(__DIR__ . '/nav/navigation.php');
    require_once (__DIR__ . '/lots/lots.class.php');
    
    $burialObj = new Lots_class();

    $lotarray = $burialObj->showALL_lots();
?>
<!-- Add DataTables CSS after Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <section id="lots">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">LOTS</h1>
            <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="lotsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Image</th>
                            <th>Lot</th>
                            <th>Location</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i = 1;
                            foreach ($lotarray as $lotarr){
                        ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td class="text-center">
                                <img src="lots/<?= $lotarr['lot_image'] ?>" alt="" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            </td>
                            <td><?= $lotarr['lot_name'] ?></td>
                            <td><?= $lotarr['location'] ?></td>
                            <td><?= $lotarr['size'] ?></td>
                            <td>₱<?= number_format($lotarr['price'], 2) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($lotarr['status']) ?>">
                                    <?= $lotarr['status'] ?>
                                </span>
                            </td>
                            <td><?= $lotarr['description'] ?></td>
                            <td>
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="lots/editlot.php?lot_id=<?= $lotarr['lot_id'] ?>" 
                                       class="btn btn-sm btn-warning d-flex align-items-center">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <button class="deleteBtn btn btn-sm btn-danger d-flex align-items-center" 
                                            data-id="<?= $lotarr['lot_id'] ?>">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Add DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#lotsTable').DataTable({
        dom: '<"row mb-3"<"col-md-6"f><"col-md-4 text-end"l><"col-md-2 text-end add-lot-button">>rtip',
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        ordering: false,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            lengthMenu: "Show _MENU_ entries"
        },
        columnDefs: [
            {
                targets: [0, 2, 3, 4, 6], // No., Lot, Location, Size, Status columns
                className: 'text-center'
            },
            {
                targets: [5], // Price column
                className: 'text-end'
            },
            {
                targets: [7], // Description column
                className: 'text-start'
            },
            {
                targets: [8], // Action column
                className: 'text-center'
            }
        ]
    });

    $('.add-lot-button').html(`
        <button type="button" class="btn btn-primary" onclick="window.location.href='lots/add_lot.php'">
            <i class="fas fa-plus-circle me-1"></i> Add Lot
        </button>
    `);

    // Delete button functionality
    let deleteButtons = document.querySelectorAll('.deleteBtn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let lotId = this.dataset.id;
            if (confirm("Are you sure you want to delete this lot?")) {
                fetch('lots/delete_lot.php?lot_id=' + lotId, { method: 'GET' })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Lot deleted successfully.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to delete lot.');
                    }
                });
            }
        });
    });
});
</script>

<style>
/* Import all styles from reservations.php first */
/* Then add only the lots-specific styles */

/* Status Badge Styling */
.status-badge {
    padding: 0.35rem 0.75rem !important;
    border-radius: 6px !important;
    font-size: 0.85rem !important;
    font-weight: 500 !important;
    display: inline-block !important;
    text-align: center !important;
}

.status-badge.available {
    background-color: #e0f2f1 !important;
    color: #00695c !important;
}

.status-badge.reserved {
    background-color: #fff3e0 !important;
    color: #f57c00 !important;
}

.status-badge.occupied {
    background-color: #ffebee !important;
    color: #c62828 !important;
}

/* Image Styling */
.img-thumbnail {
    border: 2px solid #e0f2f1 !important;
    padding: 0.25rem !important;
    border-radius: 8px !important;
    background-color: #fff !important;
}

/* Price Column Alignment */
.table td:nth-child(6) {
    text-align: right !important;
    font-weight: 500 !important;
}

/* Description Column */
.table td:nth-child(8) {
    max-width: 200px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

/* Remove table borders */
.table {
    border: none !important;
}

.table td, 
.table th {
    border: none !important;
    border-bottom: 1px solid #e9ecef !important;
}

/* Remove card hover effect */
.card {
    box-shadow: none !important;
}

.card:hover {
    transform: none !important;
    box-shadow: none !important;
}

/* Keep all the pagination, header, and other styles from reservations.php */

/* Header Styling - match reservations.php */
.table thead th {
    background-color: #00838f !important; /* Same as reservations.php */
    color: white !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.5px !important;
    padding: 1rem !important;
    text-align: center !important;
    border: none !important;
    border-bottom: 2px solid #4dd0e1 !important; /* Light teal border */
}

/* Title Styling - match reservations.php */
.display-5 {
    color: #00838f !important; /* Match reservations.php */
}

.border-bottom {
    border-color: #4dd0e1 !important; /* Match reservations.php */
}

/* DataTables Filter and Length Menu - match reservations.php */
.dataTables_filter {
    text-align: left !important;
    margin-bottom: 0 !important;
}

.dataTables_filter input {
    border: 1px solid #b2ebf2 !important;
    border-radius: 6px !important;
    padding: 0.5rem 1rem !important;
    width: 400px !important;
    font-size: 0.875rem !important;
}

.dataTables_length {
    text-align: right !important;
    margin-bottom: 0 !important;
    padding-right: 1rem !important;
}

.dataTables_length select {
    border: 1px solid #b2ebf2 !important;
    border-radius: 6px !important;
    padding: 0.5rem 2rem 0.5rem 1rem !important;
    font-size: 0.875rem !important;
}

.dt-buttons {
    margin-left: 1rem !important;
}

.dt-button {
    background-color: #00838f !important;
    border-color: #00838f !important;
    color: white !important;
    padding: 0.4rem 0.8rem !important;
    font-size: 0.875rem !important;
    border-radius: 4px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.3rem !important;
}

.dt-button:hover {
    background-color: #0097a7 !important;
    border-color: #0097a7 !important;
    color: white !important;
}

/* Adjust the layout of the controls */
.dataTables_filter {
    margin-bottom: 0 !important;
}

/* Center align table content */
.table td {
    vertical-align: middle !important;
}

/* Specific column alignments */
.table td:nth-child(6) { /* Price column */
    text-align: right !important;
}

.table td:nth-child(8) { /* Description column */
    text-align: left !important;
}

/* Make sure the button aligns properly with other elements */
.btn-primary {
    background-color: #00838f !important; /* Match theme color */
    border-color: #00838f !important;
    height: fit-content !important;
    align-self: center !important;
}

.btn-primary:hover {
    background-color: #0097a7 !important; /* Lighter version for hover */
    border-color: #0097a7 !important;
}

/* Updated Pagination Styling to match reservations.php exactly */
.dataTables_paginate {
    padding-top: 0.5rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 0.25rem !important;
}

.paginate_button {
    padding: 0.2rem 0.4rem !important;
    margin: 0 !important;
    font-size: 0.75rem !important;
    line-height: 1 !important;
}

/* Remove all custom styling from pagination buttons */
.paginate_button.previous,
.paginate_button.next,
.paginate_button.current,
.paginate_button:hover:not(.current):not(.disabled),
.paginate_button.disabled {
    background: none !important;
    border: none !important;
    color: inherit !important;
}

/* Table cell alignment */
.table td, .table th {
    vertical-align: middle !important;
}

/* Image column specific styling */
.table td:nth-child(2) {
    text-align: center !important;
}

/* Price formatting */
.table td:nth-child(6) {
    text-align: right !important;
    font-weight: 500 !important;
    padding-right: 2rem !important;
}

/* Description column */
.table td:nth-child(8) {
    text-align: left !important;
    max-width: 200px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    padding-left: 1rem !important;
}

/* Status badge centering */
.status-badge {
    display: inline-block !important;
    text-align: center !important;
    width: auto !important;
    min-width: 100px !important;
}

/* Action buttons container */
.d-flex.gap-1.justify-content-center {
    display: flex !important;
    justify-content: center !important;
    gap: 0.3rem !important;
}

/* Add these styles for the button positioning */
.add-lot-button {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
}

.add-lot-button .btn {
    margin-left: 0.5rem !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
