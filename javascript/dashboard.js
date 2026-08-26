// Global add-to-cart function (used by inline onclick and event listeners)
function addToCart(btn) {
    var card = btn.closest('.product-card');
    if (card && card.dataset.status === 'Not Available') {
        var toast = document.getElementById('toast');
        if (toast) {
            toast.textContent = 'This product is not available.';
            toast.className = 'toast show error';
            setTimeout(function() { toast.className = 'toast'; }, 2500);
        }
        return;
    }
    var name = btn.dataset.name;
    var price = btn.dataset.price;
    var cart = JSON.parse(localStorage.getItem('ube_cart') || '[]');
    var existing = cart.find(function(item) { return item.name === name; });
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ name: name, price: price, qty: 1 });
    }
    localStorage.setItem('ube_cart', JSON.stringify(cart));

    // Visual feedback on button
    var original = btn.textContent;
    btn.textContent = 'Added! \u2713';
    btn.classList.add('added');
    setTimeout(function() {
        btn.textContent = original;
        btn.classList.remove('added');
    }, 1500);

    // Toast
    var toast = document.getElementById('toast');
    if (toast) {
        toast.textContent = name + ' added to cart!';
        toast.className = 'toast show success';
        setTimeout(function() { toast.className = 'toast'; }, 2500);
    }

    // Update cart badge
    updateCartBadge();
}

function updateCartBadge() {
    var cart = JSON.parse(localStorage.getItem('ube_cart') || '[]');
    var total = cart.reduce(function(sum, item) { return sum + item.qty; }, 0);
    var badge = document.getElementById('cartBadge');
    if (badge) {
        if (total > 0) {
            badge.textContent = total;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
});

// Also run immediately in case DOMContentLoaded already fired
updateCartBadge();
