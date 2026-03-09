<?php
session_start();
require_once 'config/db.php';

// Simple Router
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Auth Protection
if (!isset($_SESSION['user_id']) && $page != 'login') {
    header('Location: index.php?page=login');
    exit;
}

// Global DB Instance
$db = Database::getInstance();

ob_start();

switch ($page) {
    case 'login':
        include 'views/auth/login.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        break;
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'alat':
        include 'views/alat/index.php';
        break;
    case 'peminjaman':
        include 'views/peminjaman/index.php';
        break;
    case 'users':
        if ($_SESSION['role'] != 'admin') {
            header('Location: index.php?page=dashboard');
        }
        include 'views/users/index.php';
        break;
    default:
        include 'views/404.php';
        break;
}

$content = ob_get_clean();

// Use layout unless it's login page
if ($page == 'login') {
    echo $content;
} else {
    include 'views/layout.php';
}
