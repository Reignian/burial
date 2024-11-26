
function filterReservations() {
    const input = document.getElementById('search').value.toLowerCase();
    const select = document.getElementById('reservation');
    const options = select.getElementsByTagName('option');

    for (let i = 0; i < options.length; i++) {
        const optionText = options[i].getAttribute('data-name').toLowerCase();
        if (optionText.includes(input)) {
            options[i].style.display = "";
        } else {
            options[i].style.display = "none";
        }
    }
}


let deleteButtons = document.querySelectorAll('.deleteBtn')

deleteButtons.forEach(button => {
    button.addEventListener('click', function(e){
        e.preventDefault();
        let paymentID = this.dataset.payment_id;

        let response = confirm('Do you want to delete payment ?')

        if(response){
            fetch('delete_payment.php?id=' + paymentID, {
                method: 'GET'
            })
            .then(response => response.text())
            .then(data=>{
                if(data === 'success'){
                    alert('payment successfully deleted')
                    window.location.reload();
                } else {
                    alert('Failed to delete the payment.');
                }
            })
        }
    });

});