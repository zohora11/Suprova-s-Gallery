/**
 * Cart JavaScript — Add to Cart, Update, Remove
 * Suprova's Gallery
 */

/**
 * Add product to cart
 */
function addToCart(productId, quantity = 1, size = '', color = '') {
    const btn = event?.currentTarget;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    }

    const params = new URLSearchParams({
        action: 'add',
        product_id: productId,
        quantity: quantity,
        size: size,
        color: color,
    });

    fetch('/232311064/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString(),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            updateCartBadge(data.cart_count);
            // Animate button
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Add to Cart';
                }, 1500);
            }
        } else {
            showToast('error', data.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Add to Cart';
            }
        }
    })
    .catch(() => {
        showToast('error', 'Something went wrong. Please try again.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Add to Cart';
        }
    });
}

/**
 * Update cart item quantity
 */
function updateCartQty(cartId, quantity) {
    if (quantity < 1) {
        if (confirm('Remove this item from cart?')) {
            removeFromCart(cartId);
        }
        return;
    }

    const qtyInput = document.getElementById('qty' + cartId);
    if (qtyInput) qtyInput.value = quantity;

    fetch('/232311064/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&cart_id=${cartId}&quantity=${quantity}`,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cart_count);
            // Reload page to recalculate totals
            location.reload();
        } else {
            showToast('error', data.message);
        }
    });
}

/**
 * Remove cart item
 */
function removeFromCart(cartId) {
    const item = document.getElementById('cartItem' + cartId);
    if (item) {
        item.style.transition = 'all 0.3s';
        item.style.opacity = '0';
        item.style.transform = 'translateX(30px)';
        item.style.height = item.offsetHeight + 'px';
    }

    fetch('/232311064/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&cart_id=${cartId}`,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            setTimeout(() => {
                item?.remove();
                updateCartBadge(data.cart_count);
                showToast('info', 'Item removed from cart');
                // Check if cart is empty
                const cartContainer = document.getElementById('cartItemsContainer');
                if (cartContainer && cartContainer.children.length === 0) {
                    location.reload();
                }
            }, 350);
        }
    });
}
