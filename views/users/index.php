<?php
// Handle Actions
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $nama = $_POST['nama_lengkap'];
    $role = $_POST['role_id'];
    
    $stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $nama, $role]);
}

if (isset($_GET['delete_user']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['delete_user'];
    if ($id != $_SESSION['user_id']) { // Don't delete self
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    header('Location: index.php?page=users');
    exit;
}

// Fetch Data
$users = $db->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id")->fetchAll();
$roles = $db->query("SELECT * FROM roles")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="font-weight: 600;">Manajemen Pengguna</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-user').style.display='flex'">
        <i class="fa-solid fa-user-plus"></i> Tambah User
    </button>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><code style="background:#f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= $u['username'] ?></code></td>
                <td><div style="font-weight: 600;"><?= $u['nama_lengkap'] ?></div></td>
                <td>
                    <span class="badge" style="background: <?= $u['role_name'] == 'admin' ? '#fee2e2' : ($u['role_name'] == 'petugas' ? '#e0e7ff' : '#f1f5f9') ?>; color: <?= $u['role_name'] == 'admin' ? '#991b1b' : ($u['role_name'] == 'petugas' ? '#3730a3' : '#475569') ?>;">
                        <?= ucfirst($u['role_name']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="index.php?page=users&delete_user=<?= $u['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="return confirm('Hapus user ini?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    <?php else: ?>
                        <span style="font-size: 0.75rem; color: var(--text-light); italic;">(Anda)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Add User -->
<div id="modal-user" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 450px; animation: slideUp 0.3s ease-out;">
        <h3 style="margin-bottom: 1.5rem;">Tambah Pengguna Baru</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role_id" class="form-control" required>
                    <?php foreach($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= ucfirst($r['role_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn" onclick="document.getElementById('modal-user').style.display='none'" style="flex: 1; border: 1px solid var(--border);">Batal</button>
                <button type="submit" name="add_user" class="btn btn-primary" style="flex: 1; justify-content: center;">Simpan User</button>
            </div>
        </form>
    </div>
</div>
