# Smart Timetable

Smart Timetable is an academic scheduling platform in development. It is being built as a Laravel REST API with a separate Next.js frontend. The backend currently provides academic-resource management, timetable entry operations, conflict checks, and an initial timetable-generation workflow.

> **Status:** Backend foundations and API workflows are under active development. The frontend is currently a starter dashboard and is not yet a complete, API-connected scheduling interface.

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| API authentication | Laravel Sanctum |
| Frontend | Next.js 16, React 19, TypeScript 5 |
| API | Versioned REST endpoints (`/api/v1`) |
| Backend testing | PHPUnit 12 |
| Frontend tooling | ESLint 9 |

The database, cache, queue, and hosting providers are deployment decisions; consult the application configuration and deployment setup before assuming a specific provider.

## Repository layout

```text
.
├── backend/       # Laravel API, academic models, and scheduling domain
├── frontend/      # Next.js App Router application
└── docs/          # Architecture and engineering notes
```

## Current implementation

### Backend

The API includes protected resource routes for:

- Departments
- Courses
- Teachers
- Rooms
- Sections
- Time slots
- Timetables
- Timetable entries

Scheduling-related endpoints currently include:

- **Generate timetable:** accepts assignment data and selected time-slot IDs, runs the scheduling generator, and reports placed and unplaced assignments.
- **Check conflicts:** evaluates a timetable entry and returns whether it is valid along with detected conflicts.
- **Publish timetable:** exposes a publish action for a timetable.
- **Health check:** `GET /api/v1/health`.

Resource-management and timetable routes are grouped behind Laravel Sanctum authentication middleware. Configure a valid authenticated API flow before calling those protected endpoints.

### Frontend

The Next.js App Router application currently provides an initial Smart Timetable landing/dashboard screen with placeholder summary counts. It is a foundation for the future scheduling interface; the full resource-management UI and API integration are still to be implemented.

## Architecture

The frontend and backend are separate applications communicating over HTTP/JSON:

```text
┌──────────────────────────────┐
│ Next.js 16 / React 19        │
│ Frontend application         │
└──────────────┬───────────────┘
               │ HTTP / JSON
               ▼
┌──────────────────────────────┐
│ Laravel 13 REST API          │
│ Sanctum • Validation         │
│ Academic resources           │
│ Scheduling domain            │
└──────────────────────────────┘
```

Scheduling rules belong in the domain layer rather than being embedded in HTTP controllers. The design distinguishes:

- **Hard constraints:** conditions that must not be violated, such as teacher, room, or section overlaps and availability restrictions.
- **Soft constraints:** preferences that can be optimized, such as gaps between classes and room utilization.

See [docs/architecture.md](docs/architecture.md) for the architecture direction and domain boundaries.

## Getting started

### Prerequisites

- PHP 8.3 or later
- Composer
- Node.js and npm
- A database supported by the Laravel application configuration

### 1. Start the backend

```bash
cd backend
composer install
```

Create and configure the backend `.env` file for your local environment. Set the application key and database connection before running migrations.

```bash
php artisan key:generate
php artisan migrate
php artisan serve
```

The Laravel development server normally starts at [http://127.0.0.1:8000](http://127.0.0.1:8000).

Check the API health endpoint:

```http
GET http://127.0.0.1:8000/api/v1/health
```

### 2. Start the frontend

Open a second terminal:

```bash
cd frontend
npm install
npm run dev
```

Open the local URL printed by Next.js (usually [http://localhost:3000](http://localhost:3000)).

## API overview

All API routes are versioned under `/api/v1`.

| Endpoint | Purpose | Access |
| --- | --- | --- |
| `GET /api/v1/health` | API health check | Public |
| `/api/v1/departments` | Department resources | Sanctum |
| `/api/v1/courses` | Course resources | Sanctum |
| `/api/v1/teachers` | Teacher resources | Sanctum |
| `/api/v1/rooms` | Room resources | Sanctum |
| `/api/v1/sections` | Section resources | Sanctum |
| `/api/v1/time-slots` | Time-slot resources | Sanctum |
| `/api/v1/timetables` | List, create, show, publish, and generate timetables | Sanctum |
| `/api/v1/timetable-entries` | List, create, show, and delete entries | Sanctum |
| `GET /api/v1/timetable-entries/{id}/conflicts` | Check an entry for conflicts | Sanctum |

For exact route definitions and controller behavior, see [backend/routes/api.php](backend/routes/api.php).

## Development commands

### Backend

Run the test suite:

```bash
php artisan test
```

Check PHP formatting:

```bash
vendor/bin/pint --test
```

Composer scripts are also available as `composer test` and `composer lint`.

### Frontend

Run the linter:

```bash
npm run lint
```

Create a production build:

```bash
npm run build
```

Run commands from the relevant `backend/` or `frontend/` directory.

## Roadmap

The following are planned areas of work, not claims that the complete product is already available:

- Complete authentication and account-management experience
- API-connected dashboards and academic-resource management screens
- Interactive timetable editor with validation feedback
- More complete scheduling constraints and optimization strategies
- Conflict explanations and resolution workflows
- Timetable publishing lifecycle, versioning, and audit history
- Notifications, calendar export, and analytics
- Automated tests, CI/CD, and production deployment

## Contributing

1. Create a focused feature or fix branch.
2. Keep changes scoped to the relevant application.
3. Run the relevant backend or frontend checks.
4. Document behavior, setup requirements, and known limitations in your pull request.

## Author

**Muhammad Talha** — Software Engineer focused on backend development with PHP and Laravel.

- GitHub: [@imiantalha](https://github.com/imiantalha)
- Portfolio: [imiantalha.vercel.app](https://imiantalha.vercel.app/)
