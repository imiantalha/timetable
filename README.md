# Smart Timetable

Smart Timetable is a university scheduling application with a **Laravel 13 REST API** and a **Next.js 16 admin dashboard**. It manages academic resources, builds timetable drafts, checks scheduling conflicts, and supports manual timetable adjustments and publishing.

The repository is split into independent backend and frontend applications.

## Features

- **Academic resource management:** API resources for departments, courses, teachers, rooms, sections, and time slots.
- **Timetable management:** create, list, and view timetable drafts, then publish a timetable.
- **Conflict detection:** identify teacher, section, and room overlaps, as well as room-capacity violations.
- **Schedule generation:** deterministic first-fit generation places assignments into available slots and reports assignments that could not be placed.
- **Availability rules:** scheduling services can check teacher and room availability windows.
- **Manual editing:** move timetable entries through a dedicated API endpoint; the dashboard provides a drag-and-drop grid.
- **Typed frontend API layer:** TypeScript types and API helpers keep dashboard requests organized.
- **Token-based API protection:** resource routes are protected by Laravel Sanctum; the health endpoint is public.

## Technology

| Area | Stack |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Authentication | Laravel Sanctum |
| Backend quality tools | PHPUnit 12, Laravel Pint |
| Frontend | Next.js 16, React 19, TypeScript 5 |
| API client | Axios |
| API style | Versioned REST, JSON |

## Architecture

```text
┌─────────────────────────────────────┐
│ Next.js 16 Admin Dashboard          │
│ Timetable grid · Generation panel   │
│ Drag & drop · TypeScript API client │
└──────────────────┬──────────────────┘
                   │ HTTP / JSON
                   ▼
┌─────────────────────────────────────┐
│ Laravel 13 REST API                 │
│ Sanctum middleware · Validation     │
│ Resource and timetable controllers │
└──────────────────┬──────────────────┘
                   │
                   ▼
┌─────────────────────────────────────┐
│ Scheduling Domain                   │
│ TimetableGenerator                  │
│ ConflictDetector                    │
│ AvailabilityConstraint              │
│ OptimizedTimetableGenerator         │
│ ScheduleScorer                      │
└──────────────────┬──────────────────┘
                   │ Eloquent
                   ▼
┌─────────────────────────────────────┐
│ Relational Database                 │
│ Academic resources · Timetables     │
│ Entries · Availability windows      │
└─────────────────────────────────────┘
```

Scheduling rules are kept in `backend/app/Domain/Scheduling`, separate from HTTP controllers. See [the architecture guide](docs/architecture.md).

> **Implementation note:** the current generation endpoint invokes the deterministic `TimetableGenerator` directly and returns its result in the API response. An optimized generator and a queued job are also present in the codebase, but the current route does not dispatch that job. The frontend generation panel is an integration scaffold and currently submits an empty assignment list.

## Repository layout

```text
.
├── backend/
│   ├── app/
│   │   ├── Domain/Scheduling/
│   │   ├── Http/Controllers/Api/V1/
│   │   ├── Http/Requests/
│   │   ├── Jobs/
│   │   └── Models/
│   ├── database/migrations/
│   ├── routes/api.php
│   └── composer.json
├── frontend/
│   ├── app/dashboard/timetables/
│   ├── lib/
│   ├── .env.example
│   └── package.json
└── docs/
    ├── architecture.md
    └── database.md
```

## API overview

Base path: `/api/v1`

All routes except `GET /health` require Sanctum authentication.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/health` | Public API health check |
| API resource | `/courses` | Course CRUD |
| API resource | `/departments` | Department CRUD |
| API resource | `/rooms` | Room CRUD |
| API resource | `/teachers` | Teacher CRUD |
| API resource | `/sections` | Section CRUD |
| API resource | `/time-slots` | Time-slot CRUD |
| GET, POST | `/timetables` | List and create timetables |
| GET | `/timetables/{id}` | View a timetable |
| POST | `/timetables/{id}/generate` | Generate timetable entries |
| POST | `/timetables/{id}/publish` | Publish a timetable |
| GET, POST | `/timetable-entries` | List and create entries |
| GET, DELETE | `/timetable-entries/{id}` | View and delete an entry |
| PATCH | `/timetable-entries/{id}/move` | Move an entry to another time slot |
| GET | `/timetable-entries/{id}/conflicts` | Validate an entry for conflicts |

The route definitions are maintained in [backend/routes/api.php](backend/routes/api.php).

## Getting started

### Prerequisites

- PHP 8.3 or newer and Composer
- A database supported by your Laravel configuration
- Node.js compatible with Next.js 16 and npm

### Repository setup status

The repository currently contains the Laravel domain, API source, migrations, and Composer manifest, but it does not yet include the standard Laravel bootstrap files (such as `artisan`, `bootstrap/app.php`, and configuration files) or a tracked backend `.env.example`. The backend therefore cannot be started from a fresh clone yet; complete the Laravel application scaffold and environment configuration before running migrations or serving the API.

The frontend has a Next.js application and an `.env.example`. Its API client imports Axios, but Axios is not currently listed in `frontend/package.json`; declare that dependency before installing and building the frontend.

### Frontend environment

Once the missing frontend dependency is declared:

```bash
cd frontend
cp .env.example .env.local
npm install
npm run dev
```

Set `NEXT_PUBLIC_API_URL` in `frontend/.env.local`:

```dotenv
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

The dashboard is served at [http://localhost:3000](http://localhost:3000). It requires a running backend and a valid Sanctum bearer token to load protected resources.

### Useful commands

**Backend** (after the Laravel application scaffold is complete):

```bash
php artisan migrate
php artisan serve
php artisan test
composer run lint
```

**Frontend** (after declaring Axios):

```bash
npm run lint
npm run build
```

## Documentation

- [Architecture](docs/architecture.md) — application boundaries and scheduling flow.
- [Database design](docs/database.md) — implemented tables and relationships.
- [Scheduling domain](backend/app/Domain/Scheduling/README.md) — responsibilities of the scheduling layer.

## Current scope and next steps

The repository contains the core resource APIs, timetable entry workflows, conflict checks, a first-fit generator, an optimized generator implementation, a queue job, and a dashboard scaffold. Areas to complete include wiring the queued generation workflow, connecting the optimizer to the API, supplying real assignment data from the UI, expanding automated coverage, and improving generation progress/results UX.

## Author

**Muhammad Talha** · Backend-focused Software Engineer

[Portfolio](https://imiantalha.vercel.app) · [LinkedIn](https://www.linkedin.com/in/imiantalha) · [GitHub](https://github.com/imiantalha)
