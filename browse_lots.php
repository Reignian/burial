<?php
    include(__DIR__ . '/includes/header.php');
    require_once __DIR__ . '/website/lots.class.php';
    
    $burialObj = new Reservation();
    $lotarray = $burialObj->displayAvailable_lots();

    // Get unique locations for filter
    $locations = array_unique(array_column($lotarray, 'location'));
?>

<div class="container-fluid py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-uppercase" style="color: #006064;">Available Lots</h1>
        <div class="border-bottom border-2 w-25 mx-auto mb-4" style="border-color: #006064 !important;"></div>
        <p class="lead text-muted">Browse through our available burial lots and find the perfect resting place for your loved ones.</p>
    </div>

    <div class="container mb-4">
        <div class="row g-3">
            <!-- Search Bar -->
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-primary text-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search lots by name or location...">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <!-- Location Filter -->
                    <select id="locationFilter" class="form-select">
                        <option value="">All Locations</option>
                        <?php foreach($locations as $location): ?>
                            <option value="<?= htmlspecialchars($location) ?>"><?= htmlspecialchars($location) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Price Sort -->
                    <select id="priceSort" class="form-select">
                        <option value="">Sort by Price</option>
                        <option value="low">Price: Low to High</option>
                        <option value="high">Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4" id="lotsContainer">
            <?php foreach ($lotarray as $lotarr): 
                $max_length = 100;
                $description = $lotarr['description'];
                if (strlen($description) > $max_length) {
                    $description = substr($description, 0, $max_length) . '...';
                }
            ?>
            <div class="col-md-6 col-lg-4 lot-item" 
                 data-name="<?= htmlspecialchars(strtolower($lotarr['lot_name'])) ?>"
                 data-location="<?= htmlspecialchars(strtolower($lotarr['location'])) ?>"
                 data-price="<?= str_replace(',', '', $lotarr['price']) ?>">
                <div class="card h-100 lot-card">
                    <div class="lot-image-container">
                        <img src="admin/lots/<?= $lotarr['lot_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($lotarr['lot_name']) ?>">
                        <div class="lot-overlay">
                            <span class="lot-price">₱<?= number_format($lotarr['price'], 2) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($lotarr['lot_name']) ?></h5>
                        <div class="lot-details mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <span><?= htmlspecialchars($lotarr['location']) ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-combined text-primary me-2"></i>
                                <span><?= htmlspecialchars($lotarr['size']) ?> sqm.</span>
                            </div>
                        </div>
                        <p class="card-text text-muted"><?= htmlspecialchars($description) ?></p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                        <a href="website/add_reservation.php?lot_id=<?= $lotarr['lot_id'] ?>" 
                           class="btn btn-primary btn-reserve w-75">
                            <i class="fas fa-calendar-check me-2"></i>Reserve Now
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5 d-none">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">No lots found matching your criteria</h3>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #006064;
        --primary-light: #428e92;
        --primary-dark: #00363a;
        --accent-color: #ffd54f;
    }

    .lot-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .lot-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .lot-image-container {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .lot-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .lot-card:hover .lot-image-container img {
        transform: scale(1.1);
    }

    .lot-overlay {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(0, 96, 100, 0.9);
        padding: 8px 15px;
        border-radius: 20px;
    }

    .lot-price {
        color: white;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .card-title {
        color: var(--primary-dark);
        font-size: 1.25rem;
    }

    .lot-details {
        font-size: 0.95rem;
    }

    .text-primary {
        color: var(--primary-color) !important;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-reserve {
        font-size: 1rem;
    }

    .card-text {
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .input-group-text {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .form-select:focus, .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 96, 100, 0.25);
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 15px;
        }
        
        .col-md-6 {
            padding: 0 10px;
        }
        
        .lot-image-container {
            height: 180px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const locationFilter = document.getElementById('locationFilter');
    const priceSort = document.getElementById('priceSort');
    const lotsContainer = document.getElementById('lotsContainer');
    const noResults = document.getElementById('noResults');
    const lotItems = document.querySelectorAll('.lot-item');

    function filterLots() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedLocation = locationFilter.value.toLowerCase();
        const sortOrder = priceSort.value;
        
        let visibleItems = Array.from(lotItems);
        
        // Filter by search term
        visibleItems = visibleItems.filter(item => {
            const name = item.dataset.name;
            const location = item.dataset.location;
            return (name.includes(searchTerm) || location.includes(searchTerm));
        });
        
        // Filter by location
        if (selectedLocation) {
            visibleItems = visibleItems.filter(item => 
                item.dataset.location === selectedLocation
            );
        }
        
        // Sort by price
        if (sortOrder) {
            visibleItems.sort((a, b) => {
                const priceA = parseFloat(a.dataset.price);
                const priceB = parseFloat(b.dataset.price);
                return sortOrder === 'low' ? priceA - priceB : priceB - priceA;
            });

            // Reorder elements in the DOM
            const fragment = document.createDocumentFragment();
            visibleItems.forEach(item => fragment.appendChild(item));
            lotsContainer.appendChild(fragment);
        }
        
        // Show/hide items
        lotItems.forEach(item => {
            const shouldShow = visibleItems.includes(item);
            item.classList.toggle('d-none', !shouldShow);
        });
        
        // Show/hide no results message
        noResults.classList.toggle('d-none', visibleItems.length > 0);
    }

    // Add event listeners
    searchInput.addEventListener('input', filterLots);
    locationFilter.addEventListener('change', filterLots);
    priceSort.addEventListener('change', filterLots);
});
</script>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Add Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Add SSE for payment checks -->
<script src="website/js/check-payments.js"></script>

<?php include('includes/footer.php'); ?>