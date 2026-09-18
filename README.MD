# Student Management System

A PHP CRUD application with role-based authentication. Admins manage students and post announcements. Students view announcements, fill out clinic forms, manage their own profile, and chat with admins in real time via Pusher.

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

If you already have the database and need live clinic occupancy counts, run:

```bash
mysql -u root -p < database/migrations/add_clinic_occupancy.sql
```

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
- Student clinic details are edited through a responsive four-step form covering personal information, guardians, medical history/allergies, and a read-only consent preview
- Wizard steps are sequential and cannot be opened from the progress indicators; the final step provides a downloadable consent PDF
- Date of Birth cannot be later than the current Manila date
- Saved guardian, emergency, medical, allergy, and stored consent details appear in student/admin record views and consent downloads
- Students can download the clinic form and consent form as PDFs
- Students can upload a parent/guardian ID image below the consent form
- The ID image View button appears only after an upload exists
- Admins can view uploaded parent/guardian ID images from the student record page
- Admins record clinic visits from the centralized `app/admin/check-in.php` workflow after searching for an existing student or staff account
- Staff check-in currently uses existing administrator accounts as staff records
- Existing medicines are selected from inventory; dosage and quantity are recorded separately, quantity appears in visit history and Reports, and available stock is deducted atomically
- Students can view clinic visit history in their patient records
- Patient and consent records stack into readable mobile layouts without horizontal table scrolling
- Clinic follow-up dates are limited to today through 30 days ahead

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
- `app/admin/nurses.php` provides nurse add, edit, delete, and animated on-duty/off-duty controls
- The nurses-on-duty dashboard count is derived from active nurse toggles and cannot be edited manually
- Clinic occupancy is derived from active check-ins and cannot be edited manually
- Admin and student occupancy cards are read-only and reflect the same live value

## Reports

- `app/admin/reports.php` combines medicine inventory and clinic visit reports
- Administrators can add medicines or stock in focused pop-up dialogs, delete medicines with confirmation, search inventory, filter low-stock items, filter visit records, and print reports
- Medicine summaries show total medicine types and items at or below their configured low-stock threshold
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
│   │   ├── nurses.php
│   │   ├── check-in.php
│   │   ├── change-password.php
│   │   ├── announcements.php
│   │   ├── add-announcement.php
│   │   ├── edit-announcement.php
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
│       ├── add_clinic_occupancy.sql
│       ├── add_clinic_checkins.sql
│       ├── add_visit_medicine_quantity.sql
│       ├── add_nurses.sql
│       ├── add_student_notification_preferences.sql
│       ├── add_medicine_inventory.sql
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
├── .env                    # Environment config (gitignored)
├── .env.example            # Template for .env
├── composer.json
└── README.MD
```