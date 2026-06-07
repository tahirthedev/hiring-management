<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/scoring.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $area = sanitize($_POST['area'] ?? '');
    $wpExperience = (int)($_POST['wp_experience'] ?? 0);
    $employmentStatus = sanitize($_POST['employment_status'] ?? '');
    $expectedSalary = (int)($_POST['expected_salary'] ?? 0);
    $portfolioUrl = sanitize($_POST['portfolio_url'] ?? '');
    $linkedinUrl = sanitize($_POST['linkedin_url'] ?? '');

    // Validation
    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (!validateEmail($email)) $errors[] = 'Valid email is required.';
    if (empty($city)) $errors[] = 'City is required.';
    if (empty($area)) $errors[] = 'Area is required.';
    if ($wpExperience < 0) $errors[] = 'Experience must be a positive number.';
    if (empty($employmentStatus)) $errors[] = 'Employment status is required.';
    if ($expectedSalary <= 0) $errors[] = 'Expected salary is required.';
    if (!empty($portfolioUrl) && !validateUrl($portfolioUrl)) $errors[] = 'Portfolio URL is not valid.';
    if (!empty($linkedinUrl) && !validateUrl($linkedinUrl)) $errors[] = 'LinkedIn URL is not valid.';

    // Check duplicate email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM applicants WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An application with this email already exists.';
        }
    }

    // Upload CV
    $cvFilename = null;
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        $cvFilename = uploadCV($_FILES['cv']);
        if (!$cvFilename) {
            $errors[] = 'CV must be a PDF file under 5MB.';
        }
    }

    if (empty($errors)) {
        // Check auto-rejection
        $rejectReason = getAutoRejectReason($city, $wpExperience);

        if ($rejectReason) {
            // Save as rejected, no test needed
            $stmt = $pdo->prepare("INSERT INTO applicants (full_name, phone, email, city, area, wp_experience, employment_status, expected_salary, portfolio_url, linkedin_url, cv_filename, status, rejection_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Rejected', ?)");
            $stmt->execute([$fullName, $phone, $email, $city, $area, $wpExperience, $employmentStatus, $expectedSalary, $portfolioUrl, $linkedinUrl, $cvFilename, $rejectReason]);

            sendConfirmationEmail($email, $fullName);
            header('Location: /thankyou.php');
            exit;
        } else {
            // Save applicant and redirect to test
            $stmt = $pdo->prepare("INSERT INTO applicants (full_name, phone, email, city, area, wp_experience, employment_status, expected_salary, portfolio_url, linkedin_url, cv_filename, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$fullName, $phone, $email, $city, $area, $wpExperience, $employmentStatus, $expectedSalary, $portfolioUrl, $linkedinUrl, $cvFilename]);

            $applicantId = $pdo->lastInsertId();
            $_SESSION['applicant_id'] = $applicantId;
            header('Location: /test.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply - WordPress Developer Position</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h1 class="fw-bold text-primary"><i class="bi bi-wordpress"></i> WordPress Developer</h1>
                    <p class="text-muted fs-5">Application Form</p>
                    <hr>
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

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" id="applicationForm">
                            <h5 class="mb-3 text-primary"><i class="bi bi-person-fill"></i> Personal Information</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Current City <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="city" name="city" value="<?= sanitize($_POST['city'] ?? '') ?>" required>
                                        <button type="button" class="btn btn-outline-primary" id="detectLocationBtn" title="Detect my location">
                                            <i class="bi bi-geo-alt-fill"></i> <span class="d-none d-md-inline">Detect</span>
                                        </button>
                                    </div>
                                    <div id="locationStatus" class="form-text"></div>
                                </div>
                                <div class="col-12">
                                    <label for="area" class="form-label">Area within City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="area" name="area" value="<?= sanitize($_POST['area'] ?? '') ?>" placeholder="e.g., DHA Phase 6, Gulshan, PECHS" required>
                                </div>
                            </div>

                            <h5 class="mb-3 text-primary"><i class="bi bi-briefcase-fill"></i> Professional Details</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="wp_experience" class="form-label">Years of WordPress Experience <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="wp_experience" name="wp_experience" min="0" max="30" value="<?= sanitize($_POST['wp_experience'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="employment_status" class="form-label">Current Employment Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="employment_status" name="employment_status" required>
                                        <option value="">Select...</option>
                                        <option value="Employed" <?= ($_POST['employment_status'] ?? '') === 'Employed' ? 'selected' : '' ?>>Employed</option>
                                        <option value="Freelancer" <?= ($_POST['employment_status'] ?? '') === 'Freelancer' ? 'selected' : '' ?>>Freelancer</option>
                                        <option value="Unemployed" <?= ($_POST['employment_status'] ?? '') === 'Unemployed' ? 'selected' : '' ?>>Unemployed</option>
                                        <option value="Student" <?= ($_POST['employment_status'] ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="expected_salary" class="form-label">Expected Monthly Salary (PKR) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="expected_salary" name="expected_salary" min="0" value="<?= sanitize($_POST['expected_salary'] ?? '') ?>" placeholder="e.g., 80000" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="portfolio_url" class="form-label">Portfolio URL</label>
                                    <input type="url" class="form-control" id="portfolio_url" name="portfolio_url" value="<?= sanitize($_POST['portfolio_url'] ?? '') ?>" placeholder="https://yourportfolio.com (optional)">
                                </div>
                                <div class="col-md-6">
                                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="<?= sanitize($_POST['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/yourprofile">
                                </div>
                                <div class="col-md-6">
                                    <label for="cv" class="form-label">Upload CV (PDF, max 5MB)</label>
                                    <input type="file" class="form-control" id="cv" name="cv" accept=".pdf">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-right-circle"></i> Continue to Screening Test
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center text-muted mt-3 small">Your information is kept confidential and used only for hiring purposes.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
