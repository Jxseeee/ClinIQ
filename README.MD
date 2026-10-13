# Student Management System

A PHP CRUD application with role-based authentication. Admins manage students and post announcements (announcements can be created or deleted, not edited). Students view announcements, fill out clinic forms, manage their own profile, and chat with admins in real time via Pusher.

## Requirements

- PHP 8.1+
- MySQL / MariaDB
- Composer
- XAMPP (or equivalent web server)
- [Pusher](https://pusher.com/) account (free tier) for real-time chat

## Setup

1. Clone the repository and install dependencies:

```bash
composer install
```

2. Create a `.env` file in the project root (use `.env.example` as a template) and configure your database credentials and Pusher keys.

No frontend build step is required. Browser-ready styles are stored in `public/assets/css/` and loaded directly by the PHP pages.

3. Import the database schema:

```bash
mysql -u root -p < database/Clinic.sql
```

If you already have the database and only need chat, run:

```bash
mysql -u root -p < database/migrations/add_messages.sql
```

If you already have the database and need the first-login password-change flag, run:

```bash
mysql -u root -p < database/migrations/add_must_change_password.sql
```

If you already have the database and need the chat active-user queue, run:

```bash
mysql -u root -p < database/migrations/add_chat_queue.sql
```

If you already have the database and need consent/guardian image uploads, run:

```bash
mysql -u root -p < database/migrations/add_consent_image_path.sql
```

If you already have the database and need admin/student profile photos, run:

```bash
mysql -u root -p < database/migrations/add_profile_images.sql
```

If you already have the database and need student notification preferences, run:

```bash
mysql -u root -p < database/migrations/add_student_notification_preferences.sql
```

If you already have the database and need persisted clinic consent choices, run:

```bash
mysql -u root -p < database/migrations/add_clinic_consent_choices.sql
```

If you already have the database and want each admin to have their own read/unread notification state, run:

```bash
mysql -u root -p < database/migrations/add_admin_notification_reads.sql
```

(`add_clinic_occupancy.sql` is no longer needed — nurse and clinic occupancy counts are calculated live.)

If you already have `ClinicVisits` and need centralized student/staff check-in, run:

```bash
mysql -u root -p < database/migrations/add_clinic_checkins.sql
```

To track dispensed quantities and deduct medicine inventory during Check In, then run:

```bash
mysql -u root -p < database/migrations/add_visit_medicine_quantity.sql
```

If you already have the database and need nurse directory and duty management, run:

```bash
mysql -u root -p < database/migrations/add_nurses.sql
```

If you already have the database and need medicine inventory reports, run:

```bash
mysql -u root -p < database/migrations/add_medicine_inventory.sql
```

If you already have the `Medicines` table and need medicine archiving, run:

```bash
mysql -u root -p < database/migrations/add_medicine_archive.sql
```

If you already have the database and need the required clinic form with Data Privacy Act consent, run:

```bash
mysql -u root -p < database/migrations/add_privacy_consent.sql
```

If you already have the database and need the audit log, run:

```bash
mysql -u root -p < database/migrations/add_audit_logs.sql
```

If you already have `ClinicVisits` and need a separate medicine-given field, run:

```bash
mysql -u root -p < database/migrations/add_visit_medicine_given.sql
```

If you already have the database and need clinic visits, notifications, and chat conversation status, run:

```bash
mysql -u root -p < database/migrations/add_clinic_workflow.sql
```

4. Configure Pusher for real-time chat:

- Create a free app at [dashboard.pusher.com](https://dashboard.pusher.com/)
- Copy **App ID**, **Key**, **Secret**, and **Cluster** into your `.env`:

```
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=ap1
```

Chat still works without Pusher (messages are saved to MySQL), but the other party must refresh the page to see new replies.

5. Open the app in your browser:

```
http://localhost/ClinIQ/public/login.php
```

## Deployment Security (Ubuntu)

The whole project sits inside the web root, so private files must be blocked from download. The root `.htaccess` blocks dotfiles (`.env`, `.git`), `*.sql`, Composer files, docs, `vendor/`, `database/`, `app/config/`, `app/includes/`, and folder listings. `public/uploads/consent/.htaccess` blocks direct access to guardian ID images.

Built-in protections:

- Session cookies are `HttpOnly`, `SameSite=Lax`, `Secure` over HTTPS, and PHP strict session mode is on
- Sign-in is refused for 15 minutes after 5 failed attempts on one account from the same IP address, 10 on one account from anywhere, or 30 from one IP address overall (counted from the audit log; the per-IP limit is high because campus networks share IP addresses)
- PHP errors are hidden from visitors unless `APP_DEBUG=true` in `.env` (they are still written to the PHP error log)
- Admin password resets must be changed by the student at their next sign-in; changing a password issues a new session ID
- Logout links carry the CSRF token; deleting a student also deletes their guardian ID image and profile photo

**Apache:** Ubuntu ignores `.htaccess` files by default. Allow them for the project folder in your site config (for example `/etc/apache2/sites-available/000-default.conf`):

```apache
<Directory /var/www/html/ClinIQ>
    AllowOverride All
</Directory>
```

Then reload Apache: `sudo systemctl reload apache2`.

**Nginx:** `.htaccess` files are never read. Add these rules to the `server { }` block instead:

```nginx
location ~ /\.                       { deny all; }
location ~* \.(sql|md|lock|log|bak|ini|sh)$ { deny all; }
location ~ /(composer|skills-lock)\.json$ { deny all; }
location ~ ^/ClinIQ/(vendor|database|app/config|app/includes|public/uploads/consent)(/|$) { deny all; }
autoindex off;
```

**Check after deploying** (each should print `403` or `404`):

```bash
for p in .env .git/HEAD database/Clinic.sql vendor/autoload.php app/config/database.php; do
  curl -s -o /dev/null -w "$p %{http_code}
" http://your-server/ClinIQ/$p
done
```

## Default Accounts

| Role    | Username / Student ID | Password    |
|---------|----------------------|--------------|
| Admin   | admin                | admin123     |
| Student | 10001                | student123   |

New student accounts are created by admins only. Admin-created students use the default password `Password@123!` and are forced to change it on first login.

## Real-time Chat

- **Students:** `students/messages.php` — one thread per student with the admin office
- **Admins:** `app/admin/messages.php` — inbox of all student conversations with unread badges
- Messages are stored in the `Messages` table and pushed instantly via Pusher private channels
- Admins can mark conversations resolved; students see the resolved indicator, and a new student message automatically reopens the conversation
- Active chat pages are limited to 5 users for testing; extra users wait on `public/chat-queue.php`
- API endpoints: `app/api/chat.php` (send/fetch), `app/api/pusher-auth.php` (channel authorization), and `app/api/chat-presence.php` (queue/heartbeat)

## Patient Records

- `students/profile.php` merges patient records and the clinic form preview
- The clinic form is required: until a student saves it with Data Privacy Act consent and all required fields (including Guardian 1 name and mobile), every other student page redirects to the form, the chat APIs return 403, and sidebar links appear locked (the consent PDF download stays available). The check runs on every request, and if `Students.PrivacyConsentAt` is missing the app adds the column itself instead of letting students through
- The final step shows a Data Privacy Notice (RA 10173); the Save button stays disabled until the student ticks the consent checkbox, and the server rejects saves without it. The consent time is stored in `Students.PrivacyConsentAt` and shown on the admin student record
- Paper forms and PDFs carry their form numbers in the top-left corner: "FCAT School Clinic Form 01" (clinic form) and "FCAT School Clinic Form 02" (consent form)
- Downloaded PDFs are single A4 pages with the FCAT letterhead (seal, school name, student number), a document title, numbered sections, signature lines, and a footer with the generation time and page numbers. The seal uses `public/assets/images/fcat-clinic-logo-print.jpg` so the PHP GD extension is not required
- Mobile numbers (guardian, emergency contact, student, and admin-entered student phone) must be exactly 11 digits starting with 09; they are validated in the browser and on the server and stored without dashes
- Student clinic details are edited through a responsive four-step form covering personal information, guardians, medical history/allergies, and a read-only consent preview
- Wizard steps are sequential and cannot be opened from the progress indicators; the final step provides a downloadable consent PDF
- Date of Birth cannot be later than the current Manila date
- Saved guardian, emergency, medical, allergy, and stored consent details appear in student/admin record views and consent downloads
- Students can download the clinic form and consent form as PDFs
- Students can upload a parent/guardian ID image below the consent form; images get random filenames and the upload folder blocks direct URL access, so they are only served through the auth-checked view pages
- The ID image View button appears only after an upload exists
- Admins can view uploaded parent/guardian ID images from the student record page
- Admins record clinic visits from the centralized `app/admin/check-in.php` workflow after searching for an existing student or staff account
- Staff check-in currently uses existing administrator accounts as staff records
- Existing medicines are selected from inventory; dosage and quantity are recorded separately, quantity appears in visit history and Reports, and available stock is deducted atomically
- Students can view clinic visit history in their patient records
- Patient and consent records stack into readable mobile layouts without horizontal table scrolling
- Clinic follow-up dates are limited to today through 30 days ahead

## Audit Log

- Admins (`app/admin/audit-log.php`) and students (`students/audit-log.php`) both have an **Audit Log** tab
- Every entry records the date and time, who performed it, which account it concerns, a description, and the IP address; names are stored with the entry so history survives account deletion, and entries cannot be edited or deleted from the app
- Logged events: sign-ins, failed sign-in attempts (recorded against the targeted account), sign-outs, password changes and admin password resets, account detail and profile photo changes, notification preference changes, clinic form completion/updates with Data Privacy Act consent (with which sections changed), guardian ID uploads, clinic/consent PDF downloads, student account creation/updates/deletion, announcements, check-ins and check-outs, medicine inventory changes, nurse changes, and chat resolve/reopen
- Admins see all activity and can switch the "Show" selector to "My account only" or a specific student's log (deleted students with history stay selectable); the student record page links to that student's log
- Students see only activity on their own account — the account always comes from the session, so URL parameters can't show another student's log. Actions by clinic staff appear as "FCAT HealthWard staff" without the admin's name or IP address, and failed sign-ins on the account are highlighted
- Admins can download a student's Clinic Form (01) and Consent Form (02) as PDFs from the student record page

## Account Settings

- Admins and students can upload, replace, or remove their own profile photo from Settings
- Profile photos support JPG, PNG, and WEBP files up to 3MB
- Uploaded profile photos appear in the dashboard sidebar and fall back to account initials when absent
- Profile photo files are stored under `public/uploads/profiles/` and ignored by Git
- Admin and student dashboards provide a moon/sun light-dark theme toggle beside notifications; the selected preference is remembered in browser storage across pages and sessions
- Student Settings provides separate Profile, Account, and Notification sections
- Profile displays student information as read-only while retaining avatar upload/removal
- Account allows students to update their `@fcat.com.ph` email, mobile number, address, and password
- Disabling student notifications suppresses future notification records and removes bell counts/lists until re-enabled

## Check In and Clinic Occupancy

- Admin and student dashboards show nurses currently on duty and people currently in the clinic
- `app/admin/check-in.php` searches existing students and staff, records their visit, and lists all active clinic check-ins
- Checking out closes an open visit and immediately removes that person from the active clinic count
- Check-in and check-out are timestamped automatically; Time in / Time out appear in the student's visit history, the admin Check In visit records, the student record page, and Reports (open visits show "Still in clinic")
- PHP and MySQL both use `APP_TIMEZONE` from `.env` (default `Asia/Manila`), so timestamps stay correct on servers running in UTC
- `app/admin/nurses.php` provides nurse add, edit, delete, and animated on-duty/off-duty controls
- The nurses-on-duty dashboard count is derived from active nurse toggles and cannot be edited manually
- Turning a nurse on duty opens a popup to choose the campus (FCAT - Main, FCAT - Re Chico, FCAT - Pinagbarilan, FCAT - La Rosa); turning them off clears it. Adding a nurse as on duty also requires a campus. The campus is stored in `Nurses.DutyCampus` (added automatically if `add_nurse_duty_campus.sql` hasn't been run) and recorded in the audit log
- The student dashboard has a "Nurses on duty by campus" panel showing, for every campus, whether a nurse is available and who is on duty there (admins manage this from the Nurses page)
- Clinic occupancy is derived from active check-ins and cannot be edited manually
- Admin and student occupancy cards are read-only and reflect the same live value

## Reports

- `app/admin/reports.php` combines medicine inventory and clinic visit reports
- Administrators can add medicines or stock in focused pop-up dialogs, search inventory, and filter low-stock items
- **Visit reports** list every clinic visit, newest first, grouped under date headings, 50 per page. Choosing a date shows only that day's visits (with Previous day / Next day buttons); without a date every visit is shown. Search and status filters combine with the date, and "Today" / "Show all" shortcuts sit next to the filters
- Filters are live (no Filter button): typing in a search box, choosing an option, or picking a date refreshes the results in place. The same live filtering is used by the medicine inventory search and the Audit Log, and links such as pagination and Previous/Next day update without a full page reload
- Each visit shows the patient (students link to their record), the complaint with vitals/assessment/treatment/disposition/follow-up date, medicine given, time in/out, who consulted, and the status; totals per status are shown for the current view
- Open and follow-up visits have an **Edit** button next to their status (Completed, Follow-up with a date from today to 30 days ahead, or Open; a follow-up can also be rescheduled). Completed visits are final and can't be changed, and a visit can't be edited while the patient is still checked in (no Edit button is shown until they are checked out); the server enforces both. Changes are recorded in the audit log and the student is notified
- Medicines are archived (with confirmation) instead of deleted; archived items are hidden from the inventory, stock dialog, and Check In, and can be restored from the Archived medicines section with one click (then use Add stock to record new quantities)
- Duplicate medicine names are blocked (ignoring letter case and spacing), both live in the Add medicine dialog and on the server; the message points to Add stock or Restore as appropriate
- Medicine summaries show total active medicine types and items at or below their configured low-stock threshold
- The medicine stock overview uses a responsive GSAP carousel with next/previous controls that advances one inventory item at a time

## Project Structure

```
├── app/
│   ├── admin/              # Admin pages (full CRUD, auth-guarded)
│   │   ├── students.php
│   │   ├── add-student.php
│   │   ├── view-student.php
│   │   ├── edit-student.php
│   │   ├── delete-student.php
│   │   ├── view-consent-image.php
│   │   ├── reports.php
│   │   ├── audit-log.php        # Admin audit log (all activity)
│   │   ├── download-student-form.php # Admin PDF download of a student's forms
│   │   ├── nurses.php
│   │   ├── check-in.php
│   │   ├── change-password.php
│   │   ├── announcements.php
│   │   ├── add-announcement.php
│   │   ├── delete-announcement.php
│   │   └── messages.php        # Admin chat inbox
│   ├── api/
│   │   ├── chat.php            # Chat REST API (JSON)
│   │   ├── chat-presence.php   # Active chat limit and queue API
│   │   └── pusher-auth.php     # Pusher private channel auth
│   ├── config/
│   │   ├── database.php        # PDO connection (reads .env)
│   │   ├── auth.php            # Session & role helpers
│   │   └── pusher.php          # Pusher client & broadcast helpers
│   └── includes/
│       ├── admin-dashboard-start.php # Admin dashboard shell
│       ├── admin-dashboard-end.php   # Admin dashboard shell footer
│       ├── student-dashboard-start.php # Student dashboard shell
│       ├── student-dashboard-end.php   # Student dashboard shell footer
│       ├── profile-photo-settings.php  # Shared profile photo Settings UI
│       ├── helpers.php         # Shared query/validation helpers
│       ├── student-form-pdf.php # Clinic/consent PDF rendering helpers
│       ├── chat-messages.php   # Chat bubble partial
│       └── ...
├── students/               # Student portal (auth-guarded)
│   ├── index.php           # Announcements dashboard
│   ├── messages.php        # Chat with admin
│   ├── audit-log.php       # Student audit log (own account)
│   ├── profile.php         # Patient records + clinic/consent forms
│   ├── edit-profile.php    # Edit clinic form
│   ├── download-clinic-form.php
│   ├── download-consent-form.php
│   ├── view-consent-image.php
│   └── change-password.php # Change own password
├── database/
│   ├── Clinic.sql          # Database schema + seed data
│   └── migrations/
│       ├── add_messages.sql
│       ├── add_must_change_password.sql
│       ├── add_chat_queue.sql
│       ├── add_consent_image_path.sql
│       ├── add_clinic_workflow.sql
│       ├── add_clinic_consent_choices.sql
│       ├── add_clinic_occupancy.sql   # obsolete (counts are live)
│       ├── add_admin_notification_reads.sql
│       ├── add_clinic_checkins.sql
│       ├── add_visit_medicine_quantity.sql
│       ├── add_nurses.sql
│       ├── add_student_notification_preferences.sql
│       ├── add_medicine_inventory.sql
│       ├── add_medicine_archive.sql
│       ├── add_privacy_consent.sql
│       ├── add_audit_logs.sql
│       ├── add_visit_medicine_given.sql
│       └── add_profile_images.sql
├── public/                 # Web entry point
│   ├── login.php           # Login page
│   ├── logout.php          # Logout handler
│   ├── chat-queue.php      # Wait page when active chat slots are full
│   ├── index.php           # Admin dashboard (student list)
│   └── assets/
│       ├── css/style.css     # Editable app CSS source
│       ├── css/tailwind.css # Browser-ready utility styles
│       ├── js/main.js
│       ├── js/chat.js      # Pusher chat client
│       ├── js/chat-queue.js # Queue polling client
│       └── images/
├── vendor/                 # Composer packages (gitignored)
├── .htaccess               # Blocks web access to private files and folders
├── .env                    # Environment config (gitignored)
├── .env.example            # Template for .env
├── composer.json
└── README.MD
```