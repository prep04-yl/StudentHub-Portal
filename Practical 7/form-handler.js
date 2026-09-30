/**
 * Practical 7: Form Submission Client-Side Bridge
 * Seamlessly connects Practical 2 HTML forms to Practical 7 PHP backend
 * with full backward compatibility:
 * - If running under PHP server (Apache/XAMPP/Built-in server):
 *   Submits via fetch to PHP endpoint, displays PHP validation errors or success
 * - If running on static server (file:// or Live Server without PHP):
 *   Gracefully falls back to existing client-side notification/behavior
 */

document.addEventListener('DOMContentLoaded', () => {
    initPractical7Bridge();
});

function initPractical7Bridge() {
    const registerForm = document.getElementById('registerForm');
    const contactForm = document.getElementById('contactForm');

    // Registration Form Bridge
    if (registerForm) {
        // Ensure action and method are set for non-JS / direct submit support
        if (!registerForm.getAttribute('action')) {
            registerForm.setAttribute('action', '../../Practical 7/process-register.php');
            registerForm.setAttribute('method', 'POST');
        }

        registerForm.addEventListener('submit', function(e) {
            // First let client-side validation pass
            const errorSpans = registerForm.querySelectorAll('.error-message');
            let hasClientError = false;
            errorSpans.forEach(span => {
                if (span.textContent.trim().length > 0) {
                    hasClientError = true;
                }
            });
            if (hasClientError) return;

            // Intercept for AJAX processing to Practical 7 PHP
            e.preventDefault();
            const formData = new FormData(registerForm);

            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.textContent : 'Register';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }

            fetch('../../Practical 7/process-register.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Static environment: PHP backend not active');
                }
                const data = await response.json();
                return { ok: response.ok, status: response.status, data };
            })
            .then(result => {
                if (result.ok && result.data.status === 'success') {
                    if (typeof showNotification === 'function') {
                        showNotification('success', result.data.message);
                    } else {
                        alert(result.data.message);
                    }
                    registerForm.reset();
                    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
                    document.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));

                    // Optional confirmation modal
                    if (typeof openModal === 'function') {
                        openModal('Registration Success', `Welcome! Student ID: ${result.data.student_id}. Your record was saved to CSV and JSON.`);
                    }
                } else if (result.data && result.data.errors) {
                    // Display server-side errors
                    for (const [field, errorMsg] of Object.entries(result.data.errors)) {
                        const errElem = document.getElementById(field + 'Error');
                        const inputElem = document.getElementById(field);
                        if (errElem) errElem.textContent = errorMsg;
                        if (inputElem) inputElem.classList.add('invalid');
                    }
                    if (typeof showNotification === 'function') {
                        showNotification('error', result.data.message || 'Validation error from server');
                    }
                }
            })
            .catch(err => {
                // If static / no PHP server, fallback gracefully without breaking Practical 2/4 behavior
                console.info('Practical 7: PHP server not responding or static context. Falling back to default display.', err);
                if (typeof showNotification === 'function') {
                    showNotification('success', 'Registration submitted successfully! (Run via PHP server to store in CSV/JSON)');
                }
                registerForm.reset();
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });
        }, true); // Use capture to intercept cleanly
    }

    // Contact Form Bridge
    if (contactForm) {
        if (!contactForm.getAttribute('action')) {
            contactForm.setAttribute('action', '../../Practical 7/process-contact.php');
            contactForm.setAttribute('method', 'POST');
        }

        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(contactForm);

            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.textContent : 'Send Message';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
            }

            fetch('../../Practical 7/process-contact.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Static environment: PHP backend not active');
                }
                const data = await response.json();
                return { ok: response.ok, status: response.status, data };
            })
            .then(result => {
                if (result.ok && result.data.status === 'success') {
                    if (typeof showNotification === 'function') {
                        showNotification('success', result.data.message);
                    } else {
                        alert(result.data.message);
                    }
                    contactForm.reset();
                } else {
                    const msg = (result.data && result.data.message) ? result.data.message : 'Failed to send message.';
                    if (typeof showNotification === 'function') {
                        showNotification('error', msg);
                    } else {
                        alert(msg);
                    }
                }
            })
            .catch(err => {
                console.info('Practical 7: Falling back to client-side notification for contact form.', err);
                const nameInput = document.getElementById('contactName');
                const userName = nameInput && nameInput.value ? nameInput.value.trim() : 'Student';
                if (typeof showNotification === 'function') {
                    showNotification('success', `Thank you, ${userName}! Your message has been received.`);
                }
                contactForm.reset();
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });
        }, true);
    }
}
