<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Ensures optimal rendering on mobile devices. -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <!-- Optimal Internet Explorer compatibility -->
</head>

<body>

<div id="app">
    <p v-show="loading">
        @{{ message }}
    </p>

    <div id="paypal-button-container"></div>
</div>

<!-- Add the checkout buttons, set up the order and approve the order -->
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}"></script>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            message: 'Hello Vue!',
            loading: false,
        },
        mounted() {
            paypal.Buttons({
                createOrder: (data, actions) => {
                    this.message = 'Waiting for PayPal...'
                    this.loading = true

                    return fetch('/my-server/create-paypal-transaction', {
                        method: 'post',
                        headers: {
                            'content-type': 'application/json'
                        }
                    }).then(function(res) {
                        return res.json();
                    }).then(function(data) {
                        console.log(data);
                        return data.result.id; // Use the same key name for order ID on the client and server
                    });
                },
                onApprove: (data) => {
                    this.message = 'Processing payment. Please wait...'

                    return fetch('/my-server/get-paypal-transaction', {
                        method: 'post',
                        headers: {
                            'content-type': 'application/json'
                        },
                        body: JSON.stringify({
                            orderID: data.orderID
                        })
                    }).then(function (res) {
                        return res.json();
                    }).then(function (details) {
                        // alert('Transaction approved by ' + details.payer_given_name);
                        alert('Transaction approved');
                        window.location = '/order-success?id=' + details.result.id
                    })
                }
            }).render('#paypal-button-container'); // Display payment options on your web page
        },
    })
</script>
</body>
</html>

