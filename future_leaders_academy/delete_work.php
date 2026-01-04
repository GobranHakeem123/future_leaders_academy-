<?php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=no_id');
    exit();
}

$workId = $_GET['id'];

// جلب بيانات العمل لحذف الصورة
$stmt = $pdo->prepare("SELECT * FROM works WHERE id = ?");
$stmt->execute([$workId]);
$work = $stmt->fetch();

if (!$work) {
    header('Location: index.php?error=not_found');
    exit();
}

try {
    // حذف الصورة من المجلد
    if (file_exists($work['media_path'])) {
        unlink($work['media_path']);
    }
    
    // حذف العمل من قاعدة البيانات
    $stmt = $pdo->prepare("DELETE FROM works WHERE id = ?");
    $stmt->execute([$workId]);
    
    header('Location: index.php?success=deleted');
    exit();
    
} catch (Exception $e) {
    header('Location: index.php?error=delete_failed');
    exit();
}
?>