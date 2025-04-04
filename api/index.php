<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight request
    header("Access-Control-Allow-Origin: *"); // Replace * with specific frontend origin for production
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    http_response_code(200); // Respond with HTTP 200 OK
    exit();
}

$base_dir = __DIR__;

// Log incoming requests for debugging
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Routing Debug: REQUEST_URI = " . $_SERVER['REQUEST_URI']);

// Routing - Each case handles a specific API endpoint
switch ($request_uri[0]) {
    // User Management Endpoints
    case '/expresso-cafe/api/users':          
        include $base_dir . '/controllers/UserController.php'; // GET: Retrieve all users (admin only), PUT: Update user details 
        break;
    case '/expresso-cafe/api/users/login':    
        include $base_dir . '/controllers/UserController.php'; //POST: User login
        break;
    case '/expresso-cafe/api/users/create':   
        include $base_dir . '/controllers/UserController.php'; // POST: Create a new user with OTP
        break;
    case '/expresso-cafe/api/users/create-for-admin': // POST: a new user without OTP (admin only)
        include $base_dir . '/controllers/UserController.php';
        break;
    case '/expresso-cafe/api/users/verify-otp': // POST: Verify OTP for user account creation or password reset
        include $base_dir . '/controllers/UserController.php';
        break;
    case '/expresso-cafe/api/users/search':   // GET: Search for users (admin only)
        include $base_dir . '/controllers/UserController.php';
        break;
    case '/expresso-cafe/api/users/change-password': // PUT: Change user password
        include $base_dir . '/controllers/UserController.php';
        break;
    case '/expresso-cafe/api/users/change-password-admin': // PUT: Change user password by Admin
        include $base_dir . '/controllers/UserController.php';
        break;
    case '/expresso-cafe/api/users/generate-password-change-otp': // POST: Generate OTP for password change
        include $base_dir . '/controllers/UserController.php';
        break;
    case '/expresso-cafe/api/users/role': // GET: Retrieve users by role
        include $base_dir . '/controllers/UserController.php';
        break;

    // Item Management Endpoints
    case '/expresso-cafe/api/items':          
        include $base_dir . '/controllers/ItemController.php'; // GET: Retrieve all items, POST: Create a new item (admin only), PUT: Update an item (admin only)
        break;
    case '/expresso-cafe/api/items/search':   // GET: Search for items
        include $base_dir . '/controllers/ItemController.php';
        break;
    case '/expresso-cafe/api/items/category': // GET: Retrieve items by category
        include $base_dir . '/controllers/ItemController.php';
        break;
    case '/expresso-cafe/api/items/subcategory': // GET: Retrieve items by subcategory
        include $base_dir . '/controllers/ItemController.php';
        break;

    // Promo Management Endpoints
    case '/expresso-cafe/api/promos':         // GET: Retrieve all promos, POST: Create a new promo (admin only), PUT: Update a promo (admin only)
        include $base_dir . '/controllers/PromoController.php';
        break;
    case '/expresso-cafe/api/promos/search':  // GET: Search for promos
        include $base_dir . '/controllers/PromoController.php';
        break;
    case '/expresso-cafe/api/promos/by-is-active': // GET: Retrieve promos by is_active status
        include $base_dir . '/controllers/PromoController.php';
        break;

    // Order Management Endpoints
    case '/expresso-cafe/api/orders':   // PUT, GET: Retrieve all orders, POST: Create a new order (deprecated - use create-with-items)     
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/create':   // POST: Create a new order (deprecated - use create-with-items)
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/create-with-items': // POST: Create a new order with items
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/update-payment-status': // PUT: Update order payment status
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/update-order-status': // PUT: Update order status by reference number
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/search-by-reference-number': // GET: Search order by reference number
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/by-status': // GET: Retrieve orders by status
        include $base_dir . '/controllers/OrderController.php';
        break;
    case '/expresso-cafe/api/orders/by-type': // GET: Retrieve orders by order type
        include $base_dir . '/controllers/OrderController.php';
        break;

    // Order Item Management Endpoints
    case '/expresso-cafe/api/order_items':    // GET: Retrieve all order items, POST: Create a new order item (admin only)
        include $base_dir . '/controllers/OrderItemController.php';
        break;
    case '/expresso-cafe/api/order_items/search': // GET: Search for order items
        include $base_dir . '/controllers/OrderItemController.php';
        break;

    // Favorite Item Management Endpoints
    case '/expresso-cafe/api/favorites':     // GET: Retrieve favorite items, POST: Add a favorite item
        include $base_dir . '/controllers/FavoriteController.php';
        break;
    case '/expresso-cafe/api/favorites/search': // GET: Search for favorite items
        include $base_dir . '/controllers/FavoriteController.php';
        break;

    // Review Management Endpoints
    case '/expresso-cafe/api/reviews':        // GET: Retrieve reviews, POST: Create a new review
        include $base_dir . '/controllers/ReviewController.php';
        break;
    case '/expresso-cafe/api/reviews/search': // GET: Search for reviews
        include $base_dir . '/controllers/ReviewController.php';
        break;

    // Notification Management Endpoints
    case '/expresso-cafe/api/notifications': // GET: Retrieve notifications by email, POST: Create a new notification (admin only)
        include $base_dir . '/controllers/NotificationController.php';
        break;
    case '/expresso-cafe/api/notifications/search': // GET: Search for notifications (admin only)
        include $base_dir . '/controllers/NotificationController.php';
        break;

    // Table Tracker Endpoint
    case '/expresso-cafe/api/table_tracker': // GET: Retrieve table tracker data
        include $base_dir . '/controllers/TableTrackerController.php';
        break;

    // Table Reservation Management Endpoints
    case '/expresso-cafe/api/table_reservations': // GET: Retrieve all table reservations, POST: Create a new table reservation
        include $base_dir . '/controllers/TableReservationController.php';
        break;
    case '/expresso-cafe/api/table_reservations/search': // GET: Search for table reservations
        include $base_dir . '/controllers/TableReservationController.php';
        break;
    case '/expresso-cafe/api/table_reservations/verify': // POST: Verify a table reservation
        include $base_dir . '/controllers/TableReservationController.php';
        break;
    case '/expresso-cafe/api/table_reservations/update-payment': // PUT: Update table reservation payment status
        include $base_dir . '/controllers/TableReservationController.php';
        break;
    case '/expresso-cafe/api/table_reservations/bundle': // GET: Retrieve table reservations by bundle
        include $base_dir . '/controllers/TableReservationController.php';
        break;

    // Receipt Management Endpoints
    case '/expresso-cafe/api/receipts':      // GET: Retrieve all receipts,  POST: Create a new receipt (admin only)
        include $base_dir . '/controllers/ReceiptController.php';
        break;
    case '/expresso-cafe/api/receipts/search': // GET: Search for receipts
        include $base_dir . '/controllers/ReceiptController.php';
        break;
    case '/expresso-cafe/api/receipts/reservation': // GET: Retrieve receipt for a reservation
        include $base_dir . '/controllers/ReceiptController.php';
        break;
    case '/expresso-cafe/api/receipts/receipt-for': // GET: Retrieve receipts by receipt_for
        include $base_dir . '/controllers/ReceiptController.php';
        break;
    case '/expresso-cafe/api/receipts/search-by-email': // GET: Search receipts by email
        include $base_dir . '/controllers/ReceiptController.php';
        break;

   // Payment Processing Endpoints
    case '/expresso-cafe/api/process_payment': // POST: Process a payment (Stripe)
        $file_to_include = $base_dir . '/stripePayment/process_payment.php';
        if (!file_exists($file_to_include)) {
            http_response_code(404);
            echo json_encode(["message" => "Internal Server Error: File not found."]);
            exit();
        }
        require_once $file_to_include;
        break;

    case '/expresso-cafe/api/process_payment_order': // POST: Process a payment for orders (Stripe)
        $file_to_include = $base_dir . '/stripePayment/process_payment_order.php';
        if (!file_exists($file_to_include)) {
            http_response_code(404);
            echo json_encode(["message" => "Internal Server Error: File not found."]);
            exit();
        }
        require_once $file_to_include;
        break;

    // Default - Handle 404 Not Found
    default:
        error_log("No matching route for: " . $request_uri[0]);
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found."]);
        break;
}

?>
