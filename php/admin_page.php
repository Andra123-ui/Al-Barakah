<?php
session_start();

//admin_page.php

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Koneksi database
include 'config.php';

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM pesanan")->fetch_assoc()['count'];
$pending_testimonials = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE is_approved = 0")->fetch_assoc()['count'];

// Get recent users
$users_query = "SELECT * FROM users ORDER BY id ASC LIMIT 10";
$users_result = $conn->query($users_query);

// Get recent orders
$orders_query = "SELECT * FROM pesanan ORDER BY id ASC LIMIT 10";
$orders_result = $conn->query($orders_query);


// Get ALL testimonials (pending, approved, and rejected)
$testimonials_query = "SELECT * FROM testimonials ORDER BY created_at ASC";
$testimonials_result = $conn->query($testimonials_query);

// Get all products
$products_query = "SELECT * FROM products ORDER BY id ASC";
$products_result = $conn->query($products_query);

function getMonthlyProductReport($conn, $month) {
    $result = [];

    $q = $conn->query("
        SELECT produk, tanggal 
        FROM pesanan
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$month'
    ");

    while ($row = $q->fetch_assoc()) {

        if (empty($row['produk'])) continue;

        $items = json_decode($row['produk'], true);
        if (!$items) continue;

        // jika object tunggal → array
        if (isset($items['id'])) {
            $items = [$items];
        }

        foreach ($items as $item) {

            $id    = $item['id'] ?? md5(json_encode($item));
            $name  = $item['name'] ?? 'Produk';

            // 🔥 QTY (fallback 1)
            $qty = $item['qty'] ?? 1;

            // 🔥 PRICE
            $price = $item['price'] ?? 0;
            $image = $item['image'] ?? null;

            // fallback ambil dari tabel products
            if (($price == 0 || !$image) && isset($item['id'])) {
                $p = $conn->query("SELECT price, image FROM products WHERE id = {$item['id']} LIMIT 1");
                if ($p && $p->num_rows > 0) {
                    $prod  = $p->fetch_assoc();
                    $price = $price ?: $prod['price'];
                    $image = $image ?: $prod['image'];
                }
            }

            if (!isset($result[$id])) {
                $result[$id] = [
                    'name'  => $name,
                    'image' => $image ?: 'assets/no-image.png',
                    'price' => $price,
                    'qty'   => 0,
                    'total' => 0,
                    'dates' => []
                ];
            }

            // ✅ AKUMULASI
            $result[$id]['qty']   += $qty;
            $result[$id]['total'] += ($price * $qty);
            $result[$id]['dates'][] = date('d M Y', strtotime($row['tanggal']));
        }
    }

    return $result;
}




// ===============================
// LAPORAN BULANAN
// ===============================
$bulan_ini  = date('Y-m');
$bulan_lalu = date('Y-m', strtotime('-1 month'));

$produk_bulan_ini  = getMonthlyProductReport($conn, $bulan_ini);
$produk_bulan_lalu = getMonthlyProductReport($conn, $bulan_lalu);
$total_pendapatan_bulan_ini = 0;
foreach ($produk_bulan_ini as $p) {
    $total_pendapatan_bulan_ini += $p['total'];
}

$total_pendapatan_bulan_lalu = 0;
foreach ($produk_bulan_lalu as $p) {
    $total_pendapatan_bulan_lalu += $p['total'];
}




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="#users" onclick="showSection('users')">Manage Users</a></li>
                <li><a href="#orders" onclick="showSection('orders')">Orders</a></li>
                <li><a href="#testimonials" onclick="showSection('testimonials')">Testimonials</a></li>
                <li><a href="#products" onclick="showSection('products')">Products & Stock</a></li>
                <li><a href="#report" onclick="showSection('report')">Laporan Bulanan</a></li>

            </ul>
        </nav>

        <main class="admin-content">
            <!-- Statistics -->
            <section class="stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?= $total_users ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?= $total_orders ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Testimonials</h3>
                    <p class="stat-number"><?= $pending_testimonials ?></p>
                </div>
            </section>

            <!-- Users Section -->
            <section class="data-section" id="users-section">
                <div class="section-header">
                    <h2>User Management</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users_result->num_rows > 0): ?>
                                <?php while($user = $users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="badge"><?= htmlspecialchars($user['role']) ?></span></td>
                                        <td>
                                            <button class="btn-action btn-delete" onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

           <!-- Orders Section -->
<section class="data-section" id="orders-section" style="display: none;">
    <div class="section-header">
        <h2>Orders Management</h2>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>WhatsApp</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>Tanggal & Waktu</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders_result->num_rows > 0): ?>
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                        <tr data-order-id="<?= $order['id'] ?>">
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['nama']) ?></td>
                            <td><?= htmlspecialchars($order['whatsapp']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                             <td><?= htmlspecialchars($order['alamat']) ?></td>
                             <td>
    <?= date('d M Y, H:i', strtotime($order['tanggal'])) ?>
</td>

                            <td>Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                            <td><span class="badge badge-payment"><?= htmlspecialchars($order['pembayaran']) ?></span></td>
                            
                            <!-- Kolom Status dengan Select -->
                            <td>
                                <select class="status-select" data-id="<?= $order['id'] ?>" onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </td>
                            
                            <td>
                                <button class="btn-action btn-view" onclick="viewOrder(<?= $order['id'] ?>)">View</button>
                                <button class="btn-action btn-delete" onclick="deleteOrder(<?= $order['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="data-section" id="report-section" style="display:none;">
    <div class="section-header">
        <h2>📊 Laporan Penjualan Bulanan</h2>
    </div>

    <!-- BULAN INI -->
     <p style="font-size:16px; font-weight:bold; margin:10px 0;">
    💰 Total Pendapatan Bulan Ini: 
    <span style="color:green;">
        Rp <?= number_format($total_pendapatan_bulan_ini, 0, ',', '.') ?>
    </span>
</p>

    <h3>🟢 Bulan Ini (<?= date('F Y') ?>)</h3>
    <table class="report-table">
        <thead>
<tr>
    <th>Gambar</th>
    <th>Produk</th>
    <th>Harga</th>
    <th>Total Dibeli</th>
    <th>Total Pendapatan</th>
    <th>Tanggal</th>
</tr>
</thead>

        <tbody>
<?php if (!empty($produk_bulan_ini)): ?>
    <?php foreach ($produk_bulan_ini as $p): ?>
    <tr>
        <td>
            <img src="<?= htmlspecialchars($p['image']) ?>" class="report-img">
        </td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
        <td><?= $p['qty'] ?></td>
        <td><strong>Rp <?= number_format($p['total'], 0, ',', '.') ?></strong></td>
        <td><?= implode(', ', array_unique($p['dates'])) ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6">Belum ada transaksi</td>
    </tr>
<?php endif; ?>
</tbody>

    </table>

    <br>
<p style="font-size:16px; font-weight:bold; margin:10px 0;">
    💰 Total Pendapatan Bulan Lalu: 
    <span style="color:#1e88e5;">
        Rp <?= number_format($total_pendapatan_bulan_lalu, 0, ',', '.') ?>
    </span>
</p>

    <!-- BULAN LALU -->
    <h3>🔵 Bulan Lalu (<?= date('F Y', strtotime('-1 month')) ?>)</h3>
    <table class="report-table">
        <thead>
<tr>
    <th>Gambar</th>
    <th>Produk</th>
    <th>Harga</th>
    <th>Total Dibeli</th>
    <th>Total Pendapatan</th>
    <th>Tanggal</th>
</tr>
</thead>

        <tbody>
<?php if (!empty($produk_bulan_lalu)): ?>
    <?php foreach ($produk_bulan_lalu as $p): ?>
    <tr>
        <td>
            <img src="<?= htmlspecialchars($p['image']) ?>" class="report-img">
        </td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
        <td><?= $p['qty'] ?></td>
        <td><strong>Rp <?= number_format($p['total'], 0, ',', '.') ?></strong></td>
        <td><?= implode(', ', array_unique($p['dates'])) ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6">Belum ada transaksi</td>
    </tr>
<?php endif; ?>
</tbody>

    </table>
</section>



<!-- Products Section -->
<section class="data-section" id="products-section" style="display: none;">
    <div class="section-header">
    <h2>Products & Stock Management</h2>
    <button class="btn-action btn-add" onclick="openAddProductModal()">+ Add Product</button>
</div>

    <div class="table-container">
        <table>
            <thead>
                <thead>
    <tr>
        <th>ID</th>
        <th>Gambar</th>
        <th>Product Name</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>
    </tr>
</thead>

            </thead>
            <tbody>
<?php if ($products_result->num_rows > 0): ?>
    <?php while($product = $products_result->fetch_assoc()): ?>
        <tr data-product-id="<?= $product['id'] ?>">

            <td><?= $product['id'] ?></td>

            <!-- 🔥 Tampilkan Gambar -->
            <td>
                <img src="<?= htmlspecialchars($product['image']) ?>"
                     style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
            </td>

            <td><?= htmlspecialchars($product['name']) ?></td>
            <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
            <td><?= $product['stock'] ?></td>

            <td>
                <button class="btn-action btn-edit" onclick="editProductStock(<?= $product['id'] ?>, <?= $product['stock'] ?>)">Edit Stock</button>
                <button class="btn-action btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
            </td>

        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="no-data">No products found</td>
    </tr>
<?php endif; ?>
</tbody>

        </table>
    </div>
</section>

            <!-- Testimonials Section -->
            <section class="data-section" id="testimonials-section" style="display: none;">
                <div class="section-header">
                    <h2>Testimonials Management</h2>
                </div>
                <div class="testimonials-grid">
                    <?php if ($testimonials_result->num_rows > 0): ?>
                        <?php while($testimonial = $testimonials_result->fetch_assoc()): ?>
                            <div class="testimonial-card" data-id="<?= $testimonial['id'] ?>" data-status="<?= $testimonial['is_approved'] ?>">
                                <div class="testimonial-status-badge">
                                    <?php if ($testimonial['is_approved'] == 1): ?>
                                        <span class="status-approved">✓ Approved</span>
                                    <?php elseif ($testimonial['is_approved'] == -1): ?>
                                        <span class="status-rejected">✗ Rejected</span>
                                    <?php else: ?>
                                        <span class="status-pending">⏱ Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="testimonial-header">
                                    <div>
                                        <h3><?= htmlspecialchars($testimonial['customer_name']) ?></h3>
                                        <p class="location"><?= htmlspecialchars($testimonial['location']) ?></p>
                                    </div>
                                    <div class="rating">
                                        <?php for($i = 0; $i < $testimonial['rating']; $i++): ?>
                                            ⭐
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="message"><?= htmlspecialchars($testimonial['message']) ?></p>
                                <div class="testimonial-actions">
                                    <button class="btn-action btn-approve" onclick="approveTestimonial(<?= $testimonial['id'] ?>)">Approve</button>
                                    <button class="btn-action btn-reject" onclick="rejectTestimonial(<?= $testimonial['id'] ?>)">Reject</button>
                                    <button class="btn-action btn-delete" onclick="deleteTestimonial(<?= $testimonial['id'] ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-data">No testimonials found</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    

    


    <!-- Modal for Order Details -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="orderDetails"></div>
        </div>
    </div>
 
    <!-- Modal Tambah Produk -->
<div class="modal" id="addProductModal" style="display:none;">
    <div class="modal-content large">
        <span class="close" onclick="closeAddProductModal()">&times;</span>

        <h2>Tambah Produk Baru</h2>

        <form id="addProductForm" action="add_product.php" method="POST" enctype="multipart/form-data">

            <label>Nama Produk</label>
            <input type="text" name="name" class="form-control" required>

            <label>Slug (opsional)</label>
            <input type="text" name="slug" class="form-control">

            <label>Harga</label>
            <input type="number" name="price" class="form-control" required>

            <label>Stok</label>
            <input type="number" name="stock" class="form-control" value="0" required>

            <label>Deskripsi Produk</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Tulis deskripsi..."></textarea>

            <label>Kategori Produk</label>
            <select name="category_id" class="form-control">
                <option value="">-- Pilih Kategori --</option>

                <?php
                $cats = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                while ($c = $cats->fetch_assoc()):
                ?>
                    <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Gambar Produk</label>
            <input type="file" name="image" class="form-control" accept="image/*">

            <button type="submit" class="btn-action btn-success" style="margin-top:15px;">Simpan Produk</button>
        </form>
    </div>
</div>


    <script src="js/admin.js"></script>
</body>
</html>