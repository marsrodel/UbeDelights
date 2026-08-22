(function() {
    'use strict';

    var ordersList = document.getElementById('ordersList');
    var emptyOrders = document.getElementById('emptyOrders');
    var filterBtns = document.querySelectorAll('.filter-btn');
    var toast = document.getElementById('toast');

    function showToast(message, type) {
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 2500);
    }

    function parsePrice(str) {
        return parseInt(String(str).replace(/[^0-9]/g, '')) || 0;
    }

    function formatPrice(num) {
        return '\u20B1' + num.toLocaleString();
    }

    function formatDate(isoStr) {
        var d = new Date(isoStr);
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
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

    // Render localStorage orders (placed via checkout)
    function renderLocalOrders() {
        var localOrders = JSON.parse(localStorage.getItem('ube_orders') || '[]');
        if (localOrders.length === 0) return;

        localOrders.forEach(function(order) {
            var card = document.createElement('div');
            card.className = 'order-card';
            card.dataset.status = order.status.toLowerCase();

            var itemsHtml = '';
            order.items.forEach(function(item) {
                itemsHtml +=
                    '<div class="order-item">' +
                        '<img src="' + getItemImage(item.name) + '" alt="" class="item-thumb">' +
                        '<div class="item-details">' +
                            '<span class="item-name">' + item.name + '</span>' +
                            '<span class="item-qty">Qty: ' + item.qty + '</span>' +
                        '</div>' +
                        '<span class="item-price">' + item.price + '</span>' +
                    '</div>';
            });

            card.innerHTML =
                '<div class="order-header">' +
                    '<div class="order-meta">' +
                        '<span class="order-id">' + order.id + '</span>' +
                        '<span class="order-date">' + formatDate(order.date) + '</span>' +
                    '</div>' +
                    '<span class="status-badge status-' + order.status.toLowerCase() + '">' + order.status + '</span>' +
                '</div>' +
                '<div class="order-items">' + itemsHtml + '</div>' +
                '<div class="order-footer">' +
                    '<span class="order-total">Total: <strong>' + formatPrice(order.total) + '</strong></span>' +
                    '<button class="btn-reorder" data-order="' + order.id + '">Reorder</button>' +
                '</div>';

            ordersList.insertBefore(card, ordersList.firstChild);
        });

        // Rebind reorder buttons
        bindReorderButtons();
        bindFilterButtons();
    }

    function bindFilterButtons() {
        var cards = document.querySelectorAll('.order-card');
        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                filterBtns.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                var status = this.dataset.status;
                cards.forEach(function(card) {
                    if (status === 'all' || card.dataset.status === status) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    function bindReorderButtons() {
        document.querySelectorAll('.btn-reorder').forEach(function(btn) {
            btn.addEventListener('click', function() {
                showToast('Reorder feature coming soon!');
            });
        });
    }

    function checkEmpty() {
        var cards = document.querySelectorAll('.order-card');
        if (cards.length === 0) {
            emptyOrders.style.display = '';
            ordersList.style.display = 'none';
        } else {
            emptyOrders.style.display = 'none';
            ordersList.style.display = '';
        }
    }

    renderLocalOrders();
    bindFilterButtons();
    bindReorderButtons();
    checkEmpty();
})();
