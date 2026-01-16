<?php
// Masukkan file utama library
include_once __DIR__ . '/../../libs/phpqrcode/qrlib.php';

// Ambil NIM dari URL
$nim = isset($_GET['nim']) ? $_GET['nim'] : '000000';

// Agar browser tahu ini adalah sebuah gambar PNG
header('Content-Type: image/png');

// Parameter: (Data, SimpanKeFile, ErrorCorrection, UkuranPixel, Margin)
// false artinya langsung tampilkan ke layar (tidak simpan ke file)
QRcode::png($nim, false, QR_ECLEVEL_L, 6, 2);
