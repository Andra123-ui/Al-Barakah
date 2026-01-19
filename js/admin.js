function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.data-section').forEach(sec => {
        sec.style.display = 'none';
    });
    
    // Show selected section
    document.getElementById(section + '-section').style.display = 'block';
    
    // Update active nav
    document.querySelectorAll('.admin-nav a').forEach(link => {
        link.classList.remove('active');
    });
    event.target.classList.add('active');
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_user', id: userId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User deleted successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function deleteOrder(orderId) {
    if (!confirm('Are you sure you want to delete this order?')) return;
    
    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_order', id: orderId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order deleted successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function viewOrder(orderId) {
    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'view_order', id: orderId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const order = data.order;
            const products = JSON.parse(order.produk);
            
            let productsHtml = '<h3>Products:</h3><ul>';
            products.forEach(item => {
                productsHtml += `<li>${item.name} - Qty: ${item.quantity} - Rp ${item.price.toLocaleString()}</li>`;
            });
            productsHtml += '</ul>';
            
            document.getElementById('orderDetails').innerHTML = `
                <h2>Order Details #${order.id}</h2>
                <p><strong>Customer:</strong> ${order.nama}</p>
                <p><strong>WhatsApp:</strong> ${order.whatsapp}</p>
                <p><strong>Email:</strong> ${order.email}</p>
                <p><strong>Address:</strong> ${order.alamat}, ${order.kota}, ${order.provinsi} ${order.kodepos}</p>
                <p><strong>Payment Method:</strong> ${order.pembayaran}</p>
                <p><strong>Notes:</strong> ${order.catatan || '-'}</p>
                ${productsHtml}
                <p><strong>Total:</strong> Rp ${parseInt(order.total).toLocaleString()}</p>
            `;
            document.getElementById('orderModal').style.display = 'block';
        }
    });
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function approveTestimonial(id) {
    if (!confirm('Are you sure you want to approve this testimonial?')) {
        return;
    }

    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'approve_testimonial', id: id})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Testimonial approved successfully!');
            
            // Update UI tanpa reload
            if (data.testimonial) {
                const card = document.querySelector(`.testimonial-card[data-id="${id}"]`);
                if (card) {
                    // Update status badge
                    const statusBadge = card.querySelector('.testimonial-status-badge');
                    if (statusBadge) {
                        statusBadge.innerHTML = '<span class="status-approved">✓ Approved</span>';
                    }
                    // Update data-status attribute
                    card.setAttribute('data-status', '1');
                }
            }
        } else {
            alert('Error: ' + (data.error || 'Failed to approve testimonial'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the testimonial');
    });
}

function rejectTestimonial(id) {
    if (!confirm('Are you sure you want to reject this testimonial?')) {
        return;
    }

    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'reject_testimonial', id: id})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Testimonial rejected successfully!');
            
            // Update UI tanpa reload
            if (data.testimonial) {
                const card = document.querySelector(`.testimonial-card[data-id="${id}"]`);
                if (card) {
                    // Update status badge
                    const statusBadge = card.querySelector('.testimonial-status-badge');
                    if (statusBadge) {
                        statusBadge.innerHTML = '<span class="status-rejected">✗ Rejected</span>';
                    }
                    // Update data-status attribute
                    card.setAttribute('data-status', '-1');
                }
            }
        } else {
            alert('Error: ' + (data.error || 'Failed to reject testimonial'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the testimonial');
    });
}

function deleteTestimonial(id) {
    if (!confirm('Are you sure you want to delete this testimonial permanently?')) return;
    
    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_testimonial', id: id})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Testimonial deleted successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function updateOrderStatus(orderId, newStatus) {
    if (confirm('Ubah status pesanan menjadi "' + newStatus + '" ?')) {
        fetch('admin_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_order_status',
                id: orderId,
                status: newStatus
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Status berhasil diubah!');
                // Opsional: tambahkan animasi atau refresh row
            } else {
                alert('Gagal: ' + (data.error || 'Unknown error'));
                location.reload(); // rollback jika gagal
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan');
            location.reload();
        });
    }
}

function editProductStock(productId, currentStock) {
    let newStock = prompt('Current stock: ' + currentStock + '\nMasukkan jumlah stok baru:', currentStock);
    if (newStock === null) return;
    
    newStock = parseInt(newStock);
    if (isNaN(newStock) || newStock < 0) {
        alert('Masukkan angka stok yang valid!');
        return;
    }

    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update_product_stock', id: productId, stock: newStock})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Stok berhasil diubah menjadi ' + newStock + '!');

            // 🔥 UPDATE ANGKA STOK LANGSUNG DI TABEL
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                const stockCell = row.cells[4]; // kolom ke-5 (index 4) adalah stok
                stockCell.textContent = newStock;
            }

        } else {
            alert('Error: ' + (data.error || 'Gagal mengubah stok'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan jaringan');
    });
}

function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this Products?')) return;
    
    fetch('admin_actions.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_product', id: id})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Products deleted successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function openAddProductModal() {
    document.getElementById("addProductModal").style.display = "block";
}

function closeAddProductModal() {
    document.getElementById("addProductModal").style.display = "none";
}

function showSection(section) {
    document.querySelectorAll('.data-section').forEach(sec => {
        sec.style.display = 'none';
    });

    document.getElementById(section + '-section').style.display = 'block';
}




// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}