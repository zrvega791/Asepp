<?php
// Proxy untuk menjalankan index.php dari root
// Vercel seringkali mencari fungsi di dalam folder api/
chdir(__DIR__ . '/../');
require 'index.php';
