<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'petugas'])) {
    die("Akses ditolak.");
}

$db = Database::getInstance();

// Simple Filter
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$query = "SELECT p.*, a.nama_alat, u.nama_lengkap, pg.tgl_kembali_aktual, pg.denda 
          FROM peminjaman p 
          JOIN alat a ON p.alat_id = a.id 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN pengembalian pg ON p.id = pg.peminjaman_id";

if ($filter != 'all') {
    $query .= " WHERE p.status = :status";
}
$query .= " ORDER BY p.tgl_pinjam DESC";

$stmt = $db->prepare($query);
if ($filter != 'all') {
    $stmt->execute(['status' => $filter]);
} else {
    $stmt->execute();
}
$data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Alat</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .no-print { margin-bottom: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak PDF / Print</button>
        <a href="../index.php?page=peminjaman">Kembali</a>
    </div>

    <div class="header">
        <h1>LAPORAN PEMINJAMAN ALAT</h1>
        <p>UKK SMK RPL 2025/2026</p>
        <p>Tanggal Cetak: <?= date('d/m/Y H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Peminjam</th>
                <th>Nama Alat</th>
                <th>Tgl Pinjam</th>
                <th>Batas Kembali</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($data as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama_lengkap'] ?></td>
                <td><?= $row['nama_alat'] ?></td>
                <td><?= date('d/m/Y', strtotime($row['tgl_pinjam'])) ?></td>
                <td><?= date('d/m/Y', strtotime($row['tgl_kembali_rencana'])) ?></td>
                <td><?= $row['tgl_kembali_aktual'] ? date('d/m/Y', strtotime($row['tgl_kembali_aktual'])) : '-' ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>Rp <?= number_format($row['denda'] ?? 0, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 50px; float: right; text-align: center;">
        <p>Dicetak oleh,</p>
        <br><br><br>
        <p><strong><?= $_SESSION['nama_lengkap'] ?></strong><br><?= ucfirst($_SESSION['role']) ?></p>
    </div>
</body>
</html>
