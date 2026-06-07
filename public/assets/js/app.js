// WordPress Developer Screening - App JS

document.addEventListener('DOMContentLoaded', function () {

    // Form validation for application form
    const appForm = document.getElementById('applicationForm');
    if (appForm) {
        appForm.addEventListener('submit', function (e) {
            const phone = document.getElementById('phone');
            if (phone && phone.value.length < 10) {
                e.preventDefault();
                alert('Please enter a valid phone number.');
                phone.focus();
                return;
            }
        });
    }

    // Test form - confirm before submit
    const testForm = document.getElementById('testForm');
    if (testForm) {
        testForm.addEventListener('submit', function (e) {
            // Check all textareas are filled
            const textareas = testForm.querySelectorAll('textarea');
            let allFilled = true;
            textareas.forEach(function (ta) {
                if (ta.value.trim().length < 20) {
                    allFilled = false;
                    ta.classList.add('is-invalid');
                } else {
                    ta.classList.remove('is-invalid');
                }
            });

            if (!allFilled) {
                e.preventDefault();
                alert('Please provide detailed answers for all questions (minimum 20 characters each).');
                return;
            }

            if (!confirm('Are you sure you want to submit? You cannot change your answers after submission.')) {
                e.preventDefault();
            }
        });

        // Remove invalid state on input
        testForm.querySelectorAll('textarea').forEach(function (ta) {
            ta.addEventListener('input', function () {
                if (this.value.trim().length >= 20) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    }

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });
});
