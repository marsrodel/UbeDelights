(function() {
    'use strict';

    var toast = document.getElementById('toast');
    var checkoutItems = document.getElementById('checkoutItems');
    var btnPlaceOrder = document.getElementById('btnPlaceOrder');

    function showToast(message, type) {
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 2500);
    }

    function getCart() {
        return JSON.parse(localStorage.getItem('ube_cart') || '[]');
    }

    function saveCart(cart) {
        localStorage.setItem('ube_cart', JSON.stringify(cart));
    }

    function parsePrice(str) {
        return parseInt(str.replace(/[^0-9]/g, '')) || 0;
    }

    function formatPrice(num) {
        return '\u20B1' + num.toLocaleString();
    }

    function renderCheckoutItems() {
        var cart = getCart();
        if (cart.length === 0) {
            window.location.href = './cart.php';
            return;
        }

        checkoutItems.innerHTML = '';
        var subtotal = 0;

        cart.forEach(function(item) {
            var price = parsePrice(item.price);
            var lineTotal = price * item.qty;
            subtotal += lineTotal;

            var div = document.createElement('div');
            div.className = 'checkout-item';
            div.innerHTML =
                '<img src="../images/cake.png" alt="">' +
                '<div class="checkout-item-info">' +
                    '<div class="checkout-item-name">' + item.name + '</div>' +
                    '<div class="checkout-item-qty">Qty: ' + item.qty + '</div>' +
                '</div>' +
                '<div class="checkout-item-price">' + formatPrice(lineTotal) + '</div>';
            checkoutItems.appendChild(div);
        });

        var shipping = subtotal >= 500 ? 0 : 99;
        var total = subtotal + shipping;

        document.getElementById('coSubtotal').textContent = formatPrice(subtotal);
        document.getElementById('coShipping').textContent = shipping === 0 ? 'FREE' : formatPrice(shipping);
        document.getElementById('coTotal').textContent = formatPrice(total);
    }

    // Payment option selection
    var paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            paymentOptions.forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Place order
    if (btnPlaceOrder) {
        btnPlaceOrder.addEventListener('click', function() {
            var firstName = document.getElementById('coFirstName').value.trim();
            var lastName = document.getElementById('coLastName').value.trim();
            var street = document.getElementById('coStreet').value.trim();
            var barangay = document.getElementById('coBarangay').value.trim();
            var city = document.getElementById('coCity').value.trim();
            var province = document.getElementById('coProvince').value.trim();
            var zip = document.getElementById('coZip').value.trim();
            var email = document.getElementById('coEmail').value.trim();
            var phone = document.getElementById('coPhone').value.trim();

            if (!firstName || !lastName || !street || !barangay || !city || !province || !zip || !email || !phone) {
                showToast('Please fill in all required fields', 'error');
                return;
            }

            if (!/^\d{4}$/.test(zip)) {
                showToast('Please enter a valid 4-digit zip code', 'error');
                return;
            }

            if (!/^09\d{9}$/.test(phone.replace(/\s/g, ''))) {
                showToast('Please enter a valid phone number (09XXXXXXXXX)', 'error');
                return;
            }

            var cart = getCart();
            if (cart.length === 0) {
                showToast('Your cart is empty', 'error');
                return;
            }

            var payment = document.querySelector('input[name="payment"]:checked').value;
            var paymentLabels = { cod: 'Cash on Delivery', gcash: 'GCash', maya: 'Maya (PayMaya)' };
            var notes = document.getElementById('coNotes').value.trim();

            // Generate order ID
            var orderId = 'UBD-' + Date.now().toString(36).toUpperCase();

            // Build order object
            var subtotal = 0;
            cart.forEach(function(item) {
                subtotal += parsePrice(item.price) * item.qty;
            });
            var shipping = subtotal >= 500 ? 0 : 99;

            var order = {
                id: orderId,
                items: cart,
                subtotal: subtotal,
                shipping: shipping,
                total: subtotal + shipping,
                payment: paymentLabels[payment] || payment,
                notes: notes,
                address: {
                    firstName: firstName,
                    lastName: lastName,
                    street: street,
                    barangay: barangay,
                    city: city,
                    province: province,
                    zip: zip,
                    email: email,
                    phone: phone
                },
                status: 'Pending',
                date: new Date().toISOString()
            };

            // Save order to localStorage
            var orders = JSON.parse(localStorage.getItem('ube_orders') || '[]');
            orders.unshift(order);
            localStorage.setItem('ube_orders', JSON.stringify(orders));

            // Clear cart
            saveCart([]);

            // Show confirmation
            showConfirmation(orderId);
        });
    }

    function showConfirmation(orderId) {
        var overlay = document.createElement('div');
        overlay.className = 'confirmation-overlay';
        overlay.innerHTML =
            '<div class="confirmation-modal">' +
                '<div class="confirmation-icon"><i class="fa-solid fa-check"></i></div>' +
                '<h2>Order Placed Successfully!</h2>' +
                '<p>Thank you for your order. We\'ll start preparing it right away.</p>' +
                '<div class="confirmation-order-id">' + orderId + '</div>' +
                '<p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:20px;">A confirmation email will be sent to you shortly.</p>' +
                '<div class="confirmation-actions">' +
                    '<a onclick="getOrders()" class="btn-primary">View My Orders</a>' +
                    '<a onclick="getShop()" class="btn-secondary">Continue Shopping</a>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        requestAnimationFrame(function() {
            overlay.classList.add('show');
        });
    }

    renderCheckoutItems();
})();
