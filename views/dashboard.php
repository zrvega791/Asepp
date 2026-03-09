<?php
// Fetch Stats
$total_alat = $db->query("SELECT COUNT(*) FROM alat")->fetchColumn();
$total_peminjaman = $db->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'approved'")->fetchColumn();
$total_pending = $db->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'pending'")->fetchColumn();
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch Recent Logs
$logs = $db->query("SELECT l.*, u.nama_lengkap FROM log_aktivitas l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.waktu DESC LIMIT 5")->fetchAll();
?>

<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">
            <i class="fa-solid fa-box"></i>
        </div>
        <div>
            <p style="color: var(--text-light); font-size: 0.9rem;">Total Alat</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?= $total_alat ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #15803d;">
            <i class="fa-solid fa-hand-holding-hand"></i>
        </div>
        <div>
            <p style="color: var(--text-light); font-size: 0.9rem;">Dipinjam</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?= $total_peminjaman ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background: #fef9c3; color: #a16207;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <p style="color: var(--text-light); font-size: 0.9rem;">Pending</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?= $total_pending ?></h3>
        </div>
    </div>
    <?php if ($_SESSION['role'] == 'admin'): ?>
    <div class="card stat-card">
        <div class="stat-icon" style="background: #f1f5f9; color: #475569;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <p style="color: var(--text-light); font-size: 0.9rem;">Total User</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?= $total_users ?></h3>
        </div>
    </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-weight: 600;">Status Peminjaman Terbaru</h3>
            <a href="index.php?page=peminjaman" style="font-size: 0.8rem; color: var(--primary);">Lihat Semua</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Alat</th>
                    <th>Peminjam</th>
                    <th>Tgl Pinjam</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $db->query("SELECT p.*, a.nama_alat, u.nama_lengkap FROM peminjaman p JOIN alat a ON p.alat_id = a.id JOIN users u ON p.user_id = u.id ORDER BY tgl_pinjam DESC LIMIT 5");
                $recent_loans = $stmt->fetchAll();
                foreach ($recent_loans as $loan):
                ?>
                <tr>
                    <td><?= $loan['nama_alat'] ?></td>
                    <td><?= $loan['nama_lengkap'] ?></td>
                    <td><?= date('d/m/Y', strtotime($loan['tgl_pinjam'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $loan['status'] == 'approved' ? 'success' : ($loan['status'] == 'pending' ? 'warning' : 'danger') ?>">
                            <?= ucfirst($loan['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; if(empty($recent_loans)) echo "<tr><td colspan='4' style='text-align:center'>Tidak ada data</td></tr>"; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="font-weight: 600; margin-bottom: 1.5rem;">Log Aktivitas</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($logs as $log): ?>
            <div style="display: flex; gap: 1rem; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); margin-top: 0.5rem; flex-shrink: 0;"></div>
                <div>
                    <p style="font-size: 0.85rem; font-weight: 500; font-family: 'Inter', sans-serif;"><?= $log['aktivitas'] ?></p>
                    <p style="font-size: 0.75rem; color: var(--text-light);"><?= $log['nama_lengkap'] ?> &bull; <?= date('H:i', strtotime($log['waktu'])) ?></p>
                </div>
            </div>
            <?php endforeach; if(empty($logs)) echo "<p style='text-align:center; color:var(--text-light)'>Kosong</p>"; ?>
        </div>
    </div>
</div>
