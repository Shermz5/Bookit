<?php
session_start();
require 'db_connect.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Redirect to login if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$listing_id = $_POST['listing_id'] ?? $_GET['listing_id'] ?? null;
$booking_id = $_POST['booking_id'] ?? $_GET['booking_id'] ?? null;

if ($listing_id === null) {
    die("Listing ID is missing.");
}
if ($booking_id === null) {
    die("Booking ID is missing.");
}

// Fetch the host_id (owner_id) from the listings table based on listing_id
$stmt = $conn->prepare("SELECT host_id FROM listings WHERE id = ?");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$stmt->bind_result($owner_id);
if (!$stmt->fetch()) {
    $stmt->close();
    die("Listing not found or invalid.");
}
$stmt->close();

// If form is submitted to process the payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount']) && isset($_POST['method_name'])) {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }
    $amount = floatval($_POST['amount']);
    $payment_method_name = trim($_POST['method_name']);

    // Basic server-side validation for amount
    if ($amount <= 0) {
        echo "<script>alert('Invalid payment amount.'); window.location.href = 'payment_method.php?listing_id=" . htmlspecialchars($listing_id) . "&booking_id=" . htmlspecialchars($booking_id) . "';</script>";
        exit;
    }

    // Get payment_method_id from payment_methods table based on name
    $stmt = $conn->prepare("SELECT id FROM payment_methods WHERE method_name = ?");
    $stmt->bind_param("s", $payment_method_name);
    $stmt->execute();
    $stmt->bind_result($payment_method_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        echo "<script>alert('Invalid payment method.'); window.location.href = 'payment_method.php?listing_id=" . htmlspecialchars($listing_id) . "&booking_id=" . htmlspecialchars($booking_id) . "';</script>";
        exit;
    }
    $stmt->close();

    // Insert payment record (id is auto-increment/transaction_id)
    $stmt = $conn->prepare("INSERT INTO payment_records (user_id, listing_id, owner_id, payment_method_id, amount, created_at, booking_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iiiisi", $user_id, $listing_id, $owner_id, $payment_method_id, $amount, $booking_id);

    if ($stmt->execute()) {
        echo "<script>alert('Payment recorded successfully'); window.location.href = 'tenant_bookings.php';</script>";
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4f46e5',secondary:'#6366f1'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <noscript>
        <div class="bg-red-100 text-red-700 p-4 text-center">This page requires JavaScript to function properly. Please enable JavaScript in your browser.</div>
    </noscript>
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="container">
                <div class="header-inner">
                    <div class="logo">
                        <a href="home.php">
                            <span class="logo-text">Bookit</span>
                        </a>
                    </div>
                    
                    <nav class="desktop-nav">
                        <a href="home.php" class="nav-link">Home</a>
                        <a href="explore.php" class="nav-link">Explore</a>
                        <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    </nav>
                    
                    <div class="auth-buttons">
                        <button class="btn btn-ghost" onclick="window.location.href='logout.php'">Logout</button>
                    </div>
    
                    <div class="flex items-center text-gray-600">
                        <div class="w-5 h-5 flex items-center justify-center mr-1">
                            <i class="ri-lock-line"></i>
                        </div>
                        <span class="text-sm">Secure Payment</span>
                    </div>
                </div>
                <button class="mobile-menu-button" id="mobileMenuButton">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>  
        </div>
    </header>

    <form method="POST" action="payment_method.php" id="payment-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <main class="max-w-3xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id) ?>">
                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id) ?>">
                <input type="hidden" name="method_name" id="selected-payment-method-name" value="">
                <h1 class="text-3xl font-bold text-gray-900">Payment Methods</h1>
                <p class="mt-2 text-gray-600">Choose your preferred payment method to complete your booking</p>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden mb-8">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <div class="payment-option flex-1 border rounded-lg p-4 cursor-pointer hover:border-primary transition-colors" data-method="mastercard">
                            <div class="flex items-center">
                                <div class="w-6 h-6 flex items-center justify-center mr-3">
                                    <i class="ri-mastercard-fill text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">Mastercard</h3>
                                    <p class="text-sm text-gray-500">Pay with credit or debit card</p>
                                </div>
                                <div class="payment-radio w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                    <div class="payment-radio-dot hidden w-3 h-3 bg-primary rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="payment-option flex-1 border rounded-lg p-4 cursor-pointer hover:border-primary transition-colors" data-method="ecocash">
                            <div class="flex items-center">
                                <div class="w-6 h-6 flex items-center justify-center mr-3">
                                    <i class="ri-wallet-3-fill text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">EcoCash</h3>
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-500 mr-2">Mobile money</span>
                                    </div>
                                </div>
                                <div class="payment-radio w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                    <div class="payment-radio-dot hidden w-3 h-3 bg-primary rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="mastercard-form" class="payment-form hidden">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Card Details</h3>
                                <div class="flex items-center text-sm text-gray-600">
                                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                                        <i class="ri-lock-line"></i>
                                    </div>
                                    <span>SSL Encrypted</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label for="card-number" class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                                    <div class="relative">
                                        <input type="text" id="card-number" name="card_number" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center">
                                            <i class="ri-mastercard-fill text-xl"></i>
                                        </div>
                                    </div>
                                    <div class="hidden error-message text-sm text-red-600 mt-1"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="expiry-date" class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                                        <input type="text" id="expiry-date" name="expiry_date" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="MM/YY" maxlength="5" required>
                                        <div class="hidden error-message text-sm text-red-600 mt-1"></div>
                                    </div>
                                    <div>
                                        <label for="cvv" class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                        <div class="relative">
                                            <input type="text" id="cvv" name="cvv" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="123" maxlength="3" required>
                                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center text-gray-400 cursor-help" id="cvv-info">
                                                <i class="ri-question-line"></i>
                                            </div>
                                        </div>
                                        <div class="hidden error-message text-sm text-red-600 mt-1"></div>
                                    </div>
                                </div>
                                <div>
                                    <label for="cardholder-name" class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                                    <input type="text" id="cardholder-name" name="cardholder_name" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="Name as it appears on card" required>
                                    <div class="hidden error-message text-sm text-red-600 mt-1"></div>
                                </div>
                                <div>
                                    <label for="amount-mastercard" class="block text-sm font-medium text-gray-700 mb-1">Amount to Pay</label>
                                    <div class="relative">
                                        <input type="number" name="amount" step="0.01" id="amount-mastercard" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="Enter amount" required>
                                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </div>
                                    </div>
                                    <div class="hidden error-message text-sm text-red-600 mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-6">
                            <button type="submit" id="pay-button-mastercard" class="w-full bg-primary text-white py-3 px-6 rounded-button font-medium hover:bg-primary/90">
                                <span id="pay-button-text-mastercard">Pay Now</span>
                                <span id="pay-button-loading-mastercard" class="hidden flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>

                    <div id="ecocash-form" class="payment-form hidden">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">EcoCash Payment</h3>
                                <div class="flex items-center text-sm text-gray-600">
                                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                                        <i class="ri-lock-line"></i>
                                    </div>
                                    <span>Secure Payment</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label for="phone-number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <div class="relative">
                                        <input type="tel" id="phone-number" name="phone_number" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="07X XXX XXXX" maxlength="10" required>
                                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center">
                                            <i class="ri-phone-line"></i>
                                        </div>
                                    </div>
                                    <div class="hidden error-message text-sm text-red-600 mt-1"></div>
                                </div>
                                <div>
                                    <label for="amount-ecocash" class="block text-sm font-medium text-gray-700 mb-1">Amount to Pay</label>
                                    <div class="relative">
                                        <input type="number" name="amount" step="0.01" id="amount-ecocash" class="w-full px-4 py-3 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 placeholder-gray-400" placeholder="Enter amount" required>
                                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-6">
                            <button type="submit" id="pay-button-ecocash" class="w-full bg-primary text-white py-3 px-6 rounded-button font-medium hover:bg-primary/90">
                                <span id="pay-button-text-ecocash">Pay with EcoCash</span>
                                <span id="pay-button-loading-ecocash" class="hidden flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center text-sm text-gray-500">
                <p>Need help? <a href="#" class="text-primary hover:text-primary/80">Contact our support team</a></p>
            </div>
        </main>
    </form>

    <script>
        // Global JS error handler
        window.onerror = function(message, source, lineno, colno, error) {
            alert('JavaScript Error: ' + message + '\nSource: ' + source + '\nLine: ' + lineno + ', Column: ' + colno);
            console.error('Global JS Error:', message, source, lineno, colno, error);
        };

        function toggleFormFields(method) {
            // Mastercard fields
            document.querySelectorAll('#mastercard-form input').forEach(input => {
                input.disabled = (method !== 'mastercard');
            });
            // EcoCash fields
            document.querySelectorAll('#ecocash-form input').forEach(input => {
                input.disabled = (method !== 'ecocash');
            });
        }

        // Ensure payment_method_name is set on click
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('border-primary'));
                option.classList.add('border-primary');
                const method = option.dataset.method;
                document.getElementById('mastercard-form').classList.toggle('hidden', method !== "mastercard");
                document.getElementById('ecocash-form').classList.toggle('hidden', method !== "ecocash");
                document.getElementById('selected-payment-method-name').value = method;
                toggleFormFields(method);
                console.log('Payment option selected:', method);
            });
        });

        // Set default method on load
        document.addEventListener('DOMContentLoaded', function() {
            const initialSelectedMethod = document.querySelector('.payment-option.border-primary')?.dataset.method || 'mastercard';
            document.getElementById('selected-payment-method-name').value = initialSelectedMethod;
            document.getElementById('mastercard-form').classList.toggle('hidden', initialSelectedMethod !== "mastercard");
            document.getElementById('ecocash-form').classList.toggle('hidden', initialSelectedMethod !== "ecocash");
            toggleFormFields(initialSelectedMethod);
            console.log('Page loaded. Initial payment method:', initialSelectedMethod);

            // Debug: Log validation and button state
            const payButtonMastercard = document.getElementById('pay-button-mastercard');
            const payButtonEcocash = document.getElementById('pay-button-ecocash');
            if (payButtonMastercard) {
                payButtonMastercard.addEventListener('click', function() {
                    console.log('Mastercard pay button clicked. Disabled:', payButtonMastercard.disabled);
                });
            }
            if (payButtonEcocash) {
                payButtonEcocash.addEventListener('click', function() {
                    console.log('EcoCash pay button clicked. Disabled:', payButtonEcocash.disabled);
                });
            }
        });

        // Debug: Alert and log on form submit
        document.getElementById('payment-form').addEventListener('submit', function(event) {
            const selectedMethod = document.getElementById('selected-payment-method-name').value;
            console.log('Form submit triggered. Selected method:', selectedMethod);
            // alert('Form is submitting! Method: ' + selectedMethod);
        });
    </script>

    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <a href="home.php" class="footer-logo">Bookit</a>
                    <p class="footer-description">
                        Making property rental easy and accessible for everyone.
                    </p>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="home.php">Home</a></li>
                        <li><a href="explore.php">Explore</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="submit.html">List Property</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Get in Touch</h3>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© 2025 Bookit. All rights reserved.</p>
                <div class="flex items-center space-x-6">
                    <div class="w-6 h-6 flex items-center justify-center text-gray-500">
                        <i class="ri-visa-line"></i>
                    </div>
                    <div class="w-6 h-6 flex items-center justify-center text-gray-500">
                        <i class="ri-mastercard-line"></i>
                    </div>
                    <div class="w-6 h-6 flex items-center justify-center text-gray-500">
                        <i class="ri-paypal-line"></i>
                    </div>
                    <div class="w-6 h-6 flex items-center justify-center text-gray-500">
                        <i class="ri-secure-payment-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>

        <div id="cvv-tooltip" class="hidden absolute bg-gray-800 text-white text-xs rounded py-2 px-3 max-w-xs">
        The CVV is the 3-digit security code on the back of your card
    </div>

    <script id="card-form-validation">
        document.addEventListener('DOMContentLoaded', function() {
            const mastercardForm = document.getElementById('mastercard-form');
            const ecocashForm = document.getElementById('ecocash-form');

            const cardNumberInput = mastercardForm.querySelector('#card-number');
            const expiryDateInput = mastercardForm.querySelector('#expiry-date');
            const cvvInput = mastercardForm.querySelector('#cvv');
            const cardholderNameInput = mastercardForm.querySelector('#cardholder-name');
            const amountMastercardInput = mastercardForm.querySelector('#amount-mastercard');
            const payButtonMastercard = document.getElementById('pay-button-mastercard');
            const payButtonTextMastercard = document.getElementById('pay-button-text-mastercard');
            const payButtonLoadingMastercard = document.getElementById('pay-button-loading-mastercard');

            const phoneNumberInput = ecocashForm.querySelector('#phone-number');
            const amountEcocashInput = ecocashForm.querySelector('#amount-ecocash');
            const payButtonEcocash = document.getElementById('pay-button-ecocash');
            const payButtonTextEcocash = document.getElementById('pay-button-text-ecocash');
            const payButtonLoadingEcocash = document.getElementById('pay-button-loading-ecocash');

            // Set the default visible form (if any) and update the hidden input
            const initialSelectedMethod = document.querySelector('.payment-option.border-primary')?.dataset.method || 'mastercard'; // Default to mastercard
            document.getElementById('selected-payment-method-name').value = initialSelectedMethod;
            document.getElementById('mastercard-form').classList.toggle('hidden', initialSelectedMethod !== "mastercard");
            document.getElementById('ecocash-form').classList.toggle('hidden', initialSelectedMethod !== "ecocash");


            // Format card number with spaces
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    let formattedValue = '';
                    for (let i = 0; i < value.length; i++) {
                        if (i > 0 && i % 4 === 0) {
                            formattedValue += ' ';
                        }
                        formattedValue += value[i];
                    }
                    e.target.value = formattedValue;
                    validateMastercardForm();
                });
            }

            // Format expiry date with slash
            if (expiryDateInput) {
                expiryDateInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 2) {
                        e.target.value = value.substring(0, 2) + '/' + value.substring(2);
                    } else {
                        e.target.value = value;
                    }
                    validateMastercardForm();
                });
            }

            // Validate CVV (numbers only)
            if (cvvInput) {
                cvvInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '');
                    validateMastercardForm();
                });
            }

            // Validate cardholder name
            if (cardholderNameInput) {
                cardholderNameInput.addEventListener('input', validateMastercardForm);
            }

            // Validate amount for mastercard
            if (amountMastercardInput) {
                amountMastercardInput.addEventListener('input', validateMastercardForm);
            }

            // Validate phone number for ecocash
            if (phoneNumberInput) {
                phoneNumberInput.addEventListener('input', validateEcocashForm);
            }

            // Validate amount for ecocash
            if (amountEcocashInput) {
                amountEcocashInput.addEventListener('input', validateEcocashForm);
            }

            // CVV tooltip
            const cvvInfo = document.getElementById('cvv-info');
            const cvvTooltip = document.getElementById('cvv-tooltip');
            if (cvvInfo && cvvTooltip) {
                cvvInfo.addEventListener('mouseenter', function(e) {
                    cvvTooltip.classList.remove('hidden');
                    const rect = cvvInfo.getBoundingClientRect();
                    cvvTooltip.style.top = (rect.top - cvvTooltip.offsetHeight - 5) + 'px'; // Adjust position
                    cvvTooltip.style.left = (rect.left + (rect.width / 2) - (cvvTooltip.offsetWidth / 2)) + 'px'; // Center horizontally
                });
                cvvInfo.addEventListener('mouseleave', function() {
                    cvvTooltip.classList.add('hidden');
                });
            }

            // Form validation for Mastercard
            function validateMastercardForm() {
                if (mastercardForm.classList.contains('hidden')) return; // Only validate if visible

                const cardNumber = cardNumberInput.value.replace(/\s/g, '');
                const expiryDate = expiryDateInput.value;
                const cvv = cvvInput.value;
                const cardholderName = cardholderNameInput.value.trim();
                const amount = parseFloat(amountMastercardInput.value);
                let isValid = true;

                // Validate card number (simple validation - should be 16 digits)
                if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                    isValid = false;
                    showError(cardNumberInput, 'Please enter a valid 16-digit card number');
                } else {
                    hideError(cardNumberInput);
                }

                // Validate expiry date (MM/YY format)
                if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                    isValid = false;
                    showError(expiryDateInput, 'Please enter a valid expiry date (MM/YY)');
                } else {
                    const [month, year] = expiryDate.split('/');
                    const currentDate = new Date();
                    const currentYear = currentDate.getFullYear() % 100;
                    const currentMonth = currentDate.getMonth() + 1;
                    if (parseInt(month) < 1 || parseInt(month) > 12) {
                        isValid = false;
                        showError(expiryDateInput, 'Invalid month');
                    } else if (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                        isValid = false;
                        showError(expiryDateInput, 'Card has expired');
                    } else {
                        hideError(expiryDateInput);
                    }
                }

                // Validate CVV (3 digits)
                if (cvv.length !== 3 || !/^\d+$/.test(cvv)) {
                    isValid = false;
                    showError(cvvInput, 'CVV must be 3 digits');
                } else {
                    hideError(cvvInput);
                }

                // Validate cardholder name
                if (cardholderName.length < 3) {
                    isValid = false;
                    showError(cardholderNameInput, 'Please enter the cardholder name');
                } else {
                    hideError(cardholderNameInput);
                }

                // Validate amount
                if (isNaN(amount) || amount <= 0) {
                    isValid = false;
                    showError(amountMastercardInput, 'Please enter a valid amount');
                } else {
                    hideError(amountMastercardInput);
                }

                payButtonMastercard.disabled = !isValid;
                return isValid;
            }

            // Form validation for EcoCash
            function validateEcocashForm() {
                if (ecocashForm.classList.contains('hidden')) return; // Only validate if visible

                const phoneNumber = phoneNumberInput.value.trim();
                const amount = parseFloat(amountEcocashInput.value);
                let isValid = true;

                // Simple phone number validation (e.g., starts with 07 and 10 digits)
                if (!/^07\d{8}$/.test(phoneNumber)) {
                    isValid = false;
                    showError(phoneNumberInput, 'Please enter a valid 10-digit phone number starting with 07');
                } else {
                    hideError(phoneNumberInput);
                }

                // Validate amount
                if (isNaN(amount) || amount <= 0) {
                    isValid = false;
                    showError(amountEcocashInput, 'Please enter a valid amount');
                } else {
                    hideError(amountEcocashInput);
                }

                payButtonEcocash.disabled = !isValid;
                return isValid;
            }


            function showError(inputElement, message) {
                const errorElement = inputElement.nextElementSibling; // Assuming error message is next sibling
                if (errorElement && errorElement.classList.contains('error-message')) {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                    inputElement.classList.add('border-red-500');
                } else {
                    // If the next sibling is not the error message div, create one.
                    // This scenario might happen if the HTML structure changes or is unexpected.
                    const newErrorElement = document.createElement('div');
                    newErrorElement.className = 'error-message text-sm text-red-600 mt-1';
                    newErrorElement.textContent = message;
                    inputElement.parentNode.insertBefore(newErrorElement, inputElement.nextSibling);
                    inputElement.classList.add('border-red-500');
                }
            }

            function hideError(inputElement) {
                const errorElement = inputElement.nextElementSibling;
                if (errorElement && errorElement.classList.contains('error-message')) {
                    errorElement.classList.add('hidden');
                    errorElement.textContent = ''; // Clear message
                    inputElement.classList.remove('border-red-500');
                }
            }
            
            // Initial validation call when the page loads, to set the initial button state
            validateMastercardForm();
            validateEcocashForm();

            // Handle form submission (using native form submission as per PHP)
            document.querySelector('form').addEventListener('submit', function(event) {
                const selectedMethodName = document.getElementById('selected-payment-method-name').value;
                let isValidForm = false;

                if (selectedMethodName === 'mastercard') {
                    isValidForm = validateMastercardForm();
                } else if (selectedMethodName === 'ecocash') {
                    isValidForm = validateEcocashForm();
                }

                if (!isValidForm) {
                    event.preventDefault(); // Prevent form submission if validation fails
                    alert('Please correct the errors in the payment form.');
                } else {
                    // Show loading state for the specific button
                    if (selectedMethodName === 'mastercard') {
                        payButtonTextMastercard.classList.add('hidden');
                        payButtonLoadingMastercard.classList.remove('hidden');
                        payButtonMastercard.disabled = true;
                    } else if (selectedMethodName === 'ecocash') {
                        payButtonTextEcocash.classList.add('hidden');
                        payButtonLoadingEcocash.classList.remove('hidden');
                        payButtonEcocash.disabled = true;
                    }
                }
            });

            // Re-validate forms when payment options are clicked
            document.querySelectorAll('.payment-option').forEach(option => {
                option.addEventListener('click', () => {
                    const method = option.dataset.method;
                    if (method === 'mastercard') {
                        validateMastercardForm();
                    } else if (method === 'ecocash') {
                        validateEcocashForm();
                    }
                });
            });
        });
    </script>
</body>
</html>