<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /admin/');
    exit;
}

$applicant = getApplicant($pdo, $id);
if (!$applicant) {
    header('Location: /admin/');
    exit;
}

$answers = getAnswers($pdo, $id);

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $newStatus = $_POST['new_status'] ?? '';
    $allowed = ['Shortlisted', 'Manual Review', 'Rejected'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE applicants SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        $applicant['status'] = $newStatus;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($applicant['full_name']) ?> - Applicant Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/admin/">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="/admin/logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Left Column: Applicant Info -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person-fill text-primary"></i> Applicant Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-circle mx-auto mb-2">
                                <?= strtoupper(substr($applicant['full_name'], 0, 1)) ?>
                            </div>
                            <h5 class="fw-bold mb-1"><?= sanitize($applicant['full_name']) ?></h5>
                            <span class="badge <?= getStatusBadgeClass($applicant['status']) ?> fs-6">
                                <?= $applicant['status'] ?>
                            </span>
                        </div>

                        <hr>

                        <div class="info-list">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-envelope"></i> Email</span>
                                <span><a href="mailto:<?= sanitize($applicant['email']) ?>"><?= sanitize($applicant['email']) ?></a></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-phone"></i> Phone</span>
                                <span><?= sanitize($applicant['phone']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-geo-alt"></i> City</span>
                                <span><?= sanitize($applicant['city']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-pin-map"></i> Area</span>
                                <span><?= sanitize($applicant['area']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-calendar"></i> Experience</span>
                                <span class="fw-semibold"><?= $applicant['wp_experience'] ?> years</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-briefcase"></i> Status</span>
                                <span><?= sanitize($applicant['employment_status']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-cash"></i> Salary</span>
                                <span class="fw-semibold">PKR <?= number_format($applicant['expected_salary']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-globe"></i> Portfolio</span>
                                <span>
                                    <?php if ($applicant['portfolio_url']): ?>
                                        <a href="<?= sanitize($applicant['portfolio_url']) ?>" target="_blank">View <i class="bi bi-box-arrow-up-right"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-linkedin"></i> LinkedIn</span>
                                <span>
                                    <?php if ($applicant['linkedin_url']): ?>
                                        <a href="<?= sanitize($applicant['linkedin_url']) ?>" target="_blank">View <i class="bi bi-box-arrow-up-right"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-file-pdf"></i> CV</span>
                                <span>
                                    <?php if ($applicant['cv_filename']): ?>
                                        <a href="/admin/cv.php?id=<?= $id ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not uploaded</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="bi bi-clock"></i> Applied</span>
                                <span><?= date('M j, Y g:i A', strtotime($applicant['applied_at'])) ?></span>
                            </div>
                        </div>

                        <?php if ($applicant['rejection_reason']): ?>
                            <div class="alert alert-danger mt-3 mb-0 py-2">
                                <small><strong>Auto-Rejected:</strong> <?= sanitize($applicant['rejection_reason']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Change Status -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-toggles text-primary"></i> Change Status</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="change_status" value="1">
                            <div class="d-flex gap-2">
                                <select name="new_status" class="form-select">
                                    <option value="Shortlisted" <?= $applicant['status'] === 'Shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                    <option value="Manual Review" <?= $applicant['status'] === 'Manual Review' ? 'selected' : '' ?>>Manual Review</option>
                                    <option value="Rejected" <?= $applicant['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-sticky text-primary"></i> Admin Notes</h5>
                    </div>
                    <div class="card-body">
                        <form id="notesForm">
                            <input type="hidden" name="applicant_id" value="<?= $id ?>">
                            <textarea class="form-control mb-2" name="notes" rows="4" placeholder="Add private notes about this applicant..."><?= sanitize($applicant['admin_notes'] ?? '') ?></textarea>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-save"></i> Save Notes
                            </button>
                        </form>
                        <div id="notesFeedback" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Score & Answers -->
            <div class="col-lg-8">
                <!-- Score Overview -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-fill text-primary"></i> Score Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($answers)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-clipboard-x" style="font-size: 2rem;"></i>
                                <p class="mt-2">No screening test taken (auto-rejected at application stage).</p>
                            </div>
                        <?php else: ?>
                            <div class="row align-items-center mb-3">
                                <div class="col-md-4 text-center">
                                    <div class="score-circle <?= $applicant['total_score'] >= 70 ? 'score-high' : ($applicant['total_score'] >= 50 ? 'score-mid' : 'score-low') ?>">
                                        <span class="score-number"><?= $applicant['total_score'] ?></span>
                                        <span class="score-label">/100</span>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <?php foreach ($answers as $ans): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="text-muted me-2" style="min-width: 30px;">Q<?= $ans['question_number'] ?></span>
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                <div class="progress-bar <?= $ans['score'] >= 15 ? 'bg-success' : ($ans['score'] >= 10 ? 'bg-warning' : 'bg-danger') ?>"
                                                     style="width: <?= ($ans['score'] / 20) * 100 ?>%">
                                                    <?= $ans['score'] ?>/20
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Answers -->
                <?php foreach ($answers as $ans): ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-chat-left-text text-primary"></i>
                                Question <?= $ans['question_number'] ?>
                            </h6>
                            <span class="badge <?= $ans['score'] >= 15 ? 'bg-success' : ($ans['score'] >= 10 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= $ans['score'] ?>/20
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small fw-semibold mb-2"><?= getQuestionText($ans['question_number']) ?></p>
                            <div class="bg-light rounded p-3 mb-2">
                                <?= nl2br(sanitize($ans['answer'])) ?>
                            </div>
                            <?php if ($ans['matched_keywords']): ?>
                                <div class="mt-2">
                                    <small class="text-muted"><i class="bi bi-tags"></i> Matched keywords: </small>
                                    <?php foreach (explode(', ', $ans['matched_keywords']) as $kw): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-1"><?= sanitize($kw) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Save notes via AJAX
        document.getElementById('notesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/admin/save-notes.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                const fb = document.getElementById('notesFeedback');
                if (data.success) {
                    fb.innerHTML = '<div class="alert alert-success py-1 small">Notes saved!</div>';
                } else {
                    fb.innerHTML = '<div class="alert alert-danger py-1 small">Failed to save.</div>';
                }
                setTimeout(() => fb.innerHTML = '', 3000);
            });
        });
    </script>
</body>
</html>
