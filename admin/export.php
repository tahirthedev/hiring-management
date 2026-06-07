<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$applicants = getApplicants($pdo, 'Shortlisted');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="shortlisted_candidates_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, [
    'ID', 'Full Name', 'Email', 'Phone', 'City', 'Area',
    'WP Experience (Years)', 'Employment Status', 'Expected Salary (PKR)',
    'Portfolio URL', 'LinkedIn URL', 'Score', 'Status', 'Applied At'
]);

foreach ($applicants as $app) {
    fputcsv($output, [
        $app['id'],
        $app['full_name'],
        $app['email'],
        $app['phone'],
        $app['city'],
        $app['area'],
        $app['wp_experience'],
        $app['employment_status'],
        $app['expected_salary'],
        $app['portfolio_url'],
        $app['linkedin_url'],
        $app['total_score'],
        $app['status'],
        $app['applied_at']
    ]);
}

fclose($output);
exit;
