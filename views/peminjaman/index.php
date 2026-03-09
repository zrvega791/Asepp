<?php
// Handle Pinjam Action (for Peminjam)
if (isset($_GET['action']) && $_GET['action'] == 'pinjam' && isset($_GET['alat_id'])) {
    $alat_id = $_GET['alat_id'];
    $user_id = $_SESSION['user_id'];
    $tgl_kembali = date('Y-m-d', strtotime('+3 days')); // Default 3 days
    
    try {
        $stmt = $db->prepare("CALL ajukan_peminjaman(?, ?, ?)");
        $stmt->execute([$user_id, $alat_id, $tgl_kembali]);
        $_SESSION['msg'] = "Peminjaman berhasil diajukan! Menunggu persetujuan petugas.";
    } catch (PDOException $e) {
        $_SESSION['err'] = "Gagal: " . $e->getMessage();
    }
    header('Location: index.php?page=peminjaman');
    exit;
}

// Handle Approve/Reject (for Admin/Petugas)
if (isset($_GET['status']) && in_array($_SESSION['role'], ['admin', 'petugas'])) {
    $id = $_GET['id'];
    $status = $_GET['status']; // approved / rejected
    
    $stmt = $db->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    // Log Activity
    $log = $db->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, ?)");
    $log->execute([$_SESSION['user_id'], "Mengubah status peminjaman ID #$id menjadi $status"]);
    
    header('Location: index.php?page=peminjaman');
    exit;
}

// Handle Return
if (isset($_POST['return_alat'])) {
    $p_id = $_POST['peminjaman_id'];
    $tgl_kembali_aktual = date('Y-m-d');
    
    // Calculate Fine using function
    $stmt_denda = $db->prepare("SELECT hitung_denda(?, ?)");
    $stmt_denda->execute([$p_id, $tgl_kembali_aktual]);
    $denda = $stmt_denda->fetchColumn();
    
    // Update status to 'returned'
    $db->prepare("UPDATE peminjaman SET status = 'returned' WHERE id = ?")->execute([$p_id]);
    
    // Insert into pengembalian
    $stmt_kembali = $db->prepare("INSERT INTO pengembalian (peminjaman_id, tgl_kembali_aktual, denda) VALUES (?, ?, ?)");
    $stmt_kembali->execute([$p_id, $tgl_kembali_aktual, $denda]);
    
    $_SESSION['msg'] = "Alat telah dikembalikan. Denda: Rp " . number_format($denda, 0, ',', '.');
    header('Location: index.php?page=peminjaman');
    exit;
}

// Fetch Data based on Role
if ($_SESSION['role'] == 'peminjam') {
    $stmt = $db->prepare("SELECT p.*, a.nama_alat, u.nama_lengkap FROM peminjaman p JOIN alat a ON p.alat_id = a.id JOIN users u ON p.user_id = u.id WHERE p.user_id = ? ORDER BY p.tgl_pinjam DESC");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $db->query("SELECT p.*, a.nama_alat, u.nama_lengkap FROM peminjaman p JOIN alat a ON p.alat_id = a.id JOIN users u ON p.user_id = u.id ORDER BY p.status DESC, p.tgl_pinjam DESC");
}
$loans = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="font-weight: 600;">Riwayat & Status Peminjaman</h2>
    <div style="display: flex; gap: 1rem;">
        <?php if(in_array($_SESSION['role'], ['admin', 'petugas'])): ?>
            <a href="views/peminjaman/laporan.php" target="_blank" class="btn" style="background: var(--surface); border: 1px solid var(--border); color: var(--text);">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </a>
        <?php endif; ?>
        <?php if(isset($_SESSION['msg'])): ?>
            <span class="badge badge-success" style="padding: 0.5rem 1rem;"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></span>
        <?php endif; ?>
        <?php if(isset($_SESSION['err'])): ?>
            <span class="badge badge-danger" style="padding: 0.5rem 1rem;"><?= $_SESSION['err']; unset($_SESSION['err']); ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Alat</th>
                <th>Peminjam</th>
                <th>Tanggal Pinjam</th>
                <th>Batas Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loans as $loan): ?>
            <tr>
                <td>#<?= $loan['id'] ?></td>
                <td><div style="font-weight: 600;"><?= $loan['nama_alat'] ?></div></td>
                <td><?= $loan['nama_lengkap'] ?></td>
                <td><?= date('d/m/Y', strtotime($loan['tgl_pinjam'])) ?></td>
                <td><?= date('d/m/Y', strtotime($loan['tgl_kembali_rencana'])) ?></td>
                <td>
                    <span class="badge badge-<?= $loan['status'] == 'approved' ? 'success' : ($loan['status'] == 'pending' ? 'warning' : ($loan['status'] == 'returned' ? 'primary' : 'danger')) ?>">
                        <?= ucfirst($loan['status']) ?>
                        <?php if($loan['status'] == 'returned'): ?>
                           <i class="fa-solid fa-check-double"></i>
                        <?php endif; ?>
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($loan['status'] == 'pending' && in_array($_SESSION['role'], ['admin', 'petugas'])): ?>
                            <a href="index.php?page=peminjaman&status=approved&id=<?= $loan['id'] ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: var(--success);">Setujui</a>
                            <a href="index.php?page=peminjaman&status=rejected&id=<?= $loan['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Tolak</a>
                        <?php endif; ?>
                        
                        <?php if ($loan['status'] == 'approved'): ?>
                            <form action="" method="POST" style="margin:0;">
                                <input type="hidden" name="peminjaman_id" value="<?= $loan['id'] ?>">
                                <button type="submit" name="return_alat" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: var(--secondary);" onclick="return confirm('Konfirmasi pengembalian alat?')">
                                    Kembalikan
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($loan['status'] == 'returned'): ?>
                            <?php 
                                $stmt_k = $db->prepare("SELECT * FROM pengembalian WHERE peminjaman_id = ?");
                                $stmt_k->execute([$loan['id']]);
                                $k = $stmt_k->fetch();
                                if($k && $k['denda'] > 0):
                            ?>
                                <span style="font-size: 0.75rem; color: var(--danger); font-weight: 600;">Denda: Rp<?= number_format($k['denda'], 0, ',', '.') ?></span>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: var(--success);">Tepat Waktu</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; if(empty($loans)) echo "<tr><td colspan='7' style='text-align:center'>Belum ada transaksi</td></tr>"; ?>
        </tbody>
    </table>
</div>
