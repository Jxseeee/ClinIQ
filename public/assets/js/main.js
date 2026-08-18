document.addEventListener('DOMContentLoaded', function () {
    initAlertAutoDismiss();
    initFormValidation();
    initDeleteModal();
    initLiveSearch();
    initPhoneFormatting();
    initPasswordToggle();
    initPasswordChecklist();
    initInputRestrictions();
    initConsentImageUpload();
    initProfilePhotoUpload();
    initBirthDatePicker();
    initFollowUpDatePicker();
    initClinicFormWizard();
    initReportDialogs();
    initReportPrint();
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
            } else if (!emailVal.endsWith('@fcat.com.ph')) {
                errors.push('Email must be a @fcat.com.ph address.');
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
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML =
        '<div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title" aria-describedby="modal-delete-message">' +
        '  <h3 id="modal-delete-title">Confirm delete</h3>' +
        '  <p id="modal-delete-message">Are you sure? This action cannot be undone.</p>' +
        '  <div class="modal-actions">' +
        '    <button id="modal-confirm" class="btn btn-danger" type="button">Delete</button>' +
        '    <button id="modal-cancel" class="btn btn-secondary" type="button">Cancel</button>' +
        '  </div>' +
        '</div>';
    document.body.appendChild(overlay);

    var confirmBtn = document.getElementById('modal-confirm');
    var cancelBtn  = document.getElementById('modal-cancel');
    var title = document.getElementById('modal-delete-title');
    var message = document.getElementById('modal-delete-message');
    var pendingForm = null;
    var activeTrigger = null;

    function closeModal() {
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        pendingForm = null;
        if (activeTrigger) activeTrigger.focus();
        activeTrigger = null;
    }

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            pendingForm = this;
            activeTrigger = document.activeElement;
            title.textContent = this.dataset.deleteTitle || 'Confirm delete';
            message.textContent = this.dataset.deleteMessage || 'Are you sure? This action cannot be undone.';
            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            confirmBtn.focus();
        });
    });

    confirmBtn.addEventListener('click', function () {
        if (pendingForm) pendingForm.submit();
    });

    cancelBtn.addEventListener('click', function () {
        closeModal();
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            closeModal();
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

    document.querySelectorAll('.integer-input').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 3);
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

function initProfilePhotoUpload() {
    document.querySelectorAll('[data-profile-photo-form]').forEach(function (form) {
        var input = form.querySelector('input[type="file"][name="profile_image"]');
        var layout = form.closest('.settings-profile-layout');
        var preview = layout ? layout.querySelector('[data-profile-preview]') : null;
        var fileName = form.querySelector('.settings-profile-file-name');
        var submit = form.querySelector('.settings-profile-submit');
        var previewUrl = null;

        if (!input || !preview || !fileName || !submit) return;

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            if (!file) {
                fileName.textContent = 'No file selected';
                submit.disabled = true;
                return;
            }

            fileName.textContent = file.name;
            submit.disabled = false;

            if (file.type && file.type.indexOf('image/') === 0) {
                previewUrl = URL.createObjectURL(file);
                var image = document.createElement('img');
                image.className = 'settings-profile-image';
                image.alt = 'Selected profile photo preview';
                image.src = previewUrl;
                preview.replaceChildren(image);
            }
        });
    });
}

function initBirthDatePicker() {
    document.querySelectorAll('[data-birth-date-picker]').forEach(function (picker) {
        var input = picker.querySelector('input[name="date_of_birth"]');
        var trigger = picker.querySelector('.birth-date-trigger');
        var display = picker.querySelector('#birth-date-display');
        var calendar = picker.querySelector('.birth-date-calendar');
        var monthSelect = picker.querySelector('[data-birth-month]');
        var yearSelect = picker.querySelector('[data-birth-year]');
        var grid = picker.querySelector('.birth-date-calendar-grid');
        var label = document.getElementById('birth-date-label');

        if (!input || !trigger || !display || !calendar || !monthSelect || !yearSelect || !grid) {
            return;
        }

        function parseDate(value) {
            var match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
            if (!match) return null;

            var year = Number(match[1]);
            var month = Number(match[2]) - 1;
            var day = Number(match[3]);
            var date = new Date(year, month, day, 12, 0, 0, 0);

            return date.getFullYear() === year && date.getMonth() === month && date.getDate() === day
                ? date
                : null;
        }

        function isoDate(date) {
            return date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');
        }

        function formatDate(date) {
            return date.toLocaleDateString(undefined, {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function isSameDate(first, second) {
            return Boolean(first && second && isoDate(first) === isoDate(second));
        }

        var minimumDate = parseDate(picker.dataset.minDate);
        var maximumDate = parseDate(picker.dataset.maxDate);
        if (!minimumDate || !maximumDate) return;

        var selectedDate = parseDate(input.value);
        if (selectedDate && (selectedDate < minimumDate || selectedDate > maximumDate)) {
            selectedDate = null;
            input.value = '';
        }

        var defaultDate = new Date(maximumDate.getFullYear() - 18, maximumDate.getMonth(), 1, 12, 0, 0, 0);
        if (defaultDate < minimumDate) defaultDate = minimumDate;
        var visibleMonth = new Date(
            (selectedDate || defaultDate).getFullYear(),
            (selectedDate || defaultDate).getMonth(),
            1,
            12,
            0,
            0,
            0
        );

        function isAllowed(date) {
            return date >= minimumDate && date <= maximumDate;
        }

        function updateTrigger() {
            display.textContent = selectedDate ? formatDate(selectedDate) : 'Select birth date';
            trigger.classList.toggle('has-value', Boolean(selectedDate));
        }

        function populateSelectors() {
            var monthNames = Array.from({ length: 12 }, function (_, month) {
                return new Date(2000, month, 1).toLocaleDateString(undefined, { month: 'long' });
            });

            monthSelect.replaceChildren();
            monthNames.forEach(function (monthName, month) {
                var option = new Option(monthName, String(month));
                var monthStart = new Date(visibleMonth.getFullYear(), month, 1, 12, 0, 0, 0);
                var monthEnd = new Date(visibleMonth.getFullYear(), month + 1, 0, 12, 0, 0, 0);
                option.disabled = monthEnd < minimumDate || monthStart > maximumDate;
                monthSelect.add(option);
            });

            yearSelect.replaceChildren();
            for (var year = maximumDate.getFullYear(); year >= minimumDate.getFullYear(); year--) {
                yearSelect.add(new Option(String(year), String(year)));
            }

            monthSelect.value = String(visibleMonth.getMonth());
            yearSelect.value = String(visibleMonth.getFullYear());
        }

        function emptyCell() {
            var cell = document.createElement('span');
            cell.className = 'birth-date-calendar-empty';
            cell.setAttribute('aria-hidden', 'true');
            return cell;
        }

        function renderCalendar() {
            populateSelectors();
            grid.replaceChildren();

            var leadingCells = visibleMonth.getDay();
            var daysInMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 0).getDate();

            for (var leading = 0; leading < leadingCells; leading++) {
                grid.appendChild(emptyCell());
            }

            for (var day = 1; day <= daysInMonth; day++) {
                var date = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), day, 12, 0, 0, 0);

                if (!isAllowed(date)) {
                    var unavailable = document.createElement('span');
                    unavailable.className = 'birth-date-calendar-day is-unavailable';
                    unavailable.textContent = String(day);
                    unavailable.setAttribute('aria-hidden', 'true');
                    grid.appendChild(unavailable);
                    continue;
                }

                var dayButton = document.createElement('button');
                dayButton.type = 'button';
                dayButton.className = 'birth-date-calendar-day';
                dayButton.dataset.date = isoDate(date);
                dayButton.textContent = String(day);
                dayButton.setAttribute('aria-label', formatDate(date));
                dayButton.setAttribute('aria-selected', isSameDate(date, selectedDate) ? 'true' : 'false');

                if (isSameDate(date, selectedDate)) {
                    dayButton.classList.add('is-selected');
                }

                grid.appendChild(dayButton);
            }

            while (grid.children.length < 42) {
                grid.appendChild(emptyCell());
            }
        }

        function setOpen(isOpen) {
            calendar.hidden = !isOpen;
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) renderCalendar();
        }

        function updateVisibleMonth() {
            var year = Number(yearSelect.value);
            var month = Number(monthSelect.value);
            var candidate = new Date(year, month, 1, 12, 0, 0, 0);
            var maximumMonth = new Date(maximumDate.getFullYear(), maximumDate.getMonth(), 1, 12, 0, 0, 0);
            var minimumMonth = new Date(minimumDate.getFullYear(), minimumDate.getMonth(), 1, 12, 0, 0, 0);

            if (candidate > maximumMonth) candidate = maximumMonth;
            if (candidate < minimumMonth) candidate = minimumMonth;
            visibleMonth = candidate;
            renderCalendar();
        }

        input.hidden = true;
        trigger.hidden = false;
        updateTrigger();

        trigger.addEventListener('click', function () {
            setOpen(calendar.hidden);
        });

        monthSelect.addEventListener('change', updateVisibleMonth);
        yearSelect.addEventListener('change', updateVisibleMonth);

        grid.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-date]');
            if (!button) return;

            selectedDate = parseDate(button.dataset.date);
            input.value = button.dataset.date;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            updateTrigger();
            setOpen(false);
            trigger.focus();
        });

        if (label) {
            label.addEventListener('click', function (event) {
                event.preventDefault();
                trigger.focus();
                setOpen(true);
            });
        }

        document.addEventListener('click', function (event) {
            if (!picker.contains(event.target)) setOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !calendar.hidden) {
                setOpen(false);
                trigger.focus();
            }
        });
    });
}

function initFollowUpDatePicker() {
    document.querySelectorAll('[data-follow-up-date-picker]').forEach(function (picker) {
        var input = picker.querySelector('input[name="follow_up_date"]');
        var trigger = picker.querySelector('.follow-up-date-trigger');
        var display = picker.querySelector('#follow-up-date-display');
        var calendar = picker.querySelector('.follow-up-calendar');
        var monthLabel = picker.querySelector('#follow-up-calendar-month');
        var grid = picker.querySelector('.follow-up-calendar-grid');
        var previousButton = picker.querySelector('[data-follow-up-previous]');
        var nextButton = picker.querySelector('[data-follow-up-next]');
        var clearButton = picker.querySelector('[data-follow-up-clear]');
        var label = document.getElementById('follow-up-date-label');

        if (!input || !trigger || !display || !calendar || !monthLabel || !grid || !previousButton || !nextButton || !clearButton) {
            return;
        }

        function parseDate(value) {
            var match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
            if (!match) return null;

            var year = Number(match[1]);
            var month = Number(match[2]) - 1;
            var day = Number(match[3]);
            var date = new Date(year, month, day, 12, 0, 0, 0);

            return date.getFullYear() === year && date.getMonth() === month && date.getDate() === day
                ? date
                : null;
        }

        function isoDate(date) {
            return date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');
        }

        function startOfMonth(date) {
            return new Date(date.getFullYear(), date.getMonth(), 1, 12, 0, 0, 0);
        }

        function monthIndex(date) {
            return date.getFullYear() * 12 + date.getMonth();
        }

        function isSameDate(first, second) {
            return Boolean(first && second && isoDate(first) === isoDate(second));
        }

        function formatDate(date) {
            return date.toLocaleDateString(undefined, {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        var minimumDate = parseDate(picker.dataset.minDate);
        var maximumDate = parseDate(picker.dataset.maxDate);
        if (!minimumDate || !maximumDate) return;

        var minimumMonth = startOfMonth(minimumDate);
        var maximumMonth = startOfMonth(maximumDate);
        var selectedDate = parseDate(input.value);

        function isAllowed(date) {
            return date >= minimumDate && date <= maximumDate;
        }

        if (selectedDate && !isAllowed(selectedDate)) {
            selectedDate = null;
            input.value = '';
        }

        var visibleMonth = startOfMonth(selectedDate || minimumDate);

        function updateTrigger() {
            display.textContent = selectedDate ? formatDate(selectedDate) : 'Select a date';
            trigger.classList.toggle('has-value', Boolean(selectedDate));
        }

        function emptyCell() {
            var cell = document.createElement('span');
            cell.className = 'follow-up-calendar-empty';
            cell.setAttribute('aria-hidden', 'true');
            return cell;
        }

        function renderCalendar() {
            grid.replaceChildren();
            monthLabel.textContent = visibleMonth.toLocaleDateString(undefined, {
                month: 'long',
                year: 'numeric'
            });

            previousButton.disabled = monthIndex(visibleMonth) <= monthIndex(minimumMonth);
            nextButton.disabled = monthIndex(visibleMonth) >= monthIndex(maximumMonth);

            var leadingCells = visibleMonth.getDay();
            var daysInMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 0).getDate();

            for (var leading = 0; leading < leadingCells; leading++) {
                grid.appendChild(emptyCell());
            }

            for (var day = 1; day <= daysInMonth; day++) {
                var date = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), day, 12, 0, 0, 0);

                if (!isAllowed(date)) {
                    var unavailable = document.createElement('span');
                    unavailable.className = 'follow-up-calendar-day is-unavailable';
                    unavailable.textContent = String(day);
                    unavailable.setAttribute('aria-hidden', 'true');
                    grid.appendChild(unavailable);
                    continue;
                }

                var dayButton = document.createElement('button');
                dayButton.type = 'button';
                dayButton.className = 'follow-up-calendar-day';
                dayButton.dataset.date = isoDate(date);
                dayButton.textContent = String(day);
                dayButton.setAttribute('aria-label', formatDate(date));
                dayButton.setAttribute('aria-selected', isSameDate(date, selectedDate) ? 'true' : 'false');

                if (isSameDate(date, minimumDate)) {
                    dayButton.classList.add('is-today');
                    dayButton.setAttribute('aria-current', 'date');
                }
                if (isSameDate(date, selectedDate)) {
                    dayButton.classList.add('is-selected');
                }

                grid.appendChild(dayButton);
            }

            while (grid.children.length < 42) {
                grid.appendChild(emptyCell());
            }
        }

        function setOpen(isOpen) {
            calendar.hidden = !isOpen;
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) renderCalendar();
        }

        function changeMonth(offset) {
            var candidate = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + offset, 1, 12, 0, 0, 0);
            if (monthIndex(candidate) < monthIndex(minimumMonth) || monthIndex(candidate) > monthIndex(maximumMonth)) {
                return;
            }

            visibleMonth = candidate;
            renderCalendar();
        }

        input.hidden = true;
        trigger.hidden = false;
        updateTrigger();

        trigger.addEventListener('click', function () {
            setOpen(calendar.hidden);
        });

        previousButton.addEventListener('click', function () {
            changeMonth(-1);
        });

        nextButton.addEventListener('click', function () {
            changeMonth(1);
        });

        grid.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-date]');
            if (!button) return;

            selectedDate = parseDate(button.dataset.date);
            input.value = button.dataset.date;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            updateTrigger();
            setOpen(false);
            trigger.focus();
        });

        clearButton.addEventListener('click', function () {
            selectedDate = null;
            input.value = '';
            updateTrigger();
            setOpen(false);
            trigger.focus();
        });

        if (label) {
            label.addEventListener('click', function (event) {
                event.preventDefault();
                trigger.focus();
                setOpen(true);
            });
        }

        document.addEventListener('click', function (event) {
            if (!picker.contains(event.target)) setOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !calendar.hidden) {
                setOpen(false);
                trigger.focus();
            }
        });
    });
}

function initClinicFormWizard() {
    var form = document.querySelector('[data-clinic-wizard]');
    if (!form) return;

    var stepElements = Array.from(form.querySelectorAll('[data-wizard-step]'));
    var progressIndicators = Array.from(document.querySelectorAll('[data-wizard-indicator]'));
    var stepInput = form.querySelector('[data-wizard-step-input]');
    var title = document.getElementById('clinic-wizard-step-title');
    var description = document.getElementById('clinic-wizard-step-description');
    var masthead = document.querySelector('.clinic-wizard-masthead');
    var stepMeta = {
        1: ['Student Information', 'Provide accurate personal and academic information.'],
        2: ['Parent / Guardian Information', 'Add guardian and emergency contact details.'],
        3: ['Past History', 'Record medical history, medicines, and allergies.'],
        4: ['Consent Form', 'Review the consent document and download a copy.']
    };
    var currentStep = Math.max(1, Math.min(4, Number(form.dataset.startStep) || 1));
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    form.noValidate = true;

    function fieldsForStep(step) {
        return stepElements
            .filter(function (element) { return Number(element.dataset.wizardStep) === step; })
            .flatMap(function (element) {
                return Array.from(element.querySelectorAll('input, select, textarea'));
            })
            .filter(function (field) { return !field.disabled && field.type !== 'hidden'; });
    }

    function firstInvalidField(step) {
        return fieldsForStep(step).find(function (field) { return !field.checkValidity(); }) || null;
    }

    function showStep(step, shouldFocus) {
        currentStep = Math.max(1, Math.min(4, step));

        stepElements.forEach(function (element) {
            element.hidden = Number(element.dataset.wizardStep) !== currentStep;
        });

        progressIndicators.forEach(function (indicator) {
            var indicatorStep = Number(indicator.dataset.wizardIndicator);
            var listItem = indicator.closest('li');
            listItem.classList.toggle('is-active', indicatorStep === currentStep);
            listItem.classList.toggle('is-complete', indicatorStep < currentStep);
            indicator.setAttribute('aria-current', indicatorStep === currentStep ? 'step' : 'false');
        });

        if (stepInput) stepInput.value = String(currentStep);
        if (title) title.textContent = stepMeta[currentStep][0];
        if (description) description.textContent = stepMeta[currentStep][1];

        if (shouldFocus && masthead) {
            masthead.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        }
    }

    form.addEventListener('click', function (event) {
        var nextButton = event.target.closest('[data-wizard-next]');
        var backButton = event.target.closest('[data-wizard-back]');

        if (nextButton) {
            var invalidField = firstInvalidField(currentStep);
            if (invalidField) {
                invalidField.reportValidity();
                invalidField.focus();
                return;
            }
            showStep(currentStep + 1, true);
        }

        if (backButton) {
            showStep(currentStep - 1, true);
        }
    });

    form.addEventListener('submit', function (event) {
        for (var step = 1; step <= 4; step++) {
            var invalidField = firstInvalidField(step);
            if (invalidField) {
                event.preventDefault();
                showStep(step, true);
                invalidField.reportValidity();
                invalidField.focus();
                return;
            }
        }
    });

    showStep(currentStep, false);
}

function initReportDialogs() {
    var dialogs = Array.from(document.querySelectorAll('.reports-dialog'));
    if (dialogs.length === 0) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function openDialog(dialog, trigger) {
        if (!dialog || dialog.open) return;

        dialog.reportTrigger = trigger || null;
        dialog.showModal();
        document.body.classList.add('reports-dialog-open');

        var initialFocus = dialog.querySelector('[autofocus]');
        if (initialFocus) {
            window.requestAnimationFrame(function () {
                if (dialog.open) initialFocus.focus({ preventScroll: true });
            });
        }

        var shell = dialog.querySelector('.reports-dialog-shell');
        if (shell && window.gsap && !reduceMotion) {
            window.gsap.fromTo(shell,
                { opacity: 0, y: 22, scale: 0.98 },
                { opacity: 1, y: 0, scale: 1, duration: 0.32, ease: 'power3.out', clearProps: 'transform,opacity' }
            );
        }
    }

    function closeDialog(dialog) {
        if (!dialog || !dialog.open) return;

        var shell = dialog.querySelector('.reports-dialog-shell');
        if (shell && window.gsap && !reduceMotion) {
            window.gsap.killTweensOf(shell);
            window.gsap.to(shell, {
                opacity: 0,
                y: 12,
                scale: 0.985,
                duration: 0.16,
                ease: 'power2.in',
                onComplete: function () {
                    dialog.close();
                    window.gsap.set(shell, { clearProps: 'transform,opacity,visibility' });
                }
            });
            return;
        }

        dialog.close();
    }

    document.querySelectorAll('[data-report-dialog-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            openDialog(document.getElementById(this.dataset.reportDialogOpen), this);
        });
    });

    dialogs.forEach(function (dialog) {
        dialog.querySelectorAll('[data-report-dialog-close]').forEach(function (button) {
            button.addEventListener('click', function () { closeDialog(dialog); });
        });

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) closeDialog(dialog);
        });

        dialog.addEventListener('close', function () {
            if (!dialogs.some(function (candidate) { return candidate.open; })) {
                document.body.classList.remove('reports-dialog-open');
            }
            if (dialog.reportTrigger && dialog.reportTrigger.isConnected) {
                dialog.reportTrigger.focus();
            }
            dialog.reportTrigger = null;
        });

        if (dialog.hasAttribute('data-report-dialog-auto-open')) {
            openDialog(dialog, null);
        }
    });
}

function initReportPrint() {
    var printButton = document.querySelector('[data-print-report]');
    if (!printButton) return;

    printButton.addEventListener('click', function () {
        window.print();
    });
}
