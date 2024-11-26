document.addEventListener('DOMContentLoaded', function() {
    let confirmButtons = document.querySelectorAll('.confirmBtn');
    let cancelButtons = document.querySelectorAll('.cancelBtn');
    let deleteButtons = document.querySelectorAll('.deleteBtn');

    confirmButtons.forEach(button => {
        button.addEventListener('click', handleConfirmClick);
    });

    cancelButtons.forEach(button => {
        button.addEventListener('click', handleCancelClick);
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', handleDeleteClick);
    });
});

function handleConfirmClick(e) {
    e.preventDefault();
    
    let requestID = this.dataset.id;
    
    if (confirm("Confirm this reservation request?")) {
        fetch('confirm_reservation.php?id=' + requestID, { method: 'GET' })
        .then(response => response.text())
        .then(data => {
            if(data === 'success') {
                window.location.href = '../reservations.php';
            } else {
                alert('Failed to confirm reservation.');
            }
        });
    }
}

function handleCancelClick(e) {
    e.preventDefault();
    
    let requestID = this.dataset.id;
    
    if (confirm("Cancel this reservation request?")) {
        fetch('cancel_reservation.php?id=' + requestID, { method: 'GET' })
        .then(response => response.text())
        .then(data => {
            if(data === 'success') {
                window.location.href = '../notifications.php';
            } else {
                alert('Failed to cancel reservation.');
            }
        });
    }
}


