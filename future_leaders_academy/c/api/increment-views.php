<?php
// api/increment-views.php
require_once '../config1.php';
require_once '../functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'معرّف غير صالح']);
    exit;
}

$workId = (int)$_GET['id'];
Helper::incrementViews($workId);

echo json_encode(['success' => true]);
?>