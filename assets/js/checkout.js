/**
 * Checkout Form Validation
 * Suprova's Gallery
 */
document.getElementById('checkoutForm')?.addEventListener('submit', function (e) {
    let valid = true;
    const required = [
        { id: 'shipping_name',    msg: 'Full name is required' },
        { id: 'shipping_email',   msg: 'Valid email required', type: 'email' },
        { id: 'shipping_phone',   msg: 'Valid phone required (01XXXXXXXXX)', type: 'phone' },
        { id: 'shipping_address', msg: 'Address is required' },
        { id: 'shipping_city',    msg: 'City is required' },
    ];

    required.forEach(({ id, msg, type }) => {
        const el = document.querySelector(`[name="${id}"]`);
        if (!el) return;
        let ok = el.value.trim().length > 0;
        if (ok && type === 'email') ok = /\S+@\S+\.\S+/.test(el.value);
        if (ok && type === 'phone') ok = /^(?:\+88|88)?01[3-9]\d{8}$/.test(el.value.replace(/\s/g, ''));
        if (!ok) {
            el.classList.add('is-invalid');
            let err = el.parentElement?.querySelector('.form-error');
            if (!err) { err = document.createElement('div'); err.className = 'form-error'; el.parentElement.appendChild(err); }
            err.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`;
            valid = false;
        } else {
            el.classList.remove('is-invalid');
            const err = el.parentElement?.querySelector('.form-error');
            if (err) err.remove();
        }
    });

    if (!valid) {
        e.preventDefault();
        showToast('error', 'Please fix the errors before placing your order.');
        document.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        // Show loading state
        const btn = document.getElementById('placeOrderBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';
        }
    }
});
