<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$applicantId = (int)($_POST['applicant_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if (!$applicantId) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE applicants SET admin_notes = ? WHERE id = ?");
$result = $stmt->execute([$notes, $applicantId]);

echo json_encode(['success' => $result]);
