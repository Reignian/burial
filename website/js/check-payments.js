// Initialize SSE connections
let paymentSource = null;
let dueDateSource = null;

function initializePaymentSource() {
    if (paymentSource) {
        paymentSource.close();
    }
    
    try {
        paymentSource = new EventSource("/burial/website/check_payments_sse.php");
        
        paymentSource.addEventListener('message', function(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.status === 'alive') {
                    console.log('Payment check completed at:', data.timestamp);
                } else if (data.status === 'error') {
                    console.error('Payment check error:', data.message);
                }
            } catch (error) {
                console.error('Error parsing payment check message:', error);
            }
        });

        paymentSource.addEventListener('open', function() {
            console.log('Payment check SSE connection established');
        });

        paymentSource.addEventListener('error', function(err) {
            if (paymentSource.readyState === EventSource.CLOSED) {
                console.log('Payment check connection closed, attempting to reconnect...');
                setTimeout(initializePaymentSource, 5000);
            }
        });
    } catch (error) {
        console.error('Error initializing payment source:', error);
    }
}

function initializeDueDateSource() {
    if (dueDateSource) {
        dueDateSource.close();
    }
    
    try {
        dueDateSource = new EventSource("/burial/website/check_due_dates_sse.php");
        
        dueDateSource.addEventListener('message', function(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.status === 'alive') {
                    console.log('Due date check completed at:', data.timestamp);
                    if (data.reservations_checked > 0) {
                        console.log(`Checked ${data.reservations_checked} reservations, applied ${data.penalties_applied} penalties`);
                    }
                } else if (data.status === 'error') {
                    console.error('Due date check error:', data.message);
                }
            } catch (error) {
                console.error('Error parsing due date check message:', error);
                console.log('Raw event data:', event.data); // Log raw data for debugging
            }
        });

        dueDateSource.addEventListener('open', function() {
            console.log('Due date check SSE connection established');
        });

        dueDateSource.addEventListener('error', function(err) {
            if (dueDateSource.readyState === EventSource.CLOSED) {
                console.log('Due date check connection closed, attempting to reconnect in 5 seconds...');
                setTimeout(initializeDueDateSource, 5000);
            } else if (dueDateSource.readyState === EventSource.CONNECTING) {
                console.log('Due date check connection is attempting to connect...');
            } else {
                console.error('Due date check connection error:', err);
            }
        });
    } catch (error) {
        console.error('Error initializing due date source:', error);
    }
}

function initializeChecks() {
    if (typeof(EventSource) !== "undefined") {
        initializePaymentSource();
        initializeDueDateSource();

        // Add cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (paymentSource) paymentSource.close();
            if (dueDateSource) dueDateSource.close();
        });
    } else {
        console.error('SSE not supported by this browser');
    }
}

// Initialize when the page loads
document.addEventListener('DOMContentLoaded', initializeChecks);
