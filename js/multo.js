// multo.js - Complete Integration dengan Midtrans

// ===== CART STORAGE =====
let cart = [];

// ===== MIDTRANS SNAP SCRIPT =====
const snapScript = document.createElement('script');
snapScript.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
snapScript.setAttribute('data-client-key', 'Mid-client-igV48Me9RQHuy9rq'); // GANTI!
document.head.appendChild(snapScript);

// ===== TEMA TETAP SAAT PINDAH HALAMAN =====
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('multo-theme', newTheme);

    const btn = document.querySelector('.theme-toggle');
    if (btn) {
        btn.textContent = newTheme === 'dark' ? '☀️' : '🌙';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('multo-theme');
    const html = document.documentElement;

    if (savedTheme) {
        html.setAttribute('data-theme', savedTheme);
    } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        html.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        localStorage.setItem('multo-theme', prefersDark ? 'dark' : 'light');
    }

    const btn = document.querySelector('.theme-toggle');
    if (btn) {
        const current = html.getAttribute('data-theme');
        btn.textContent = current === 'dark' ? '☀️' : '🌙';
    }
});

// ===== USER MENU & NAVIGATION =====
function toggleUserMenu() {
    window.location.href = "user.php";
}

function togglelogout() {
    window.location.href = "logout.php";
}

function scrollToProducts() {
    document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ===== SEARCH PRODUCTS =====
function searchProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach(card => {
        const productName = card.getAttribute('data-name').toLowerCase();
        const category = card.getAttribute('data-category').toLowerCase();
        
        if (productName.includes(searchTerm) || category.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// ===== CAROUSEL AUTO SCROLL =====
const carouselContainer = document.querySelector('.carousel-container');
let isDown = false;
let startX;
let scrollLeft;
let autoScrollInterval;

function filterByCategory(category) {
    const products = document.querySelectorAll('.product-card');
    const carouselItems = document.querySelectorAll('.carousel-item');

    products.forEach(product => {
        product.style.display = (category === 'all' || product.dataset.category === category) ? 'block' : 'none';
    });

    carouselItems.forEach(item => {
        item.classList.toggle('active', item.dataset.category === category);
    });

    document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
}

function startAutoScroll() {
    autoScrollInterval = setInterval(() => {
        if (!isDown) {
            carouselContainer.scrollLeft += 1;
            
            if (carouselContainer.scrollLeft >= carouselContainer.scrollWidth - carouselContainer.clientWidth) {
                setTimeout(() => {
                    carouselContainer.style.scrollBehavior = 'auto';
                    carouselContainer.scrollLeft = 0;
                    setTimeout(() => {
                        carouselContainer.style.scrollBehavior = 'smooth';
                    }, 50);
                }, 500);
            }
        }
    }, 20);
}

function stopAutoScroll() {
    clearInterval(autoScrollInterval);
}

carouselContainer.addEventListener('mousedown', (e) => {
    isDown = true;
    carouselContainer.style.cursor = 'grabbing';
    startX = e.pageX - carouselContainer.offsetLeft;
    scrollLeft = carouselContainer.scrollLeft;
    stopAutoScroll();
});

carouselContainer.addEventListener('mouseleave', () => {
    isDown = false;
    carouselContainer.style.cursor = 'grab';
});

carouselContainer.addEventListener('mouseup', () => {
    isDown = false;
    carouselContainer.style.cursor = 'grab';
    setTimeout(() => {
        if (!isDown) startAutoScroll();
    }, 2000);
});

carouselContainer.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - carouselContainer.offsetLeft;
    const walk = (x - startX) * 2;
    carouselContainer.scrollLeft = scrollLeft - walk;
});

carouselContainer.addEventListener('mouseenter', () => {
    stopAutoScroll();
});

carouselContainer.addEventListener('mouseleave', () => {
    if (!isDown) {
        setTimeout(() => {
            startAutoScroll();
        }, 1000);
    }
});

document.querySelectorAll('.carousel-item').forEach(item => {
    item.addEventListener('click', () => {
        const category = item.dataset.category;
        filterByCategory(category);
    });
});

startAutoScroll();

// ===== CART FUNCTIONS =====
function addToCart(name, price, image, id) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            quantity: 1
        });
    }
    
    updateCart();
    showNotification(`${name} ditambahkan ke keranjang!`);
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');
    const cartBadge = document.getElementById('cartBadge');
    const cartSummary = document.getElementById('cartSummary');
    const cartTotal = document.getElementById('cartTotal');
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartBadge.textContent = totalItems;
    
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <p>Keranjang Anda masih kosong</p>
                <p>Yuk mulai belanja! 🛍️</p>
            </div>
        `;
        cartSummary.style.display = 'none';
    } else {
        cartItems.innerHTML = cart.map((item, index) => `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">Rp ${formatPrice(item.price)}</div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                        <span style="padding: 0 1rem;">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                </div>
                <button class="remove-item" onclick="removeFromCart(${index})">🗑️</button>
            </div>
        `).join('');
        
        cartSummary.style.display = 'block';
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = `Rp ${formatPrice(total)}`;
    }
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    
    updateCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
    showNotification('🗑️ Produk dihapus dari keranjang');
}

function toggleCart() {
    const cartModal = document.getElementById('cartModal');
    cartModal.classList.toggle('active');
}

// ===== CHECKOUT FUNCTIONS =====
function openCheckout() {
    if (cart.length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }

    const overlay = document.getElementById('checkoutOverlay');
    const modal = document.getElementById('checkoutModal');
    const content = document.getElementById('checkoutContent');

    content.innerHTML = `
        <div style="text-align:center; padding:5rem;">
            <div style="width:60px; height:60px; border:6px solid #e0e0e0; border-top:6px solid #00695c; border-radius:50%; animation:spin 1s linear infinite; margin:0 auto 1rem;"></div>
            <p style="font-size:1.2rem; color:#333;">Memuat checkout...</p>
        </div>
    `;

    overlay.classList.add('active');
    modal.classList.add('active');

    fetch('check_biodata.php')
        .then(r => r.json())
        .then(data => {
            if (!data.biodata_lengkap) {
                content.innerHTML = `
                    <div style="text-align:center; padding:3rem; background:#ffebee; border-radius:20px; border:2px solid #e74c3c;">
                        <h3 style="color:#c0392b; margin-bottom:1rem;">Biodata Belum Lengkap!</h3>
                        <p style="margin:1rem 0; font-size:1.1rem; color:#333;">Silakan lengkapi biodata Anda terlebih dahulu.</p>
                        <a href="profile.php" style="display:inline-block; margin:2rem 0; padding:16px 50px; background:#00695c; color:white; border-radius:50px; text-decoration:none; font-weight:700; font-size:1.2rem;">
                            Lengkapi Biodata Sekarang
                        </a>
                        <br><br>
                        <button type="button" onclick="closeCheckout()" style="padding:12px 30px; background:#999; color:white; border:none; border-radius:50px;">Tutup</button>
                    </div>
                `;
                return;
            }

            let itemsHtml = '';
            let totalHarga = 0;
            cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                totalHarga += subtotal;
                itemsHtml += `
                    <div style="display:flex; justify-content:space-between; padding:1rem 0; border-bottom:1px solid #ddd; color:#000;">
                        <div>
                            <strong style="color:#00695c;">${escapeHtml(item.name)}</strong><br>
                            <small style="color:#555;">${item.quantity} × Rp ${formatPrice(item.price)}</small>
                        </div>
                        <div style="font-weight:600; color:#00695c;">
                            Rp ${formatPrice(subtotal)}
                        </div>
                    </div>
                `;
            });

            content.innerHTML = `
                <h2 style="text-align:center; color:#00695c; margin-bottom:2rem; font-size:2rem; font-weight:700;">Checkout Pesanan</h2>

                <!-- PENGIRIMAN KE -->
                <div style="background:#e8f5e9; padding:2rem; border-radius:20px; margin-bottom:2.5rem; border-left:6px solid #00695c;">
                    <h3 style="color:#00695c; text-align:center; margin-bottom:1rem; font-weight:700;">Pengiriman Ke:</h3>
                    <div style="text-align:center; line-height:2; font-size:1.15rem; color:#000;">
                        <strong style="font-size:1.4rem;">${escapeHtml(data.biodata.full_name)}</strong><br>
                        ${escapeHtml(data.biodata.whatsapp)}<br>
                        ${escapeHtml(data.biodata.address)}<br>
                        ${escapeHtml(data.biodata.city)}, ${escapeHtml(data.biodata.province)} ${escapeHtml(data.biodata.postalcode)}
                    </div>
                    <div style="text-align:center; margin-top:1rem;">
                        <a href="profile.php" style="color:#00695c; font-weight:600; text-decoration:underline;">Ubah alamat?</a>
                    </div>
                </div>

                <form onsubmit="submitOrder(event)">
                    <!-- METODE PEMBAYARAN -->
                    <div style="margin:2rem 0;">
                        <label style="display:block; margin-bottom:0.8rem; font-weight:700; color:#00695c; font-size:1.2rem;">Metode Pembayaran *</label>
                        <select name="payment" required style="width:100%; padding:16px; border-radius:16px; border:2px solid #00695c; font-size:1.1rem; background:white;">
                            <option value="">-- Pilih Metode --</option>
                            <option value="midtrans">Midtrans (Kartu/E-Wallet)</option>
                            <option value="transfer">Transfer Bank Manual</option>
                            <option value="cod">Bayar di Tempat (COD)</option>
                        </select>
                    </div>

                    <!-- CATATAN -->
                    <div style="margin:2rem 0;">
                        <label style="display:block; margin-bottom:0.8rem; font-weight:700; color:#00695c; font-size:1.2rem;">Catatan Pesanan (Opsional)</label>
                        <textarea name="notes" rows="4" placeholder="Contoh: Taruh di pos satpam, antar sebelum jam 3 sore..." style="width:100%; padding:16px; border-radius:16px; border:2px solid #00695c; font-size:1.1rem; resize:vertical;"></textarea>
                    </div>

                    <!-- RINGKASAN -->
                    <div style="background:#e8f5e9; padding:2rem; border-radius:20px; margin:3rem 0; border:2px solid #00695c;">
                        <h3 style="text-align:center; color:#00695c; margin-bottom:1.5rem; font-size:1.5rem; font-weight:700;">Ringkasan Pesanan</h3>
                        ${itemsHtml}
                        <div style="text-align:right; font-size:2rem; font-weight:800; color:#00695c; margin-top:2rem; padding-top:1.5rem; border-top:3px solid #00695c;">
                            Total: Rp ${formatPrice(totalHarga)}
                        </div>
                    </div>

                    <!-- TOMBOL -->
                    <div style="text-align:center; margin-top:2rem;">
                        <button type="button" onclick="closeCheckout()" style="padding:16px 40px; background:#999; color:white; border:none; border-radius:50px; margin:0.5rem; font-size:1.1rem;">
                            Batal
                        </button>
                        <!-- TOMBOL BAYAR -->
                        <button type="submit" id="submitBtn" style="
                            padding: 20px 90px;
                            background: linear-gradient(45deg, #00695c, #009688);
                            color: white;
                            border: none;
                            border-radius: 50px;
                            font-size: 1.6rem;
                            font-weight: 700;
                            cursor: pointer;
                            box-shadow: 0 10px 30px rgba(0,105,92,0.4);
                            transition: all 0.3s;
                            margin: 0.5rem;
                        ">
                            BAYAR SEKARANG
                        </button>
                    </div>
                </form>
            `;
        })
        .catch(() => {
            content.innerHTML = `<p style="color:#c0392b; text-align:center; padding:3rem; font-size:1.2rem;">Gagal memuat. Silakan <a href="multo.php" style="color:#00695c; font-weight:bold;">refresh halaman</a>.</p>`;
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeCheckout() {
    const checkoutOverlay = document.getElementById('checkoutOverlay');
    const checkoutModal = document.getElementById('checkoutModal');
    
    checkoutOverlay.classList.remove('active');
    checkoutModal.classList.remove('active');
}

async function submitOrder(event) {
    event.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    if (!submitBtn) return;

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Memproses...';
    submitBtn.disabled = true;

    const form = event.target;
    const formData = new FormData(form);
    formData.append('cart', JSON.stringify(cart));

    try {
        const orderResponse = await fetch('submit_order.php', {
            method: 'POST',
            body: formData
        });

        const orderResult = await orderResponse.json();

        if (!orderResult.success) {
            throw new Error(orderResult.message || 'Gagal menyimpan pesanan');
        }

        const paymentMethod = formData.get('payment');

        if (paymentMethod === 'midtrans') {
            const paymentData = new FormData();
            paymentData.append('order_id', orderResult.order_id);

            const paymentResponse = await fetch('placeOrder.php', {
                method: 'POST',
                body: paymentData
            });

            const paymentResult = await paymentResponse.json();

            if (!paymentResult.success) {
                throw new Error(paymentResult.message || 'Gagal membuat pembayaran');
            }

            window.snap.pay(paymentResult.snap_token, {
                onSuccess: function() {
                    alert('Pembayaran berhasil! Terima kasih telah berbelanja di Al-Barakah.');
                    cart = [];
                    updateCart();
                    closeCheckout();
                    window.location.href = 'my_orders.php';
                },
                onPending: function() {
                    alert('Menunggu pembayaran. Kami akan update status secepatnya!');
                    window.location.href = 'my_orders.php';
                },
                onError: function() {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                },
                onClose: function() {
                    alert('Pesanan tetap tersimpan. Lanjutkan pembayaran di menu Pesanan Saya');
                    window.location.href = 'my_orders.php';
                }
            });

        } else {
            // Pembayaran Manual
            const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const waNumber = '6289523586766';

            let message = 'PESANAN BARU DARI AL-BARAKAH\n\n';
            message += `*Order ID:* ${orderResult.order_id}\n\n`;
            message += '*Produk:*\n';
            cart.forEach(item => {
                message += `• ${item.name} (x${item.quantity}) - Rp ${formatPrice(item.price * item.quantity)}\n`;
            });
            message += `\n*Total: Rp ${formatPrice(total)}*\n\n`;
            message += `*Pembayaran:* ${paymentMethod === 'transfer' ? 'Transfer Bank' : 'COD'}\n`;
            if (formData.get('notes')) message += `Catatan: ${formData.get('notes')}\n`;
            message += '\n_Barakallahu fiikum!_';

            alert('Pesanan berhasil dibuat! Anda akan diarahkan ke WhatsApp.');
            window.open(`https://wa.me/${waNumber}?text=${encodeURIComponent(message)}`, '_blank');

            cart = [];
            updateCart();
            closeCheckout();
            setTimeout(() => window.location.href = 'my_orders.php', 1000);
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// ===== UTILITY FUNCTIONS =====
function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function showNotification(message) {
    alert(message);
}

// ===== SCROLL ANIMATIONS =====
window.addEventListener('scroll', () => {
    const scrollTop = document.querySelector('.scroll-top');
    if (window.pageYOffset > 300) {
        scrollTop.classList.add('visible');
    } else {
        scrollTop.classList.remove('visible');
    }

    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        const rect = product.getBoundingClientRect();
        if (rect.top < window.innerHeight - 100) {
            product.classList.add('visible');
        }
    });
});


// ===== INTERSECTION OBSERVER =====
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.product-card').forEach(card => {
    observer.observe(card);
});

function toggleUserDropdown(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('userDropdown');
    const profile = document.querySelector('.user-profile');
    
    profile.classList.toggle('active');
}

// Tutup dropdown kalau klik di luar
document.addEventListener('click', function() {
    document.querySelector('.user-profile')?.classList.remove('active');
});
