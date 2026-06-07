<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(404);
    exit('Not found');
}

$stmt = $pdo->prepare("SELECT cv_filename, cv_data FROM applicants WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['cv_data']) {
    http_response_code(404);
    exit('CV not found');
}

$filename = $row['cv_filename'] ?: 'cv.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . strlen($row['cv_data']));
echo $row['cv_data'];
exit;
