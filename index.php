<?php

require_once __DIR__ . '/config/database.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $pdo = Database::getInstance()->getConnection();

    if ($search !== '') {
        $stmt = $pdo->prepare(
            "SELECT products.id, products.name, categories.name AS category_name,
                    suppliers.name AS supplier_name, products.price, products.stock
             FROM products
             JOIN categories ON products.category_id = categories.id
             JOIN suppliers ON products.supplier_id = suppliers.id
             WHERE products.name LIKE :search
             ORDER BY products.id DESC"
        );
        $stmt->execute(['search' => '%' . $search . '%']);
    } else {
        $stmt = $pdo->prepare(
            "SELECT products.id, products.name, categories.name AS category_name,
                    suppliers.name AS supplier_name, products.price, products.stock
             FROM products
             JOIN categories ON products.category_id = categories.id
             JOIN suppliers ON products.supplier_id = suppliers.id
             ORDER BY products.id DESC"
        );
        $stmt->execute();
    }

    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $dbError = true;
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$messages = [
    'created' => 'Produk berhasil ditambahkan.',
    'updated' => 'Produk berhasil diubah.',
    'deleted' => 'Produk berhasil dihapus.',
    'error' => 'Terjadi kesalahan saat memproses data.',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventaris Barang</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Inventaris Barang</h1>

    <?php if (isset($dbError)): ?>
    <div class="flash flash-error">Tidak dapat memuat data produk saat ini.</div>
    <?php endif; ?>

    <?php if ($msg !== '' && isset($messages[$msg])): ?>
    <div class="flash <?php echo $msg === 'error' ? 'flash-error' : 'flash-success'; ?>">
        <?php echo htmlspecialchars($messages[$msg]); ?>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <a class="btn btn-primary" href="create.php">Tambah Produk</a>
        <form class="search-form" method="GET" action="index.php">
            <input type="text" name="search" placeholder="Cari nama produk..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-secondary" type="submit">Cari</button>
            <?php if ($search !== ''): ?>
            <a class="btn btn-secondary" href="index.php">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <table class="data-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Supplier</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($products) === 0): ?>
        <tr>
            <td colspan="7" class="empty-row">Tidak ada data produk.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><?php echo htmlspecialchars((string) $product['id']); ?></td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
            <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
            <td>Rp <?php echo htmlspecialchars(number_format((float) $product['price'], 0, ',', '.')); ?></td>
            <td><?php echo htmlspecialchars((string) $product['stock']); ?></td>
            <td class="actions">
                <a class="btn btn-edit" href="edit.php?id=<?php echo urlencode((string) $product['id']); ?>">Edit</a>
                <form method="POST" action="delete.php" onsubmit="return confirm('Hapus produk ini?');">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $product['id']); ?>">
                    <button class="btn btn-delete" type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
