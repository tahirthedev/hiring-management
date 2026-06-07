<?php
require_once __DIR__ . '/../config/database.php';

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUrl(string $url): bool {
    if (empty(trim($url))) return true; // Optional fields
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function uploadCV(array $file): ?string {
    $allowed = ['application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > $maxSize) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'pdf') return null;

    $filename = uniqid('cv_') . '_' . time() . '.pdf';
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return null;
}

function getApplicantCount(PDO $pdo, ?string $status = null): int {
    if ($status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM applicants WHERE status = ?");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM applicants");
    }
    return (int) $stmt->fetchColumn();
}

function getApplicants(PDO $pdo, ?string $status = null, ?string $search = null): array {
    $sql = "SELECT * FROM applicants WHERE 1=1";
    $params = [];

    if ($status && $status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    if ($search) {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY applied_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getApplicant(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getAnswers(PDO $pdo, int $applicantId): array {
    $stmt = $pdo->prepare("SELECT * FROM screening_answers WHERE applicant_id = ? ORDER BY question_number");
    $stmt->execute([$applicantId]);
    return $stmt->fetchAll();
}

function getQuestionText(int $qNum): string {
    $questions = [
        1 => 'A client wants a Blog, Contact Form, Payment Gateway, and User Dashboard. Which plugins/tools would you use and why?',
        2 => 'A WordPress website loads in 8 seconds. Name the first 5 things you would check.',
        3 => 'Elementor Pro stops loading after an update. Describe your troubleshooting process.',
        4 => 'A client wants a fully custom WooCommerce checkout design. How would you approach it and why?',
        5 => 'Describe the most difficult WordPress project you have personally worked on. Explain exactly what YOU built and your responsibilities.',
    ];
    return $questions[$qNum] ?? '';
}

function sendConfirmationEmail(string $to, string $name): bool {
    $subject = "Application Received - WordPress Developer Position";
    $message = "Dear $name,\n\n";
    $message .= "Thank you for applying for the WordPress Developer position.\n\n";
    $message .= "We have received your application and screening test responses. ";
    $message .= "If shortlisted, our team will contact you.\n\n";
    $message .= "Best regards,\nHiring Team";

    $headers = "From: noreply@example.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($to, $subject, $message, $headers);
}

function getStatusBadgeClass(string $status): string {
    return match($status) {
        'Shortlisted' => 'bg-success',
        'Manual Review' => 'bg-warning text-dark',
        'Rejected' => 'bg-danger',
        default => 'bg-secondary',
    };
}
