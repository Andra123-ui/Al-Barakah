function submitTestimonial(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const messageDiv = document.getElementById('testimonialMessage');
    const submitBtn = form.querySelector('.submit-testimonial-btn');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Mengirim...';

    fetch('submit_testimonials.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error('Server error');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            form.style.display = 'none';
            messageDiv.innerHTML = `
                <div style="text-align:center; padding:40px; background:#d4edda; border-radius:15px; color:#155724;">
                    <h3>🌟 Alhamdulillah, Terima Kasih! 🌟</h3>
                    <p style="font-size:1.1rem;">Testimoni Anda telah kami terima dengan baik.<br>
                    Kami sangat menghargai kepercayaan Anda ❤️</p>
                    <p><strong>Kembali ke pesanan dalam <span id="countdown">3</span> detik...</strong></p>
                </div>
            `;

            let detik = 3;
            const timer = setInterval(() => {
                document.getElementById('countdown').textContent = --detik;
                if (detik <= 0) {
                    clearInterval(timer);
                    window.location.href = 'my_orders.php';
                }
            }, 1000);

        } else {
            alert(data.message || 'Gagal mengirim testimoni');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan jaringan. Pastikan koneksi internet stabil.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Kirim Testimoni';
    });
}