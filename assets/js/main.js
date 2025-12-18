document.addEventListener('DOMContentLoaded', function() {

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        navbar.style.background = window.scrollY > 50
            ? 'linear-gradient(135deg, #e55a28, #ff6b35)'
            : 'linear-gradient(135deg, #FF6B35, #ff8c5a)';
        navbar.style.boxShadow = window.scrollY > 50
            ? '0 5px 30px rgba(0,0,0,0.2)'
            : '0 2px 20px rgba(0,0,0,0.1)';
    });

    // Category filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const category = btn.dataset.category;
            document.querySelectorAll('.food-card-wrapper').forEach(card => {
                card.style.display = category === 'all' || card.dataset.category === category ? 'block' : 'none';
                card.style.animation = 'fadeIn 0.5s ease';
            });
        });
    });
});

// Add item to cart
function addToCart(itemId) {
    fetch('/StreetGO/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'add', item_id: itemId, quantity: 1 })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            updateCartBadge(data.cart_count);
            showToast('Item added to cart', 'success');
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showToast(data.message, 'error');
        }
    });
}

// Update quantity
function updateCartQuantity(itemId, change) {
    fetch('/StreetGO/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'update', item_id: itemId, change })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) location.reload();
        else showToast(data.message, 'error');
    });
}

// Remove item
function removeFromCart(itemId) {
    if(confirm('Remove this item from cart?')) {
        fetch('/StreetGO/api/cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'remove', item_id: itemId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) location.reload();
            else showToast(data.message, 'error');
        });
    }
}

// Update cart badge
function updateCartBadge(count) {
    let badge = document.querySelector('.cart-badge');
    if(!badge) {
        const cartLink = document.querySelector('.cart-link');
        if(cartLink) {
            badge = document.createElement('span');
            badge.className = 'badge bg-danger cart-badge';
            cartLink.appendChild(badge);
        }
    }
    if(badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

// Toast notification
function showToast(message, type='info') {
    let container = document.querySelector('.toast-container');
    if(!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type==='success'?'success':type==='error'?'danger':'primary'} border-0`;
    toast.setAttribute('role','alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    container.appendChild(toast);
    setTimeout(()=> toast.remove(), 3000);
}

// Fade-in animation
const style = document.createElement('style');
style.textContent = `@keyframes fadeIn {from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);}}`;
document.head.appendChild(style);
