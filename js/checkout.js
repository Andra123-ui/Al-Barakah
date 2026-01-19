const snapScript = document.createElement('script');
snapScript.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
snapScript.setAttribute('data-client-key', 'Mid-client-igV48Me9RQHuy9rq'); // ganti sesuai client key
document.head.appendChild(snapScript);

document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    formData.append('cart', JSON.stringify(cart));

    try {
        // 1️⃣ Simpan order dulu
        const res = await fetch('submit_order.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (!data.success) {
            alert('Error: ' + data.message);
            return;
        }

        // 2️⃣ Buka Snap popup Midtrans
        window.snap.pay(data.snap_token, {
    onSuccess: updateStatusProcessing,
    onPending: updateStatusProcessing,
    onError: function() { alert('Pembayaran gagal'); },
    onClose: function() { alert('Popup ditutup'); }
});

function updateStatusProcessing(result) {
    fetch('instant_processing.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: data.order_id })
    })
    .then(() => {
        localStorage.removeItem('cart');
        window.location.href = 'my_orders.php';
    });
}
    } catch (error) {
        alert('Terjadi kesalahan: ' + error.message);
    }
});