# PlanHive

> Your projects. Your people. Your plan. All in one hive.

PlanHive is a **multi-project management SaaS** built with **Laravel 11 + Vue 3 + Tailwind CSS**. It provides a unified calendar-first dashboard for managing tasks, goals, notes, documents, and contacts across multiple projects — with bilingual support (English / Deutsch).

## Features

- **Calendar Homepage** — Color-coded calendar with month/week/day views, drag & drop
- **Multi-Project Management** — Projects with roles (Owner/Boss/Manager/Member/Viewer)
- **Task Management** — Kanban board, priorities, subtasks, assignments, due dates
- **Goal Tracking** — Visual progress bars, target dates, status tracking
- **Smart Reminders** — In-app, email, push, and MS Teams notifications
- **Rich Notes** — Tiptap-powered rich text editor, per-project or personal
- **Document Management** — Upload, preview, download (S3-compatible storage)
- **Contact Management** — Manual entry + business card OCR scanning
- **Bilingual UI** — Full English & German (Deutsch) support
- **MS Outlook & Teams** — Calendar sync, contact sync, notifications (Phase 2)
- **SaaS Ready** — Subscription plans via Stripe/Paddle (Phase 3)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11, PHP 8.2+, Sanctum |
| Frontend | Vue 3, Inertia.js, Tailwind CSS |
| Calendar | FullCalendar.js |
| Rich Text | Tiptap Editor |
| Database | SQLite (dev) / PostgreSQL (prod) |
| i18n | vue-i18n + Laravel localization |

## Getting Started

```bash
# Clone
git clone https://github.com/mrshahbazdev/PlanHive.git
cd PlanHive

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate
touch database/database.sqlite

# Database
php artisan migrate

# Run
php artisan serve &
npm run dev
```

Visit `http://localhost:8000` to see the app.

## Project Structure

```
app/
├── Http/Controllers/    # Auth, Dashboard, Project, Task, Note, Contact, etc.
├── Models/              # User, Project, Task, Goal, CalendarEvent, Note, etc.
├── Http/Middleware/      # HandleInertiaRequests, SetLocale
resources/
├── js/
│   ├── pages/           # Vue 3 pages (Dashboard, Projects, Tasks, Notes, etc.)
│   ├── layouts/         # AppLayout with sidebar navigation
│   ├── locales/         # en.json, de.json translation files
│   └── app.js           # Vue + Inertia + i18n entry point
├── css/app.css          # Tailwind CSS with custom components
database/
└── migrations/          # All 11 migration files
```

## License

MIT
