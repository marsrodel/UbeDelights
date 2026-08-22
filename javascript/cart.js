(function() {
    'use strict';

    var emptyCart = document.getElementById('emptyCart');
    var cartList = document.getElementById('cartList');
    var cartItemsList = document.getElementById('cartItemsList');
    var cartSummary = document.getElementById('cartSummary');
    var toast = document.getElementById('toast');

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
        return '₱' + num.toLocaleString();
    }

    function getItemImage(itemName) {
        var imageMap = {
            'Ube Cheesecake': '../images/items/cheesecake.jpg',
            'Ube Roll': '../images/items/uberoll.jpg',
            'Ube Crinkles': '../images/items/crinkles.jpg',
            'Ube Halo-Halo': '../images/items/halohalo.jpg',
            'Classic Ube Cake': '../images/items/classic.jpg',
            'Ube Pandesal': '../images/items/pandesal.jpg',
            'Ube Latte': '../images/items/latte.jpg',
            'Ube Macapuno Cake': '../images/items/macapuno.jpg'
        };

        return imageMap[itemName] || '../images/cake.png';
    }

    var cartLayout = document.querySelector('.cart-layout');

    function renderCart() {
        var cart = getCart();
        if (cart.length === 0) {
            emptyCart.style.display = '';
            cartList.style.display = 'none';
            cartSummary.style.display = 'none';
            cartLayout.classList.add('empty');
            return;
        }

        emptyCart.style.display = 'none';
        cartList.style.display = '';
        cartSummary.style.display = '';
        cartLayout.classList.remove('empty');
        var subtotal = 0;

        cart.forEach(function(item, index) {
            var price = parsePrice(item.price);
            var lineTotal = price * item.qty;
            subtotal += lineTotal;

            var row = document.createElement('div');
            row.className = 'cart-item-row';
            row.innerHTML =
                '<div class="col-product">' +
                    '<img src="' + getItemImage(item.name) + '" class="item-thumb" alt="">' +
                    '<span>' + item.name + '</span>' +
                '</div>' +
                '<span class="col-price">' + item.price + '</span>' +
                '<div class="col-quantity">' +
                    '<button class="qty-btn" data-action="decrease" data-index="' + index + '">−</button>' +
                    '<span class="qty-value">' + item.qty + '</span>' +
                    '<button class="qty-btn" data-action="increase" data-index="' + index + '">+</button>' +
                '</div>' +
                '<span class="col-subtotal">' + formatPrice(lineTotal) + '</span>' +
                '<button class="col-action btn-remove" data-index="' + index + '">✕</button>';
            cartItemsList.appendChild(row);
        });

        var shipping = subtotal >= 500 ? 0 : 99;
        var total = subtotal + shipping;

        document.getElementById('summarySubtotal').textContent = formatPrice(subtotal);
        document.getElementById('summaryShipping').textContent = shipping === 0 ? 'FREE' : formatPrice(shipping);
        document.getElementById('summaryTotal').textContent = formatPrice(total);

        cartItemsList.querySelectorAll('.qty-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.index);
                var action = this.dataset.action;
                var cart = getCart();
                if (action === 'increase') {
                    cart[idx].qty += 1;
                } else if (action === 'decrease') {
                    cart[idx].qty -= 1;
                    if (cart[idx].qty <= 0) cart.splice(idx, 1);
                }
                saveCart(cart);
                renderCart();
            });
        });

        cartItemsList.querySelectorAll('.btn-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.index);
                var cart = getCart();
                var name = cart[idx].name;
                cart.splice(idx, 1);
                saveCart(cart);
                renderCart();
                showToast(name + ' removed from cart');
            });
        });
    }

    var btnCheckout = document.getElementById('btnCheckout');
    if (btnCheckout) {
        btnCheckout.addEventListener('click', function() {
            window.location.href = './checkout.php';
        });
    }

    renderCart();
})();
