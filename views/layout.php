<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat - Modern App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="logo" style="margin-bottom: 2rem;">
                <h2 style="color: var(--primary); font-weight: 700;">Pinjam<span style="color: var(--text);">Alat</span></h2>
            </div>
            
            <nav style="flex: 1;">
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="index.php?page=dashboard" class="btn <?= $_GET['page'] == 'dashboard' ? 'btn-primary' : '' ?>" style="width: 100%; justify-content: flex-start; background: <?= $_GET['page'] == 'dashboard' ? 'var(--primary)' : 'transparent' ?>; color: <?= $_GET['page'] == 'dashboard' ? 'white' : 'var(--text-light)' ?>;">
                            <i class="fa-solid fa-gauge"></i> Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="index.php?page=alat" class="btn <?= $_GET['page'] == 'alat' ? 'btn-primary' : '' ?>" style="width: 100%; justify-content: flex-start; background: <?= $_GET['page'] == 'alat' ? 'var(--primary)' : 'transparent' ?>; color: <?= $_GET['page'] == 'alat' ? 'white' : 'var(--text-light)' ?>;">
                            <i class="fa-solid fa-box-archive"></i> Daftar Alat
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="index.php?page=peminjaman" class="btn <?= $_GET['page'] == 'peminjaman' ? 'btn-primary' : '' ?>" style="width: 100%; justify-content: flex-start; background: <?= $_GET['page'] == 'peminjaman' ? 'var(--primary)' : 'transparent' ?>; color: <?= $_GET['page'] == 'peminjaman' ? 'white' : 'var(--text-light)' ?>;">
                            <i class="fa-solid fa-hand-holding-hand"></i> Peminjaman
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="index.php?page=users" class="btn <?= $_GET['page'] == 'users' ? 'btn-primary' : '' ?>" style="width: 100%; justify-content: flex-start; background: <?= $_GET['page'] == 'users' ? 'var(--primary)' : 'transparent' ?>; color: <?= $_GET['page'] == 'users' ? 'white' : 'var(--text-light)' ?>;">
                            <i class="fa-solid fa-users"></i> Manajemen User
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="user-info" style="border-top: 1px solid var(--border); padding-top: 1rem;">
                <p style="font-weight: 600; font-size: 0.9rem;"><?= $_SESSION['nama_lengkap'] ?? 'Administrator' ?></p>
                <p style="font-size: 0.8rem; color: var(--text-light); text-transform: capitalize; margin-bottom: 1rem;"><?= $_SESSION['role'] ?? 'Guest' ?></p>
                <a href="index.php?page=logout" class="btn btn-danger" style="width: 100%; font-size: 0.9rem;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 style="font-size: 1.5rem; text-transform: capitalize;"><?= str_replace('_', ' ', $_GET['page']) ?></h1>
                    <p style="color: var(--text-light); font-size: 0.9rem;">Selamat datang di Sistem Peminjaman Alat</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <div class="glass" style="padding: 0.5rem 1rem; border-radius: var(--radius); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-regular fa-calendar"></i>
                        <span id="date-now"><?= date('d M Y') ?></span>
                    </div>
                </div>
            </header>

            <div class="content">
                <?= $content ?>
            </div>
        </main>
    </div>
</body>
</html>
