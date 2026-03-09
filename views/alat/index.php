<?php
// Handle Actions
if (isset($_POST['add_alat'])) {
    $nama = $_POST['nama_alat'];
    $kategori = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    
    $stmt = $db->prepare("INSERT INTO alat (nama_alat, kategori_id, stok, deskripsi) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nama, $kategori, $stok, $deskripsi]);
    
    // Log Activity
    $log = $db->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, ?)");
    $log->execute([$_SESSION['user_id'], "Menambah alat baru: $nama"]);
}

if (isset($_GET['delete_id']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['delete_id'];
    $db->prepare("DELETE FROM alat WHERE id = ?")->execute([$id]);
    header('Location: index.php?page=alat');
    exit;
}

// Fetch Data
$items = $db->query("SELECT a.*, k.nama_kategori FROM alat a LEFT JOIN kategori k ON a.kategori_id = k.id")->fetchAll();
$categories = $db->query("SELECT * FROM kategori")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="font-weight: 600;">Data Inventaris Alat</h2>
    <?php if ($_SESSION['role'] == 'admin'): ?>
    <button class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='flex'">
        <i class="fa-solid fa-plus"></i> Tambah Alat
    </button>
    <?php endif; ?>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Alat</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>#<?= $item['id'] ?></td>
                <td>
                    <div style="font-weight: 600;"><?= $item['nama_alat'] ?></div>
                </td>
                <td><span class="badge" style="background:#f1f5f9; color:var(--text);"><?= $item['nama_kategori'] ?></span></td>
                <td>
                    <span style="font-weight: 700; color: <?= $item['stok'] > 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                        <?= $item['stok'] ?>
                    </span>
                </td>
                <td style="font-size: 0.85rem; color: var(--text-light); max-width: 200px;"><?= $item['deskripsi'] ?></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($_SESSION['role'] == 'peminjam' && $item['stok'] > 0): ?>
                            <a href="index.php?page=peminjaman&action=pinjam&alat_id=<?= $item['id'] ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Pinjam</a>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <a href="index.php?page=alat&delete_id=<?= $item['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="return confirm('Hapus alat ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; if(empty($items)) echo "<tr><td colspan='6' style='text-align:center'>Data belum tersedia</td></tr>"; ?>
        </tbody>
    </table>
</div>

<!-- Simple Add Modal -->
<div id="modal-add" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; animation: slideUp 0.3s ease-out;">
        <h3 style="margin-bottom: 1.5rem;">Tambah Alat Baru</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Nama Alat</label>
                <input type="text" name="nama_alat" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="kategori_id" class="form-control" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['nama_kategori'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Stok Awal</label>
                <input type="number" name="stok" class="form-control" value="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn" onclick="document.getElementById('modal-add').style.display='none'" style="flex: 1; border: 1px solid var(--border);">Batal</button>
                <button type="submit" name="add_alat" class="btn btn-primary" style="flex: 1; justify-content: center;">Simpan Alat</button>
            </div>
        </form>
    </div>
</div>
