document.addEventListener('DOMContentLoaded', function () {
    initAlertAutoDismiss();
    initFormValidation();
    initDeleteModal();
    initNurseManagement();
    initLiveSearch();
    initCheckInSearch();
    initCheckInMedicineFields();
    initCheckInFollowUpDate();
    initLiveFilters();
    initVisitStatusDialog();
    initPhoneFormatting();
    initPasswordToggle();
    initPasswordChecklist();
    initInputRestrictions();
    initConsentImageUpload();
    initProfilePhotoUpload();
    initStudentNotificationPreference();
    initBirthDatePicker();
    initPastDateInputs();
    initClinicFormWizard();
    initCourseDepartment();
    initMedicineCarousel();
    initReportDialogs();
    initMedicineDuplicateCheck();
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

/* ── Check In person search ────────────────── */

function initCheckInSearch() {
    var form = document.querySelector('[data-checkin-search-form]');
    var results = document.querySelector('[data-checkin-search-results]');
    if (!form || !results) return;

    var input = form.querySelector('input[type="search"]');
    var people = Array.from(results.querySelectorAll('[data-checkin-person]'));
    var noMatches = results.querySelector('[data-checkin-search-empty]');
    var isOpen = false;

    if (!input) return;

    function normalizeText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase()
            .trim();
    }

    function visiblePeople() {
        return people.filter(function (person) { return !person.hidden; });
    }

    function filterPeople() {
        var query = normalizeText(input.value);
        var matchCount = 0;

        people.forEach(function (person) {
            var matches = query === '' || normalizeText(person.dataset.searchText).includes(query);
            person.hidden = !matches;
            if (matches) matchCount += 1;
        });

        if (noMatches) noMatches.hidden = matchCount !== 0 || people.length === 0;
    }

    function setOpen(open) {
        isOpen = open;
        results.hidden = !open;
        input.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    input.addEventListener('focus', function () {
        filterPeople();
        setOpen(true);
    });

    input.addEventListener('input', function () {
        filterPeople();
        setOpen(true);
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') {
            var firstPerson = visiblePeople()[0];
            if (firstPerson) {
                event.preventDefault();
                firstPerson.focus();
            }
        }

        if (event.key === 'Escape' && isOpen) {
            event.preventDefault();
            setOpen(false);
        }
    });

    people.forEach(function (person) {
        person.addEventListener('keydown', function (event) {
            var matches = visiblePeople();
            var index = matches.indexOf(person);

            if (event.key === 'ArrowDown' && matches[index + 1]) {
                event.preventDefault();
                matches[index + 1].focus();
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (matches[index - 1]) {
                    matches[index - 1].focus();
                } else {
                    input.focus();
                }
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                setOpen(false);
                input.focus();
            }
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        filterPeople();
        setOpen(true);
    });

    document.addEventListener('click', function (event) {
        if (!form.contains(event.target) && !results.contains(event.target)) {
            setOpen(false);
        }
    });

    filterPeople();
    if (input.value.trim() !== '') setOpen(true);
}

/*
 * Live filters: a GET filter form marked data-live-filter refreshes its results as you type, choose an
 * option, or pick a date, without reloading the page or pressing Enter.
 *   data-live-filter = comma-separated selectors of the parts of the page to refresh
 *   data-live-params = the query parameters this filter controls (other filters on the page are kept)
 * Links marked data-live-link inside the same data-live-scope (pagination, "Today", day buttons) load
 * the same way. Results come from the page's own server-rendered HTML, so no separate API is needed.
 */
function initLiveFilters() {
    document.querySelectorAll('form[data-live-filter]').forEach(function (form) {
        var scope = form.closest('[data-live-scope]') || form;
        var selectors = form.dataset.liveFilter.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        var ownedParams = (form.dataset.liveParams || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        var errorBox = scope.querySelector('[data-live-filter-error]');
        var debounceTimer = null;
        var controller = null;

        function fieldFor(name) {
            var field = form.elements.namedItem(name);
            return field && field.type !== 'hidden' ? field : null;
        }

        // Current page URL, with this filter's parameters taken from the form (others left as they are).
        function urlFromForm() {
            var url = new URL(window.location.href);
            url.searchParams.delete('success'); // one-time "saved" messages shouldn't come back on refresh
            ownedParams.forEach(function (name) {
                var field = fieldFor(name);
                var value = field ? String(field.value || '').trim() : '';
                if (value === '') url.searchParams.delete(name); else url.searchParams.set(name, value);
            });
            return url;
        }

        // Current page URL, with this filter's parameters taken from a link.
        function urlFromLink(link) {
            var target = new URL(link.href, window.location.href);
            var url = new URL(window.location.href);
            url.searchParams.delete('success');
            ownedParams.forEach(function (name) {
                var value = target.searchParams.get(name);
                if (value === null || value === '') url.searchParams.delete(name); else url.searchParams.set(name, value);
            });
            if (target.hash) url.hash = target.hash;
            return url;
        }

        function syncFields(url) {
            ownedParams.forEach(function (name) {
                var field = fieldFor(name);
                if (field) field.value = url.searchParams.get(name) || '';
            });
        }

        function showError(text) {
            if (!errorBox) return;
            errorBox.textContent = text;
            errorBox.hidden = !text;
        }

        function load(url) {
            if (controller) controller.abort();
            controller = window.AbortController ? new AbortController() : null;
            var regions = selectors.map(function (sel) { return scope.querySelector(sel); }).filter(Boolean);
            regions.forEach(function (region) { region.classList.add('is-updating'); });

            fetch(url.toString(), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller ? controller.signal : undefined
            }).then(function (response) {
                if (!response.ok) throw new Error('The results could not be loaded (server error ' + response.status + '). Please refresh the page.');
                return response.text();
            }).then(function (html) {
                var page = new DOMParser().parseFromString(html, 'text/html');
                selectors.forEach(function (sel) {
                    var current = scope.querySelector(sel);
                    var next = page.querySelector(sel);
                    if (current && next) current.replaceWith(document.importNode(next, true));
                });
                window.history.replaceState(null, '', url.toString());
                showError('');
            }).catch(function (error) {
                if (error && error.name === 'AbortError') return;
                regions.forEach(function (region) { region.classList.remove('is-updating'); });
                showError(error && error.message ? error.message : 'The results could not be loaded. Please refresh the page.');
            });
        }

        form.addEventListener('input', function (event) {
            if (!event.target.matches('input[type="search"], input[type="text"]')) return;
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(function () { load(urlFromForm()); }, 350);
        });

        form.addEventListener('change', function (event) {
            var field = event.target;
            if (field.matches('select')) {
                load(urlFromForm());
            } else if (field.matches('input[type="date"]')) {
                // min/max keep half-typed years (e.g. 0002) from loading; clearing the date shows all dates.
                if (field.value === '' || field.checkValidity()) load(urlFromForm());
            }
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            window.clearTimeout(debounceTimer);
            load(urlFromForm());
        });

        scope.addEventListener('click', function (event) {
            var link = event.target.closest('a[data-live-link]');
            if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            if ((link.closest('[data-live-scope]') || form) !== scope) return;
            event.preventDefault();
            var url = urlFromLink(link);
            syncFields(url);
            load(url);
        });
    });
}

// The follow-up date only applies to follow-up visits: enabled and required for them, cleared otherwise.
// Check In can show two visit forms (the selected person, and "add a patient"), so each form is wired on its own.
function initCheckInFollowUpDate() {
    document.querySelectorAll('[data-checkin-status]').forEach(function (status) {
        var followUp = (status.form || document).querySelector('[data-checkin-follow-up]');
        if (followUp) wireCheckInFollowUpDate(status, followUp);
    });
}

function wireCheckInFollowUpDate(status, followUp) {
    function syncFollowUp() {
        var isFollowUp = status.value === 'follow-up';
        followUp.disabled = !isFollowUp;
        followUp.required = isFollowUp;
        followUp.title = isFollowUp ? '' : 'Set the status to Follow-up to choose a date';
        if (!isFollowUp) followUp.value = '';
    }

    status.addEventListener('change', syncFollowUp);
    syncFollowUp();
}

function initCheckInMedicineFields() {
    document.querySelectorAll('[data-checkin-medicine]').forEach(function (medicine) {
        var scope = medicine.form || document;
        var dosage = scope.querySelector('[data-checkin-medicine-dosage]');
        var quantity = scope.querySelector('[data-checkin-medicine-quantity]');
        if (dosage && quantity) wireCheckInMedicineFields(medicine, dosage, quantity);
    });
}

function wireCheckInMedicineFields(medicine, dosage, quantity) {
    function syncMedicineFields(clearValues) {
        var selectedOption = medicine.options[medicine.selectedIndex];
        var hasMedicine = medicine.value !== '';
        var availableStock = Number(selectedOption ? selectedOption.dataset.stock : 0);

        if (clearValues) {
            dosage.value = '';
            quantity.value = '';
        }

        dosage.disabled = !hasMedicine;
        dosage.required = hasMedicine;
        quantity.disabled = !hasMedicine;
        quantity.required = hasMedicine;

        quantity.dataset.availableStock = hasMedicine ? String(Math.max(0, availableStock)) : '';
        quantity.placeholder = hasMedicine ? availableStock + ' available' : 'Select medicine first';
        validateQuantity();
    }

    // Whole units only, from 1 up to the selected medicine's available stock.
    function validateQuantity() {
        var available = Number(quantity.dataset.availableStock || 0);
        quantity.value = quantity.value.replace(/\D/g, '').slice(0, 6);
        var value = quantity.value;
        var message = '';
        if (!quantity.disabled && value !== '') {
            if (!/^[0-9]+$/.test(value) || Number(value) < 1) {
                message = 'Enter a whole number of units (1 or more).';
            } else if (available > 0 && Number(value) > available) {
                message = 'Only ' + available + ' unit(s) are available.';
            }
        }
        quantity.setCustomValidity(message);
    }

    quantity.addEventListener('input', validateQuantity);
    medicine.addEventListener('change', function () {
        syncMedicineFields(true);
    });

    // The check-in confirmation mentions the medicine that will be deducted from stock.
    if (medicine.form) {
        medicine.form.addEventListener('confirmdialog:open', function (event) {
            var option = medicine.options[medicine.selectedIndex];
            if (!medicine.value || !quantity.value || !option) return;
            event.detail.message += ' ' + quantity.value + ' × ' + (option.dataset.name || 'the selected medicine')
                + ' will be deducted from stock.';
        });
    }

    syncMedicineFields(false);
}

/* ── 2. Client-side form validation ──────────────────── */

function initFormValidation() {
    var forms = document.querySelectorAll('form[method="POST"]:not(.delete-form)');
    if (forms.length === 0) return;

    forms.forEach(function (form) {
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
    });
}

function initStudentNotificationPreference() {
    var input = document.querySelector('.student-notification-preference input[type="checkbox"]');
    var state = document.querySelector('.student-notification-state strong');
    if (!input || !state) return;

    function updateState() {
        state.textContent = input.checked ? 'enabled' : 'disabled';
    }

    input.addEventListener('change', updateState);
    updateState();
}

/* ── 3. Delete confirmation modal ────────────────────── */

// Shared confirmation popup for forms with class "delete-form" or a data-confirm attribute
// (title/message/button from data-confirm-title, data-confirm-message, data-confirm-label,
// data-confirm-class; data-delete-title/-message still work). Before it opens, the form receives a
// "confirmdialog:open" event whose detail.message it may extend. Listens at the document level (and
// builds the popup on first use) so forms added later, e.g. by a live filter, are covered too.
function initDeleteModal() {
    var overlay = null;
    var confirmBtn = null;
    var title = null;
    var message = null;
    var pendingForm = null;
    var activeTrigger = null;

    function closeModal() {
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        pendingForm = null;
        if (activeTrigger && activeTrigger.isConnected) activeTrigger.focus();
        activeTrigger = null;
    }

    function ensureOverlay() {
        if (overlay) return;
        overlay = document.createElement('div');
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

        confirmBtn = overlay.querySelector('#modal-confirm');
        title = overlay.querySelector('#modal-delete-title');
        message = overlay.querySelector('#modal-delete-message');

        confirmBtn.addEventListener('click', function () {
            if (pendingForm) pendingForm.submit();
        });
        overlay.querySelector('#modal-cancel').addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) closeModal();
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || e.defaultPrevented) return;
        if (!form.classList.contains('delete-form') && !form.hasAttribute('data-confirm')) return;

        e.preventDefault();
        ensureOverlay();
        pendingForm = form;
        activeTrigger = document.activeElement;
        var detail = {
            message: form.dataset.confirmMessage || form.dataset.deleteMessage || 'Are you sure? This action cannot be undone.'
        };
        form.dispatchEvent(new CustomEvent('confirmdialog:open', { detail: detail }));
        title.textContent = form.dataset.confirmTitle || form.dataset.deleteTitle || 'Confirm delete';
        message.textContent = detail.message;
        confirmBtn.textContent = form.dataset.confirmLabel || 'Delete';
        confirmBtn.className = 'btn ' + (form.dataset.confirmClass || 'btn-danger');
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        confirmBtn.focus();
    });
}

function initNurseManagement() {
    var page = document.querySelector('[data-nurses-page]');
    if (!page) return;

    var dialogs = Array.from(page.querySelectorAll('.nurse-dialog'));
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var ajaxAlert = page.querySelector('[data-nurses-ajax-alert]');

    function openDialog(dialog, trigger) {
        if (!dialog || dialog.open) return;
        dialog.nurseTrigger = trigger || null;
        dialog.showModal();
        document.body.classList.add('nurse-dialog-open');

        var initialFocus = dialog.querySelector('[autofocus]');
        if (initialFocus) {
            window.requestAnimationFrame(function () {
                if (dialog.open) initialFocus.focus({ preventScroll: true });
            });
        }

        var shell = dialog.querySelector('.nurse-dialog-shell');
        if (shell && window.gsap && !reduceMotion) {
            window.gsap.fromTo(shell,
                { opacity: 0, y: 20, scale: 0.985 },
                { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out', clearProps: 'transform,opacity' }
            );
        }
    }

    function closeDialog(dialog) {
        if (!dialog || !dialog.open) return;
        var shell = dialog.querySelector('.nurse-dialog-shell');
        if (shell && window.gsap && !reduceMotion) {
            window.gsap.killTweensOf(shell);
            window.gsap.to(shell, {
                opacity: 0,
                y: 10,
                scale: 0.99,
                duration: 0.15,
                ease: 'power2.in',
                onComplete: function () {
                    dialog.close();
                    window.gsap.set(shell, { clearProps: 'transform,opacity' });
                }
            });
            return;
        }
        dialog.close();
    }

    page.querySelectorAll('[data-nurse-dialog-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            openDialog(document.getElementById(this.dataset.nurseDialogOpen), this);
        });
    });

    dialogs.forEach(function (dialog) {
        dialog.querySelectorAll('[data-nurse-dialog-close]').forEach(function (button) {
            button.addEventListener('click', function () { closeDialog(dialog); });
        });
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) closeDialog(dialog);
        });
        dialog.addEventListener('close', function () {
            document.body.classList.remove('nurse-dialog-open');
            if (dialog.nurseTrigger && dialog.nurseTrigger.isConnected) dialog.nurseTrigger.focus();
            dialog.nurseTrigger = null;
        });
        if (dialog.hasAttribute('data-nurse-dialog-auto-open')) openDialog(dialog, null);
    });

    var editDialog = document.getElementById('edit-nurse-dialog');
    page.querySelectorAll('[data-nurse-edit]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!editDialog) return;
            editDialog.querySelector('[data-edit-nurse-id]').value = this.dataset.nurseId || '';
            editDialog.querySelector('[data-edit-nurse-name]').value = this.dataset.nurseName || '';
            editDialog.querySelector('[data-edit-nurse-position]').value = this.dataset.nursePosition || '';
            editDialog.querySelector('[data-edit-nurse-license]').value = this.dataset.nurseLicense || '';
            var usernameField = editDialog.querySelector('[data-edit-nurse-username]');
            if (usernameField) usernameField.value = this.dataset.nurseUsername || '';
            var resetField = editDialog.querySelector('[data-edit-nurse-reset]');
            if (resetField) resetField.checked = false;
            openDialog(editDialog, this);
        });
    });

    function updateDutyState(button, isOnDuty, campus) {
        var row = button.closest('[data-nurse-row]');
        var status = row ? row.querySelector('[data-nurse-duty-status]') : null;
        var campusLabel = row ? row.querySelector('[data-nurse-duty-campus]') : null;
        var value = button.closest('form').querySelector('[data-nurse-duty-value]');

        button.setAttribute('aria-checked', isOnDuty ? 'true' : 'false');
        button.setAttribute('title', isOnDuty ? 'Set off duty' : 'Set on duty');
        if (value) value.value = isOnDuty ? '0' : '1';
        if (status) {
            status.textContent = isOnDuty ? 'On duty' : 'Off duty';
            status.classList.toggle('is-on-duty', isOnDuty);
            status.classList.toggle('is-off-duty', !isOnDuty);
        }
        if (campusLabel) {
            var campusName = campusLabel.querySelector('[data-nurse-duty-campus-name]');
            if (campusName && typeof campus === 'string') campusName.textContent = campus;
            campusLabel.hidden = !isOnDuty;
        }
    }

    function updateCount(selector, value) {
        page.querySelectorAll(selector).forEach(function (element) {
            element.textContent = String(value);
            if (window.gsap && !reduceMotion) {
                window.gsap.fromTo(element, { scale: 1.2 }, { scale: 1, duration: 0.28, ease: 'back.out(2)' });
            }
        });
    }

    // Turning a nurse on duty first asks which campus; turning them off happens immediately.
    var campusDialog = page.querySelector('[data-nurse-campus-dialog]');
    var campusForm = campusDialog ? campusDialog.querySelector('[data-nurse-campus-form]') : null;
    var campusError = campusDialog ? campusDialog.querySelector('[data-nurse-campus-error]') : null;
    var pendingDutyForm = null;

    if (campusForm) {
        campusForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var choice = campusForm.querySelector('input[name="campus_choice"]:checked');
            if (!choice) {
                if (campusError) campusError.hidden = false;
                return;
            }
            var dutyForm = pendingDutyForm;
            pendingDutyForm = null;
            closeDialog(campusDialog);
            if (dutyForm) sendDutyUpdate(dutyForm, true, choice.value);
        });
        campusDialog.addEventListener('close', function () {
            pendingDutyForm = null;
        });
    }

    function askForCampus(form) {
        var button = form.querySelector('[data-nurse-duty-toggle]');
        pendingDutyForm = form;
        campusForm.reset();
        if (campusError) campusError.hidden = true;
        var name = campusDialog.querySelector('[data-nurse-campus-name]');
        if (name) name.textContent = (button && button.dataset.nurseName) || 'this nurse';
        openDialog(campusDialog, button);
    }

    page.querySelectorAll('[data-nurse-duty-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = form.querySelector('[data-nurse-duty-toggle]');
            if (!button || button.disabled) return;

            var turningOn = button.getAttribute('aria-checked') !== 'true';
            if (turningOn && campusDialog) {
                askForCampus(form);
                return;
            }
            sendDutyUpdate(form, turningOn, '');
        });
    });

    function sendDutyUpdate(form, nextState, campus) {
            var button = form.querySelector('[data-nurse-duty-toggle]');
            if (!button || button.disabled) return;

            var currentState = button.getAttribute('aria-checked') === 'true';
            var row = button.closest('[data-nurse-row]');
            var campusName = row ? row.querySelector('[data-nurse-duty-campus-name]') : null;
            var previousCampus = campusName ? campusName.textContent : '';
            var body = new FormData(form);
            body.set('is_on_duty', nextState ? '1' : '0');
            body.set('duty_campus', nextState ? campus : '');

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            updateDutyState(button, nextState, nextState ? campus : '');
            if (ajaxAlert) ajaxAlert.hidden = true;

            fetch(window.location.href, {
                method: 'POST',
                body: body,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'The duty status could not be updated.');
                    }
                    return payload;
                });
            }).then(function (payload) {
                updateDutyState(button, Boolean(payload.isOnDuty), payload.campus || '');
                updateCount('[data-nurses-on-duty]', payload.onDutyCount);
                updateCount('[data-nurses-off-duty]', payload.offDutyCount);
            }).catch(function (error) {
                updateDutyState(button, currentState, previousCampus);
                if (ajaxAlert) {
                    ajaxAlert.textContent = error.message;
                    ajaxAlert.hidden = false;
                    ajaxAlert.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
                }
            }).finally(function () {
                button.disabled = false;
                button.removeAttribute('aria-busy');
            });
    }

    // Add nurse: "Mark this nurse as on duty" reveals the campus choice and makes it required.
    var addDuty = page.querySelector('[data-add-nurse-duty]');
    var addCampus = page.querySelector('[data-add-nurse-campus]');
    if (addDuty && addCampus) {
        var syncAddCampus = function () {
            addCampus.hidden = !addDuty.checked;
            addCampus.querySelectorAll('input[type="radio"]').forEach(function (radio, index) {
                radio.required = addDuty.checked && index === 0;
                radio.disabled = !addDuty.checked;
            });
        };
        addDuty.addEventListener('change', syncAddCampus);
        syncAddCampus();
    }
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

    // Digits only, capped at the field's maxlength (illness ages use 3, stock quantities use 6).
    document.querySelectorAll('.integer-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var limit = this.maxLength > 0 ? this.maxLength : 6;
            this.value = this.value.replace(/\D/g, '').slice(0, limit);
        });
    });

    // Philippine mobile numbers: digits only, exactly 11, starting with 09.
    document.querySelectorAll('.mobile-input').forEach(function (input) {
        function validate() {
            var value = input.value;
            input.setCustomValidity(
                value === '' || /^09\d{9}$/.test(value)
                    ? ''
                    : 'Enter an 11-digit mobile number starting with 09 (e.g. 09171234567).'
            );
        }
        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 11);
            validate();
        });
        validate();
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
        // The Settings pages wrap the form in an avatar panel; the shared include uses a profile layout.
        var container = form.closest('.settings-profile-layout, .student-profile-avatar-panel');
        var preview = container ? container.querySelector('[data-profile-preview]') : null;
        var fileName = form.querySelector('.settings-profile-file-name');
        var submit = form.querySelector('.settings-profile-submit');
        var previewUrl = null;

        if (!input || !fileName || !submit) return;

        // Save stays usable without JavaScript; with it, Save waits until a file is picked.
        submit.disabled = !(input.files && input.files.length);

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

            if (preview && file.type && file.type.indexOf('image/') === 0) {
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

function initPastDateInputs() {
    document.querySelectorAll('[data-past-date-picker]').forEach(function (input) {
        function openCalendar() {
            if (typeof input.showPicker !== 'function') return;

            try {
                input.showPicker();
            } catch (error) {
                // The browser still exposes its normal date control as a fallback.
            }
        }

        input.addEventListener('click', openCalendar);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Tab' || event.key === 'Escape') return;

            event.preventDefault();
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'ArrowDown') {
                openCalendar();
            }
        });
        input.addEventListener('paste', function (event) {
            event.preventDefault();
        });
        input.addEventListener('drop', function (event) {
            event.preventDefault();
        });
    });
}

// Department isn't chosen by the student; it follows the selected course (the server assigns it the same way).
function initCourseDepartment() {
    var course = document.querySelector('[data-course-select]');
    var department = document.querySelector('[data-course-department]');
    if (!course || !department) return;

    var departments = {};
    try {
        departments = JSON.parse(course.dataset.courseDepartments || '{}');
    } catch (error) {
        departments = {};
    }

    function syncDepartment() {
        department.value = departments[course.value] || '';
    }

    course.addEventListener('change', syncDepartment);
    syncDepartment();
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
        4: ['Consent Form', 'Review the consent document, agree to the data privacy notice, and save.']
    };
    var currentStep = Math.max(1, Math.min(4, Number(form.dataset.startStep) || 1));
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var errorBox = form.querySelector('[data-wizard-error]');

    form.noValidate = true;

    function fieldLabel(field) {
        var group = field.closest('.form-group');
        var label = group ? group.querySelector('label') : null;
        var text = label ? label.textContent : (field.getAttribute('aria-label') || field.name || 'This field');
        return text.replace(/\*/g, '').trim();
    }

    // Browsers hide their validation bubble as soon as the page scrolls, so the problem is also
    // written into a visible alert and the field is outlined until the student edits it.
    function reportInvalidField(step, field) {
        showStep(step, false);
        field.setAttribute('aria-invalid', 'true');

        if (errorBox) {
            errorBox.textContent = 'Step ' + step + ' (' + stepMeta[step][0] + '): '
                + fieldLabel(field) + ' - ' + (field.validationMessage || 'Please check this field.');
            errorBox.hidden = false;
        }

        field.scrollIntoView({ behavior: 'auto', block: 'center' });
        field.focus({ preventScroll: true });
        field.reportValidity();
    }

    function clearInvalidField(event) {
        var field = event.target;
        if (!field || field.getAttribute('aria-invalid') !== 'true' || !field.checkValidity()) return;
        field.removeAttribute('aria-invalid');
        if (errorBox && !form.querySelector('[aria-invalid="true"]')) {
            errorBox.hidden = true;
            errorBox.textContent = '';
        }
    }

    form.addEventListener('input', clearInvalidField);
    form.addEventListener('change', clearInvalidField);

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
                reportInvalidField(currentStep, invalidField);
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
                reportInvalidField(step, invalidField);
                return;
            }
        }
    });

    var privacyConsent = form.querySelector('[data-privacy-consent]');
    var privacySubmit = form.querySelector('[data-privacy-consent-submit]');
    if (privacyConsent && privacySubmit) {
        var syncPrivacyConsent = function () {
            privacySubmit.disabled = !privacyConsent.checked;
            privacySubmit.title = privacyConsent.checked ? '' : 'Agree to the Data Privacy Notice to save';
        };
        privacyConsent.addEventListener('change', syncPrivacyConsent);
        syncPrivacyConsent();
    }

    showStep(currentStep, false);
}

function initMedicineCarousel() {
    document.querySelectorAll('[data-medicine-carousel]').forEach(function (carousel, carouselIndex) {
        var viewport = carousel.querySelector('.medicine-showcase-viewport');
        var track = carousel.querySelector('[data-medicine-carousel-track]');
        var cards = Array.from(carousel.querySelectorAll('[data-medicine-carousel-item]'));
        var previousButton = carousel.querySelector('[data-medicine-carousel-previous]');
        var nextButton = carousel.querySelector('[data-medicine-carousel-next]');
        var status = carousel.querySelector('[data-medicine-carousel-status]');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var currentIndex = 0;
        var resizeTimer = null;

        if (!viewport || !track || cards.length === 0 || !previousButton || !nextButton) return;

        track.id = track.id || 'medicine-carousel-track-' + (carouselIndex + 1);
        previousButton.setAttribute('aria-controls', track.id);
        nextButton.setAttribute('aria-controls', track.id);

        function visibleCount() {
            var value = parseInt(getComputedStyle(carousel).getPropertyValue('--medicine-visible'), 10);
            return Number.isFinite(value) && value > 0 ? value : 1;
        }

        function maximumIndex() {
            return Math.max(0, cards.length - visibleCount());
        }

        function updateControls() {
            var visible = visibleCount();
            var maximum = maximumIndex();
            var isStatic = maximum === 0;
            var isAtStart = currentIndex === 0;
            var isAtEnd = currentIndex >= maximum;

            carousel.classList.toggle('is-static', isStatic);

            previousButton.hidden = isStatic;
            previousButton.disabled = isAtStart;
            previousButton.classList.toggle('is-disabled', isAtStart);
            previousButton.setAttribute('aria-hidden', isAtStart ? 'true' : 'false');

            nextButton.hidden = isStatic;
            nextButton.disabled = isAtEnd;
            nextButton.classList.toggle('is-disabled', isAtEnd);
            nextButton.setAttribute('aria-hidden', isAtEnd ? 'true' : 'false');

            if (status) {
                status.textContent = 'Showing medicines ' + (currentIndex + 1) + ' to ' +
                    Math.min(cards.length, currentIndex + visible) + ' of ' + cards.length + '.';
            }
        }

        function targetOffset() {
            var cardWidth = cards[0].getBoundingClientRect().width;
            var trackStyle = getComputedStyle(track);
            var gap = parseFloat(trackStyle.columnGap || trackStyle.gap) || 0;
            return currentIndex * (cardWidth + gap);
        }

        function render(animate) {
            currentIndex = Math.min(currentIndex, maximumIndex());
            updateControls();

            // Force grid sizing to settle after an arrow enters or leaves the flow.
            viewport.getBoundingClientRect();
            var x = -targetOffset();

            if (window.gsap) {
                window.gsap.killTweensOf(track);
                if (animate && !reduceMotion) {
                    window.gsap.to(track, {
                        x: x,
                        duration: 0.55,
                        ease: 'power3.inOut',
                        overwrite: true
                    });
                } else {
                    window.gsap.set(track, { x: x });
                }
                return;
            }

            track.style.transition = animate && !reduceMotion
                ? 'transform 0.55s cubic-bezier(0.22, 1, 0.36, 1)'
                : 'none';
            track.style.transform = 'translate3d(' + x + 'px, 0, 0)';
        }

        previousButton.addEventListener('click', function () {
            if (currentIndex === 0) return;
            currentIndex--;
            render(true);
        });

        nextButton.addEventListener('click', function () {
            if (currentIndex >= maximumIndex()) return;
            currentIndex++;
            render(true);
        });

        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(function () { render(false); }, 120);
        });

        render(false);
    });
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

    // Delegated so buttons in rows added later (live filters) open dialogs too. The dialog gets a
    // "reportdialog:open" event first, so it can fill its fields from the clicked button.
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-report-dialog-open]');
        if (!button) return;
        var dialog = document.getElementById(button.dataset.reportDialogOpen);
        if (!dialog) return;
        dialog.dispatchEvent(new CustomEvent('reportdialog:open', { detail: { trigger: button } }));
        openDialog(dialog, button);
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

// Reports → Visit reports "Change status" dialog: filled from the clicked row; the follow-up date is
// shown (and required) only when Follow-up is chosen. Completed visits have no button, so they stay final.
function initVisitStatusDialog() {
    var dialog = document.getElementById('visit-status-dialog');
    if (!dialog) return;

    var idInput = dialog.querySelector('[data-visit-status-id]');
    var patient = dialog.querySelector('[data-visit-status-patient]');
    var current = dialog.querySelector('[data-visit-status-current]');
    var select = dialog.querySelector('[data-visit-status-select]');
    var dateField = dialog.querySelector('[data-visit-status-date-field]');
    var dateInput = dialog.querySelector('[data-visit-status-date]');
    var errorBox = dialog.querySelector('[data-visit-status-error]');
    var labels = { open: 'Open', completed: 'Completed', 'follow-up': 'Follow Up' };
    if (!idInput || !select || !dateField || !dateInput) return;

    function syncDate() {
        var isFollowUp = select.value === 'follow-up';
        dateField.hidden = !isFollowUp;
        dateInput.disabled = !isFollowUp;
        dateInput.required = isFollowUp;
    }

    dialog.addEventListener('reportdialog:open', function (event) {
        var trigger = event.detail && event.detail.trigger;
        if (!trigger || !trigger.dataset.visitId) return;

        var status = trigger.dataset.visitStatus || 'open';
        var patientInput = dialog.querySelector('[data-visit-status-patient-input]');
        var currentInput = dialog.querySelector('[data-visit-status-current-input]');
        idInput.value = trigger.dataset.visitId;
        if (patientInput) patientInput.value = trigger.dataset.visitPatient || '';
        if (currentInput) currentInput.value = status;
        if (patient) patient.textContent = trigger.dataset.visitPatient || 'this patient';
        if (current) {
            current.textContent = labels[status] || status;
            current.className = 'visit-status visit-status-' + status;
        }
        // Completing is the usual next step, so it is preselected.
        select.value = 'completed';
        dateInput.value = trigger.dataset.visitFollowUp || '';
        if (errorBox) errorBox.hidden = true;
        syncDate();
    });

    select.addEventListener('change', syncDate);
    syncDate();
}

function initMedicineDuplicateCheck() {
    var input = document.querySelector('[data-medicine-name-input]');
    var message = document.querySelector('[data-medicine-name-duplicate]');
    if (!input || !message) return;

    var submitButton = input.form ? input.form.querySelector('button[type="submit"]') : null;
    var existing = [];
    try {
        existing = JSON.parse(input.dataset.existingMedicines || '[]');
    } catch (error) {
        existing = [];
    }

    // Matches the server rule: spacing and letter case are ignored.
    function nameKey(value) {
        return value.replace(/\s+/g, '').toLowerCase();
    }

    function checkName() {
        var key = nameKey(input.value);
        var match = key === '' ? null : existing.find(function (medicine) {
            return nameKey(medicine.name) === key;
        });

        if (match) {
            message.textContent = match.archived
                ? match.name + ' already exists in Archived medicines. Restore it instead.'
                : match.name + ' already exists in the inventory. Use Add stock instead.';
            message.hidden = false;
            input.setAttribute('aria-invalid', 'true');
        } else {
            message.textContent = '';
            message.hidden = true;
            input.removeAttribute('aria-invalid');
        }

        if (submitButton) submitButton.disabled = Boolean(match);
    }

    input.addEventListener('input', checkName);
    checkName();
}
