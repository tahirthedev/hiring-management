// WordPress Developer Screening - App JS

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // APPLICATION FORM - Geolocation Auto-fill
    // ==========================================
    const appForm = document.getElementById('applicationForm');
    if (appForm) {
        const detectBtn = document.getElementById('detectLocationBtn');
        const cityInput = document.getElementById('city');
        const areaInput = document.getElementById('area');
        const locationStatus = document.getElementById('locationStatus');

        if (detectBtn) {
            detectBtn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    locationStatus.innerHTML = '<span class="text-danger">Geolocation is not supported by your browser.</span>';
                    return;
                }

                detectBtn.disabled = true;
                detectBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Detecting...';
                locationStatus.innerHTML = '<span class="text-muted">Requesting your location...</span>';

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        var lat = position.coords.latitude;
                        var lon = position.coords.longitude;
                        locationStatus.innerHTML = '<span class="text-muted">Looking up address...</span>';

                        // Use OpenStreetMap Nominatim for reverse geocoding (free, no API key)
                        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon + '&zoom=16&addressdetails=1', {
                            headers: { 'Accept-Language': 'en' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            var addr = data.address || {};
                            var city = addr.city || addr.town || addr.state_district || addr.county || '';
                            var area = addr.suburb || addr.neighbourhood || addr.road || addr.district || '';

                            if (city) {
                                cityInput.value = city;
                                cityInput.classList.add('is-valid');
                                setTimeout(function () { cityInput.classList.remove('is-valid'); }, 3000);
                            }
                            if (area) {
                                areaInput.value = area;
                                areaInput.classList.add('is-valid');
                                setTimeout(function () { areaInput.classList.remove('is-valid'); }, 3000);
                            }

                            locationStatus.innerHTML = city
                                ? '<span class="text-success"><i class="bi bi-check-circle"></i> Location detected: ' + city + (area ? ', ' + area : '') + '</span>'
                                : '<span class="text-warning">Could not determine city. Please enter manually.</span>';

                            detectBtn.disabled = false;
                            detectBtn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> <span class="d-none d-md-inline">Detect</span>';
                        })
                        .catch(function () {
                            locationStatus.innerHTML = '<span class="text-danger">Could not look up address. Please enter manually.</span>';
                            detectBtn.disabled = false;
                            detectBtn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> <span class="d-none d-md-inline">Detect</span>';
                        });
                    },
                    function (error) {
                        var msg = 'Location access denied. Please enter your city manually.';
                        if (error.code === 2) msg = 'Location unavailable. Please enter manually.';
                        if (error.code === 3) msg = 'Location request timed out. Please enter manually.';
                        locationStatus.innerHTML = '<span class="text-danger">' + msg + '</span>';
                        detectBtn.disabled = false;
                        detectBtn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> <span class="d-none d-md-inline">Detect</span>';
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });

            // Auto-trigger on page load if fields are empty
            if (!cityInput.value && !areaInput.value) {
                detectBtn.click();
            }
        }

        // Phone validation
        appForm.addEventListener('submit', function (e) {
            var phone = document.getElementById('phone');
            if (phone && phone.value.length < 10) {
                e.preventDefault();
                alert('Please enter a valid phone number.');
                phone.focus();
            }
        });
    }

    // ==========================================
    // SCREENING TEST - Tab Switch Detection
    // ==========================================
    var testForm = document.getElementById('testForm');
    if (testForm) {
        var tabSwitchTriggered = false;

        var tabSwitchField = document.getElementById('tabSwitched');

        function autoSubmitTabSwitch() {
            if (tabSwitchTriggered) return;
            tabSwitchTriggered = true;
            tabSwitchField.value = '1';
            testForm.submit();
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) autoSubmitTabSwitch();
        });

        // Also detect window blur (covers alt-tab, clicking other windows)
        window.addEventListener('blur', function () {
            autoSubmitTabSwitch();
        });

        // Prevent right-click (discourages copying questions)
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        // Warn before manual page leave
        window.addEventListener('beforeunload', function (e) {
            if (!tabSwitchTriggered) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Submit button validation (only on manual submit)
        testForm.addEventListener('submit', function (e) {
            // If auto-submitted by tab switch, skip validation
            if (tabSwitchTriggered) return;

            var textareas = testForm.querySelectorAll('textarea');
            var allFilled = true;
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

    // ==========================================
    // Auto-dismiss success alerts
    // ==========================================
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });
});
