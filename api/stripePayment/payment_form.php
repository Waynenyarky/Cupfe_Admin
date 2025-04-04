<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CupfeExpresso | Table Reservation Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        /* Updated body styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f3e5d4; /* Beige color */
            color: #4b2e2b; /* Brown text color */
            margin: 0;
            overflow: hidden; /* Prevent scrolling from animations */
        }

        #payment-container {
            text-align: center;
            padding: 40px;
        }

        #payment-form {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }

        #payment-form h2 {
            margin-top: 0;
            color: #4b2e2b;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #4b2e2b;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        #card-element {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
        }

        button {
            background-color: #6f4e37; /* Darker brown */
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            padding: 12px;
            width: 100%;
            font-size: 18px;
            font-weight: bold;
        }

        button:hover {
            background-color: #5a3b2e; /* Slightly darker brown */
        }

        #card-errors {
            color: #e74c3c;
            margin-top: 10px;
        }

        /* Toast Styling */
        #toast {
            visibility: hidden;
            max-width: 90%;
            margin: auto;
            background-color: #6f4e37; /* Brown for cafe */
            color: #fff;
            text-align: center;
            border-radius: 10px;
            position: fixed;
            z-index: 1;
            left: 0;
            right: 0;
            bottom: 30px;
            font-size: 16px; /* Larger font */
            font-weight: bold; /* Emphasize the text */
            padding: 20px; /* Increased padding */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: visibility 0s, opacity 0.5s ease-in-out;
        }

        /* Coffee Bean Styling */
        .coffee-bean {
            position: fixed; /* Ensures beans are fixed to the viewport */
            top: -20px; /* Start slightly above the viewport */
            width: 20px; /* Slightly bigger size */
            height: 20px; /* Slightly bigger size */
            background-image: url('coffee-bean.png'); /* Use image for coffee bean */
            background-size: cover; /* Ensure the image covers the element */
            animation: fall 6s linear infinite; /* Falling animation */
            z-index: -1; /* Ensure beans stay behind content */
        }

        @keyframes fall {
            0% {
                transform: translateY(0) rotate(0deg); /* Start above viewport and rotation */
                opacity: 1; /* Fully visible */
            }
            100% {
                transform: translateY(100vh) rotate(360deg); /* End below the viewport and complete rotation */
                opacity: 0; /* Fade out at the end */
            }
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg); /* Start rotation */
            }
            100% {
                transform: rotate(360deg); /* Complete rotation */
            }
        }
    </style>
</head>
<body>
    <div id="payment-container">
        <h1>CupfeExpresso</h1>
        <h3>Table Reservation Payment</h3>
        <form id="payment-form">
            <h2>Secure Your Reservation</h2>
            <div class="form-group">
                <label for="reference-number">Reference Number</label>
                <input type="text" id="reference-number" placeholder="Enter your reference number" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="card-element">Card Details</label>
                <div id="card-element"></div>
            </div>
            <button type="submit">Submit Payment</button>
            <div id="card-errors" role="alert"></div>
        </form>
        <div id="toast">
            <div id="desc"></div>
        </div>
    </div>

    <script>
        var stripe = Stripe('pk_test_51QzHASBZDDPGc55vknc16LSQ70oeZkxNQ3C98BhgleBU4Msjpiv1zihZRe7Agn3VvqMOaPA0NWXSSaUIpVrlThfs00veD6STJ9');
        var elements = stripe.elements();
        var card = elements.create('card', {style: {base: {fontSize: '16px', color: '#32325d'}}});
        card.mount('#card-element');

        card.addEventListener('change', function(event) {
            var errorElement = document.getElementById('card-errors');
            if (event.error) {
                errorElement.textContent = event.error.message;
            } else {
                errorElement.textContent = '';
            }
        });

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            showToast("Processing Payment...", 'success');

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    showToast(result.error.message, 'error');
                } else {
                    var referenceNumber = document.getElementById('reference-number').value;
                    var email = document.getElementById('email').value;
                    var username = document.getElementById('username').value;

                    fetch('/expresso-cafe/api/process_payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token: result.token.id,
                            reference_number: referenceNumber,
                            email: email,
                            username: username
                        })
                    })
                    .then(response => response.json())
                    .then(function(data) {
                        if (data && data.success) {
                            showToast(data.message, 'success');
                        } else if (data && !data.success) {
                            showToast(data.message, 'error');
                        } else {
                            showToast("An unexpected error occurred.", 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showToast("An unexpected error occurred. Please try again.", 'error');
                    });
                }
            });
        });

        function showToast(message, type) {
            var toast = document.getElementById("toast");
            var desc = document.getElementById("desc");
            toast.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
            desc.textContent = message;
            toast.style.visibility = "visible";
            toast.style.opacity = 1;
            setTimeout(function(){
                toast.style.opacity = 0;
                toast.style.visibility = "hidden";
            }, 10000); // Show for 10 seconds
        }

        // Function to generate falling coffee beans from the top of the page
        function generateCoffeeBeans() {
            // Body element to contain the beans
            const body = document.body;

            // Generate 5 coffee beans
            for (let i = 0; i < 15; i++) {
                // Create the bean element
                const bean = document.createElement('div');
                bean.className = 'coffee-bean';

                // Random horizontal position within the viewport
                bean.style.left = Math.random() * window.innerWidth + 'px';

                // Randomize fall duration for natural effect
                bean.style.animationDuration = Math.random() * 4 + 10 + 's'; // 6-10 seconds

                // Add the bean to the body
                body.appendChild(bean);

                // Remove the bean from the DOM after animation is complete
                setTimeout(() => bean.remove(), 10000);
            }
        }

        // Continuously generate beans at intervals
        setInterval(generateCoffeeBeans, 1000); // Every 2 seconds
    </script>
</body>
</html>
