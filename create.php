<?php

require_once __DIR__ . '/config/database.php';

$errors = [];
$name = '';
$categoryId = '';
$supplierId = '';
$price = '';
$stock = '';

try {
    $pdo = Database::getInstance()->getConnection();
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
    $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $suppliers = [];
    $errors[] = 'Tidak dapat memuat data kategori atau supplier.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $categoryId = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $supplierId = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';
    $price = isset($_POST['price']) ? $_POST['price'] : '';
    $stock = isset($_POST['stock']) ? $_POST['stock'] : '';

    if ($name === '' || mb_strlen($name) > 150) {
        $errors[] = 'Nama produk wajib diisi dan maksimal 150 karakter.';
    }

    if (!ctype_digit((string) $categoryId)) {
        $errors[] = 'Kategori wajib dipilih.';
    }

    if (!ctype_digit((string) $supplierId)) {
        $errors[] = 'Supplier wajib dipilih.';
    }

    if (!is_numeric($price) || (float) $price < 0) {
        $errors[] = 'Harga harus berupa angka positif.';
    }

    if (!is_numeric($stock) || (int) $stock < 0 || (string) (int) $stock !== (string) $stock) {
        $errors[] = 'Stok harus berupa bilangan bulat positif.';
    }

    if (count($errors) === 0) {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO products (name, category_id, supplier_id, price, stock)
                 VALUES (:name, :category_id, :supplier_id, :price, :stock)"
            );
            $stmt->execute([
                'name' => $name,
                'category_id' => (int) $categoryId,
                'supplier_id' => (int) $supplierId,
                'price' => (float) $price,
                'stock' => (int) $stock,
            ]);

            header('Location: index.php?msg=created');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan saat menyimpan data.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Tambah Produk</h1>

    <?php if (count($errors) > 0): ?>
    <div class="flash flash-error">
        <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form class="data-form" method="POST" action="create.php">
        <label for="name">Nama Produk</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" maxlength="150" required>

        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars((string) $category['id']); ?>" <?php echo ((string) $categoryId === (string) $category['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <label for="supplier_id">Supplier</label>
        <select id="supplier_id" name="supplier_id" required>
            <option value="">-- Pilih Supplier --</option>
            <?php foreach ($suppliers as $supplier): ?>
            <option value="<?php echo htmlspecialchars((string) $supplier['id']); ?>" <?php echo ((string) $supplierId === (string) $supplier['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($supplier['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <label for="price">Harga</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars((string) $price); ?>" required>

        <label for="stock">Stok</label>
        <input type="number" id="stock" name="stock" step="1" min="0" value="<?php echo htmlspecialchars((string) $stock); ?>" required>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="index.php">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
