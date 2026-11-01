# Smart Timetable

A university timetable management project with a Laravel REST API and a Next.js frontend foundation. The backend models academic resources and provides API endpoints for managing the data used in timetable planning.

> **Project status:** Active development. The backend domain and API are being built incrementally; the frontend is currently an early application foundation. This README distinguishes implemented foundations from planned product features.

## Tech stack

| Area | Technology |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| API authentication | Laravel Sanctum |
| Frontend | Next.js 16, React 19, TypeScript 5 |
| API style | Versioned REST API |

Database, queue, cache, container, and deployment choices should be treated as environment- or roadmap-dependent unless configured in the relevant application files.

## Repository structure

```text
.
├── backend/       # Laravel API and scheduling domain
├── frontend/      # Next.js App Router application
└── docs/          # Architecture and project documentation
```

## Backend domains

The API is organized around the resources required to describe an academic timetable:

- Departments
- Courses
- Teachers
- Rooms
- Sections
- Time slots
- Timetables

The backend includes versioned API routes, request validation, Eloquent models, authentication middleware, and resource-management endpoints. Scheduling and generation-related code is under active development; the presence of a route, job, or domain class does not necessarily mean the complete end-to-end workflow is production-ready.

## Architecture

The project separates the API and web application:

```text
┌──────────────────────────┐
│ Next.js / React frontend │
│ Web application          │
└────────────┬─────────────┘
             │ HTTP / JSON
             ▼
┌──────────────────────────┐
│ Laravel 13 API           │
│ Auth • Validation        │
│ Academic resources       │
│ Timetable domain         │
└──────────────────────────┘
```

See [docs/architecture.md](docs/architecture.md) for the architecture notes.

## Getting started

### Prerequisites

- PHP 8.3 or later
- Composer
- Node.js and npm
- A database supported by the backend configuration

### Backend

```bash
cd backend
composer install
```

Configure the backend environment using the Laravel environment setup appropriate to your local machine, then configure the database connection before running migrations.

```bash
php artisan key:generate
php artisan migrate
php artisan serve
```

If the repository does not contain a ready-to-copy `.env.example`, create the local `.env` from the configuration required by the application rather than assuming that file is available.

### Frontend

```bash
cd frontend
npm install
npm run dev
```

The Next.js development server prints the local URL when it starts. Configure any API base URL expected by the frontend through its environment configuration.

## API

The backend exposes a versioned API under the `/api/v1` route prefix. Routes cover authentication and academic resource management, with protected endpoints using Sanctum authentication middleware where configured.

For the authoritative endpoint list and middleware configuration, see [backend/routes/api.php](backend/routes/api.php).

## Development status

The repository is a work in progress. Current foundations include:

- Laravel API application and versioned routes
- Authentication middleware and protected resource routes
- Models and request validation for core academic resources
- Timetable and generation-related backend foundations
- Next.js App Router frontend scaffold

The following are product capabilities to build or complete, not claims of finished functionality:

- End-to-end automatic timetable optimization
- Complete conflict-resolution and explanation workflows
- A fully interactive timetable editor
- Publishing, version history, and audit trails
- Notifications, calendar export, and analytics
- Production deployment and CI/CD automation

## Engineering direction

The project is intended to explore maintainable API design, validation at system boundaries, explicit scheduling constraints, background processing for computational work, and a clear separation between frontend and backend responsibilities.

## Contributing

1. Create a focused feature or fix branch.
2. Keep changes scoped to the relevant application.
3. Run the checks available for the area you changed.
4. Describe implementation status and any known limitations in your pull request.

## Author

**Muhammad Talha** — Software Engineer focused on backend development with PHP and Laravel.

- GitHub: [@imiantalha](https://github.com/imiantalha)
- Portfolio: [imiantalha.vercel.app](https://imiantalha.vercel.app/)
