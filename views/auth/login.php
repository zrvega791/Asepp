<?php
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role_name'];
        
        // Log Activity
        $log = $db->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, 'User login ke sistem')");
        $log->execute([$user['id']]);
        
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Peminjaman Alat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-container">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: var(--primary); font-weight: 700; font-size: 2rem;">Pinjam<span style="color: var(--text);">Alat</span></h2>
            <p style="color: var(--text-light);">Silakan login untuk meminjam alat</p>
        </div>

        <?php if(isset($error)): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.9rem; border: 1px solid #fecaca;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                    <input type="text" name="username" class="form-control" style="padding-left: 2.5rem;" placeholder="Masukkan username" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                    <input type="password" name="password" class="form-control" style="padding-left: 2.5rem;" placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                Login ke Akun
            </button>
        </form>
        
        <div style="margin-top: 2rem; text-align: center;">
            <p style="font-size: 0.8rem; color: var(--text-light);">UKK RPL 2025/2026 &bull; Antigravity Framework</p>
        </div>
    </div>
</body>
</html>
