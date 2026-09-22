<?php

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?msg=error');
    exit;
}

$id = isset($_POST['id']) ? $_POST['id'] : '';

if (!ctype_digit((string) $id)) {
    header('Location: index.php?msg=error');
    exit;
}

$id = (int) $id;

try {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header('Location: index.php?msg=deleted');
    exit;
} catch (PDOException $e) {
    header('Location: index.php?msg=error');
    exit;
}
