<?php
// Kết nối CSDL
require_once 'db.php';

// Xác định role (guest hoặc admin)
$role = $_GET['role'] ?? 'guest';

// ===== LẤY DỮ LIỆU HOA TỪ CSDL =====
$sql = "SELECT id, name, description, image FROM flowers ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

$flowers = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $flowers[] = $row;
    }
    mysqli_free_result($result);
}

// ===== XỬ LÝ THÊM / XÓA (CRUD) CHO ADMIN =====
if ($role === 'admin') {
    // Thêm hoa mới
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['action'])
        && $_POST['action'] === 'add'
    ) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');

        if ($name !== '' && $description !== '' && $image !== '') {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO flowers (name, description, image) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, 'sss', $name, $description, $image);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        header('Location: ?role=admin');
        exit;
    }

    // Xóa hoa
    if (isset($_GET['delete_id'])) {
        $id = (int) $_GET['delete_id'];
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM flowers WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        header('Location: ?role=admin');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách các loài hoa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Danh sách các loài hoa xuân – hè</h1>
    <div class="role-switch">
        Vai trò:
        <a href="?role=guest" class="<?php echo $role === 'guest' ? 'active' : ''; ?>">Khách</a>
        |
        <a href="?role=admin" class="<?php echo $role === 'admin' ? 'active' : ''; ?>">Quản trị</a>
    </div>
</header>

<div class="container">
    <?php if ($role === 'guest'): ?>
        <!-- ========== GIAO DIỆN NGƯỜI DÙNG KHÁCH (dạng bài viết) ========== -->
        <h2 style="margin-top:0;">Gợi ý các loài hoa nên trồng dịp xuân – hè</h2>

        <?php if (empty($flowers)): ?>
            <p>Hiện chưa có dữ liệu hoa trong CSDL.</p>
        <?php else: ?>
            <?php foreach ($flowers as $flower): ?>
                <article class="flower-article">
                    <img src="images/<?php echo htmlspecialchars($flower['image']); ?>" 
                         alt="<?php echo htmlspecialchars($flower['name']); ?>">
                    <div>
                        <h2><?php echo htmlspecialchars($flower['name']); ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($flower['description'])); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php else: ?>
        <!-- ========== GIAO DIỆN QUẢN TRỊ (dạng bảng CRUD) ========== -->
        <h2 style="margin-top:0;">Quản lý danh sách hoa</h2>

        <!-- Form thêm hoa mới -->
        <div class="add-form">
            <form method="post">
                <input type="hidden" name="action" value="add">
                <label>Tên hoa:</label>
                <input type="text" name="name" required>

                <label>Mô tả:</label>
                <textarea name="description" required></textarea>

                <label>Tên file ảnh:</label>
                <input type="text" name="image" placeholder="vd: hoa_hong.jpg" required>

                <button type="submit" class="btn-add" style="margin-top:8px;">+ Thêm hoa</button>
            </form>
        </div>

        <!-- Bảng danh sách hoa -->
        <?php if (empty($flowers)): ?>
            <p>Hiện chưa có dữ liệu hoa trong CSDL.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên hoa</th>
                        <th>Mô tả</th>
                        <th>Ảnh</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($flowers as $index => $flower): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($flower['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($flower['description'])); ?></td>
                        <td>
                            <img src="images/<?php echo htmlspecialchars($flower['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($flower['name']); ?>">
                            <div class="note"><?php echo htmlspecialchars($flower['image']); ?></div>
                        </td>
                        <td class="actions">
                            <a href="?role=admin&delete_id=<?php echo $flower['id']; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa?');">
                               Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
