<?php
// api/get-work.php
require_once '../config1.php';
require_once '../functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'معرّف غير صالح']);
    exit;
}

$workId = (int)$_GET['id'];
$work = Helper::getWorkById($workId);

if ($work) {
    // إضافة نوع الملف
    $work['file_type'] = Helper::getFileType($work['file_extension']);
    echo json_encode($work, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'لم يتم العثور على العمل']);
}
?>