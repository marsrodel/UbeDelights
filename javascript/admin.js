(function() {
    'use strict';

    var toast = document.getElementById('toast');
    var modal = document.getElementById('productModal');
    var modalForm = document.getElementById('productForm');
    var imageInput = document.getElementById('productImage');
    var imagePreview = document.getElementById('imagePreview');
    var previewPlaceholder = document.querySelector('.preview-placeholder');
    var productsGrid = document.getElementById('productsGrid');
    var nextProductId = 8;

    function showToast(message, type) {
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 2500);
    }
    window.adminToast = showToast;

    // ===== FILTER TABS (Orders & Products) =====
    var filterBtns = document.querySelectorAll('.filter-btn');
    var filterItems = document.querySelectorAll('.order-card, .admin-product-card');
    var emptyState = document.getElementById('ordersEmpty') || document.getElementById('productsEmpty');

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var key = this.dataset.status || this.dataset.category;
            var visible = 0;
            filterItems.forEach(function(item) {
                if (key === 'all' || item.dataset.status === key || item.dataset.category === key) {
                    item.style.display = '';
                    visible++;
                } else {
                    item.style.display = 'none';
                }
            });
            if (emptyState) {
                emptyState.style.display = visible === 0 ? '' : 'none';
            }
        });
    });

    // ===== IMAGE PREVIEW =====
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            var file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    if (previewPlaceholder) previewPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
                var ph = document.querySelector('.preview-placeholder');
                if (ph) ph.style.display = 'block';
            }
        });
    }

    // ===== MODAL HANDLING =====
    var btnAddProduct = document.getElementById('btnAddProduct');
    var modalClose = document.getElementById('modalClose');
    var modalCancel = document.getElementById('modalCancel');
    var modalSubmit = document.getElementById('modalSubmit');
    var modalForm = document.getElementById('productForm');
    var productIdInput = document.getElementById('productId');
    var modalTitle = document.getElementById('modalTitle');
    var modalSubmitBtn = document.getElementById('modalSubmit');
    var productsGrid = document.getElementById('productsGrid');

    function openModal(editCard) {
        modalForm.reset();
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        var ph = document.querySelector('.preview-placeholder');
        if (ph) ph.style.display = 'block';
        productIdInput.value = '';

        if (editCard) {
            modalTitle.textContent = 'Edit Product';
            modalSubmitBtn.textContent = 'Save Changes';
            productIdInput.value = editCard.dataset.id;
            document.getElementById('productName').value = editCard.dataset.name;
            document.getElementById('productDesc').value = editCard.dataset.desc;
            document.getElementById('productType').value = editCard.dataset.type;
            document.getElementById('productStatus').value = editCard.dataset.status;
            document.getElementById('productPrice').value = editCard.dataset.price;
            if (editCard.dataset.image) {
                imagePreview.src = editCard.dataset.image;
                imagePreview.style.display = 'block';
                var ph = document.querySelector('.preview-placeholder');
                if (ph) ph.style.display = 'none';
            }
        } else {
            modalTitle.textContent = 'Add Product';
            modalSubmitBtn.textContent = 'Add Product';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        // Focus first input
        setTimeout(function() {
            document.getElementById('productName').focus();
        }, 100);
    }

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Open Add modal
    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', function() {
            openModal(null);
        });
    }

    // Open Edit modal via delegation
    if (productsGrid) {
        productsGrid.addEventListener('click', function(e) {
            var editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                var card = editBtn.closest('.admin-product-card');
                if (card) openModal(card);
            }
        });
    }

    // Close modal
    [modalClose, modalCancel].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    // Close on overlay click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // ===== FORM SUBMIT =====
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var name = document.getElementById('productName').value.trim();
            var desc = document.getElementById('productDesc').value.trim();
            var type = document.getElementById('productType').value;
            var status = document.getElementById('productStatus').value;
            var price = document.getElementById('productPrice').value;
            var imageFile = imageInput.files[0];
            var productId = productIdInput.value;
            var isEdit = !!productId;

            if (!name || !desc || !type || !price) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }
            if (!isEdit && !imageInput.files[0]) {
                showToast('Please select a product image.', 'error');
                return;
            }

            var priceStr = '₱' + parseInt(price).toLocaleString();
            var statusClassMap = {
                'Premium': 'badge-premium',
                'Best Seller': 'badge-best-seller',
                'Popular': 'badge-popular',
                'New': 'badge-new',
                'Not Available': 'badge-unavailable'
            };
            var statusClass = status ? (statusClassMap[status] || 'badge-unavailable') : '';
            var unavailable = status === 'Not Available';

            var imageSrc = '';
            if (imageFile) {
                imageSrc = imagePreview.src;
            } else if (isEdit) {
                var oldCard = document.querySelector('.admin-product-card[data-id="' + productId + '"]');
                if (oldCard) imageSrc = oldCard.querySelector('.product-photo img').src;
            }

            var cardHtml = buildCardHtml({
                id: isEdit ? productId : nextProductId++,
                name: name,
                desc: desc,
                type: type,
                price: price,
                priceStr: priceStr,
                status: status,
                statusClass: statusClass,
                unavailable: unavailable,
                image: imageSrc
            });

            if (isEdit) {
                var oldCard = document.querySelector('.admin-product-card[data-id="' + productId + '"]');
                if (oldCard) {
                    oldCard.outerHTML = cardHtml;
                    showToast('Product updated successfully!', 'success');
                }
            } else {
                if (productsGrid.firstChild) {
                    productsGrid.insertAdjacentHTML('afterbegin', cardHtml);
                } else {
                    productsGrid.innerHTML = cardHtml;
                }
                showToast('Product added successfully!', 'success');
            }

            // Re-attach event listeners for the new card
            attachCardEvents();
            closeModal();
        });
    }

    function buildCardHtml(data) {
        var unavailableClass = data.unavailable ? ' unavailable' : '';
        var badgeHtml = data.status ? 
            '<span class="product-status-badge ' + data.statusClass + '">' + escapeHtml(data.status) + '</span>' : '';
        return '' +
            '<div class="admin-product-card' + unavailableClass + '"' +
            ' data-id="' + data.id + '"' +
            ' data-category="' + data.type + '"' +
            ' data-name="' + escapeHtml(data.name) + '"' +
            ' data-desc="' + escapeHtml(data.desc) + '"' +
            ' data-type="' + data.type + '"' +
            ' data-price="' + data.price + '"' +
            ' data-status="' + escapeHtml(data.status) + '"' +
            ' data-image="' + escapeHtml(data.image) + '">' +
                '<div class="product-photo">' +
                    '<img src="' + data.image + '" alt="' + escapeHtml(data.name) + '">' +
                    badgeHtml +
                '</div>' +
                '<div class="product-card-body">' +
                    '<h3>' + escapeHtml(data.name) + '</h3>' +
                    '<p class="product-desc">' + escapeHtml(data.desc) + '</p>' +
                    '<div class="product-meta">' +
                        '<span class="product-type">' + escapeHtml(data.type) + '</span>' +
                        '<span class="product-price">' + data.priceStr + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="product-card-actions">' +
                    '<button class="btn-outline btn-edit" data-id="' + data.id + '"><i class="fa-solid fa-pen-to-square"></i> Edit</button>' +
                    '<button class="btn-outline btn-delete" data-id="' + data.id + '"><i class="fa-solid fa-trash-can"></i> Delete</button>' +
                '</div>' +
            '</div>';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/"/g, '"')
            .replace(/'/g, '&#039;');
    }

    // ===== DELETE PRODUCT =====
    if (productsGrid) {
        productsGrid.addEventListener('click', function(e) {
            var deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                var card = deleteBtn.closest('.admin-product-card');
                if (!card) return;
                var name = card.dataset.name;
                if (confirm('Delete "' + name + '"?')) {
                    card.remove();
                    showToast(name + ' deleted.', 'error');
                    checkEmptyProducts();
                }
            }
        });
    }

    function checkEmptyProducts() {
        var emptyState = document.getElementById('productsEmpty');
        var cards = productsGrid ? productsGrid.querySelectorAll('.admin-product-card') : [];
        var visible = 0;
        cards.forEach(function(c) { if (c.style.display !== 'none') visible++; });
        if (emptyState) emptyState.style.display = visible === 0 ? '' : 'none';
    }

    // Re-attach edit/delete event listeners for dynamically added cards
    function attachCardEvents() {
        // No need for explicit attachment since we use delegation on productsGrid
        // But need to ensure filter works on new cards
        filterItems = document.querySelectorAll('.order-card, .admin-product-card');
    }

    // ===== MOCK ACTIONS =====
    document.querySelectorAll('.mock-action').forEach(function(btn) {
        btn.addEventListener('click', function() {
            showToast(this.dataset.msg || 'Done.', this.dataset.type);
        });
    });

    // ===== ORDER ACTIONS =====
    document.querySelectorAll('.btn-action[data-action]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var action = this.dataset.action;
            var orderId = this.closest('.order-card').querySelector('.order-id').textContent;
            var messages = {
                confirm: orderId + ' confirmed! Customer has been notified.',
                deliver: orderId + ' marked as delivered.',
                cancel: orderId + ' cancelled.'
            };
            var types = { confirm: 'success', deliver: 'success', cancel: 'error' };
            showToast(messages[action] || 'Action done.', types[action]);
        });
    });

})();