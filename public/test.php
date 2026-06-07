<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/scoring.php';

// Must have applicant_id from form submission
if (!isset($_SESSION['applicant_id'])) {
    header('Location: /');
    exit;
}

$applicantId = (int) $_SESSION['applicant_id'];
$applicant = getApplicant($pdo, $applicantId);

if (!$applicant || $applicant['status'] === 'Rejected') {
    header('Location: /');
    exit;
}

// Check if already answered
$existing = getAnswers($pdo, $applicantId);
if (!empty($existing)) {
    header('Location: /thankyou.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [];
    $isTabSwitch = isset($_POST['tab_switched']) && $_POST['tab_switched'] === '1';

    for ($i = 1; $i <= 5; $i++) {
        $answer = trim($_POST["answer_$i"] ?? '');
        if (!$isTabSwitch) {
            if (empty($answer)) {
                $errors[] = "Question $i is required.";
            } elseif (strlen($answer) < 20) {
                $errors[] = "Question $i: Please provide a more detailed answer (minimum 20 characters).";
            }
        }
        $answers[$i] = $answer ?: '(No answer - tab switch detected)';
    }

    if (empty($errors)) {
        // If tab switch, force reject
        $tabSwitchPenalty = $isTabSwitch;
        // Score all answers
        $scoreResults = scoreAnswers($answers);
        $totalScore = calculateTotalScore($scoreResults);
        $status = determineStatus($totalScore);

        // Save answers
        $stmtAnswer = $pdo->prepare("INSERT INTO screening_answers (applicant_id, question_number, answer, score, matched_keywords) VALUES (?, ?, ?, ?, ?)");

        foreach ($answers as $qNum => $answer) {
            $stmtAnswer->execute([
                $applicantId,
                $qNum,
                $answer,
                $scoreResults[$qNum]['score'],
                implode(', ', $scoreResults[$qNum]['matched'])
            ]);
        }

        // Override status if tab switch detected
        if ($tabSwitchPenalty) {
            $status = 'Rejected';
            $rejectionReason = 'Tab switch detected during screening test';
            $stmtUpdate = $pdo->prepare("UPDATE applicants SET total_score = ?, status = ?, rejection_reason = ? WHERE id = ?");
            $stmtUpdate->execute([$totalScore, $status, $rejectionReason, $applicantId]);
        } else {
            $stmtUpdate = $pdo->prepare("UPDATE applicants SET total_score = ?, status = ? WHERE id = ?");
            $stmtUpdate->execute([$totalScore, $status, $applicantId]);
        }

        // Send confirmation email
        sendConfirmationEmail($applicant['email'], $applicant['full_name']);

        unset($_SESSION['applicant_id']);
        header('Location: /thankyou.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screening Test - WordPress Developer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="text-center mb-4">
                    <h1 class="fw-bold text-primary"><i class="bi bi-clipboard-check"></i> WordPress Screening Test</h1>
                    <p class="text-muted">Welcome, <strong><?= sanitize($applicant['full_name']) ?></strong>. Please answer all questions thoroughly.</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Take your time. Your answers help us understand your WordPress expertise. Be specific and detailed.
                    </div>
                    <div class="alert alert-danger fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>WARNING:</strong> Do NOT switch tabs or leave this page during the test. Tab switching will be detected and your test will be <strong>automatically submitted</strong> with whatever answers you have filled in so far.
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="testForm">
                    <input type="hidden" name="tab_switched" id="tabSwitched" value="0">
                    <!-- Question 1 -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <strong>Question 1 of 5</strong>
                        </div>
                        <div class="card-body p-4">
                            <p class="fw-semibold">A client wants:</p>
                            <ul>
                                <li>Blog</li>
                                <li>Contact Form</li>
                                <li>Payment Gateway</li>
                                <li>User Dashboard</li>
                            </ul>
                            <p class="fw-semibold">Which plugins/tools would you use and why?</p>
                            <textarea class="form-control" name="answer_1" rows="5" placeholder="Describe the plugins and tools you would use for each requirement..."><?= sanitize($_POST['answer_1'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Question 2 -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <strong>Question 2 of 5</strong>
                        </div>
                        <div class="card-body p-4">
                            <p class="fw-semibold">A WordPress website loads in 8 seconds. Name the first 5 things you would check.</p>
                            <textarea class="form-control" name="answer_2" rows="5" placeholder="List and explain the 5 things you would check..."><?= sanitize($_POST['answer_2'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Question 3 -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <strong>Question 3 of 5</strong>
                        </div>
                        <div class="card-body p-4">
                            <p class="fw-semibold">Elementor Pro stops loading after an update. Describe your troubleshooting process.</p>
                            <textarea class="form-control" name="answer_3" rows="5" placeholder="Walk through your step-by-step troubleshooting process..."><?= sanitize($_POST['answer_3'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Question 4 -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <strong>Question 4 of 5</strong>
                        </div>
                        <div class="card-body p-4">
                            <p class="fw-semibold">A client wants a fully custom WooCommerce checkout design. How would you approach it and why?</p>
                            <textarea class="form-control" name="answer_4" rows="5" placeholder="Explain your approach to customizing the WooCommerce checkout..."><?= sanitize($_POST['answer_4'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Question 5 -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <strong>Question 5 of 5</strong>
                        </div>
                        <div class="card-body p-4">
                            <p class="fw-semibold">Describe the most difficult WordPress project you have personally worked on. Explain exactly what YOU built and your responsibilities.</p>
                            <textarea class="form-control" name="answer_5" rows="6" placeholder="Be specific about your role, challenges, and solutions..."><?= sanitize($_POST['answer_5'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                            <i class="bi bi-send-fill"></i> Submit Screening Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
