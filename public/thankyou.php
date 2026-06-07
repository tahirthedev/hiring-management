<?php
// Simple thank you page - no auth needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-primary mb-3">Thank You for Applying!</h2>
                    <p class="text-muted fs-5">
                        Thank you for applying. If shortlisted, our team will contact you.
                    </p>
                    <hr>
                    <p class="text-muted small">
                        You will receive a confirmation email shortly. Please check your inbox and spam folder.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
