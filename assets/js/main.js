/**
 * Main JavaScript — Suprova's Gallery
 * Core UI interactions, toast system, navbar
 */

const SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';

// ============================================================
// TOAST NOTIFICATION SYSTEM
// ============================================================
function showToast(type, message, duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-icon"><i class="fas ${icons[type] || icons.info}"></i></div>
        <div class="toast-body">${message}</div>
        <button class="toast-close" onclick="dismissToast(this.parentElement)">
            <i class="fas fa-times"></i>
        </button>
    `;

    container.appendChild(toast);

    // Auto dismiss
    setTimeout(() => dismissToast(toast), duration);
}

function dismissToast(toast) {
    if (!toast || !toast.parentElement) return;
    toast.classList.add('dismissing');
    setTimeout(() => toast.remove(), 350);
}

// ============================================================
// NAVBAR — Sticky + Mobile Menu
// ============================================================
const navbar      = document.getElementById('navbar');
const hamburger   = document.getElementById('hamburger');
const navMenu     = document.getElementById('navMenu');
const navOverlay  = document.getElementById('navOverlay');

// Sticky shadow
window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
        navbar?.classList.add('scrolled');
        document.getElementById('backToTop')?.classList.add('visible');
    } else {
        navbar?.classList.remove('scrolled');
        document.getElementById('backToTop')?.classList.remove('visible');
    }
}, { passive: true });

// Hamburger toggle
hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu?.classList.toggle('open');
    navOverlay?.classList.toggle('active');
    document.body.style.overflow = navMenu?.classList.contains('open') ? 'hidden' : '';
});

navOverlay?.addEventListener('click', closeMenu);

function closeMenu() {
    hamburger?.classList.remove('active');
    navMenu?.classList.remove('open');
    navOverlay?.classList.remove('active');
    document.body.style.overflow = '';
}

// Back to top
document.getElementById('backToTop')?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ============================================================
// LIVE SEARCH
// ============================================================
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const suggestions = document.getElementById('searchSuggestions');

searchInput?.addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(searchTimeout);
    if (q.length < 2) { suggestions?.classList.remove('active'); return; }

    searchTimeout = setTimeout(() => {
        fetch(`/232311064/api/search.php?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                if (!data.results?.length) { suggestions?.classList.remove('active'); return; }
                suggestions.innerHTML = data.results.map(p => `
                    <a href="${p.url}" class="suggestion-item">
                        <img src="${p.image}" alt="${p.name}">
                        <div class="suggestion-info">
                            <strong>${p.name}</strong><br>
                            <small>${p.category} · ${p.price}</small>
                        </div>
                    </a>
                `).join('');
                suggestions.classList.add('active');
            });
    }, 300);
});

// Close suggestions on click outside
document.addEventListener('click', e => {
    if (!searchInput?.contains(e.target) && !suggestions?.contains(e.target)) {
        suggestions?.classList.remove('active');
    }
});

// ============================================================
// FLASH MESSAGE AUTO-DISMISS
// ============================================================
const flashMsg = document.getElementById('flashMessage');
if (flashMsg) {
    setTimeout(() => {
        flashMsg.style.transition = 'opacity 0.5s';
        flashMsg.style.opacity = '0';
        setTimeout(() => flashMsg.remove(), 500);
    }, 5000);
}

// ============================================================
// INTERSECTION OBSERVER — Reveal Animations
// ============================================================
const reveals = document.querySelectorAll('.reveal');
if (reveals.length) {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    reveals.forEach(el => revealObserver.observe(el));
}

// ============================================================
// WISHLIST BUTTON (on product cards globally)
// ============================================================
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.wishlist-btn');
    if (!btn) return;

    const productId = btn.dataset.id;
    if (!productId) return;

    fetch('/232311064/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId,
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast('success', d.message);
            // Toggle icon and class
            const icon = btn.querySelector('i');
            if (d.action === 'added') {
                btn.classList.add('wishlisted');
                if (icon) { icon.className = 'fas fa-heart'; }
                btn.title = 'Remove from Wishlist';
            } else {
                btn.classList.remove('wishlisted');
                if (icon) { icon.className = 'far fa-heart'; }
                btn.title = 'Add to Wishlist';
            }
            // Update badge
            updateWishlistBadge(d.wishlist_count);
        } else {
            if (d.redirect) window.location.href = d.redirect;
            else showToast('error', d.message);
        }
    });
});

function updateWishlistBadge(count) {
    let badge = document.getElementById('wishlistBadge');
    if (count > 0) {
        if (!badge) {
            const parent = document.querySelector('a[href*="wishlist"]');
            badge = document.createElement('span');
            badge.className = 'badge';
            badge.id = 'wishlistBadge';
            parent?.appendChild(badge);
        }
        badge.textContent = count;
        badge.style.display = '';
    } else if (badge) {
        badge.style.display = 'none';
    }
}

// ============================================================
// UTILITY
// ============================================================
function updateCartBadge(count) {
    const badge = document.getElementById('cartBadge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = '';
    } else {
        badge.style.display = 'none';
    }
}
