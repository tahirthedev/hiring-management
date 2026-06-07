<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$statusFilter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$totalAll = getApplicantCount($pdo);
$totalShortlisted = getApplicantCount($pdo, 'Shortlisted');
$totalReview = getApplicantCount($pdo, 'Manual Review');
$totalRejected = getApplicantCount($pdo, 'Rejected');

$applicants = getApplicants($pdo, $statusFilter, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WP Screening Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/admin/">
                <i class="bi bi-wordpress"></i> WP Screening Admin
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 d-none d-md-inline">
                    <i class="bi bi-person-circle"></i> <?= sanitize($_SESSION['admin_user']) ?>
                </span>
                <a href="/admin/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <a href="?status=all" class="text-decoration-none">
                    <div class="card stat-card border-0 shadow-sm h-100 <?= $statusFilter === 'all' ? 'border-primary border-2' : '' ?>">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-2">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h2 class="fw-bold mb-0"><?= $totalAll ?></h2>
                            <p class="text-muted mb-0 small">Total Applicants</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="?status=Shortlisted" class="text-decoration-none">
                    <div class="card stat-card border-0 shadow-sm h-100 <?= $statusFilter === 'Shortlisted' ? 'border-success border-2' : '' ?>">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-2">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h2 class="fw-bold mb-0 text-success"><?= $totalShortlisted ?></h2>
                            <p class="text-muted mb-0 small">Shortlisted</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="?status=Manual Review" class="text-decoration-none">
                    <div class="card stat-card border-0 shadow-sm h-100 <?= $statusFilter === 'Manual Review' ? 'border-warning border-2' : '' ?>">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-2">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <h2 class="fw-bold mb-0 text-warning"><?= $totalReview ?></h2>
                            <p class="text-muted mb-0 small">Manual Review</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="?status=Rejected" class="text-decoration-none">
                    <div class="card stat-card border-0 shadow-sm h-100 <?= $statusFilter === 'Rejected' ? 'border-danger border-2' : '' ?>">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-2">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <h2 class="fw-bold mb-0 text-danger"><?= $totalRejected ?></h2>
                            <p class="text-muted mb-0 small">Rejected</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Search & Actions Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="status" value="<?= sanitize($statusFilter) ?>">
                            <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone..." value="<?= sanitize($search) ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if ($search): ?>
                                <a href="?status=<?= sanitize($statusFilter) ?>" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="/admin/export.php" class="btn btn-success">
                            <i class="bi bi-download"></i> Export Shortlisted (CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applicants Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th class="d-none d-md-table-cell">Phone</th>
                                <th>City</th>
                                <th class="d-none d-md-table-cell">Experience</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th class="d-none d-lg-table-cell">Portfolio</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applicants)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No applicants found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applicants as $i => $app): ?>
                                    <tr>
                                        <td class="text-muted"><?= $app['id'] ?></td>
                                        <td class="fw-semibold"><?= sanitize($app['full_name']) ?></td>
                                        <td class="d-none d-md-table-cell"><?= sanitize($app['phone']) ?></td>
                                        <td><?= sanitize($app['city']) ?></td>
                                        <td class="d-none d-md-table-cell"><?= $app['wp_experience'] ?> yrs</td>
                                        <td>
                                            <span class="fw-bold <?= $app['total_score'] >= 70 ? 'text-success' : ($app['total_score'] >= 50 ? 'text-warning' : 'text-danger') ?>">
                                                <?= $app['total_score'] ?>/100
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= getStatusBadgeClass($app['status']) ?>">
                                                <?= $app['status'] ?>
                                            </span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php if ($app['portfolio_url']): ?>
                                                <a href="<?= sanitize($app['portfolio_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-link-45deg"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/admin/applicant.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
