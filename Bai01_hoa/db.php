<?php
$host = 'localhost';
$user = 'root';      // XAMPP mặc định là root
$pass = '';          // XAMPP mặc định là rỗng
$dbname = 'bai01_hoa';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die('Kết nối CSDL thất bại: ' . mysqli_connect_error());
}

// Đặt charset để tránh lỗi tiếng Việt
mysqli_set_charset($conn, 'utf8mb4');
