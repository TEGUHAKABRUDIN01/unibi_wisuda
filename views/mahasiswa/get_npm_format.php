<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_GET['id_fakultas'])) {
    echo json_encode(['status' => 'error', 'message' => 'Fakultas tidak dipilih']);
    exit;
}

$id_fakultas = intval($_GET['id_fakultas']);

$query = mysqli_query($conn, "SELECT npm_format FROM fakultas WHERE id_fakultas = $id_fakultas");
$result = mysqli_fetch_assoc($query);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Fakultas tidak ditemukan']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'npm_format' => $result['npm_format'] ?? ''
]);
