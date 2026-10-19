(function() {
    'use strict';

    var toast = document.getElementById('toast');
    var modal = document.getElementById('productModal');
    var modalForm = document.getElementById('productForm');
    var imageInput = document.getElementById('productImage');
    var imagePreview = document.getElementById('imagePreview');
    var previewPlaceholder = document.querySelector('.preview-placeholder');
    var productsGrid = document.getElementById('productsGrid');
    var emptyState = document.getElementById('productsEmpty');

    function showToast(message, type) {
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 2500);
    }
    window.adminToast = showToast;

    function clearProductFieldError(field) {
        if (!field) return;

        var error = document.getElementById(field.id + '-error');
        if (error && error.classList.contains('product-field-error')) {
            error.remove();
        }

        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');

        var describedBy = (field.getAttribute('aria-describedby') || '')
            .split(/\s+/)
            .filter(function(id) { return id && id !== field.id + '-error'; });
        if (describedBy.length) {
            field.setAttribute('aria-describedby', describedBy.join(' '));
        } else {
            field.removeAttribute('aria-describedby');
        }
    }

    function clearAllProductFieldErrors() {
        if (!modalForm) return;

        modalForm.querySelectorAll('.product-field-error').forEach(function(error) {
            var fieldId = error.id.replace(/-error$/, '');
            clearProductFieldError(document.getElementById(fieldId));
        });
    }

    function clearRequiredProductErrors() {
        if (!modalForm) return;

        modalForm.querySelectorAll('.product-field-error').forEach(function(error) {
            if (error.textContent === 'This field is required.') {
                var fieldId = error.id.replace(/-error$/, '');
                clearProductFieldError(document.getElementById(fieldId));
            }
        });
    }

    function showProductFieldError(field, message) {
        if (!field) return;

        clearProductFieldError(field);

        var error = document.createElement('span');
        error.id = field.id + '-error';
        error.className = 'product-field-error';
        error.setAttribute('role', 'alert');
        error.textContent = message;
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', error.id);

        var group = field.closest('.form-group');
        var preview = field === imageInput && group
            ? group.querySelector('.image-preview-wrapper')
            : null;
        if (preview) {
            preview.insertAdjacentElement('afterend', error);
        } else {
            field.insertAdjacentElement('afterend', error);
        }
    }

    function validateProductFields(isEdit) {
        var nameField = document.getElementById('productName');
        var descField = document.getElementById('productDesc');
        var typeField = document.getElementById('productType');
        var priceField = document.getElementById('productPrice');
        var isValid = true;

        clearAllProductFieldErrors();

        if (!nameField.value.trim()) {
            showProductFieldError(nameField, 'This field is required.');
            isValid = false;
        }
        if (!descField.value.trim()) {
            showProductFieldError(descField, 'This field is required.');
            isValid = false;
        }
        if (!typeField.value) {
            showProductFieldError(typeField, 'This field is required.');
            isValid = false;
        }

        var priceValue = priceField.value.trim();
        var priceNumber = Number(priceValue);
        if (!priceValue && !(priceField.validity && priceField.validity.badInput)) {
            showProductFieldError(priceField, 'This field is required.');
            isValid = false;
        } else if (!Number.isFinite(priceNumber) || priceNumber <= 0 || !Number.isInteger(priceNumber)) {
            showProductFieldError(priceField, 'Enter a whole-number price greater than 0.');
            isValid = false;
        }

        if (!isEdit && (!imageInput || !imageInput.files || !imageInput.files.length)) {
            showProductFieldError(imageInput, 'This field is required.');
            isValid = false;
        }

        return isValid;
    }

    // ===== FILTER TABS =====
    var filterBtns = document.querySelectorAll('.filter-btn');

    function refreshFilterItems() {
        return document.querySelectorAll('.order-card, .admin-product-card');
    }

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var key = this.dataset.status || this.dataset.category;
            var items = refreshFilterItems();
            var visible = 0;
            items.forEach(function(item) {
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

    function checkEmptyProducts() {
        var cards = productsGrid ? productsGrid.querySelectorAll('.admin-product-card') : [];
        if (emptyState) emptyState.style.display = cards.length === 0 ? '' : 'none';
    }

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
    var productIdInput = document.getElementById('productId');
    var modalTitle = document.getElementById('modalTitle');
    var modalSubmitBtn = document.getElementById('modalSubmit');
    var productImageRequired = document.getElementById('productImageRequired');

    function openModal(editCard) {
        modalForm.reset();
        clearAllProductFieldErrors();
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        var ph = document.querySelector('.preview-placeholder');
        if (ph) ph.style.display = 'block';
        productIdInput.value = '';
        imageInput.required = !editCard;
        if (productImageRequired) productImageRequired.hidden = !!editCard;

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
                var ph2 = document.querySelector('.preview-placeholder');
                if (ph2) ph2.style.display = 'none';
            }
        } else {
            modalTitle.textContent = 'Add Product';
            modalSubmitBtn.textContent = 'Add Product';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(function() {
            document.getElementById('productName').focus();
        }, 100);
    }

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', function() {
            openModal(null);
        });
    }

    if (productsGrid) {
        productsGrid.addEventListener('click', function(e) {
            var editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                var card = editBtn.closest('.admin-product-card');
                if (card) openModal(card);
            }
        });
    }

    [modalClose, modalCancel].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    if (modalSubmitBtn) {
        modalSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (modalForm) modalForm.requestSubmit();
        });
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // ===== FORM SUBMIT (ADD / EDIT) =====
    if (modalForm) {
        modalForm.addEventListener('focusin', clearRequiredProductErrors, true);
        modalForm.addEventListener('click', function(e) {
            if (e.target && e.target.closest('input, select, textarea')) {
                clearRequiredProductErrors();
            }
        }, true);
        ['input', 'change'].forEach(function(eventName) {
            modalForm.addEventListener(eventName, function(e) {
                clearRequiredProductErrors();
                if (e.target && e.target.id) {
                    clearProductFieldError(e.target);
                }
            }, true);
        });

        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var name = document.getElementById('productName').value.trim();
            var desc = document.getElementById('productDesc').value.trim();
            var type = document.getElementById('productType').value;
            var status = document.getElementById('productStatus').value;
            var price = document.getElementById('productPrice').value;
            var imageFile = imageInput && imageInput.files ? imageInput.files[0] : null;
            var productId = productIdInput.value;
            var isEdit = !!productId;

            if (!validateProductFields(isEdit)) return;

            var formData = new FormData();
            formData.append('productName', name);
            formData.append('productDesc', desc);
            formData.append('productType', type);
            formData.append('productStatus', status);
            formData.append('productPrice', price);
            if (imageFile) {
                formData.append('productImage', imageFile);
            }
            if (isEdit) {
                formData.append('productId', productId);
            }

            var action = isEdit ? 'edit' : 'add';
            modalSubmitBtn.disabled = true;
            modalSubmitBtn.textContent = 'Saving...';

            fetch('../../server/product_actions.php?action=' + action, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) {
                    showToast(data.message || 'Something went wrong.', 'error');
                    modalSubmitBtn.disabled = false;
                    modalSubmitBtn.textContent = isEdit ? 'Save Changes' : 'Add Product';
                    return;
                }

                var p = data.product;
                var cardHtml = buildCardHtml(p);

                if (isEdit) {
                    var oldCard = productsGrid.querySelector('.admin-product-card[data-id="' + productId + '"]');
                    if (oldCard) {
                        oldCard.outerHTML = cardHtml;
                    }
                    showToast('Product updated successfully!', 'success');
                } else {
                    if (productsGrid.firstChild) {
                        productsGrid.insertAdjacentHTML('afterbegin', cardHtml);
                    } else {
                        productsGrid.innerHTML = cardHtml;
                    }
                    showToast('Product added successfully!', 'success');
                }

                checkEmptyProducts();
                updateFeaturedButtons();
                closeModal();
                modalSubmitBtn.disabled = false;
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
                modalSubmitBtn.disabled = false;
                modalSubmitBtn.textContent = isEdit ? 'Save Changes' : 'Add Product';
            });
        });
    }

    function buildCardHtml(p) {
        var statusMap = {
            'Premium': 'badge-premium',
            'Best Seller': 'badge-best-seller',
            'Popular': 'badge-popular',
            'New': 'badge-new',
            'Not Available': 'badge-unavailable'
        };
        var badgeClass = p.status ? (statusMap[p.status] || '') : '';
        var badgeHtml = badgeClass ?
            '<span class="product-status-badge ' + badgeClass + '">' + escapeHtml(p.status) + '</span>' : '';
        var unavailable = p.status === 'Not Available' ? ' unavailable' : '';
        var imageSrc = p.image ? ('../../' + p.image.replace(/^\.\.\/+/, '')) : '';

        return '' +
            '<div class="admin-product-card' + unavailable + '"' +
            ' data-id="' + p.id + '"' +
            ' data-category="' + escapeHtml(p.category) + '"' +
            ' data-name="' + escapeHtml(p.name) + '"' +
            ' data-desc="' + escapeHtml(p.description) + '"' +
            ' data-type="' + escapeHtml(p.category) + '"' +
            ' data-price="' + p.priceNum + '"' +
            ' data-status="' + escapeHtml(p.status || '') + '"' +
            ' data-image="' + escapeHtml(imageSrc) + '">' +
                '<div class="product-photo">' +
                    '<img src="' + escapeHtml(imageSrc) + '" alt="' + escapeHtml(p.name) + '">' +
                    badgeHtml +
                '</div>' +
                '<div class="product-card-body">' +
                    '<h3>' + escapeHtml(p.name) + '</h3>' +
                    '<p class="product-desc">' + escapeHtml(p.description) + '</p>' +
                    '<div class="product-meta">' +
                        '<span class="product-type">' + escapeHtml(p.category) + '</span>' +
                        '<span class="product-price">' + p.price + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="product-card-actions">' +
                    '<button class="btn-featured" data-id="' + p.id + '" title="Add to featured"><i class="fa-regular fa-star"></i></button>' +
                    '<button class="btn-outline btn-edit" data-id="' + p.id + '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>' +
                    '<button class="btn-outline btn-delete" data-id="' + p.id + '" title="Delete"><i class="fa-solid fa-trash-can"></i></button>' +
                '</div>' +
            '</div>';
    }

    function escapeHtml(str) {
        return String(str || '')
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
            if (!deleteBtn) return;
            var card = deleteBtn.closest('.admin-product-card');
            if (!card) return;
            var name = card.dataset.name;
            var id = card.dataset.id;

            if (!confirm('Delete "' + name + '"?')) return;

            fetch('../../server/product_actions.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(id, 10) })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) {
                    showToast(data.message || 'Failed to delete product.', 'error');
                    return;
                }
                card.classList.add('fade-out');
                setTimeout(function() {
                    card.remove();
                    checkEmptyProducts();
                }, 250);
                showToast(name + ' deleted.', 'success');
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });
    }

    // ===== FEATURED TOGGLE =====
    function updateFeaturedButtons() {
        var allCards = productsGrid ? productsGrid.querySelectorAll('.admin-product-card') : [];
        var featuredCount = 0;
        allCards.forEach(function(card) {
            var btn = card.querySelector('.btn-featured');
            if (btn && btn.classList.contains('active')) featuredCount++;
        });
        allCards.forEach(function(card) {
            var btn = card.querySelector('.btn-featured');
            if (!btn) return;
            if (!btn.classList.contains('active') && featuredCount >= 4) {
                btn.classList.add('maxed');
                btn.title = 'Max 4 featured products';
            } else {
                btn.classList.remove('maxed');
            }
        });
    }

    if (productsGrid) {
        productsGrid.addEventListener('click', function(e) {
            var starBtn = e.target.closest('.btn-featured');
            if (!starBtn) return;
            if (starBtn.classList.contains('maxed')) {
                showToast('Maximum 4 featured products allowed.', 'error');
                return;
            }

            var productId = parseInt(starBtn.dataset.id, 10);
            fetch('../../server/toggle_featured.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) {
                    showToast(data.message || 'Could not update featured status.', 'error');
                    return;
                }
                var icon = starBtn.querySelector('i');
                if (data.added) {
                    starBtn.classList.add('active');
                    starBtn.title = 'Remove from featured';
                    icon.className = 'fa-solid fa-star';
                    showToast('Added to featured products.', 'success');
                } else {
                    starBtn.classList.remove('active');
                    starBtn.title = 'Add to featured';
                    icon.className = 'fa-regular fa-star';
                    showToast('Removed from featured products.', 'success');
                }
                updateFeaturedButtons();
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });
    }

    updateFeaturedButtons();
    checkEmptyProducts();

})();
