(function() {
    'use strict';

    var tabBtns = document.querySelectorAll('.tab-btn');
    var productCards = document.querySelectorAll('.product-card');
    var noResults = document.getElementById('noResults');
    var toast = document.getElementById('toast');

    function showToast(message, type) {
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 2500);
    }

    function filterProducts(category) {
        var visibleCount = 0;
        productCards.forEach(function(card) {
            var cardCategory = card.dataset.category;
            if (category === 'all' || cardCategory === category) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            tabBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            filterProducts(this.dataset.category);
        });
    });

    document.querySelectorAll('.btn-add-cart').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var card = this.closest('.product-card');
            if (card && card.dataset.status === 'Not Available') {
                showToast('This product is not available.', 'error');
                return;
            }
            var id = this.dataset.id || '';
            var name = this.dataset.name;
            var price = this.dataset.price;
            var image = this.dataset.image || '';
            var cart = JSON.parse(localStorage.getItem('ube_cart') || '[]');
            var existing = cart.find(function(item) { return item.name === name; });
            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({ id: id, name: name, price: price, qty: 1, image: image });
            }
            localStorage.setItem('ube_cart', JSON.stringify(cart));

            var original = this.textContent;
            this.textContent = 'Added! \u2713';
            this.classList.add('added');
            var self = this;
            setTimeout(function() {
                self.textContent = original;
                self.classList.remove('added');
            }, 1500);

            showToast(name + ' added to cart!', 'success');
            updateCartBadge();
        });
    });
})();
