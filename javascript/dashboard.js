(function() {
    'use strict';

    const categoryCards = document.querySelectorAll('.category-card');
    const productCards = document.querySelectorAll('.product-card');
    const noResults = document.getElementById('noResults');
    const toast = document.getElementById('toast');

    let activeCategory = null;

    function showToast(message, type) {
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() {
            toast.classList.add('show');
        });
        setTimeout(function() {
            toast.classList.remove('show');
        }, 2500);
    }

    function filterProducts() {
        var visibleCount = 0;

        productCards.forEach(function(card) {
            var category = card.dataset.category;
            var matchesCategory = activeCategory === null || category === activeCategory;

            if (matchesCategory) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? '' : 'none';
    }

    categoryCards.forEach(function(card) {
        card.addEventListener('click', function() {
            var category = this.dataset.category;

            if (activeCategory === category) {
                activeCategory = null;
                this.classList.remove('active');
            } else {
                categoryCards.forEach(function(c) {
                    c.classList.remove('active');
                });
                activeCategory = category;
                this.classList.add('active');
            }

            filterProducts();
        });
    });

    document.querySelectorAll('.btn-add-cart').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var name = this.dataset.name;
            var self = this;

            self.classList.add('added');
            self.textContent = '✓ Added';
            showToast(name + ' added to cart!', 'success');

            setTimeout(function() {
                self.classList.remove('added');
                self.textContent = 'Add to Cart';
            }, 2000);
        });
    });

    document.querySelectorAll('.btn-wishlist').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var name = this.dataset.name;

            if (this.classList.contains('active')) {
                this.classList.remove('active');
                this.textContent = '♡';
                showToast(name + ' removed from wishlist');
            } else {
                this.classList.add('active');
                this.textContent = '♥';
                showToast(name + ' added to wishlist!', 'success');
            }
        });
    });
})();
