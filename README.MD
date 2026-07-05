# Student Management System

A PHP CRUD application with role-based authentication. Admins manage students and post announcements. Students view announcements, fill out clinic forms, manage their own profile, and chat with admins in real time via Pusher.

## Requirements

- PHP 8.0+
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
http://localhost/Software%20Engineering%20101/public/login.php
```

## Default Accounts

| Role    | Username / Student ID | Password     |
|---------|----------------------|--------------|
| Admin   | admin                | admin123     |
| Student | 10001                | student123   |

New student accounts are created by admins only. Admin-created students use the default password `Password@123!` and are forced to change it on first login.

## Real-time Chat

- **Students:** `students/messages.php` — one thread per student with the admin office
- **Admins:** `app/admin/messages.php` — inbox of all student conversations with unread badges
- Messages are stored in the `Messages` table and pushed instantly via Pusher private channels
- Active chat pages are limited to 5 users for testing; extra users wait on `public/chat-queue.php`
- API endpoints: `app/api/chat.php` (send/fetch), `app/api/pusher-auth.php` (channel authorization), and `app/api/chat-presence.php` (queue/heartbeat)

## Patient Records

- `students/profile.php` merges patient records and the clinic form preview
- Students can download the clinic form and consent form as PDFs
- Students can upload a parent/guardian ID image below the consent form
- The ID image View button appears only after an upload exists

## Project Structure

```
├── app/
│   ├── admin/              # Admin pages (full CRUD, auth-guarded)
│   │   ├── add-student.php
│   │   ├── view-student.php
│   │   ├── edit-student.php
│   │   ├── delete-student.php
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
│       ├── admin-nav.php       # Admin navigation bar
│       ├── student-nav.php     # Student navigation bar
│       ├── helpers.php         # Shared query/validation helpers
│       ├── profile-display.php # Shared read-only profile view
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
│       └── add_consent_image_path.sql
├── public/                 # Web entry point
│   ├── login.php           # Login page
│   ├── logout.php          # Logout handler
│   ├── chat-queue.php      # Wait page when active chat slots are full
│   ├── register.php        # Admin-only registration notice
│   ├── index.php           # Admin dashboard (student list)
│   └── assets/
│       ├── css/style.css
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
