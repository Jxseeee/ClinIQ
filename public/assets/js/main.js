document.addEventListener('DOMContentLoaded', function () {
    initAlertAutoDismiss();
    initLoginModal();
    initFormValidation();
    initDeleteModal();
    initLiveSearch();
    initPhoneFormatting();
    initPasswordToggle();
    initPasswordChecklist();
    initInputRestrictions();
    initConsentImageUpload();
    initDashboardMobileNav();
    initNotificationMenu();
});

/* ── 1. Auto-dismiss alerts ──────────────────────────── */

function initAlertAutoDismiss() {
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });
}

/* ── Mobile dashboard navigation ───────────── */

function initDashboardMobileNav() {
    var sidebar = document.querySelector('.student-sidebar');
    var toggle = document.querySelector('.student-mobile-nav-toggle');
    var nav = document.getElementById('student-mobile-nav');

    if (!sidebar || !toggle || !nav) return;

    function setOpen(isOpen) {
        sidebar.classList.toggle('is-nav-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    toggle.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('is-nav-open'));
    });

    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            setOpen(false);
        });
    });
}

/* ── Notification dropdown ─────────────────── */

function initNotificationMenu() {
    var menu = document.querySelector('.notification-menu');
    if (!menu) return;

    var button = menu.querySelector('.student-notification');
    var count = menu.querySelector('.notification-count');
    var notificationsUrl = menu.dataset.notificationsUrl;
    var csrf = menu.dataset.csrf;
    var hasMarkedRead = false;

    if (!button) return;

    function markRead() {
        if (hasMarkedRead || !notificationsUrl || !csrf) return;
        hasMarkedRead = true;

        if (count) count.remove();
        menu.querySelectorAll('.notification-dropdown a.is-unread').forEach(function (item) {
            item.classList.remove('is-unread');
        });

        var body = new FormData();
        body.append('csrf_token', csrf);

        fetch(notificationsUrl, {
            method: 'POST',
            body: body,
            credentials: 'same-origin'
        }).catch(function () { /* badge is already cleared locally */ });
    }

    function setOpen(isOpen) {
        menu.classList.toggle('is-open', isOpen);
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (isOpen) markRead();
    }

    button.addEventListener('click', function (event) {
        event.stopPropagation();
        setOpen(!menu.classList.contains('is-open'));
    });

    document.addEventListener('click', function (event) {
        if (!menu.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
}

/* ── Login modal ───────────────────────────── */

function initLoginModal() {
    var modal = document.getElementById('login-modal');
    var openBtn = document.getElementById('open-login-modal');
    var closeBtn = document.getElementById('close-login-modal');

    if (!modal || !openBtn || !closeBtn) return;

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        var firstInput = modal.querySelector('input');
        if (firstInput) firstInput.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        openBtn.focus();
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
}

/* ── 2. Client-side form validation ──────────────────── */

function initFormValidation() {
    var form = document.querySelector('form[method="POST"]:not(.delete-form)');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var errors = [];
        var firstName = form.querySelector('[name="first_name"]');
        var lastName  = form.querySelector('[name="last_name"]');
        var email     = form.querySelector('[name="email"]');

        if (firstName && firstName.value.trim() === '') {
            errors.push('First name is required.');
        }
        if (lastName && lastName.value.trim() === '') {
            errors.push('Last name is required.');
        }
        if (email && email.value.trim() !== '') {
            var emailVal = email.value.trim().toLowerCase();
            var emailRe  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRe.test(emailVal)) {
                errors.push('Invalid email format.');
            } else if (!emailVal.endsWith('@university.edu.ph')) {
                errors.push('Email must be a @university.edu.ph address.');
            }
        }

        if (errors.length === 0) return;

        e.preventDefault();

        var existing = form.parentElement.querySelector('.js-validation-alert');
        if (existing) existing.remove();

        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger js-validation-alert';
        alertDiv.innerHTML =
            '<ul style="margin:0;padding-left:18px;">' +
            errors.map(function (msg) { return '<li>' + msg + '</li>'; }).join('') +
            '</ul>';
        form.parentElement.insertBefore(alertDiv, form);
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

/* ── 3. Delete confirmation modal ────────────────────── */

function initDeleteModal() {
    var forms = document.querySelectorAll('form.delete-form');
    if (forms.length === 0) return;

    var overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML =
        '<div class="modal-box">' +
        '  <h3>Confirm Delete</h3>' +
        '  <p>Are you sure? This action cannot be undone.</p>' +
        '  <div class="modal-actions">' +
        '    <button id="modal-confirm" class="btn btn-danger" type="button">Delete</button>' +
        '    <button id="modal-cancel" class="btn btn-secondary" type="button">Cancel</button>' +
        '  </div>' +
        '</div>';
    document.body.appendChild(overlay);

    var confirmBtn = document.getElementById('modal-confirm');
    var cancelBtn  = document.getElementById('modal-cancel');
    var pendingForm = null;

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            pendingForm = this;
            overlay.classList.add('active');
        });
    });

    confirmBtn.addEventListener('click', function () {
        if (pendingForm) pendingForm.submit();
    });

    cancelBtn.addEventListener('click', function () {
        overlay.classList.remove('active');
        pendingForm = null;
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.classList.remove('active');
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            overlay.classList.remove('active');
        }
    });
}

/* ── 4. Live search / filter ─────────────────────────── */

function initLiveSearch() {
    var input = document.getElementById('student-search');
    var table = document.querySelector('table');
    if (!input || !table) return;

    var rows = table.querySelectorAll('tbody tr');

    input.addEventListener('input', function () {
        var query = this.value.toLowerCase();

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

/* ── 5. Phone number formatting (XXXX-XXX-XXXX) ─────── */

function initPhoneFormatting() {
    document.querySelectorAll('.phone-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var digits = this.value.replace(/\D/g, '');
            if (digits.length > 11) digits = digits.slice(0, 11);

            if (digits.length > 7) {
                this.value = digits.slice(0, 4) + '-' + digits.slice(4, 7) + '-' + digits.slice(7);
            } else if (digits.length > 4) {
                this.value = digits.slice(0, 4) + '-' + digits.slice(4);
            } else {
                this.value = digits;
            }
        });
    });
}

/* ── 6. Input restrictions (numbers-only, text-only) ── */

function initInputRestrictions() {
    document.querySelectorAll('.text-only').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-Z\s\-'.ñÑ]/g, '');
        });
    });

    document.querySelectorAll('.numeric-input').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });
    });
}

/* ── 7. Password show/hide toggle ────────────────────── */

function initPasswordToggle() {
    var eyeOpenIcon =
        '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
        '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>' +
        '<circle cx="12" cy="12" r="3"/>' +
        '</svg>';
    var eyeClosedIcon =
        '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
        '<path d="M4 13c2.2 2.7 4.8 4 8 4s5.8-1.3 8-4"/>' +
        '</svg>';

    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.innerHTML = eyeClosedIcon;
        btn.title = 'Show password';

        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = eyeOpenIcon;
                this.title = 'Hide password';
            } else {
                input.type = 'password';
                this.innerHTML = eyeClosedIcon;
                this.title = 'Show password';
            }
        });
    });
}

/* ── 8. Password strength checklist ──────────────────── */

function initPasswordChecklist() {
    var password = document.getElementById('new_password');
    var confirm = document.getElementById('confirm_password');
    var checklist = document.getElementById('password-checklist');

    if (!password || !checklist) return;

    var submitButton = checklist.closest('form').querySelector('button[type="submit"]');

    function setRule(rule, valid, active) {
        var item = checklist.querySelector('[data-rule="' + rule + '"]');
        if (!item) return;

        item.classList.toggle('is-valid', valid);
        item.classList.toggle('is-invalid', active && !valid);
    }

    function updateChecklist() {
        var value = password.value;
        var confirmation = confirm ? confirm.value : '';
        var rules = {
            length: value.length >= 8,
            number: /\d/.test(value),
            special: /[^A-Za-z0-9]/.test(value),
            match: !confirm || (value !== '' && value === confirmation)
        };
        var hasValue = value.length > 0;

        setRule('length', rules.length, hasValue);
        setRule('number', rules.number, hasValue);
        setRule('special', rules.special, hasValue);
        if (confirm) {
            setRule('match', rules.match, confirmation.length > 0);
        }

        if (submitButton) {
            var passwordIsRequired = password.hasAttribute('required');
            var canSubmit = !hasValue && !passwordIsRequired
                ? true
                : (rules.length && rules.number && rules.special && rules.match);

            submitButton.disabled = !canSubmit;
        }
    }

    password.addEventListener('input', updateChecklist);
    if (confirm) {
        confirm.addEventListener('input', updateChecklist);
    }
    updateChecklist();
}

/* ── 9. Consent image upload preview ─────────────────── */

function initConsentImageUpload() {
    document.querySelectorAll('.consent-upload-form').forEach(function (form) {
        var input = form.querySelector('input[type="file"][name="consent_image"]');
        var box = form.querySelector('.consent-upload-box');
        var fileName = form.querySelector('.consent-file-name');
        var submit = form.querySelector('.consent-upload-submit');

        if (!input || !box || !submit) return;

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) {
                submit.hidden = true;
                if (fileName) fileName.textContent = '';
                return;
            }

            submit.hidden = false;
            if (fileName) fileName.textContent = file.name;

            if (file.type && file.type.indexOf('image/') === 0) {
                var preview = box.querySelector('.consent-upload-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'consent-upload-preview';
                    preview.alt = 'Selected parent or guardian ID';
                    box.insertBefore(preview, box.firstChild);
                }

                preview.src = URL.createObjectURL(file);
                var prompt = box.querySelector('.consent-upload-prompt');
                if (prompt) prompt.textContent = 'Change image';
            }
        });
    });
}
